<?php
namespace My\Web\Lib\View\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\ViewAwareInterface;
use My\Web\Lib\View\ViewInjectionTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundHandler implements MiddlewareInterface, ViewAwareInterface
{
    use ViewInjectionTrait;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var ResponseInterface
     */
    private $responsePrototype;

    /**
     * NotFoundHandler constructor.
     * @param Router $router
     * @param ResponseInterface $responsePrototype
     */
    public function __construct(Router $router, ResponseInterface $responsePrototype)
    {
        $this->router = $router;
        $this->responsePrototype = $responsePrototype;
    }

    /**
     * Creates and returns a 404 response.
     *
     * @param ServerRequestInterface $request Ignored.
     * @param DelegateInterface $delegate Ignored.
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var ResponseInterface $response */
        $response = $this->responsePrototype
            ->withStatus(404);
        $stream = $response->getBody();
        if (!$stream->isSeekable()) {
            return $response;
        }

        $response->getBody()->rewind();

        try{
            $response = $this->router->dispatch([
                'controller' => 'error',
                'action' => 'actionIndex',
                'statusCode' => $response->getStatusCode(),
                'reasonPhrase' => $response->getReasonPhrase(),
                'request' => $request->withAttribute('responsePrototype', $response),
                'response' => $response,
            ], $request, $response);
        } /** @noinspection PhpUndefinedClassInspection */ catch (\Throwable $ee) {
            $response = $this->handleErrorViewError($request, $response);
        } catch (\Exception $ee) {
            $response = $this->handleErrorViewError($request, $response);
        }

        return $response;
    }

    private function handleErrorViewError(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response->getBody()->write(
            sprintf("Cannot %s %s", $request->getMethod(), (string)$request->getUri())
        );
        return $response->withHeader('Content-Type', 'text/html');
    }
}
