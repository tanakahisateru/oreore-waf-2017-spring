<?php
use My\Web\Lib\App\App;
use My\Web\Lib\Container\Container;
use Psr\Log\LoggerAwareInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;

/** @var Container $di */
/** @var array $params */

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