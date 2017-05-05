<?php
use Aura\Di\Container;
use Aura\Router\RouterContainer;
use DebugBar\Bridge\MonologCollector;
use DebugBar\StandardDebugBar;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use My\Web\Lib\App\WebApp;
use My\Web\Lib\Container\AliasContainer;
use My\Web\Lib\Http\DiactorosHttpFactory;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\Middleware\WhoopsErrorResponseGenerator;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Util\PlainPhp;
use My\Web\Lib\View\Asset\AssetManager;
use My\Web\Lib\View\Middleware\ErrorResponseGenerator;
use My\Web\Lib\View\Template\TemplateEngine;
use My\Web\Lib\View\View;
use My\Web\Lib\View\ViewAwareInterface;
use My\Web\Lib\View\ViewEngine;
use Psr\Http\Message\ServerRequestInterface;
use Zend\EventManager\SharedEventManager;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\MiddlewarePipe;

/** @var Container $di */
/** @var array $params */

$di->setters[HttpFactoryAwareInterface::class] = [
    'setHttpFactory' => $di->lazyGet('httpFactory'),
];

$di->setters[ViewAwareInterface::class] = [
    'setViewEngine' => $di->lazyGet('viewEngine'),
];

/////////////////////////////////////////////////////////////////////
// Application

$di->set('app', $di->lazyNew(WebApp::class, [
    'middlewarePipe' => $di->lazyGet('middlewarePipe'),
]));

$di->set('logger', $di->lazyNew(Logger::class, [
    'name' => 'default',
    'handlers' => $di->lazyArray([
        $di->lazyNew(RotatingFileHandler::class, [
            'filename' => __DIR__ . '/../../log/web.log',
            'level' => Logger::getLevels()[$params['defaultLogLevel']],
        ]),
    ]),
    'processors' => [],
]));

$di->set('sharedEventManager', $di->lazy(function() use ($di) {
    $events = $di->newInstance(SharedEventManager::class);
    PlainPhp::runner()->with([
        'di' => $di,
        'events' => $events,
    ])->doRequire(__DIR__ . '/events.php');
    return $events;
}));

/////////////////////////////////////////////////////////////////////
// PSR-15 pipeline

$di->set('httpFactory', $di->lazyNew(DiactorosHttpFactory::class));

$di->set('middlewarePipe', $di->lazy(function () use ($di) {
    $pipe = $di->newInstance(MiddlewarePipe::class);
    PlainPhp::runner()->with([
        'di' => $di,
        'pipe' => $pipe,
    ])->doRequire(__DIR__ . '/middleware.php');
    return $pipe;
}));

$di->set('errorHandlerMiddleware', $di->lazy(function () use ($di, $params) {
    $errorHandler = $di->newInstance(ErrorHandler::class, [
        'responsePrototype' => $di->get('httpFactory')->createResponse(),
        'responseGenerator' => $params['env'] == 'dev' ?
            $di->newInstance(WhoopsErrorResponseGenerator::class) :
            $di->newInstance(ErrorResponseGenerator::class, [
                'router' => $di->get('router'),
                'controller' => 'error',
            ]),
    ]);

    $errorHandler->attachListener(function ($error, ServerRequestInterface $request) use ($di) {
        /** @var Exception|mixed $error */
        $logger = $di->get('logger');
        $logger->error(sprintf("%s(\"%s\") - %s", get_class($error), $error->getMessage(), $request->getUri()));
        foreach (explode("\n", $error->getTraceAsString()) as $trace) {
            $logger->error($trace);
        }
    });

    return $errorHandler;
}));

/////////////////////////////////////////////////////////////////////
// routing - dispatching

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

/////////////////////////////////////////////////////////////////////
// HTML rendering

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

/////////////////////////////////////////////////////////////////////
// debugbar

if ($params['env'] == 'dev') {
    $di->set('debugbar', $di->lazy(function () use ($di, $params) {
        $debugBar = $di->newInstance(StandardDebugBar::class);
        $debugBar->addCollector($di->newInstance(MonologCollector::class, [
            'logger' => $di->get('logger'),
            'level' => Logger::getLevels()[$params['defaultLogLevel']],
        ]));
        return $debugBar;
    }));
}
