<?php
use Acme\App\Controller\ControllerInterface;
use Acme\App\Controller\ControllerProvider;
use Acme\Controller\ErrorController;
use Acme\Controller\SiteController;
use Aura\Di\Container;

/** @var Container $di */

$di->setters[ControllerInterface::class] = [
    'setResponseAgent' => $di->lazyGet('responseAgent'),
];

$di->set('controllerProvider', $di->lazyNew(ControllerProvider::class, ['controllerFactories' => [

    'error' => $di->newFactory(ErrorController::class, [
        'statusToTemplate' => [
            404 => '_error/404.php',
        ],
        'defaultTemplate' => '_error/default.php'
    ]),

    'site' => $di->newFactory(SiteController::class, [
        'db' => $di->lazyGet('db1'),
    ]),

    // Example:
    // 'admin.news' => $di->lazyNew(Admin\NewsController::class, [
    //     'db' => $di->lazyGet('db1'),
    //     'backendDb' => $di->lazyGet('db2'),
    // ], [
    //     'setAuditTrailStamper' => $di->get('auditTrailStamper'),
    // ]),
]]));
