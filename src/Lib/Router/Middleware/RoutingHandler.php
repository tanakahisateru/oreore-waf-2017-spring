<?php
namespace My\Web\Lib\Router\Middleware;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Router\RoutingException;
use Psr\Http\Message\ServerRequestInterface;

class RoutingHandler implements MiddlewareInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * RoutingMiddleware constructor.
     *
     * @param Router $router
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(Router $router, ResponseFactoryInterface $responseFactory)
    {
        $this->router = $router;
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $responsePrototype = $this->responseFactory->createResponse();

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
