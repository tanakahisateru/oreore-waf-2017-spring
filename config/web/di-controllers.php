<?php
use Acme\App\Router\ControllerProvider;
use Acme\Controller\ErrorController;
use Acme\Controller\SiteController;
use Aura\Di\Container;

/** @var Container $di */

$di->set('controllerProvider', $di->lazyNew(ControllerProvider::class, ['factories' => [

    'error' => $di->newFactory(ErrorController::class, [
        'statusToTemplate' => [
            404 => 'current::404.php',
        ],
        'defaultTemplate' => 'current::default.php'
    ]),

    'site' => $di->newFactory(SiteController::class, [
        'db' => $di->lazyGet('db1'),
    ]),

    // Example:
    // 'admin' => [
    //     'news' => $di->lazyNew(Admin\NewsController::class, [
    //         'db' => $di->lazyGet('db1'),
    //         'backendDb' => $di->lazyGet('db2'),
    //     ], [
    //         'setAuditTrailStamper' => $di->get('auditTrailStamper'),
    //     ]),  // --- Matches to route: admin.news.*
    // ],
]]));
