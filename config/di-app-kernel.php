<?php
use Aura\Di\Container;
use Monolog\Logger;
use My\Web\App;
use Psr\Log\LoggerAwareInterface;

/** @var Container $di */

$di->set('logger', $di->lazyNew(Logger::class, [
    'name' => 'default',
    'handlers' => $di->lazyValue('logHandlersDefault'),
    'processors' => [],
]));

$di->setters[LoggerAwareInterface::class] = [
    'setLogger' => $di->lazyGet('logger'),
];

$di->params[App::class] = [
    'container' => $di,
    'params' => $di->lazyRequire(__DIR__ . '/params.php'),
];
