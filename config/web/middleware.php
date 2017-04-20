<?php
use Aura\Di\Container;
use My\Web\Lib\Http\HttpFactoryInterface;
use My\Web\Lib\Http\WhoopsResponseGenerator;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Router\RoutingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;
use Zend\Stratigility\Middleware\NotFoundHandler;
use Zend\Stratigility\MiddlewarePipe;

/** @var Container $di */
/** @var MiddlewarePipe $mp */

/** @var HttpFactoryInterface $httpFactory */
$httpFactory = $di->get('httpFactory');

/** @var ResponseInterface $responsePrototype */
$responsePrototype = $httpFactory->createResponse();

$mp->setResponsePrototype($responsePrototype);

$errorResponseGenerator = getenv('MY_APP_ENV') == 'dev' ?
    new WhoopsResponseGenerator() :
    new ErrorResponseGenerator(false);

$mp->pipe(new ErrorHandler($responsePrototype, $errorResponseGenerator));

// Express style middleware
$mp->pipe(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($di) {
    $di->get('logger')->info($request->getMethod() . ' ' . $request->getUri());
    return $next($request, $response);
});

/** @var Router $router */
$router = $di->get('router');
$mp->pipe(new RoutingMiddleware($router, $httpFactory));
$mp->pipe(new NotFoundHandler($responsePrototype));
