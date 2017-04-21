<?php
use Aura\Di\Container;
use Aura\Dispatcher\Dispatcher;
use Aura\Router\RouterContainer;
use My\Web\Lib\Container\AliasContainer;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Util\PlainPhp;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Template\TemplateEngine;
use My\Web\Lib\View\ViewAwareInterface;
use My\Web\Lib\View\ViewEngine;
use My\Web\Lib\WebApp;
use Zend\EventManager\SharedEventManager;

/** @var Container $di */

$di->setters[ViewAwareInterface::class] = [
    'setViewEngine' => $di->lazyGet('viewEngine'),
];

$di->set('app', $di->lazyNew(WebApp::class, [
    'middlewarePipe' => $di->lazyGet('middlewarePipe'),
]));

$di->set('sharedEventManager', $di->lazy(function() use ($di) {
    $events = new SharedEventManager();
    PlainPhp::runner()->with([
        'di' => $di,
        'events' => $events,
    ])->doRequire(__DIR__ . '/events.php');
    return $events;
}));

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
    'objects' => $di->lazyValue('controllers'),
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

$di->set('viewEngine', $di->lazyNew(ViewEngine::class, [
    'container' => $di->lazyNew(AliasContainer::class, [
        'parent' => $di,
        'alias' => [
            'templateEngine' => 'templateEngine',
            'assetManager' => 'assetManager',
            'router' => 'router',
        ],
    ]),
]));
