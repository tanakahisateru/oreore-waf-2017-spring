<?php
use Aura\Di\Container;
use My\Web\Controller\SiteController;

/** @var Container $di */

$controllers['site'] = $di->lazyNew(SiteController::class, [
    'db' => $di->lazyGet('db1'),
], [
    'templateFolder' => 'site',
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
