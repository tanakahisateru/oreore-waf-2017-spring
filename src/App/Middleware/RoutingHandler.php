<?php
namespace Acme\App\Middleware;

use Acme\App\Router\Router;
use Acme\App\Router\RoutingException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RoutingHandler implements MiddlewareInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var ResponseInterface
     */
    protected $responsePrototype;

    /**
     * RoutingMiddleware constructor.
     *
     * @param Router $router
     * @param ResponseInterface $responsePrototype
     */
    public function __construct(Router $router, ResponseInterface $responsePrototype)
    {
        $this->router = $router;
        $this->responsePrototype = $responsePrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        try {
            $response = $this->router->handle($request, $this->responsePrototype);
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
