<?php
namespace Acme\App\Middleware;

use Acme\App\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sumeko\Http\Exception\NotFoundException;

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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $this->router->handle($request, $this->responsePrototype);
        } catch (NotFoundException $e) {
            return $handler->handle($request);
        }
    }
}
