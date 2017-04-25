<?php
namespace My\Web\Lib\App\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use My\Web\Lib\App\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class WebAppBootstrap implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $appName;

    /**
     * WebAppBootstrap constructor.
     * @param ContainerInterface $container
     * @param string $appName
     */
    public function __construct(ContainerInterface $container, $appName)
    {
        $this->container = $container;
        $this->appName = $appName;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $app = $this->container->get($this->appName);
        assert($app instanceof App);

        $app->getLogger()->debug("Request handling started");
        $startedAt = microtime(true);
        $app->getEventManager()->trigger('beforeServe', $this);

        $response = $delegate->process($request);

        $app->getEventManager()->trigger('afterServe', $this);
        $elapsed = microtime(true) - $startedAt;
        $app->getLogger()->debug(sprintf("Request handling finished in %0.3fms", $elapsed * 1000));

        return $response;
    }
}
