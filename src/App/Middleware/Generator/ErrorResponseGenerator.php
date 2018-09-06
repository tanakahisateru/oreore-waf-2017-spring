<?php
namespace Acme\App\Middleware\Generator;

use Acme\App\Router\ActionDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sumeko\Http\Exception as HttpException;
use Sumeko\Http\Exception\InternalServerErrorException;

class ErrorResponseGenerator
{
    /**
     * @var ActionDispatcher
     */
    protected $dispatcher;

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
     * @param ActionDispatcher $dispatcher
     * @param object|callable|string $controller
     * @param string|null $action
     */
    public function __construct(ActionDispatcher $dispatcher, $controller, ?string $action = null)
    {
        $this->dispatcher = $dispatcher;
        $this->controller = $controller;
        $this->action = $action;
    }

    /**
     * Create/update the response representing the error.
     *
     * @param \Throwable $e
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(\Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        if (!($e instanceof HttpException)) {
            if ($e instanceof \Exception) {
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
            'request' => $request,
        ]);

        $response = $this->dispatcher->dispatch($params);

        return $response;
    }
}
