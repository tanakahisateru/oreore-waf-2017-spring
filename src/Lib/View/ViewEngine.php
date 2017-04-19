<?php
namespace My\Web\Lib\View;

use League\Plates\Engine;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\Asset\AssetCollection;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Template\TemplateEngine;
use Webmozart\PathUtil\Path;

class ViewEngine
{
    /**
     * @var callable
     */
    protected $templateEngineFactory;

    /**
     * @var callable
     */
    protected $assetManagerFactory;

    /**
     * @var callable
     */
    protected $routerFactory;

    /**
     * @var TemplateEngine
     */
    protected $templateEngine;

    /**
     * @var AssetManager
     */
    protected $assetManager;

    /**
     * @var Router
     */
    protected $router;


    /**
     * View constructor.
     * @param callable $templateEngineFactory
     * @param callable $assetManagerFactory
     * @param callable $routerFactory
     */
    public function __construct($templateEngineFactory, $assetManagerFactory, $routerFactory)
    {
        $this->templateEngineFactory = $templateEngineFactory;
        $this->assetManagerFactory = $assetManagerFactory;
        $this->routerFactory = $routerFactory;
    }

    /**
     * @return Engine
     */
    protected function getTemplateEngine()
    {
        if (!$this->templateEngine) {
            $this->templateEngine = call_user_func($this->templateEngineFactory);
        }

        return $this->templateEngine;
    }

    /**
     * @return AssetManager
     */
    protected function getAssetManager()
    {
        if (!$this->assetManager) {
            $this->assetManager = call_user_func($this->assetManagerFactory);
        }

        return $this->assetManager;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        if (!$this->router) {
            $this->router = call_user_func($this->routerFactory);
        }

        return $this->router;
    }

    /**
     * @return View
     */
    public function createView()
    {
        return new View($this, new AssetCollection($this->getAssetManager()));
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
            return $this->getRouter()->rawUrlTo($name, $data);
        } else {
            return $this->getRouter()->urlTo($name, $data);
        }
    }

    /**
     * @param View $view
     * @param string $name
     * @param array $data
     * @return string
     */
    public function renderIn(View $view, $name, array $data = [])
    {
        // Plate engine is stateful
        $engine = clone $this->getTemplateEngine();
        $rootPath = $engine->getDirectory();

        $engine->registerFunction('view', function () use ($view) {
            return $view;
        });

        foreach ($view->getFolderMap() as $folder => $path) {
            $engine->addFolder($folder, Path::join($rootPath, $path));
        }

        return $engine->render($name, $data);
    }
}
