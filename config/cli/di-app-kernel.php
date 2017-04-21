<?php
use Aura\Di\Container;
use My\Web\Lib\App;
use My\Web\Lib\Util\PlainPhp;
use Zend\EventManager\SharedEventManager;

/** @var Container $di */

$di->set('app', $di->lazyNew(App::class));

$di->set('sharedEventManager', $di->lazy(function() use ($di) {
    $events = new SharedEventManager();
    PlainPhp::runner()->with([
        'di' => $di,
        'events' => $events,
    ])->doRequire(__DIR__ . '/events.php');
    return $events;
}));
