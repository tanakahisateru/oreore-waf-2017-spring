<?php
namespace My\Web\Lib\Router;


use Aura\Dispatcher\Dispatcher;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router
{
    /** @var RouterContainer */
    protected $routes;

    /** @var Dispatcher */
    protected $dispatcher;

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
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response)
    {
        $matcher = $this->routes->getMatcher();
        $route = $matcher->match($request);

        if (!$route) {
            return $this->routingError($response, static::guessHttpStatus($matcher->getFailedRoute()));
        }

        $params = $this->guessDispatcherParams($route);
        $params['request'] = $request;
        $params['response'] = $response;
        foreach ($route->attributes as $k => $v) {
            $params[$k] = $v;
        }

//        try {
        $response = call_user_func($this->dispatcher, $params);
//        } catch (\Exception $ex) {
//        }

        return $response;
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
     * @param ResponseInterface $response
     * @param int $status
     * @return ResponseInterface
     */
    private function routingError(ResponseInterface $response, $status)
    {
        /** @var ResponseInterface $response */
        $response = $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'text/html');

        $response->getBody()->write($response->getReasonPhrase());

        return $response;
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
            case 'Aura\Router\Rule\Allows':
                // 405 METHOD NOT ALLOWED
                return 405;
            case 'Aura\Router\Rule\Accepts':
                // 406 NOT ACCEPTABLE
                return 406;
            default:
                // 404 NOT FOUND
                return 404;
        }
    }
}
