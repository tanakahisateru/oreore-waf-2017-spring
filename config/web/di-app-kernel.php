<?php
use Aura\Di\Container;
use Aura\Router\RouterContainer;
use DebugBar\Bridge\MonologCollector;
use DebugBar\StandardDebugBar;
use Lapaz\Amechan\AssetManager;
use Lapaz\PlainPhp\ScriptRunner;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use My\Web\Lib\App\WebApp;
use My\Web\Lib\Http\DiactorosHttpFactory;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\Middleware\WhoopsErrorResponseGenerator;
use My\Web\Lib\Router\Router;
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

$di->set('sharedEventManager', $di->lazy(function () use ($di) {
    $events = $di->newInstance(SharedEventManager::class);
    ScriptRunner::which()->requires(__DIR__ . '/events.php')->with([
        'di' => $di,
        'events' => $events,
    ])->run();
    return $events;
}));

//Planning:
//$di->set('sharedEventManager', $di2->lazyNew(SharedEventManager::class)
//    ->initAs(function ($events) use ($di) {
//        ScriptRunner::which()->requires(__DIR__ . '/events.php')->with([
//            'di' => $di,
//            'events' => $events,
//        ])->run();
//    })
//);
//
//$di->set('sharedEventManager', $di2->lazyNew(SharedEventManager::class)
//    ->initByScript(__DIR__ . '/events.php', [
//        'di' => $di,
//    ])
//);

/////////////////////////////////////////////////////////////////////
// PSR-15 pipeline

$di->set('httpFactory', $di->lazyNew(DiactorosHttpFactory::class));

$di->set('middlewarePipe', $di->lazy(function () use ($di) {
    $pipe = $di->newInstance(MiddlewarePipe::class);
    ScriptRunner::which()->requires(__DIR__ . '/middleware.php')->with([
        'di' => $di,
        'pipe' => $pipe,
    ])->run();
    return $pipe;
}));

$di->set('errorResponseGenerator', $di->lazyNew(ErrorResponseGenerator::class, [
    'router' => $di->lazyGet('router'),
    'controller' => 'error',
]));

$di->set('errorHandlerMiddleware', $di->lazy(function () use ($di) {
    $errorHandler = $di->newInstance(ErrorHandler::class, [
        'responsePrototype' => $di->get('httpFactory')->createResponse(),
        'responseGenerator' => $di->get('errorResponseGenerator'),
    ]);

    $logger = $di->get('logger');
    $errorHandler->attachListener(function ($error, ServerRequestInterface $request) use ($logger) {
        /** @var Exception|mixed $error */
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
        ScriptRunner::which()->requires(__DIR__ . '/routing.php')->with([
            'di' => $di,
            'map' => $map,
        ])->run();
    },
]));

// Planning
// $di->set('loggerFactory', $di2->newLocator('logger'));
//
// [
//    'setLoggerFactory' => $di->lazyGet('loggerFactory'),

// [
//    'setLoggerFactory' => $di2->lazyCallableReturns('logger'),

/////////////////////////////////////////////////////////////////////
// HTML rendering

$di->set('templateEngine', $di->lazy(function () use ($di) {
    $engine = $di->newInstance(TemplateEngine::class, [
        'directory' => __DIR__ . '/../../templates',
        'fileExtension' => null,
        'encoding' => 'utf-8',
    ]);
    ScriptRunner::which()->requires(__DIR__ . '/template-functions.php')->with([
        'di' => $di,
        'engine' => $engine,
    ])->run();
    return $engine;
}));

$di->set('assetManager', $di->lazy(function () use ($di) {
    $am = $di->newInstance(AssetManager::class);
    ScriptRunner::which()->requires(__DIR__ . '/assets.php')->with([
        'di' => $di,
        'am' => $am,
    ])->run();
    return $am;
}));

$di->set('viewEngine', $di->lazyNew(ViewEngine::class, [
    'router' => $di->lazyGet('router'),
    'templateEngine' => $di->lazyGet('templateEngine'),
    'assetManager' => $di->lazyGet('assetManager'),
    'viewFactory' => function (ViewEngine $engine) use ($di) {
        return $di->newInstance(View::class, [
            'engine' => $engine,
        ]);
    },
]));

/////////////////////////////////////////////////////////////////////
// DebugBar

if ($params['env'] == 'dev') {

    $di->set('errorResponseGenerator', $di->lazyNew(WhoopsErrorResponseGenerator::class));

    $di->set('debugbar', $di->lazy(function () use ($di, $params) {
        $debugBar = $di->newInstance(StandardDebugBar::class);
        $debugBar->addCollector($di->newInstance(MonologCollector::class, [
            'logger' => $di->get('logger'),
            'level' => Logger::getLevels()[$params['defaultLogLevel']],
        ]));
        return $debugBar;
    }));
}
