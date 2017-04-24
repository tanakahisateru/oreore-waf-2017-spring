<?php
use Aura\Di\Container;
use DebugBar\Bridge\MonologCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\StandardDebugBar;
use Monolog\Logger;

/** @var Container $di */
/** @var array $params */

if ($params['env'] != 'dev') {
    return;
}

$di->set('debugbar', $di->lazy(function () use ($di) {
    $debugbar = new StandardDebugBar();

    /** @var DataCollectorInterface $logCollector */
    $logCollector = $di->get('debugbar-logHandler');
    $debugbar->addCollector($logCollector);

    return $debugbar;
}));

$di->set('debugbar-logHandler', $di->lazyNew(MonologCollector::class, [
    'level' => Logger::getLevels()[$params['defaultLogLevel']],
]));
