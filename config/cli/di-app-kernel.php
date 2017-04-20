<?php
use Aura\Di\Container;
use My\Web\Lib\App;

/** @var Container $di */

$di->set('app', $di->lazyNew(App::class));
