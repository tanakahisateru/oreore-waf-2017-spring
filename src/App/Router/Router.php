<?php
namespace Acme\App\Router;

use Acme\App\Http\StreamFactoryAwareInterface;
use Acme\App\Http\StreamFactoryAwareTrait;
use Aura\Dispatcher\Dispatcher;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Allows;
use Lapaz\Odango\AdviceComposite;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ray\Aop\MethodInvocation;
use Zend\EventManager\EventsCapableInterface;

class Router implements LoggerAwareInterface, StreamFactoryAwareInterface
{
    const EVENT_BEFORE_ACTION = 'beforeAction';
    const EVENT_AFTER_ACTION = 'afterAction';

    use LoggerAwareTrait;
    use StreamFactoryAwareTrait;

    /**
     * @var RouterContainer
     */
    protected $routerContainer;

    /**
     * @var callable[]
     */
    protected $controllerFactories;

    // TODO Option to throw exception instead of logging

    /**
     * Router constructor.
     * @param RouterContainer $routerContainer
     * @param callable[] $controllerFactories
     */
    public function __construct(
        RouterContainer $routerContainer,
        array $controllerFactories
    ) {
        $this->routerContainer = $routerContainer;
        $this->controllerFactories = $controllerFactories;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $responsePrototype
     * @return ResponseInterface
     * @throws RoutingException
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $responsePrototype)
    {
        $matcher = $this->routerContainer->getMatcher();
        $route = $matcher->match($request);

        if (!$route) {
            $failedRoute = $matcher->getFailedRoute();
            throw new RoutingException(static::guessHttpStatus($failedRoute));
        }

        $params = $this->guessDispatcherParams($route);
        foreach ($route->attributes as $k => $v) {
            $params[$k] = $v;
        }
        $params['request'] = $request->withAttribute('responsePrototype', $responsePrototype);
        $params['response'] = $responsePrototype;

        return $this->dispatch($params);
    }



    /**
     * @param array $params
     * @return ResponseInterface
     */
    public function dispatch(array $params)
    {
        assert(isset($params['controller']));

        if (is_scalar($params['controller'])) {
            if (!isset($this->controllerFactories[$params['controller']])) {
                throw new \LogicException("Controller not defined for: " . $params['controller']);
            }
            $controllerFactory = $this->controllerFactories[$params['controller']];
            $controller = $controllerFactory();
        } else {
            $controller = $params['controller'];
        }

        $responsePrototype = null;
        if (isset($params['response'])) {
            $responsePrototype = $params['response'];
        } elseif (isset($params['request'])) {
            $request = $params['request'];
            if ($request instanceof ServerRequestInterface) {
                $responsePrototype = $request->getAttribute('responsePrototype');
            }
        }
        if ($responsePrototype) {
            // cloned
            $responsePrototype = $responsePrototype->withBody($this->streamFactory->createStream());
        }

        $dispatcher = new Dispatcher(['__target' => $controller], null, 'action');

        ob_start();
        try {
            $dispatch = function () use ($params, $dispatcher, $responsePrototype) {

                $returnedValue = call_user_func($dispatcher, $params, '__target');

                // Aura.Dispatcher returns object itself if not invokable.
                if ($returnedValue === $dispatcher->getObject('__target')) {
                    throw new \LogicException("Request was not dispatched to any handler.");
                }

                if ($returnedValue instanceof ResponseInterface) {
                    $response = $returnedValue;
                } else {
                    if ($responsePrototype === null) {
                        throw new \LogicException("Response prototype required for informal result value.");
                    }
                    $response = $this->fixUpReturnedValue($returnedValue, $responsePrototype);
                }

                $echo = ob_get_contents();
                if (!empty($echo)) {
                    $response = $this->insertEchoIntoBody($echo, $response);
                }

                return $response;
            };

            $adviser = $this->eventTriggerAdviser($controller, $params);
            $dispatch = $adviser->bind($dispatch);

            return $dispatch();
        } finally {
            ob_end_clean();
        }
    }

    /**
     * @param $controller
     * @param array $params
     * @return AdviceComposite
     */
    protected function eventTriggerAdviser($controller, array $params)
    {
        $request = $params['request'];
        $responsePrototype = $params['response'];

        return AdviceComposite::of(function (MethodInvocation $invocation) use ($controller, $request, $responsePrototype) {
            if (!($controller instanceof EventsCapableInterface)) {
                return $invocation->proceed();
            }

            $events = $controller->getEventManager();

            $argv = new \ArrayObject([
                'request' => $request,
                'responsePrototype' => $responsePrototype,
            ]);
            $result = $events->trigger(static::EVENT_BEFORE_ACTION, $controller, $argv);

            if ($result->stopped()) {
                if (isset($argv['response'])) {
                    return $argv['response'];
                } elseif ($result->last()) {
                    return $result->last();
                } else {
                    return $responsePrototype;
                }
            }

            // invoke
            $response = $invocation->proceed();

            $argv = new \ArrayObject([
                'request' => $request,
                'response' => $response,
            ]);
            $result = $events->trigger(static::EVENT_AFTER_ACTION, $controller, $argv);

            if ($result->stopped()) {
                if (isset($argv['response'])) {
                    return $argv['response'];
                } elseif ($result->last()) {
                    return $result->last();
                } else {
                    return $responsePrototype;
                }
            }

            return $response;
        });
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
     * @param mixed $returnedValue
     * @param ResponseInterface $responsePrototype
     * @return ResponseInterface
     */
    private function fixUpReturnedValue($returnedValue, ResponseInterface $responsePrototype)
    {
        if (empty($returnedValue)) {
            $returnedValue = $responsePrototype;
        } elseif (is_scalar($returnedValue)) {
            $value = $returnedValue;
            $returnedValue = $responsePrototype;
            $returnedValue->getBody()->write($value);
        } elseif (is_array($returnedValue)) {
            $value = $returnedValue;
            $returnedValue = $responsePrototype->withHeader('Content-Type', 'application/json');
            $returnedValue->getBody()->write(json_encode($value));
        }

        if (!($returnedValue instanceof ResponseInterface)) {
            throw new \LogicException('Unsupported response returned');
        }

        return $returnedValue;
    }

    /**
     * @param string $echo
     * @param ResponseInterface $response
     * @return mixed
     */
    private function insertEchoIntoBody($echo, ResponseInterface $response)
    {
        $stream = $response->getBody();
        return $response->withBody($this->streamFactory->createStream($echo . strval($stream)));
    }


    /**
     * @param string $name
     * @param array $data
     * @param bool $raw
     * @return bool
     */
    public function urlTo($name, $data=[], $raw = false)
    {
        $generator = $this->routerContainer->getGenerator();
        if ($raw) {
            return $generator->generateRaw($name, $data);
        } else {
            return $generator->generateRaw($name, $data);
        }
    }
}
