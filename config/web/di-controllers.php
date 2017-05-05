<?php
use Aura\Di\Container;
use Aura\Dispatcher\Dispatcher;
use My\Web\Controller\ErrorController;
use My\Web\Controller\SiteController;

/** @var Container $di */
/** @var array $params */

$di->set('routerDispatcher', $di->lazyNew(Dispatcher::class, [
    'objects' => [

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
    ],
]));
