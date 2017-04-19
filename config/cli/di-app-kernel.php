<?php
use Aura\Di\Container;
use My\Web\App;

/** @var Container $di */

$di->set('app', $di->lazyNew(App::class));
