<?php
use Aura\Di\Container;

/** @var Container $di */

return [
    'site' => $di->lazyNew(\My\Web\Controller\SiteController::class),
];
