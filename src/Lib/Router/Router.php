<?php
namespace My\Web\Lib\Router;

use Aura\Dispatcher\Dispatcher;
use Aura\Router\Exception\RouteNotFound;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Allows;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Lapaz\Odango\AdviceComposite;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ray\Aop\MethodInvocation;
use Zend\EventManager\EventsCapableInterface;

class Router implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RouterContainer
     */
    protected $routes;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    // TODO Option to throw exception instead of logging

    /**
     * Router constructor.
     * @param RouterContainer $routes
     * @param Dispatcher $dispatcher
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(
        RouterContainer $routes,
        Dispatcher $dispatcher,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->routes = $routes;
        $this->dispatcher = $dispatcher;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
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

        assert(isset($params['controller']));
        if (is_scalar($params['controller'])) {
            $controller = $dispatcher->getObject($params['controller']);
        } else {
            $controller = $params['controller'];
        }

        ob_start();
        try {
            $dispatch = function () use ($params, $responsePrototype, $dispatcher) {
                $response = call_user_func($dispatcher, $params);
                $response = $this->fixUpReturnedValue($response, $responsePrototype);

                $echo = ob_get_contents();
                if (!empty($echo)) {
                    $response = $this->insertEchoIntoBody($echo, $response);
                }

                return $response;
            };

            $adviser = $this->eventTriggerAdviser($controller, $request, $responsePrototype);
            $dispatch = $adviser->bind($dispatch);

            return $dispatch();
        } finally {
            ob_end_clean();
        }
    }

    /**
     * @param $controller
     * @param ServerRequestInterface $request
     * @param ResponseInterface $responsePrototype
     * @return AdviceComposite
     */
    protected function eventTriggerAdviser($controller, ServerRequestInterface $request, ResponseInterface $responsePrototype)
    {
        return AdviceComposite::of(function (MethodInvocation $invocation) use ($controller, $request, $responsePrototype) {
            if (!($controller instanceof EventsCapableInterface)) {
                return $invocation->proceed();
            }

            $events = $controller->getEventManager();

            $argv = new \ArrayObject([
                'request' => $request,
                'responsePrototype' => $responsePrototype,
            ]);
            $result = $events->trigger('beforeAction', $controller, $argv);

            if ($result->stopped()) {
                if (isset($argv['response'])) {
                    return $argv['response'];
                } elseif ($result->last()) {
                    return $result->last();
                } else {
                    return $this->responseFactory->createResponse();
                }
            }

            // invoke
            $response = $invocation->proceed();

            $argv = new \ArrayObject([
                'request' => $request,
                'response' => $response,
            ]);
            $events->trigger('afterAction', $controller, $argv);

            return $response;
        });
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
            $this->logger->warning('Route not found: '. $e->getMessage());
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
            $this->logger->warning('Route not found: '. $e->getMessage());
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
            $stream = $this->streamFactory->createStream();
            $response = $response->withBody($stream);
            $stream->write($echo);
            $stream->write($streamedContents);
        } else {
            $stream->write($echo);
        }
        return $response;
    }
}
