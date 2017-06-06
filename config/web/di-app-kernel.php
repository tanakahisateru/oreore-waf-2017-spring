<?php
use Acme\App\Http\StreamFactoryAwareTrait;
use Acme\App\Middleware\Generator\ErrorResponseGenerator;
use Acme\App\Middleware\Generator\WhoopsErrorResponseGenerator;
use Acme\App\Router\Router;
use Acme\App\Router\RouterAwareInterface;
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\View;
use Acme\App\View\ViewFactoryAwareInterface;
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
use Psr\Http\Message\ServerRequestInterface;
use Zend\Escaper\Escaper;
use Zend\EventManager\SharedEventManager;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\MiddlewarePipe;

/** @var Container $di */
/** @var array $params */

$dix = ContainerExtension::createFrom($di);

/////////////////////////////////////////////////////////////////////
// Application

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

$di->setters[StreamFactoryAwareTrait::class] = [
    'setStreamFactory' => $di->lazyGet('http.streamFactory'),
];

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
    'routerContainer' => $di->lazyGet('routerContainer'),
    'controllerFactories' => $di->lazyValue('controllerFactories'),
]));

$di->set('routerContainer', $dix->lazyNew(RouterContainer::class, [
    'basepath' => null,
], [
    'setLoggerFactory' => $dix->newLocator('logger'),
])->modifiedByScript(__DIR__ . '/routing.php', [
    'di' => $di,
    'params' => $params,
]));

$di->setters[RouterAwareInterface::class] = [
    'setRouter' => $di->lazyGet('router'),
];

/////////////////////////////////////////////////////////////////////
// HTML rendering

$di->set('templateEngineFactory', $dix->newFactory(Engine::class, [
    'directory' => __DIR__ . '/../../templates',
    'fileExtension' => null,
    // 'encoding' => 'utf-8',
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

$di->set('viewFactory', $di->newFactory(View::class, [
    'templateEngineFactory' => $di->lazyGet('templateEngineFactory'),
    'routerContainer' => $di->lazyGet('routerContainer'),
    'assetManager' => $di->lazyGet('assetManager'),
]));

$di->setters[ViewFactoryAwareInterface::class] = [
    'setViewFactory' => $di->lazyGet('viewFactory'),
];

/////////////////////////////////////////////////////////////////////
// Debug

if ($params['env'] == 'dev') {

    $di->set('errorResponseGenerator', $di->lazyNew(WhoopsErrorResponseGenerator::class, [
        'delegateGenerator' => $di->lazyNew(ErrorResponseGenerator::class, [
            'router' => $di->lazyGet('router'),
            'controller' => 'error',
        ])
    ]));

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
