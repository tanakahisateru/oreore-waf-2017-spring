<?php
use Aura\Di\Container;

/** @var Container $di */

$di->set('app', $di->lazyNew(\My\Web\WebApp::class, [
    'container' => $di,
    'params' => $di->lazyRequire(__DIR__ . '/../params.php'),
]));
