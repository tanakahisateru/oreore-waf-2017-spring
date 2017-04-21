<?php
namespace My\Web\Lib\Router;

use Aura\Dispatcher\Dispatcher;
use Aura\Router\Exception\RouteNotFound;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Allows;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\HttpFactoryInjectionTrait;
use My\Web\Lib\Log\LoggerInjectionTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Zend\EventManager\EventsCapableInterface;

class Router implements LoggerAwareInterface, HttpFactoryAwareInterface
{
    use LoggerInjectionTrait;
    use HttpFactoryInjectionTrait;

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
     * @return ResponseInterface
     * @throws RoutingException
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $responsePrototype)
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

        return $this->dispatch($params, $request, $responsePrototype);
    }

    /**
     * @param array $params
     * @param ServerRequestInterface $request
     * @param ResponseInterface $responsePrototype
     * @return ResponseInterface
     */
    public function dispatch(array $params, ServerRequestInterface $request, ResponseInterface $responsePrototype)
    {
        $dispatcher = $this->dispatcher;
        $dispatcher->setObjectParam('controller');
        $dispatcher->setMethodParam('action');

        $controller = $dispatcher->getObject($params['controller']);

        ob_start();
        try {
            $this->triggerEvent('beforeAction', $controller, $request, $responsePrototype);
            try {
                $response = call_user_func($dispatcher, $params);

                $response = $this->fixUpReturnedValue($response, $responsePrototype);
                $echo = ob_get_contents();
                if (!empty($echo)) {
                    $response = $this->insertEchoIntoBody($echo, $response);
                }
            } finally {
                $this->triggerEvent('afterAction', $controller, $request, $response);
            }
        } catch (ActionStoppedException $e) {
            return $e->getResponse();
        } finally {
            ob_end_clean();
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $response;
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

    /**
     * @param string $eventName
     * @param object $target
     * @param ServerRequestInterface $request
     * @param ResponseInterface $responsePrototype
     * @throws ActionStoppedException
     */
    private function triggerEvent($eventName, $target, ServerRequestInterface $request, ResponseInterface $responsePrototype)
    {
        if (!($target instanceof EventsCapableInterface)) {
            return;
        }

        $argv = new \ArrayObject(compact('request', 'responsePrototype'));
        $results = $target->getEventManager()->trigger($eventName, $target, $argv);

        if ($results->stopped()) {
            if ($argv->offsetExists('response')) {
                $response = $argv->offsetGet('response');
            } else {
                $response = $results->last();
                if (empty($response)) {
                    $response = $this->getHttpFactory()->createEmptyResponse();
                }
            }
            throw new ActionStoppedException($response);
        }
    }

    /**
     * @param mixed $response
     * @param ResponseInterface $responsePrototype
     * @return ResponseInterface
     */
    private function fixUpReturnedValue($response, ResponseInterface $responsePrototype)
    {
        if (empty($response)) {
            $response = $responsePrototype;
        } elseif (is_scalar($response)) {
            $value = $response;
            $response = $responsePrototype;
            $response->getBody()->write($value);
        } elseif (is_array($response)) {
            $value = $response;
            $response = $responsePrototype->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($value));
        }

        if (!($response instanceof ResponseInterface)) {
            throw new \LogicException('Unsupported response returned');
        }

        return $response;
    }


    /**
     * @param string $echo
     * @param ResponseInterface $response
     * @return mixed
     */
    private function insertEchoIntoBody($echo, ResponseInterface $response)
    {
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
            $streamedContents = $stream->getContents();
            $stream = $this->getHttpFactory()->createStream('php://temp', 'rw');
            $response = $response->withBody($stream);
            $stream->write($echo);
            $stream->write($streamedContents);
        } else {
            $stream->write($echo);
        }
        return $response;
    }
}
