<?php

use Acme\App\Debug\Middleware\DebugBarInsertion;
use Acme\App\Debug\Middleware\Generator\WhoopsErrorResponseGenerator;
use Acme\App\Middleware\Generator\ErrorResponseGenerator;
use Acme\App\Middleware\RoutingHandler;
use Acme\App\Middleware\WebAppBootstrap;
use Acme\App\Responder\Responder;
use Acme\App\Responder\ResponderAwareInterface;
use Acme\App\Router\Router;
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\ViewFactory;
use Aura\Di\Container;
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
use League\Plates\Engine as TemplateEngine;
use League\Plates\Extension\ExtensionInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Zend\Escaper\Escaper;
use Zend\EventManager\SharedEventManager;
use Zend\EventManager\SharedEventManagerInterface;
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
            'filename' => __DIR__ . '/../../var/log/web.log',
            'level' => Logger::getLevels()[$params['defaultLogLevel']],
        ]),
    ]),
    'processors' => [],
]));

//$di->set(SharedEventManagerInterface::class, $di->lazy(function () use ($di) {
//    $events = $di->newInstance(SharedEventManager::class);
//    ScriptRunner::which()->requires(__DIR__ . '/events.php')->with([
//        'di' => $di,
//        'events' => $events,
//    ])->run();
//    return $events;
//}));

$di->set(SharedEventManagerInterface::class, $dix->lazyNew(SharedEventManager::class)
    ->modifiedByScript(__DIR__ . '/events.php', [
        'di' => $di,
        'params' => $params,
    ])
);

/////////////////////////////////////////////////////////////////////
// PSR-17 factories

$di->set(ServerRequestFactoryInterface::class, $di->lazyNew(ServerRequestFactory::class));
$di->set(ResponseFactoryInterface::class, $di->lazyNew(ResponseFactory::class));
$di->set(UploadedFileFactoryInterface::class, $di->lazyNew(UploadedFileFactory::class));
$di->set(UriFactoryInterface::class, $di->lazyNew(UriFactory::class));
$di->set(StreamFactoryInterface::class, $di->lazyNew(StreamFactory::class));

/////////////////////////////////////////////////////////////////////
// PSR-15 pipeline

$di->set('middlewarePipe', $dix->lazyNew(MiddlewarePipe::class)
    ->modifiedBy(function (MiddlewarePipe $middlewarePipe) use ($di) {
        $middlewarePipe->pipe($di->get(ErrorHandler::class));
        if ($di->has('debugbar')) {
            $middlewarePipe->pipe($di->get(DebugBarInsertion::class));
        }
        $middlewarePipe->pipe($di->get(WebAppBootstrap::class));
        $middlewarePipe->pipe($di->get(RoutingHandler::class));
    })
);

$di->set(ErrorHandler::class, $dix->lazyNew(ErrorHandler::class, [
    'responseFactory' => function () use ($di) {
        return $di->get(ResponseFactoryInterface::class)->createResponse();
    },
    'responseGenerator' => $di->lazyGet('errorResponseGenerator'),
])->modifiedBy(function (ErrorHandler $errorHandler) use ($di) {
    $logger = $di->get('logger');
    $errorHandler->attachListener(function ($error, ServerRequestInterface $request) use ($logger) {
        if ($error instanceof \Sumeko\Http\ClientException) {
            $logger->info(sprintf("%s(\"%s\") - %s", get_class($error), $error->getMessage(), $request->getUri()));
        } else {
            /** @var Exception $error */
            $logger->error(sprintf("%s(\"%s\") - %s", get_class($error), $error->getMessage(), $request->getUri()));
            foreach (explode("\n", $error->getTraceAsString()) as $trace) {
                $logger->error($trace);
            }
        }
    });
}));

// Maybe overridden
$di->set('errorResponseGenerator', $di->lazyNew(ErrorResponseGenerator::class, [
    'dispatcher' => $di->lazyGetCall(Router::class, 'getDispatcher'),
    'controller' => 'error',
]));

$di->set(DebugBarInsertion::class, $di->lazyNew(DebugBarInsertion::class, [
    'debugbar' => $di->lazyGet('debugbar'),
    'baseUrl' => '/assets/debugbar',
    'streamFactory' => $di->lazyGet(StreamFactoryInterface::class),
]));

$di->set(WebAppBootstrap::class, $di->lazyNew(WebAppBootstrap::class, [
    'container' => $di,
    'appName' => 'app',
]));

$di->set(RoutingHandler::class, $di->lazyNew(RoutingHandler::class, [
    'router' => $di->lazyGet(Router::class),
]));

/////////////////////////////////////////////////////////////////////
// routing - dispatching

$di->set(Router::class, $dix->lazyNew(Router::class, [
    'controllerFactories' => require __DIR__ . '/controllers.php',
    'responseFactory' => $di->lazyGet(ResponseFactoryInterface::class),
    'pathPrefix' => null,
])->modifiedByScript(__DIR__ . '/routing.php', [
    'di' => $di,
    'params' => $params,
]));

/////////////////////////////////////////////////////////////////////
// Controller helpers

$di->set(Responder::class, $di->lazyNew(Responder::class, [
    'viewFactory' => $di->lazyGet(ViewFactory::class),
    'router' => $di->lazyGet(Router::class),
    'responseFactory' => $di->lazyGet(ResponseFactoryInterface::class),
]));

$di->setters[ResponderAwareInterface::class] = [
    'setResponder' => $di->lazyGet(Responder::class),
];

/////////////////////////////////////////////////////////////////////
// HTML rendering
// Template engine and view instances are modified often while request handling,
// so stable instances are their factories.

$di->set('templateEngineFactory', $dix->newFactory(TemplateEngine::class, [
    'directory' => __DIR__ . '/../../templates',
    'fileExtension' => null,
    // 'encoding' => 'utf-8',
])->modifiedBy(function (TemplateEngine $engine) use ($di) {
    $extension = $di->newInstance(EscaperExtension::class, [
        'escaper' => $di->newInstance(Escaper::class),
    ]);
    assert($extension instanceof ExtensionInterface);
    $engine->loadExtension($extension);
}));

$di->set(AssetManager::class, $dix->lazyNew(AssetManager::class)
    ->modifiedByScript(__DIR__ . '/assets.php', [
        'di' => $di,
        'params' => $params,
    ])
);

$di->set(ViewFactory::class, $di->lazyNew(ViewFactory::class, [
    'templateEngineFactory' => $di->lazyGet('templateEngineFactory'),
    'assetManager' => $di->lazyGet(AssetManager::class),
]));

/////////////////////////////////////////////////////////////////////
// Debug

if ($params['env'] == 'dev') {

    // override error screen
    $di->set('errorResponseGenerator', $di->lazyNew(WhoopsErrorResponseGenerator::class, [
        'delegateGenerator' => $di->lazyNew(ErrorResponseGenerator::class, [
            'dispatcher' => $di->lazyGetCall(Router::class, 'getDispatcher'),
            'controller' => 'error',
        ]),
        'responseFactory' => $di->lazyGet(ResponseFactoryInterface::class),
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
