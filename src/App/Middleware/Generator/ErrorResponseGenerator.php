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
     * @var object|callable|string
     */
    protected $controller;

    /**
     * @var string|null
     */
    protected $action;

    /**
     * ErrorResponseGenerator constructor.
     * @param Router $router
     * @param object|callable|string $controller
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

        $params = [
            'controller' => $this->controller,
        ];
        if ($this->action) {
            $params['action'] = $this->action;
        }
        $params = array_merge($params, [
            'error' => $e,
            'request' => $request->withAttribute('responsePrototype', $response),
            'response' => $response,
        ]);

        $response = $this->router->dispatch($params);

        return $response;
    }
}
