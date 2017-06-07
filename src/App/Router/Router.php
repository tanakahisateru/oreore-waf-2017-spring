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
    public function __construct(
        RouterContainer $routes,
        ActionDispatcher $dispatcher
    ) {
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
            throw static::guessHttpException($failedRoute);
        }

        $params = $this->guessDispatcherParams($route);
        foreach ($route->attributes as $k => $v) {
            $params[$k] = $v;
        }
        $params['request'] = $request->withAttribute('responsePrototype', $responsePrototype);
        $params['response'] = $responsePrototype;

        return $this->dispatcher->dispatch($params);
    }

    /**
     * @param Route $route
     * @return array
     */
    private function guessDispatcherParams(Route $route)
    {
        if (is_callable($route->handler)) {
            return [
                'controller' => $route->handler
            ];
        }

        $routeName = $route->handler;
        if (($sep = strpos($routeName, ':')) !== false) {
            $routeName = substr($routeName, 0, $sep);
        }
        $elements = explode(".", $routeName);
        if (count($elements) < 2) {
            throw new \UnexpectedValueException('Bad route name: ' . $routeName);
        }

        $action = array_pop($elements);
        $controller = implode('.', $elements);

        return [
            'controller' => $controller,
            'action' => 'action' . ucfirst($action),
        ];
    }

    /**
     * @param Route $failedRoute
     * @return HttpException
     */
    private static function guessHttpException($failedRoute)
    {
        if (!$failedRoute) {
            return new NotFoundException();
        }
        switch ($failedRoute->failedRule) {
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
