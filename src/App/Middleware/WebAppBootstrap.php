<?php
namespace Acme\App\Middleware;

use Acme\App\App;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Lapaz\Odango\AdviceComposite;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ray\Aop\Invocation;

class WebAppBootstrap implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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

        $process = function ($request) use ($delegate) {
            return $delegate->process($request);
        };

        $adviser = AdviceComposite::of(function (Invocation $invocation) {
            $this->logger->debug("Request handling started");
            $startedAt = microtime(true);
            $response = $invocation->proceed();
            $elapsed = microtime(true) - $startedAt;
            $this->logger->debug(sprintf("Request handling finished in %0.3fms", $elapsed * 1000));
            return $response;
        })->with(function (Invocation $invocation) use ($app) {
            $app->getEventManager()->trigger('beforeServe', $app);
            $response = $invocation->proceed();
            $app->getEventManager()->trigger('afterServe', $app);
            return $response;
        });

        $process = $adviser->bind($process);

        return $process($request);
    }
}
