<?php
use Aura\Di\Container;
use Aura\Dispatcher\Dispatcher;

/** @var Container $di */
/** @var Dispatcher $dispatcher */

$dispatcher->addObjects([
    'site' => $di->lazyNew(\My\Web\Controller\SiteController::class),
]);
