<?php
namespace Acme\App\View;

use Acme\App\View\Template\ViewAccessExtension;
use Aura\Router\Generator;
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
     * @var Generator
     */
    protected $urlGenerator;

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
     * @param Generator $urlGenerator
     * @param AssetManager $assetManager
     * @internal param Router $router
     */
    public function __construct(callable $templateEngineFactory, Generator $urlGenerator, AssetManager $assetManager)
    {
        $this->templateEngineFactory = $templateEngineFactory;
        $this->urlGenerator = $urlGenerator;
        $this->assetManager = $assetManager;

        $this->folderMap = [];
        $this->attributeCollection = [];
        $this->requiredAssets = $assetManager->newCollection();
    }

    /**
     * @return array
     */
    public function getFolderMap()
    {
        return $this->folderMap;
    }

    /**
     * @param string $folderName
     * @return bool
     */
    public function hasFolder($folderName)
    {
        return isset($this->folderMap[$folderName]);
    }

    /**
     * @param string $folderName
     * @return string
     */
    public function getFolder($folderName)
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
    public function setFolder($folderName, $subPath)
    {
        $this->folderMap[$folderName] = $subPath;
    }

    /**
     * @return array
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollection;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributeCollection[$name]);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributeCollection[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributeCollection[$name] = $value;
    }

    /**
     * @param string $name
     * @param array $data
     * @param bool $raw
     * @return bool
     */
    public function routeUrlTo($name, $data=[], $raw = false)
    {
        try {
            if ($raw) {
                return $this->urlGenerator->generateRaw($name, $data);
            } else {
                return $this->urlGenerator->generate($name, $data);
            }
        } catch (\Exception $e) {
            $this->logger->warning('Route not found: '. $e->getMessage());
            return '#';
        }
    }

    /**
     * @param string $url
     * @return string
     */
    public function resourceUrlTo($url)
    {
        return $this->assetManager->url($url);
    }

    /**
     * @param string $name
     */
    public function requireAsset($name)
    {
        $this->requiredAssets->add($name);
    }

    /**
     * @param string $section
     * @return array
     */
    public function assetUrls($section = null)
    {
        return $this->requiredAssets->collectUrls($section);
    }

    /**
     * @param string $templateName
     * @param array $data
     * @return string
     */
    public function render($templateName, array $data = [])
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

    /**
     * @param Template $template
     * @param array $data
     * @return AdviceComposite
     */
    protected function eventTriggerAdviser(Template $template, array $data)
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
