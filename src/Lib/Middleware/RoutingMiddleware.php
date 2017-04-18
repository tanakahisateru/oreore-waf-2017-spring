<?php
namespace My\Web\Lib\Middleware;


use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Router\RoutingException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class RoutingMiddleware implements MiddlewareInterface
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

        ob_start();
        try {
            $response = $this->router->dispatch($request, $responsePrototype);
        } catch (RoutingException $e) {
            ob_clean();
            if ($e->getStatus() == 404) {
                return $delegate->process($request);
            } else {
                return (new Response())->withStatus($e->getStatus()); // TODO generate page
            }
        }
        $echo = ob_get_clean();

        if (empty($response)) {
            $response = clone $responsePrototype;
            $response->getBody()->write($echo);
        } elseif (is_scalar($response)) {
            $value = $response;
            $response = clone $responsePrototype;
            $response->getBody()->write($echo);
            $response->getBody()->write($value);
        } elseif (is_array($response)) {
            $value = $response;
            $response = $responsePrototype->withHeader('Content-Type', 'application/json');
            $response->getBody()->write($echo);
            $response->getBody()->write(json_encode($value));
        } elseif ($response instanceof ResponseInterface) {
            if (!empty($echo)) {
                $stream = $response->getBody();
                $stream->rewind();
                $buffered = $stream->getContents();
                $stream->rewind();
                $stream->write($echo . $buffered);
            }
        } else {
            throw new \LogicException('Unsupported response returned from: ' . $request->getUri());
        }

        return $response;
    }
}
