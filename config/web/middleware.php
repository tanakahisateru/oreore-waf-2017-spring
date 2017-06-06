<?php
use Acme\App\Middleware\DebugBarInsertion;
use Acme\App\Middleware\NotFoundHandler;
use Acme\App\Middleware\RoutingHandler;
use Acme\App\Middleware\WebAppBootstrap;
use Aura\Di\Container;
use Zend\Stratigility\MiddlewarePipe;

/** @var MiddlewarePipe $this */
/** @var Container $di */
/** @var array $params */

$this->pipe($di->get('errorHandlerMiddleware'));

if ($di->has('debugbar')) {
    $this->pipe($di->newInstance(DebugBarInsertion::class, [
        'debugbar' => $di->get('debugbar'),
        'baseUrl' => '/assets/debugbar',
        'streamFactory' => $di->get('http.streamFactory'),
    ]));
}

$this->pipe($di->newInstance(WebAppBootstrap::class, [
    'container' => $di,
    'appName' => 'app',
]));

// Express style middleware example:
//
// use Psr\Http\Message\ServerRequestInterface;
//
// $pipe->pipe(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($di) {
//     $di->get('logger')->info($request->getMethod() . ' ' . $request->getUri());
//     return $next($request, $response);
// });

$this->pipe($di->newInstance(RoutingHandler::class, [
    'router' => $di->get('router'),
    'responsePrototype' => $di->get('http.responseFactory')->createResponse(),
]));

$this->pipe($di->newInstance(NotFoundHandler::class));
