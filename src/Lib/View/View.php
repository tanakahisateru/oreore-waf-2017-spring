<?php
namespace My\Web\Lib\View;


use League\Plates\Engine;
use My\Web\Lib\Router\Router;
use Psr\Http\Message\ResponseInterface;

class View
{
    /**
     * @var callable
     */
    protected $engineFactory;

    /**
     * @var callable
     */
    protected $assetsFactory;

    /**
     * @var callable
     */
    protected $routerFactory;

    /**
     * @var Engine
     */
    protected $engine;

    // assets (planning Assetic)


    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * View constructor.
     * @param callable $engineFactory
     * @param callable $assetsFactory
     * @param callable $routerFactory
     */
    public function __construct($engineFactory, $assetsFactory, $routerFactory)
    {
        $this->engineFactory = $engineFactory;
        $this->assetsFactory = $assetsFactory;
        $this->routerFactory = $routerFactory;

        $this->attributes = [];
    }

    /**
     * @return Engine
     */
    public function getEngine()
    {
        if (!$this->engine) {
            $factory = $this->engineFactory;
            $this->engine = $factory($this);
        }

        return $this->engine;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        if (!$this->router) {
            $factory = $this->routerFactory;
            $this->router = $factory();
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
     * @param array $value
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param string $folderName
     * @param string $subPath
     */
    public function setTemplateFolder($folderName, $subPath)
    {
        $engine = $this->getEngine();
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
        $template = $this->getEngine()->make($name);
        return $template->render($data);
    }
}
