<?php
use Aura\Router\RouterContainer;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DebugBar;
use DebugBar\StandardDebugBar;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use My\Web\Lib\App\WebApp;
use My\Web\Lib\Container\Container;
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

$di->set('sharedEventManager', $di->lazyNew(SharedEventManager::class, [], [], function ($events) use ($di) {
    PlainPhp::runner()->with([
        'di' => $di,
        'events' => $events,
    ])->run(__DIR__ . '/events.php');
}));

/////////////////////////////////////////////////////////////////////
// PSR-15 pipeline

$di->set('httpFactory', $di->lazyNew(DiactorosHttpFactory::class));

$di->set('middlewarePipe', $di->lazyNew(MiddlewarePipe::class, [], [], function ($pipe) use ($di) {
    PlainPhp::runner()->with([
        'di' => $di,
        'pipe' => $pipe,
    ])->run(__DIR__ . '/middleware.php');
}));

$di->set('errorHandlerMiddleware', $di->lazyNew(ErrorHandler::class, [
    'responsePrototype' => $di->lazyGetCall('httpFactory', 'createResponse'),
    'responseGenerator' => $params['env'] == 'dev' ?
        $di->lazyNew(WhoopsErrorResponseGenerator::class) :
        $di->lazyNew(ErrorResponseGenerator::class, [
            'router' => $di->lazyGet('router'),
            'controller' => 'error',
        ]),
], [], function (ErrorHandler $errorHandler) use ($di, $params) {
    $errorHandler->attachListener(
        function ($error, ServerRequestInterface $request) use ($di) {
            /** @var Exception|mixed $error */
            $logger = $di->get('logger');
            $logger->error(sprintf("%s(\"%s\") - %s", get_class($error), $error->getMessage(), $request->getUri()));
            foreach (explode("\n", $error->getTraceAsString()) as $trace) {
                $logger->error($trace);
            }
        }
    );
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

$di->set('templateEngine', $di->lazyNew(TemplateEngine::class, [
    'directory' => __DIR__ . '/../../templates',
    'fileExtension' => null,
    'encoding' => 'utf-8',
], [], function ($engine) use ($di) {
    PlainPhp::runner()->with([
        'di' => $di,
        'engine' => $engine,
    ])->run(__DIR__ . '/template-functions.php');
}));

$di->set('assetManager', $di->lazyNew(AssetManager::class, [], [], function ($am) use ($di) {
    PlainPhp::runner()->with([
        'di' => $di,
        'am' => $am,
    ])->run(__DIR__ . '/assets.php');
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
    $di->set('debugbar', $di->lazyNew(StandardDebugBar::class, [], [],
        function (DebugBar $debugBar) use ($di, $params) {
            $debugBar->addCollector($di->newInstance(MonologCollector::class, [
                'logger' => $di->get('logger'),
                'level' => Logger::getLevels()[$params['defaultLogLevel']],
            ]));
        }
    ));
}
