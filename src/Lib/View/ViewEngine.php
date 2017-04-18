<?php
namespace My\Web\Lib\View;

use League\Plates\Engine;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\Asset\AssetInterface;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Template\TemplateEngine;

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
     * @param string $name
     * @return AssetInterface
     */
    public function getAsset($name)
    {
        return $this->getAssetManager()->getAsset($name);
    }

    /**
     * @return View
     */
    public function createView()
    {
        return new View($this);
    }

    /**
     * @param array $assetNames
     * @param string $stage
     * @return array
     */
    public function assetUrlsOf(array $assetNames, $stage = null)
    {
        return $this->assetManager->collectAllUrls($assetNames, $stage);
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
     * @param string $folderName
     * @param string $subPath
     */
    public function setTemplateFolder($folderName, $subPath)
    {
        $engine = $this->getTemplateEngine();
        $pe = [
            rtrim($engine->getDirectory(), '/'),
            trim($subPath, '/'),
        ];

        $engine->addFolder($folderName, implode('/', $pe));
    }

    /**
     * @param View $view
     * @param string $name
     * @param array $data
     * @return string
     */
    public function fetchTemplateIn(View $view, $name, array $data = [])
    {
        $engine = $this->getTemplateEngine();
        $engine->registerFunction('view', function () use ($view) {
            return $view;
        });
        $result = $engine->render($name, $data);
        $engine->dropFunction('view');
        return $result;
    }
}
