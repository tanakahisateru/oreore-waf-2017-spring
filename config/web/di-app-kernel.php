<?php
use Aura\Di\Container;
use Aura\Dispatcher\Dispatcher;
use Aura\Router\RouterContainer;
use My\Web\Lib\Util\PlainPhp;
use My\Web\Lib\View\TemplateEngine;
use My\Web\Lib\View\View;

/** @var Container $di */

$di->set('router', $di->lazyNew(\My\Web\Lib\Router\Router::class, [
    'routes' => $di->lazyGet('routerContainer'),
    'dispatcher' => $di->lazyGet('routerDispatcher'),
]));

$di->set('routerContainer', $di->lazyNew(RouterContainer::class, [
    'basepath' => null,
], [
    'setLoggerFactory' => function () use ($di) {
        return $di->get('logger');
    },
    'setMapBuilder' => function ($map) use ($di) {
        PlainPhp::runner()->with([
            'di' => $di,
            'map' => $map,
        ])->doRequire(__DIR__ . '/routing.php');
    },
]));

$di->set('routerDispatcher', $di->lazyNew(Dispatcher::class, [
    'object_param' => 'controller',
    'method_param' => 'action',
], [
    'setObjects' => $di->lazyValue('controllers'),
]));

$di->set('templateEngine', $di->lazy(function () use($di) {
    $engine = $di->newInstance(TemplateEngine::class, [
        'directory' => __DIR__ . '/../../templates',
        'fileExtension' => null,
        'encoding' => 'utf-8',
    ]);
    PlainPhp::runner()->with([
        'di' => $di,
        'engine' => $engine,
    ])->doRequire(__DIR__ . '/template-functions.php');
    return $engine;
}));

$di->params[View::class] = [
    'engineFactory' => function () use ($di) {
        return $di->get('templateEngine');
    },
    'assetsFactory' => null,
    'routerFactory' => function () use ($di) {
        return $di->get('router');
    },
];
