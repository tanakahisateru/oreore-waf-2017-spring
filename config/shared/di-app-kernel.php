<?php

use Acme\App\App;
use Aura\Di\Container;
use Psr\Log\LoggerAwareInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\SharedEventManagerInterface;

/** @var Container $di */

$di->setters[LoggerAwareInterface::class] = [
    'setLogger' => $di->lazyGet('logger'),
];

$di->setters[EventManagerAwareInterface::class] = [
    'setEventManager' => $di->lazyNew(EventManager::class, [
        'sharedEventManager' => $di->lazyGet(SharedEventManagerInterface::class),
    ]),
];

$di->params[App::class] = [
    'container' => $di,
    'params' => $di->lazyRequire(__DIR__ . '/params.php'),
];

$di->set('app', $di->lazyNew(App::class, ['container' => $di]));
