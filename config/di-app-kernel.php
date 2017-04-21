<?php
use Aura\Di\Container;
use Monolog\Logger;
use My\Web\Lib\App;
use Psr\Log\LoggerAwareInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

/** @var Container $di */

$di->set('logger', $di->lazyNew(Logger::class, [
    'name' => 'default',
    'handlers' => $di->lazyValue('logHandlersDefault'),
    'processors' => [],
]));

$di->setters[LoggerAwareInterface::class] = [
    'setLogger' => $di->lazyGet('logger'),
];

$di->setters[EventManagerAwareInterface::class] = [
    'setEventManager' => $di->lazyNew(EventManager::class, [
        'sharedEventManager' => $di->lazyGet('sharedEventManager'),
    ]),
];

$di->params[App::class] = [
    'container' => $di,
    'params' => $di->lazyRequire(__DIR__ . '/params.php'),
];
