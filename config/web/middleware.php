<?php

use Acme\App\Debug\Middleware\DebugBarInsertion;
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

$this->pipe($di->newInstance(RoutingHandler::class, [
    'router' => $di->get('router'),
    'responsePrototype' => $di->get('http.responseFactory')->createResponse(),
]));
