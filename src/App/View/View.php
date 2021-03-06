<?php
namespace Acme\App\View;

use Acme\App\Router\NoSuchRouteException;
use Acme\App\Router\Router;
use Acme\App\View\Template\ViewAccessExtension;
use Lapaz\Amechan\AssetCollection;
use Lapaz\Amechan\AssetManager;
use Lapaz\Odango\AdviceComposite;
use League\Plates\Engine;
use League\Plates\Template\Template;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ray\Aop\MethodInvocation;
use Webmozart\PathUtil\Path;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

class View implements EventManagerAwareInterface, LoggerAwareInterface
{
    const EVENT_TEMPLATE_ENGINE_CREATED = 'templateEngineCreated';
    const EVENT_TEMPLATE_CREATED = 'templateCreated';
    const EVENT_BEFORE_RENDER = 'beforeRender';
    const EVENT_AFTER_RENDER = 'afterRender';

    use EventManagerAwareTrait;
    use LoggerAwareTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['view'];

    /**
     * @var callable
     */
    protected $templateEngineFactory;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var AssetManager
     */
    protected $assetManager;

    /**
     * @var array
     */
    protected $folderMap;

    /**
     * @var array
     */
    protected $attributeCollection;

    /**
     * @var AssetCollection
     */
    protected $requiredAssets;

    /**
     * View constructor.
     *
     * @param callable $templateEngineFactory
     * @param Router $router
     * @param AssetManager $assetManager
     * @internal param Router $router
     */
    public function __construct(callable $templateEngineFactory, Router $router, AssetManager $assetManager)
    {
        $this->templateEngineFactory = $templateEngineFactory;
        $this->router = $router;
        $this->assetManager = $assetManager;

        $this->folderMap = [];
        $this->attributeCollection = [];
        $this->requiredAssets = $assetManager->newCollection();
    }

    /**
     * @return array
     */
    public function getFolderMap(): array
    {
        return $this->folderMap;
    }

    /**
     * @param string $folderName
     * @return bool
     */
    public function hasFolder(string $folderName): bool
    {
        return isset($this->folderMap[$folderName]);
    }

    /**
     * @param string $folderName
     * @return string
     */
    public function getFolder(string $folderName): string
    {
        if ($this->hasFolder($folderName)) {
            return $this->folderMap[$folderName];
        } else {
            return null;
        }
    }

    /**
     * @param string $folderName
     * @param string $subPath
     */
    public function setFolder(string $folderName, string $subPath): void
    {
        $this->folderMap[$folderName] = $subPath;
    }

    /**
     * @return array
     */
    public function getAttributeCollection(): array
    {
        return $this->attributeCollection;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributeCollection[$name]);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributeCollection[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute(string $name, $value)
    {
        $this->attributeCollection[$name] = $value;
    }

    /**
     * @param string $name
     * @param array $data
     * @param bool $raw
     * @return string
     */
    public function routeUrlTo(string $name, array $data = [], $raw = false): string
    {
        try {
            return $this->router->uriTo($name, $data, $raw);
        } catch (NoSuchRouteException $e) {
            $this->logger->warning('Route not found: '. $e->getMessage());
            return '#';
        }
    }

    /**
     * @param string $url
     * @return string
     */
    public function resourceUrlTo(string $url): string
    {
        return $this->assetManager->url($url);
    }

    /**
     * @param string $name
     */
    public function requireAsset(string $name): void
    {
        $this->requiredAssets->add($name);
    }

    /**
     * @param string $section
     * @return array
     */
    public function assetUrls(?string $section = null): array
    {
        return $this->requiredAssets->collectUrls($section);
    }

    /**
     * @param string $templateName
     * @param array $data
     * @return string
     */
    public function render(string $templateName, array $data = []): string
    {
        // Plate engine is stateful
        $engine = call_user_func($this->templateEngineFactory);
        assert($engine instanceof Engine);

        $engine->loadExtension(new ViewAccessExtension($this));

        $rootPath = $engine->getDirectory();
        foreach ($this->getFolderMap() as $folder => $path) {
            $engine->addFolder($folder, Path::join($rootPath, $path));
        }

        $this->getEventManager()->trigger(static::EVENT_TEMPLATE_ENGINE_CREATED, $this, ['engine' => $engine]);

        $template = $engine->make($templateName);

        $this->getEventManager()->trigger(static::EVENT_TEMPLATE_CREATED, $this, ['template' => $template]);

        $render = function (array $data) use ($template) {
            return $template->render($data);
        };

        $adviser = $this->eventTriggerAdviser($template, $data);
        $render = $adviser->bind($render);

        return $render($data);
    }

    private function eventTriggerAdviser(Template $template, array $data): AdviceComposite
    {
        $interceptor = AdviceComposite::of(function (MethodInvocation $invocation) use ($template, $data) {
            $events = $this->getEventManager();

            $argv = new \ArrayObject([
                'template' => $template,
                'data' => $data,
            ]);
            $result = $events->trigger(static::EVENT_BEFORE_RENDER, $this, $argv);

            if ($result->stopped()) {
                if (isset($argv['content'])) {
                    return $argv['content'];
                } elseif ($result->last()) {
                    return $result->last();
                } else {
                    return "";
                }
            }

            // invoke
            $content = $invocation->proceed();

            $argv = new \ArrayObject([
                'content' => $content,
                'data' => $data,
            ]);
            $result = $events->trigger(static::EVENT_AFTER_RENDER, $this, $argv);

            if ($result->stopped()) {
                if (isset($argv['content'])) {
                    return $argv['content'];
                } elseif ($result->last()) {
                    return $result->last();
                } else {
                    return "";
                }
            }

            return $content;
        });

        return $interceptor;
    }
}
