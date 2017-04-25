<?php
namespace My\Web\Lib\App;

use Aura\Di\Container;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\HttpFactoryInjectionTrait;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Server;
use Zend\Stratigility\NoopFinalHandler;

class WebApp extends App implements HttpFactoryAwareInterface
{
    use HttpFactoryInjectionTrait;

    /**
     * @var MiddlewareInterface|callable
     */
    protected $middlewarePipe;

    /**
     * WebApp constructor.
     * @param Container $container
     * @param MiddlewareInterface|callable $middlewarePipe
     */
    public function __construct(Container $container, $middlewarePipe)
    {
        parent::__construct($container);
        $this->middlewarePipe = $middlewarePipe;
    }

    /**
     * @return MiddlewareInterface|callable
     */
    public function getMiddlewarePipe()
    {
        return $this->middlewarePipe;
    }

    /**
     *
     */
    public function run()
    {
        $request = $this->httpFactory->createRequestFromGlobals();
        $server = Server::createServerFromRequest($this->getMiddlewarePipe(), $request);
        $server->setEmitter(new SapiEmitter());
        $server->listen(new NoopFinalHandler());
    }
}
