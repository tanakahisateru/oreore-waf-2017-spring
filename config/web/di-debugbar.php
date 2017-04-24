<?php
use Aura\Di\Container;
use DebugBar\StandardDebugBar;

/** @var Container $di */
/** @var array $params */

if ($params['env'] != 'dev') {
    return;
}

$di->set('debugbar', $di->lazy(function () use ($di) {
    $debugbar = new StandardDebugBar();
    // $debugbar->addCollector(...);
    return $debugbar;
}));
