<?php
namespace My\Web\Lib\View;

use League\Plates\Engine;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\Asset\AssetInterface;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Template\TemplateEngine;
use Psr\Http\Message\ResponseInterface;

class View
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
     * @var array
     */
    protected $attributes;

    /**
     * @var AssetInterface[]
     */
    protected $requiredAssets;

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

        $this->attributes = [];
        $this->requiredAssets = [];
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
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function requireAsset($name)
    {
        $asset = $this->getAssetManager()->getAsset($name);
        if (!$asset) {
            throw new \UnexpectedValueException('No such asset: ' . $name);
        }

        $this->requiredAssets[$asset->getName()] = $asset;
    }

    /**
     * @param string $stage
     * @return array
     */
    public function assetUrls($stage = null)
    {
        return $this->assetManager->collectAllUrls($this->requiredAssets, $stage);
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
     * @param ResponseInterface $response
     * @param string $template
     * @param array $data
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, $template, array $data = [])
    {
        $content = $this->fetchTemplate($template, $data);
        $response->getBody()->write($content);
        return $response;
    }

    /**
     * @param string $name
     * @param array $data
     * @return string
     */
    public function fetchTemplate($name, array $data)
    {
        $engine = $this->getTemplateEngine();
        $engine->registerFunction('view', function () {
            return $this;
        });
        return $engine->render($name, $data);
    }
}
