<?php
namespace Acme\App\Router;

use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Allows;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sumeko\Http\Exception as HttpException;
use Sumeko\Http\Exception\MethodNotAllowedException;
use Sumeko\Http\Exception\NotAcceptableException;
use Sumeko\Http\Exception\NotFoundException;

class Router
{
    /**
     * @var RouterContainer
     */
    protected $routes;

    /**
     * @var ActionDispatcher
     */
    protected $dispatcher;

    /**
     * Router constructor.
     * @param RouterContainer $routes
     * @param ActionDispatcher $dispatcher
     */
    public function __construct(RouterContainer $routes, ActionDispatcher $dispatcher)
    {
        $this->routes = $routes;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $responsePrototype
     * @return ResponseInterface
     * @throws HttpException
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $responsePrototype)
    {
        $matcher = $this->routes->getMatcher();
        $route = $matcher->match($request);

        if (!$route) {
            $failedRoute = $matcher->getFailedRoute();
            throw static::createHttpExceptionFromFailedRoute($failedRoute);
        }

        $params = $this->dispatcherParams($route);

        $params['request'] = $request->withAttribute('responsePrototype', $responsePrototype);
        $params['response'] = $responsePrototype;

        return $this->dispatcher->dispatch($params);
    }

    /**
     * @param Route $route
     * @return array
     */
    protected function dispatcherParams(Route $route)
    {
        if (is_callable($route->handler)) {
            $params = [
                'controller' => $route->handler
            ];
        } else {
            list($controller, $action) = $this->guessControllerAndActionFromName($route->handler);
            $params = [
                'controller' => $controller,
                'action' => 'action' . ucfirst($action),
            ];
        }

        foreach ($route->attributes as $k => $v) {
            $params[$k] = $v;
        }

        return $params;
    }

    /**
     * @param string $name
     * @return array
     */
    protected function guessControllerAndActionFromName($name)
    {
        if (($sep = strpos($name, ':')) !== false) {
            $name = substr($name, 0, $sep);
        }
        $elements = explode(".", $name);
        if (count($elements) < 2) {
            throw new \UnexpectedValueException('Bad route name: ' . $name);
        }

        $action = array_pop($elements);
        $controller = implode('.', $elements);
        return [$controller, $action];
    }

    /**
     * @param Route $route
     * @return HttpException
     */
    protected static function createHttpExceptionFromFailedRoute(Route $route)
    {
        if (!$route) {
            return new NotFoundException();
        }
        switch ($route->failedRule) {
            case Allows::class:
                // 405 METHOD NOT ALLOWED
                return new MethodNotAllowedException();
            case Accepts::class:
                // 406 NOT ACCEPTABLE
                return new NotAcceptableException();
            default:
                // 404 NOT FOUND
                return new NotFoundException();
        }
    }
}
