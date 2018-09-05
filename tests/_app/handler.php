<?php

use Acme\App\App;

$app = App::configure([
    __DIR__ . '/../../config/shared',
    __DIR__ . '/../../config/web',
    __DIR__
], 'di-*.php', array_merge(
    require __DIR__ . '/../../config/params.php',
    [
        'env' => 'test',
        'defaultLogLevel' => 'EMERGENCY',
    ]
));

return $app->getContainer()->get('middlewarePipe');
