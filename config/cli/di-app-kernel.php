<?php

use Aura\Di\Container;
use Lapaz\PlainPhp\ScriptRunner;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Zend\EventManager\SharedEventManager;

/** @var Container $di */
/** @var array $params */

/////////////////////////////////////////////////////////////////////
// Application

$di->set('logger', $di->lazyNew(Logger::class, [
    'name' => 'default',
    'handlers' => $di->lazyArray([
        $di->lazyNew(RotatingFileHandler::class, [
            'filename' => __DIR__ . '/../../var/log/cli.log',
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


$di->set('sharedEventManager', $di->lazy(function () use ($di) {
    $events = $di->newInstance(SharedEventManager::class);
    ScriptRunner::which()->requires(__DIR__ . '/events.php')->with([
        'di' => $di,
        'events' => $events,
    ])->run();
    return $events;
}));
