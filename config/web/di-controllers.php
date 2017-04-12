<?php
use Aura\Di\Container;
use My\Web\Controller\SiteController;
use My\Web\Lib\View\View;
use My\Web\Lib\View\ViewAwareInterface;

/** @var Container $di */

$di->setters[ViewAwareInterface::class] = [
    'setView' => $di->lazyNew(View::class),
];

$controllers['site'] = $di->lazyNew(SiteController::class, [
    'db' => $di->lazyGet('db1'),
], [
    'setTemplateFolder' => 'site',
]);

// example:
// $controllers['admin.news'] = $di->lazyNew(SiteController::class, [
//     'db' => $di->lazyGet('db1'),
//     'backendDb' => $di->lazyGet('db2'),
// ], [
//     'setTemplateFolder' => 'admin/news',
//     'setAuditTrailStamper' => $di->get('auditTrailStamper'),
// ]);

$di->values['controllers'] = $di->lazyArray($controllers);
