<?php
use Aura\Di\Container;
use Aura\Router\RouterContainer;
use My\Web\Lib\App\WebApp;
use My\Web\Lib\Container\AliasContainer;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Util\PlainPhp;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Template\TemplateEngine;
use My\Web\Lib\View\View;
use My\Web\Lib\View\ViewAwareInterface;
use My\Web\Lib\View\ViewEngine;
use Zend\EventManager\SharedEventManager;

/** @var Container $di */
/** @var array $params */

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

$di->set('viewFactory', $di->lazy(function () use ($di) {
    return function (ViewEngine $engine, AssetManager $assetManager, $class = View::class) use ($di) {
        return $di->newInstance($class, [
            'engine' => $engine,
            'requiredAssets' => $assetManager->createUsage(),
        ]);
    };
}));
// $factory = $container->get('viewFactory');
// $view = $factory($engine, $assetManager, ClassName::class);

$di->set('viewEngine', $di->lazyNew(ViewEngine::class, [
    'container' => $di->lazyNew(AliasContainer::class, [
        'parent' => $di,
        'alias' => [
            'router' => 'router',
            'templateEngine' => 'templateEngine',
            'assetManager' => 'assetManager',
            'viewFactory' => 'viewFactory',
        ],
    ]),
]));
