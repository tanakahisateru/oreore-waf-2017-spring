<?php
use Aura\Di\Container;
use My\Web\Lib\Util\PlainPhp;

/** @var Container $di */

//////////////////////////////////////////////
// core
$di->set('logger', $di->lazyNew(\Monolog\Logger::class, [
    'name' => 'default',
    'handlers' => $di->lazyValue('logHandlersDefault'),
    'processors' => [],
]));

$di->set('router', $di->lazyNew(\My\Web\Lib\Router\Router::class, [
    'routes' => $di->lazyGet('routerContainer'),
    'dispatcher' => $di->lazyGet('routerDispatcher'),
]));

$di->set('routerContainer', $di->lazyNew(\Aura\Router\RouterContainer::class, [
    'basepath' => null,
], [
    'setMapBuilder' => function ($map) use ($di) {
        PlainPhp::runner()->with([
            'di' => $di,
            'map' => $map,
        ])->doRequire(__DIR__ . '/routing.php');
    },
]));

$di->set('routerDispatcher', $di->lazyNew(\Aura\Dispatcher\Dispatcher::class, [
    'object_param' => 'controller',
    'method_param' => 'action',
], [
    'setObjects' => $di->lazy(function () use ($di) {
        return PlainPhp::runner()->with([
            'di' => $di,
        ])->doRequire(__DIR__ . '/controllers.php');
    }),
]));

$di->set('templateEngine', $di->lazy(function () use($di) {
    $engine = $di->newInstance(\My\Web\Lib\View\TemplateEngine::class, [
        'directory' => __DIR__ . '/../templates',
        'fileExtension' => null,
        'encoding' => 'utf-8',
    ]);
    PlainPhp::runner()->with([
        'di' => $di,
        'engine' => $engine,
    ])->doRequire(__DIR__ . '/template-functions.php');
    return $engine;
}));

$di->params[\My\Web\Lib\View\View::class] = [
    'engineFactory' => function () use ($di) {
        return $di->get('templateEngine');
    },
    'assetsFactory' => null,
    'routerFactory' => function () use ($di) {
        return $di->get('router');
    },
];

//////////////////////////////////////////////
// log handlers
$logHandlers = [];
$logHandlers[] = $di->lazyNew(\Monolog\Handler\RotatingFileHandler::class, [
    'filename' => __DIR__ . '/../log/app.log',
    'level' => \Monolog\Logger::DEBUG,
]);

if (defined('STDERR')) {
    $logHandlers[] = $di->lazyNew(\Monolog\Handler\StreamHandler::class, [
        'stream' => STDERR,
        'level' => \Monolog\Logger::DEBUG,
    ]);
}
$di->values['logHandlersDefault'] = $di->lazyArray($logHandlers);

//////////////////////////////////////////////
// misc services
$di->set('db1', $di->lazyNew(Aura\Sql\ExtendedPdo::class, [
    'dsn' => getenv('MY_APP_DB_DSN'),
    'username' => getenv('MY_APP_DB_USERNAME'),
    'password' => getenv('MY_APP_DB_PASSWORD'),
]));

//////////////////////////////////////////////
// controller creation params
$di->params[\My\Web\Controller\SiteController::class] = [
    'db' => $di->lazyGet('db1'),
];

$di->setters[\My\Web\Controller\SiteController::class] = [
    'setLogger' => $di->lazyGet('logger'),
    'setView' => $di->lazyNew(\My\Web\Lib\View\View::class),
    'setCurrentTemplateFolder' => 'site',
];
