<?php
namespace Acme\App\View;

use Acme\App\Router\Router;
use Acme\App\View\Template\ViewAccessExtension;
use Lapaz\Amechan\AssetCollection;
use Lapaz\Amechan\AssetManager;
use Lapaz\Odango\AdviceComposite;
use League\Plates\Engine;
use Ray\Aop\MethodInvocation;
use Webmozart\PathUtil\Path;
use Zend\EventManager\EventsCapableInterface;

class ViewEngine
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Engine
     */
    protected $templateEngine;

    /**
     * @var AssetManager
     */
    protected $assetManager;

    /**
     * @var callable
     */
    protected $viewFactory;

    /**
     * View constructor.
     * @param Router $router
     * @param Engine $templateEngine
     * @param AssetManager $assetManager
     * @param callable $viewFactory
     */
    public function __construct(
        Router $router,
        Engine $templateEngine,
        AssetManager $assetManager,
        callable $viewFactory
    )
    {
        $this->router = $router;
        $this->templateEngine = $templateEngine;
        $this->assetManager = $assetManager;
        $this->viewFactory = $viewFactory;
    }

    /**
     * @return View
     */
    public function createView()
    {
        return call_user_func($this->viewFactory, $this);
    }

    /**
     * @return AssetCollection
     */
    public function createAssetCollection()
    {
        return $this->assetManager->newCollection();
    }

    /**
     * @param string $name
     * @param array $data
     * @param bool $raw
     * @return bool
     */
    public function routeUrlTo($name, $data=[], $raw = false)
    {
        if ($raw) {
            return $this->router->rawUrlTo($name, $data);
        } else {
            return $this->router->urlTo($name, $data);
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
     * @param View $view
     * @param string $templateName
     * @param array $data
     * @return string
     */
    public function renderIn(View $view, $templateName, array $data = [])
    {
        // Plate engine is stateful
        $engine = clone $this->templateEngine;

        $engine->loadExtension(new ViewAccessExtension($view));

        $rootPath = $engine->getDirectory();
        foreach ($view->getFolderMap() as $folder => $path) {
            if ($engine->getFolders()->exists($folder)) {
                $engine->removeFolder($folder);
            }
            $engine->addFolder($folder, Path::join($rootPath, $path));
        }

        $template = $engine->make($templateName);

        $render = function (array $data) use ($template) {
            return $template->render($data);
        };

        $adviser = $this->eventTriggerAdviser($view, $template, $data);
        $render = $adviser->bind($render);

        return $render($data);
    }

    /**
     * @param View $view
     * @param string $template
     * @param array $data
     * @return AdviceComposite
     */
    protected function eventTriggerAdviser($view, $template, array $data)
    {
        $interceptor = AdviceComposite::of(function (MethodInvocation $invocation) use ($view, $template, $data) {
            if (!$view instanceof EventsCapableInterface) {
                return $invocation->proceed();
            }

            $events = $view->getEventManager();

            $argv = new \ArrayObject([
                'template' => $template,
                'data' => $data,
            ]);
            $result = $events->trigger('beforeRender', $view, $argv);

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
            $result = $events->trigger('afterRender', $view, $argv);

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
