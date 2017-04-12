<?php
use Aura\Di\Container;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;

/** @var Container $di */

$di->values['logHandlersDefault'] = $di->lazyArray([
    $di->lazyNew(RotatingFileHandler::class, [
        'filename' => __DIR__ . '/../../log/cli.log',
        'level' => \Monolog\Logger::INFO,
    ]),
    $di->lazyNew(StreamHandler::class, [
        'stream' => STDERR,
        'level' => \Monolog\Logger::INFO,
    ]),
]);
