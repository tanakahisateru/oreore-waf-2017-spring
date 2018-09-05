<?php
namespace Acme\App\Router;

use Aura\Dispatcher\Dispatcher;
use Lapaz\Odango\AdviceComposite;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Ray\Aop\MethodInvocation;
use Zend\EventManager\EventsCapableInterface;

class ActionDispatcher
{
    const EVENT_BEFORE_ACTION = 'beforeAction';
    const EVENT_AFTER_ACTION = 'afterAction';

    /**
     * @var ControllerProvider
     */
    protected $controllerProvider;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * ActionDispatcher constructor.
     * @param ControllerProvider $controllerProvider
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(ControllerProvider $controllerProvider, StreamFactoryInterface $streamFactory)
    {
        $this->controllerProvider = $controllerProvider;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @param array $params
     * @return ResponseInterface
     */
    public function dispatch(array $params)
    {
        assert(isset($params['controller']));

        if (is_scalar($params['controller'])) {
            $controller = $this->controllerProvider->createController($params['controller']);
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
}
