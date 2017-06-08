<?php
use Acme\App\Debug\Middleware\Generator\WhoopsErrorResponseGenerator;
use Acme\App\Middleware\Generator\ErrorResponseGenerator;
use Acme\App\Presentation\PresentationHelper;
use Acme\App\Presentation\PresentationHelperAwareInterface;
use Acme\App\Router\ActionDispatcher;
use Acme\App\Router\Router;
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\View;
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

/////////////////////////////////////////////////////////////////////
// PSR-15 pipeline

$di->set('middlewarePipe', $dix->lazyNew(MiddlewarePipe::class)
    ->modifiedByScript(__DIR__ . '/middleware.php', [
        'di' => $di,
        'params' => $params,
    ])
);

$di->set('errorResponseGenerator', $di->lazyNew(ErrorResponseGenerator::class, [
    'dispatcher' => $di->lazyGet('dispatcher'),
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
    'routes' => $di->lazyGet('routes'),
    'dispatcher' => $di->lazyGet('dispatcher'),
]));

$di->set('routes', $dix->lazyNew(RouterContainer::class, [
    'basepath' => null,
], [
    'setLoggerFactory' => $dix->newLocator('logger'),
])->modifiedByScript(__DIR__ . '/routing.php', [
    'di' => $di,
    'params' => $params,
]));

$di->set('dispatcher', $di->lazyNew(ActionDispatcher::class, [
    'controllerProvider' => $di->lazyGet('controllerProvider'),
    'streamFactory' => $di->lazyGet('http.streamFactory'),
]));

$di->set('urlGenerator', $di->lazyGetCall('routes', 'getGenerator'));

/////////////////////////////////////////////////////////////////////
// Controller helpers

$di->set('presentationHelper', $di->lazyNew(PresentationHelper::class, [
    'viewFactory' => $di->lazyGet('viewFactory'),
    'urlGenerator' => $di->lazyGet('urlGenerator'),
    'responsePrototype' => $di->lazyGetCall('http.responseFactory', 'createResponse'),
    'streamFactory' => $di->lazyGet('http.streamFactory'),
]));

$di->setters[PresentationHelperAwareInterface::class] = [
    'setPresentationHelper' => $di->lazyGet('presentationHelper'),
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
    'urlGenerator' => $di->lazyGet('urlGenerator'),
    'assetManager' => $di->lazyGet('assetManager'),
]));

/////////////////////////////////////////////////////////////////////
// Debug

if ($params['env'] == 'dev') {

    $di->set('errorResponseGenerator', $di->lazyNew(WhoopsErrorResponseGenerator::class, [
        'delegateGenerator' => $di->lazyNew(ErrorResponseGenerator::class, [
            'dispatcher' => $di->lazyGet('dispatcher'),
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
