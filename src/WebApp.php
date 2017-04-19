<?php
namespace My\Web;

use Aura\Di\Container;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\HttpFactoryInjectionTrait;
use My\Web\Lib\Router\Router;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Server;
use Zend\Stratigility\NoopFinalHandler;

class WebApp extends App implements HttpFactoryAwareInterface
{
    use HttpFactoryInjectionTrait;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var callable
     */
    protected $middlewarePipe;

    /**
     * WebApp constructor.
     * @param Container $container
     * @param callable $middlewarePipe
     * @param Router $router
     * @param array $params
     */
    public function __construct(
        Container $container,
        callable $middlewarePipe,
        Router $router,
        array $params
    )
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
        $request = $this->httpFactory->createRequestFromGlobals();

        $this->getLogger()->debug("Request handling started");
        $startedAt = microtime(true);

        $server = Server::createServerFromRequest($this->middlewarePipe, $request);
        $server->setEmitter(new SapiEmitter());
        $server->listen(new NoopFinalHandler());

        $elapsed = microtime(true) - $startedAt;
        $this->getLogger()->debug(sprintf("Request handling finished in %0.3fms", $elapsed * 1000));
    }
}
