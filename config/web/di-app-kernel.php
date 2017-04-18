<?php
use Aura\Di\Container;
use Aura\Dispatcher\Dispatcher;
use Aura\Router\RouterContainer;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Util\PlainPhp;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Template\TemplateEngine;
use My\Web\Lib\View\View;

/** @var Container $di */

$di->set('app', $di->lazyNew(\My\Web\WebApp::class, [
    'container' => $di,
    'router' => $di->lazyGet('router'),
    'middlewarePipe' => $di->lazy(function () use ($di) {
        $mp = new \Zend\Stratigility\MiddlewarePipe();
        PlainPhp::runner()->with([
            'di' => $di,
            'mp' => $mp,
        ])->doRequire(__DIR__ . '/middleware.php');
        return $mp;
    }),
    'params' => $di->lazyRequire(__DIR__ . '/../params.php'),
]));

$di->set('router', $di->lazyNew(Router::class, [
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

$di->set('assetManager', $di->lazy(function () use ($di) {
    $am = $di->newInstance(AssetManager::class);
    PlainPhp::runner()->with([
        'di' => $di,
        'am' => $am,
    ])->doRequire(__DIR__ . '/assets.php');
    return $am;
}));

$di->params[View::class] = [
    'templateEngineFactory' => function () use ($di) {
        return $di->get('templateEngine');
    },
    'assetManagerFactory' => function () use ($di) {
        return $di->get('assetManager');
    },
    'routerFactory' => function () use ($di) {
        return $di->get('router');
    },
];
