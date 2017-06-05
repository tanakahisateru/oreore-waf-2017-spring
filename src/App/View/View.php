<?php
namespace Acme\App\View;

use Acme\App\View\Template\ViewAccessExtension;
use Aura\Router\Exception\RouteNotFound;
use Aura\Router\RouterContainer;
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
    use EventManagerAwareTrait;
    use LoggerAwareTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['view'];

    /**
     * @var Engine
     */
    protected $templateEngine;

    /**
     * @var RouterContainer
     */
    protected $routerContainer;

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
     * @param Engine $templateEngine
     * @param RouterContainer $routerContainer
     * @param AssetManager $assetManager
     * @internal param Router $router
     */
    public function __construct(
        Engine $templateEngine,
        RouterContainer $routerContainer,
        AssetManager $assetManager
    )
    {
        // Plate engine is stateful
        $this->templateEngine = clone $templateEngine;
        $this->routerContainer = $routerContainer;
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
            $generator = $this->routerContainer->getGenerator();

            if ($raw) {
                return $generator->generateRaw($name, $data);
            } else {
                return $generator->generateRaw($name, $data);
            }
        } catch (RouteNotFound $e) {
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
        $engine = clone $this->templateEngine;

        $engine->loadExtension(new ViewAccessExtension($this));

        $rootPath = $engine->getDirectory();
        foreach ($this->getFolderMap() as $folder => $path) {
            if ($engine->getFolders()->exists($folder)) {
                $engine->removeFolder($folder);
            }
            $engine->addFolder($folder, Path::join($rootPath, $path));
        }

        $template = $engine->make($templateName);

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
            $result = $events->trigger('beforeRender', $this, $argv);

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
            $result = $events->trigger('afterRender', $this, $argv);

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
