<?php
namespace Acme\App\Middleware\Generator;

use Acme\App\Router\Router;
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
     * @var object|callable
     */
    protected $controller;

    /**
     * @var string|null
     */
    protected $action;

    /**
     * ErrorResponseGenerator constructor.
     * @param Router $router
     * @param object|callable $controller
     * @param string|null $action
     */
    public function __construct($router, $controller, $action = null)
    {
        $this->router = $router;
        $this->controller = $controller;
        $this->action = $action;
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
            $params = [
                'controller' => $this->controller,
            ];
            if ($this->action) {
                $params['action'] = $this->action;
            }
            $params = array_merge($params, [
                'statusCode' => $response->getStatusCode(),
                'reasonPhrase' => $response->getReasonPhrase(),
                'request' => $request->withAttribute('responsePrototype', $response),
                'response' => $response,
            ]);

            $response = $this->router->dispatch($params, $request, $response);
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
