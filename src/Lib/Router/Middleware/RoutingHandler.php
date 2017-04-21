<?php
namespace My\Web\Lib\Router\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\HttpFactoryInjectionTrait;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Router\RoutingException;
use Psr\Http\Message\ServerRequestInterface;

class RoutingHandler implements MiddlewareInterface, HttpFactoryAwareInterface
{
    use HttpFactoryInjectionTrait;

    /**
     * @var Router
     */
    protected $router;

    /**
     * RoutingMiddleware constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $responsePrototype = $this->getHttpFactory()->createResponse();

        try {
            $response = $this->router->handle($request, $responsePrototype);
        } catch (RoutingException $e) {
            if ($e->getStatus() == 404) {
                return $delegate->process($request);
            } else {
                throw $e;
            }
        }

        return $response;
    }
}
