<?php
use Aura\Router\RouterContainer;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DebugBar;
use DebugBar\StandardDebugBar;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use My\Web\Lib\App\WebApp;
use My\Web\Lib\Container\Alias\AliasContainer;
use My\Web\Lib\Container\Container;
use My\Web\Lib\Http\DiactorosHttpFactory;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\Middleware\WhoopsErrorResponseGenerator;
use My\Web\Lib\Router\Router;
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

$di->set('sharedEventManager', $di->lazyNew(SharedEventManager::class, [], [], $di->requireBuilder(
    __DIR__ . '/events.php', 'events', ['di' => $di]
)));

/////////////////////////////////////////////////////////////////////
// PSR-15 pipeline

$di->set('httpFactory', $di->lazyNew(DiactorosHttpFactory::class));

$di->set('middlewarePipe', $di->lazyNew(MiddlewarePipe::class, [], [], $di->requireBuilder(
    __DIR__ . '/middleware.php', 'pipe', ['di' => $di]
)));

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
    'setLoggerFactory' => $di->callbackReturns('logger'),
    'setMapBuilder' => $di->requireBuilder(__DIR__ . '/routing.php', 'map', [
        'di' => $di,
    ]),
]));

/////////////////////////////////////////////////////////////////////
// HTML rendering

$di->set('templateEngine', $di->lazyNew(TemplateEngine::class, [
    'directory' => __DIR__ . '/../../templates',
    'fileExtension' => null,
    'encoding' => 'utf-8',
], [], $di->requireBuilder(
    __DIR__ . '/template-functions.php', 'engine', ['di' => $di]
)));

$di->set('assetManager', $di->lazyNew(AssetManager::class, [], [], $di->requireBuilder(
    __DIR__ . '/assets.php', 'am', ['di' => $di]
)));

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
