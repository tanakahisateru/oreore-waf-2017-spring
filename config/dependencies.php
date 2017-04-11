<?php
use Aura\Di\Container;

/** @var Container $di */

//////////////////////////////////////////////
// core
$di->set('app', $di->lazyNew(\My\Web\App::class, [
    'container' => $di,
    'params' => [
        // misc params
    ],
]));

$di->set('logger', $di->lazyNew(\Monolog\Logger::class, [
    'name' => 'default',
    'handlers' => $di->lazyValue('logHandlers'),
    'processors' => [],
]));

$di->set('router', $di->lazyNew(\My\Web\Lib\Router\Router::class, [
    'routes' => $di->lazyNew(\Aura\Router\RouterContainer::class, [
        'basepath' => null,
    ])
]));

$di->set('view', $di->lazyNew(\My\Web\Lib\View\View::class, [
    'engineFactory' => $di->newFactory(\My\Web\Lib\View\TemplateEngine::class),
    'assetsFactory' => null,
    'routerFactory' => function () use($di) {
        return $di->get('router');
    },
]));


$di->params[\My\Web\Lib\Router\Router::class] = [
    'controllersBuilder' => function($dispatcher) use ($di) {
        \My\Web\App::includePhp(__DIR__, 'controllers.php', [
            'di' => $di,
            'dispatcher' => $dispatcher,
        ]);
    },
];

$di->setters[\Aura\Router\RouterContainer::class] = [
    'setMapBuilder' => function ($map) use ($di) {
        \My\Web\App::includePhp(__DIR__, 'routing.php', [
            'di' => $di,
            'map' => $map,
        ]);
    },
];

$di->params[\My\Web\Lib\View\TemplateEngine::class] = [
    'builder' => function ($engine) use ($di) {
        \My\Web\App::includePhp(__DIR__, 'templating.php', [
            'di' => $di,
            'engine' => $engine,
        ]);
    },
    'encoding' => 'utf-8',
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
$di->values['logHandlers'] = $di->lazyArray($logHandlers);

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
    'setView' => $di->lazyGet('view'),
    'setCurrentTemplateFolder' => 'site',
];
