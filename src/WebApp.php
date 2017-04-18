<?php
namespace My\Web;

use Aura\Di\Container;
use My\Web\Lib\Router\Router;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\NoopFinalHandler;

class WebApp extends App
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var MiddlewarePipe
     */
    protected $middlewarePipe;

    /**
     * WebApp constructor.
     * @param Container $container
     * @param Router $router
     * @param MiddlewarePipe $middlewarePipe
     * @param array $params
     */
    public function __construct(Container $container, Router $router, MiddlewarePipe $middlewarePipe, array $params)
    {
        parent::__construct($container, $params);
        $this->middlewarePipe = $middlewarePipe;
        $this->router = $router;
    }

    /**
     *
     */
    public function run()
    {
        $request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        $this->getLogger()->debug("Request handling started");
        $startedAt = microtime(true);

        $server = Server::createServerFromRequest($this->middlewarePipe, $request);
        $server->setEmitter(new SapiEmitter());
        $server->listen(new NoopFinalHandler());

        $elapsed = microtime(true) - $startedAt;
        $this->getLogger()->debug(sprintf("Request handling finished in %0.3fms", $elapsed * 1000));
    }
}
