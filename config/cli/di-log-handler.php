<?php
use Aura\Di\Container;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/** @var Container $di */
/** @var array $params */

$di->values['logHandlersDefault'] = $di->lazyArray([
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
]);
