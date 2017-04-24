<?php
namespace My\Web\Lib\App;

use Aura\Di\Container;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\HttpFactoryInjectionTrait;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Server;
use Zend\Stratigility\NoopFinalHandler;

class WebApp extends App implements HttpFactoryAwareInterface
{
    use HttpFactoryInjectionTrait;

    /**
     * @var callable
     */
    protected $middlewarePipe;

    /**
     * WebApp constructor.
     * @param Container $container
     * @param callable $middlewarePipe
     */
    public function __construct(Container $container, callable $middlewarePipe)
    {
        parent::__construct($container);
        $this->middlewarePipe = $middlewarePipe;
    }

    /**
     * @param $request
     * @param $response
     * @param callable $finalHandler
     * @return callable
     */
    public function processMiddlewarePipe($request, $response, $finalHandler = null) {
        $this->getLogger()->debug("Request handling started");
        $startedAt = microtime(true);
        $this->getEventManager()->trigger('beforeServe', $this);

        if ($finalHandler === null) {
            $finalHandler = function () {
                return func_get_arg(1); // = response
            };
        }

        $response = call_user_func($this->middlewarePipe, $request, $response, $finalHandler);

        $this->getEventManager()->trigger('afterServe', $this);
        $elapsed = microtime(true) - $startedAt;
        $this->getLogger()->debug(sprintf("Request handling finished in %0.3fms", $elapsed * 1000));

        return $response;
    }

    /**
     *
     */
    public function run()
    {
        $request = $this->httpFactory->createRequestFromGlobals();
        $server = Server::createServerFromRequest([$this, 'processMiddlewarePipe'], $request);
        $server->setEmitter(new SapiEmitter());
        $server->listen(new NoopFinalHandler());
    }
}
