<?php
namespace Acme\App\Router;

use Aura\Dispatcher\Dispatcher;
use Lapaz\Odango\AdviceComposite;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
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
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * ActionDispatcher constructor.
     * @param ControllerProvider $controllerProvider
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ControllerProvider $controllerProvider, ResponseFactoryInterface $responseFactory)
    {
        $this->controllerProvider = $controllerProvider;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param array $params
     * @return ResponseInterface
     */
    public function dispatch(array $params): ResponseInterface
    {
        assert(isset($params['controller']));

        if (is_scalar($params['controller'])) {
            $controller = $this->controllerProvider->createController($params['controller']);
        } else {
            $controller = $params['controller'];
        }

        $dispatcher = new Dispatcher(['__target' => $controller], null, 'action');

        ob_start();
        try {
            $dispatch = function () use ($params, $dispatcher) {

                $returnedValue = call_user_func($dispatcher, $params, '__target');

                // Aura.Dispatcher returns object itself if not invokable.
                if ($returnedValue === $dispatcher->getObject('__target')) {
                    throw new \UnexpectedValueException("Request was not dispatched to any handler.");
                }

                if ($returnedValue instanceof ResponseInterface) {
                    $response = $returnedValue;
                } else {
                    $echoContent = ob_get_contents();
                    $response = $this->createFallbackResponse($returnedValue, $echoContent);
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

    private function eventTriggerAdviser($controller, array $params): AdviceComposite
    {
        assert(is_object($controller));

        $request = $params['request'];

        return AdviceComposite::of(function (MethodInvocation $invocation) use ($controller, $request) {
            if (!($controller instanceof EventsCapableInterface)) {
                return $invocation->proceed();
            }

            $events = $controller->getEventManager();

            $argv = new \ArrayObject([
                'request' => $request,
            ]);
            $result = $events->trigger(static::EVENT_BEFORE_ACTION, $controller, $argv);

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
            $result = $events->trigger(static::EVENT_AFTER_ACTION, $controller, $argv);

            if ($result->stopped()) {
                if (isset($argv['response'])) {
                    return $argv['response'];
                } elseif ($result->last()) {
                    return $result->last();
                } else {
                    return $this->responseFactory->createResponse();
                }
            }

            return $response;
        });
    }

    private function createFallbackResponse($returnedValue, ?string $echoContent): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();

        if (!empty($echoContent)) {
            $response->getBody()->write($echoContent);
        }

        if (empty($returnedValue)) {
            return $response;
        } elseif (is_scalar($returnedValue)) {
            $response->getBody()->write($returnedValue);
            return $response;
        } elseif (is_array($returnedValue) || is_object($returnedValue)) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($returnedValue, JSON_PRETTY_PRINT));
            return $response;
        } else {
            throw new \UnexpectedValueException('Unsupported returned value');
        }
    }
}
