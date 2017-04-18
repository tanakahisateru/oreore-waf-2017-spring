<?php
namespace My\Web;

use Aura\Di\Container;
use My\Web\Lib\Http\HttpFactoryInterface;
use My\Web\Lib\Router\Router;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Server;
use Zend\Stratigility\NoopFinalHandler;

class WebApp extends App
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var callable
     */
    protected $middlewarePipe;

    /**
     * @var HttpFactoryInterface
     */
    protected $httpFactory;

    /**
     * WebApp constructor.
     * @param Container $container
     * @param Router $router
     * @param callable $middlewarePipe
     * @param HttpFactoryInterface $httpFactory
     * @param array $params
     */
    public function __construct(
        Container $container,
        Router $router,
        callable $middlewarePipe,
        HttpFactoryInterface $httpFactory,
        array $params
    )
    {
        parent::__construct($container, $params);
        $this->router = $router;
        $this->middlewarePipe = $middlewarePipe;
        $this->httpFactory = $httpFactory;
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
