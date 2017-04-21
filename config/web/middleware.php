<?php
use Aura\Di\Container;
use My\Web\Lib\Http\HttpFactoryInterface;
use My\Web\Lib\Router\Middleware\RoutingHandler;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\Middleware\NotFoundHandler;
use Zend\Stratigility\MiddlewarePipe;

/** @var Container $di */
/** @var MiddlewarePipe $pipe */

/** @var Router $router */
$router = $di->get('router');

/** @var HttpFactoryInterface $httpFactory */
$httpFactory = $di->get('httpFactory');
$responsePrototype = $httpFactory->createResponse();

$pipe->pipe($di->get('errorHandlerMiddleware'));

// Express style middleware example:
//
// use Psr\Http\Message\ServerRequestInterface;
//
// $pipe->pipe(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($di) {
//     $di->get('logger')->info($request->getMethod() . ' ' . $request->getUri());
//     return $next($request, $response);
// });

$pipe->pipe($di->newInstance(RoutingHandler::class, [
    'router' => $router,
]));

$pipe->pipe($di->newInstance(NotFoundHandler::class, [
    'router' => $router,
    'responsePrototype' => $responsePrototype,
]));
