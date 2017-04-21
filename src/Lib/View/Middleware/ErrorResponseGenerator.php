<?php
namespace My\Web\Lib\View\Middleware;

use My\Web\Lib\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Utils;

class ErrorResponseGenerator
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * ErrorResponseGenerator constructor.
     * @param Router $router
     */
    public function __construct($router)
    {
        $this->router = $router;
    }

    /**
     * Create/update the response representing the error.
     *
     * @param \Exception|mixed $e
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke($e, ServerRequestInterface $request, ResponseInterface $response)
    {
        /** @var ResponseInterface $response */
        $response = $response->withStatus(Utils::getStatusCode($e, $response));

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
            $response = $this->handleErrorViewError($response);
        } catch (\Exception $ee) {
            $response = $this->handleErrorViewError($response);
        }

        return $response;
    }

    private function handleErrorViewError(ResponseInterface $response)
    {
        $response->getBody()->write($response->getReasonPhrase() ?: 'Unknown Error');
        return $response->withHeader('Content-Type', 'text/html');
    }
}
