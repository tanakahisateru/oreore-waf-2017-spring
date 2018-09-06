<?php
namespace Acme\App\Router;

use Aura\Router\Exception\RouteNotFound as AuraRouteNotFound;
use Aura\Router\Route;
use Aura\Router\RouterContainer;
use Aura\Router\Rule\Accepts;
use Aura\Router\Rule\Allows;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Sumeko\Http\Exception as HttpException;
use Sumeko\Http\Exception\MethodNotAllowedException;
use Sumeko\Http\Exception\NotAcceptableException;
use Sumeko\Http\Exception\NotFoundException;

class Router implements RequestHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     *
     * @param callable[] $controllerFactories
     * @param ResponseFactoryInterface $responseFactory
     * @param null $pathPrefix
     */
    public function __construct(
        array $controllerFactories,
        ResponseFactoryInterface $responseFactory,
        $pathPrefix = null
    )
    {
        $this->routes = new RouterContainer($pathPrefix);

        $controllerProvider = new ControllerProvider($controllerFactories);
        $this->dispatcher = new ActionDispatcher($controllerProvider, $responseFactory);
    }

    /**
     * @return RouterContainer
     */
    public function getRoutes(): RouterContainer
    {
        return $this->routes;
    }

    /**
     * @return ActionDispatcher
     */
    public function getDispatcher(): ActionDispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @inheritdoc
     * @throws HttpException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        assert($this->logger instanceof LoggerInterface);
        $this->routes->setLoggerFactory(function () {
            return $this->logger;
        });

        $matcher = $this->routes->getMatcher();
        $route = $matcher->match($request);

        if (!$route) {
            $failedRoute = $matcher->getFailedRoute();
            throw static::createHttpExceptionFromFailedRoute($failedRoute);
        }

        $params = $this->dispatcherParams($route);

        $params['request'] = $request;

        return $this->dispatcher->dispatch($params);
    }

    /**
     * @param string $name
     * @param array $data
     * @param bool $raw
     * @return string
     * @throws NoSuchRouteException
     */
    public function uriTo($name, $data=[], $raw = false)
    {
        $generator = $this->routes->getGenerator();
        try {
            if ($raw) {
                $path = $generator->generateRaw($name, $data);
            } else {
                $path = $generator->generate($name, $data);
            }
            if ($path === false) {
                throw new NoSuchRouteException($name . " not found");
            }
            return $path;
        } catch (AuraRouteNotFound $e) {
            throw new NoSuchRouteException($name . " not found");
        }
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
                'action' => $action,
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
        if ($route) {
            switch ($route->failedRule) {
                case Allows::class:
                    // 405 METHOD NOT ALLOWED
                    return new MethodNotAllowedException();
                case Accepts::class:
                    // 406 NOT ACCEPTABLE
                    return new NotAcceptableException();
            }
        }

        // 404 NOT FOUND
        return new NotFoundException();
    }
}
