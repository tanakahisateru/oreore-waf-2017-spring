<?php
namespace Acme\App\Middleware;

use Acme\App\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sumeko\Http\Exception as HttpException;

class RoutingHandler implements MiddlewareInterface
{
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
     * @throws HttpException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->router->handle($request);
    }
}
