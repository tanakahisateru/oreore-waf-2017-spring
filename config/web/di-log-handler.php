<?php
use Aura\Di\Container;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/** @var Container $di */
/** @var array $params */

$di->values['logHandlersDefault'] = $di->lazyArray([
    $di->lazyNew(RotatingFileHandler::class, [
        'filename' => __DIR__ . '/../../log/web.log',
        'level' => Logger::getLevels()[$params['defaultLogLevel']],
    ]),

    // DebugBar or Null
    $di->lazy(function () use ($di) {
        return $di->has('debugbar-logHandler') ?
            $di->get('debugbar-logHandler') : new NullHandler();
    }),
]);
