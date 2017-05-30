<?php
use Aura\Di\Container;
use Aura\Router\RouterContainer;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DebugBar;
use DebugBar\StandardDebugBar;
use Http\Factory\Diactoros\ResponseFactory;
use Http\Factory\Diactoros\ServerRequestFactory;
use Http\Factory\Diactoros\StreamFactory;
use Http\Factory\Diactoros\UploadedFileFactory;
use Http\Factory\Diactoros\UriFactory;
use Lapaz\Amechan\AssetManager;
use Lapaz\Aura\Di\ContainerExtension;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use My\Web\Lib\App\WebApp;
use My\Web\Lib\Router\Router;
use My\Web\Lib\Util\Middleware\WhoopsErrorResponseGenerator;
use My\Web\Lib\View\Middleware\ErrorResponseGenerator;
use My\Web\Lib\View\Template\EscaperExtension;
use My\Web\Lib\View\View;
use My\Web\Lib\View\ViewEngine;
use My\Web\Lib\View\ViewEngineAwareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Escaper\Escaper;
use Zend\EventManager\SharedEventManager;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\MiddlewarePipe;

/** @var Container $di */
/** @var array $params */

$dix = ContainerExtension::createFrom($di);

$di->setters[ViewEngineAwareInterface::class] = [
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

//$di->set('sharedEventManager', $di->lazy(function () use ($di) {
//    $events = $di->newInstance(SharedEventManager::class);
//    ScriptRunner::which()->requires(__DIR__ . '/events.php')->with([
//        'di' => $di,
//        'events' => $events,
//    ])->run();
//    return $events;
//}));

$di->set('sharedEventManager', $dix->lazyNew(SharedEventManager::class)
    ->modifiedByScript(__DIR__ . '/events.php', [
        'di' => $di,
        'params' => $params,
    ])
);

/////////////////////////////////////////////////////////////////////
// PSR-17 factories

$di->set('http.requestFactory', $di->lazyNew(ServerRequestFactory::class));
$di->set('http.responseFactory', $di->lazyNew(ResponseFactory::class));
$di->set('http.uploadedFileFactory', $di->lazyNew(UploadedFileFactory::class));
$di->set('http.uriFactory', $di->lazyNew(UriFactory::class));
$di->set('http.streamFactory', $di->lazyNew(StreamFactory::class));

/////////////////////////////////////////////////////////////////////
// PSR-15 pipeline

$di->set('middlewarePipe', $dix->lazyNew(MiddlewarePipe::class)
    ->modifiedByScript(__DIR__ . '/middleware.php', [
        'di' => $di,
        'params' => $params,
    ])
);

$di->set('errorResponseGenerator', $di->lazyNew(ErrorResponseGenerator::class, [
    'router' => $di->lazyGet('router'),
    'controller' => 'error',
]));

$di->set('errorHandlerMiddleware', $dix->lazyNew(ErrorHandler::class, [
    'responsePrototype' => $di->lazyGetCall('http.responseFactory', 'createResponse'),
    'responseGenerator' => $di->lazyGet('errorResponseGenerator'),
])->modifiedBy(function (ErrorHandler $errorHandler) use ($di) {
    $logger = $di->get('logger');
    $errorHandler->attachListener(function ($error, ServerRequestInterface $request) use ($logger) {
        /** @var Exception|mixed $error */
        $logger->error(sprintf("%s(\"%s\") - %s", get_class($error), $error->getMessage(), $request->getUri()));
        foreach (explode("\n", $error->getTraceAsString()) as $trace) {
            $logger->error($trace);
        }
    });
}));

/////////////////////////////////////////////////////////////////////
// routing - dispatching

$di->set('router', $di->lazyNew(Router::class, [
    'routes' => $di->lazyGet('routerContainer'),
    'dispatcher' => $di->lazyGet('routerDispatcher'),
    'responseFactory' => $di->lazyGet('http.responseFactory'),
    'streamFactory' => $di->lazyGet('http.streamFactory'),
]));

$di->set('routerContainer', $dix->lazyNew(RouterContainer::class, [
    'basepath' => null,
], [
    'setLoggerFactory' => $dix->newLocator('logger'),
])->modifiedByScript(__DIR__ . '/routing.php', [
    'di' => $di,
    'params' => $params,
]));

/////////////////////////////////////////////////////////////////////
// HTML rendering

$di->set('templateEngine', $dix->lazyNew(Engine::class, [
    'directory' => __DIR__ . '/../../templates',
    'fileExtension' => null,
    'encoding' => 'utf-8',
])->modifiedBy(function (Engine $engine) use ($di) {
    $extension = $di->newInstance(EscaperExtension::class, [
        'escaper' => $di->newInstance(Escaper::class),
    ]);
    assert($extension instanceof ExtensionInterface);
    $engine->loadExtension($extension);
}));

$di->set('assetManager', $dix->lazyNew(AssetManager::class)
    ->modifiedByScript(__DIR__ . '/assets.php', [
        'di' => $di,
        'params' => $params,
    ])
);

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

    $di->set('debugbar', $dix->lazyNew(StandardDebugBar::class)
        ->modifiedBy(function (DebugBar $debugBar) use ($di, $params) {
            $collector = $di->newInstance(MonologCollector::class, [
                'logger' => $di->get('logger'),
                'level' => Logger::getLevels()[$params['defaultLogLevel']],
            ]);
            assert($collector instanceof MonologCollector);
            $debugBar->addCollector($collector);
        })
    );
}
