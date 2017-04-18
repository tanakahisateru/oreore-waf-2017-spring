<?php
namespace My\Web\Lib\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use My\Web\Lib\Http\HttpFactoryInterface;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Router\RoutingException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var HttpFactoryInterface
     */
    protected $httpFactory;

    /**
     * RoutingMiddleware constructor.
     *
     * @param Router $router
     * @param HttpFactoryInterface $httpFactory
     */
    public function __construct(Router $router, HttpFactoryInterface $httpFactory)
    {
        $this->router = $router;
        $this->httpFactory = $httpFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $responsePrototype = $this->httpFactory->createResponse();

        ob_start();
        try {
            $response = $this->router->dispatch($request, $responsePrototype);
        } catch (RoutingException $e) {
            ob_end_clean();
            if ($e->getStatus() == 404) {
                return $delegate->process($request);
            } else {
                return $this->httpFactory->createResponse()->withStatus($e->getStatus());
                // TODO generate page
            }
        }
        $echo = ob_get_clean();

        if (empty($response)) {
            $response = $responsePrototype;
        } elseif (is_scalar($response)) {
            $value = $response;
            $response = $responsePrototype;
            $response->getBody()->write($value);
        } elseif (is_array($response)) {
            $value = $response;
            $response = $responsePrototype->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($value));
        }

        if (!($response instanceof ResponseInterface)) {
            throw new \LogicException('Unsupported response returned from: ' . $request->getUri());
        }

        if (!empty($echo)) {
            $stream = $response->getBody();
            if ($stream->isSeekable()) {
                $stream->rewind();
                $streamedContents = $stream->getContents();
                $stream = $this->httpFactory->createStream('php://temp', 'rw');
                $response = $response->withBody($stream);
                $stream->write($echo);
                $stream->write($streamedContents);
            } else {
                $stream->write($echo);
            }
        }

        return $response;
    }
}
