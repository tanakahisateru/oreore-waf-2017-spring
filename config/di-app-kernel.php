<?php
use Aura\Di\Container;
use Monolog\Logger;
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
