<?php
use Aura\Di\Container;
use My\Web\Lib\Middleware\RoutingMiddleware;
use My\Web\Lib\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;
use Zend\Stratigility\Middleware\NotFoundHandler;
use Zend\Stratigility\MiddlewarePipe;

/** @var Container $di */
/** @var MiddlewarePipe $mp */

$responseFactory = new \Http\Factory\Diactoros\ResponseFactory();

/** @var ResponseInterface $responsePrototype */
$responsePrototype = $responseFactory->createResponse(200);

/** @var Router $router */
$router = $di->get('router');

$mp->setResponsePrototype($responsePrototype);

// Express style middleware
$mp->pipe(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($di) {
    $di->get('logger')->info($request->getMethod() . ' ' . $request->getUri());
    return $next($request, $response);
});

$mp->pipe(new ErrorHandler($responsePrototype, new ErrorResponseGenerator(true)));
$mp->pipe(new RoutingMiddleware($router, $responseFactory));
$mp->pipe(new NotFoundHandler($responsePrototype));
