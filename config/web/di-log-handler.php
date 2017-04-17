<?php
use Aura\Di\Container;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/** @var Container $di */

$di->values['logHandlersDefault'] = $di->lazyArray([
    $di->lazyNew(RotatingFileHandler::class, [
        'filename' => __DIR__ . '/../../log/web.log',
        'level' => Logger::getLevels()[getenv('MY_APP_DEFAULT_LOG_LEVEL')],
    ])
]);
