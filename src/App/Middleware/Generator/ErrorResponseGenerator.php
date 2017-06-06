<?php
namespace Acme\App\Middleware\Generator;

use Acme\App\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sumeko\Http\Exception as HttpException;
use Sumeko\Http\Exception\InternalServerErrorException;

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
    public function __construct(Router $router, $controller, $action = null)
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
        if (!($e instanceof HttpException)) {
            if (interface_exists('\Throwable') && $e instanceof \Throwable) {
                $e = new InternalServerErrorException("Internal Server Error", 500, $e);
            } elseif ($e instanceof \Exception) {
                $e = new InternalServerErrorException("Internal Server Error", 500, $e);
            } else {
                $e = new InternalServerErrorException();
            }
        }

        $response = $response->withStatus($e->getCode(), $e->getMessage());

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

            $response = $this->router->dispatch($params);
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
