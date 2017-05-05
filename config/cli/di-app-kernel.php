<?php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use My\Web\Lib\App\App;
use My\Web\Lib\Container\Container;
use Zend\EventManager\SharedEventManager;

/** @var Container $di */
/** @var array $params */

/////////////////////////////////////////////////////////////////////
// Application

$di->set('app', $di->lazyNew(App::class));

$di->set('logger', $di->lazyNew(Logger::class, [
    'name' => 'default',
    'handlers' => $di->lazyArray([
        $di->lazyNew(RotatingFileHandler::class, [
            'filename' => __DIR__ . '/../../log/cli.log',
            'level' => Logger::getLevels()[$params['defaultLogLevel']],
        ]),
        $di->lazyNew(StreamHandler::class, [
            'stream' => STDERR,
            'level' => Logger::getLevels()[$params['defaultLogLevel']],
        ], [
            'setFormatter' => $di->lazyNew(LineFormatter::class, [
                'format' => "  φ(.. )  [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            ])
        ]),
    ]),
    'processors' => [],
]));

$di->set('sharedEventManager', $di->lazyNew(SharedEventManager::class, [], [], $di->requireBuilder(
    __DIR__ . '/events.php', 'events', ['di' => $di]
)));
