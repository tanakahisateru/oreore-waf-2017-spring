<?php
namespace My\Web\Lib\Router;

use Aura\Dispatcher\Dispatcher;
use Aura\Router\Exception\RouteNotFound;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Allows;
use My\Web\Lib\Injection\LoggerInjectionTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;

class Router implements LoggerAwareInterface
{
    use LoggerInjectionTrait;

    /**
     * @var RouterContainer
     */
    protected $routes;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    // TODO Option to throw exception instead of logging

    /**
     * Router constructor.
     * @param RouterContainer $routes
     * @param Dispatcher $dispatcher
     */
    public function __construct(RouterContainer $routes, Dispatcher $dispatcher) {
        $this->routes = $routes;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $responsePrototype
     * @return array|null|ResponseInterface|string
     * @throws RoutingException
     */
    public function dispatch($request, $responsePrototype)
    {
        $matcher = $this->routes->getMatcher();
        $route = $matcher->match($request);

        if (!$route) {
            $failedRoute = $matcher->getFailedRoute();
            throw new RoutingException(static::guessHttpStatus($failedRoute));
        }

        $params = $this->guessDispatcherParams($route);
        $params['request'] = $request->withAttribute('responsePrototype', $responsePrototype);
        $params['response'] = $responsePrototype;
        foreach ($route->attributes as $k => $v) {
            $params[$k] = $v;
        }

        return call_user_func($this->dispatcher, $params);
    }

    /**
     * @param string $name
     * @param array $data
     * @return string
     */
    public function urlTo($name, $data = [])
    {
        try {
            return $this->routes->getGenerator()->generate($name, $data);
        } catch (RouteNotFound $e) {
            $this->getLogger()->warning('Route not found: '. $e->getMessage());
            return '#';
        }
    }

    /**
     * @param string $name
     * @param array $data
     * @return false|string
     */
    public function rawUrlTo($name, $data = [])
    {
        try {
            return $this->routes->getGenerator()->generateRaw($name, $data);
        } catch (RouteNotFound $e) {
            $this->getLogger()->warning('Route not found: '. $e->getMessage());
            return '#';
        }
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
     * @return int
     */
    private static function guessHttpStatus($failedRoute)
    {
        if (!$failedRoute) {
            return 404;
        }
        switch ($failedRoute->failedRule) {
            case Allows::class:
                // 405 METHOD NOT ALLOWED
                return 405;
            case Accepts::class:
                // 406 NOT ACCEPTABLE
                return 406;
            default:
                // 404 NOT FOUND
                return 404;
        }
    }
}
