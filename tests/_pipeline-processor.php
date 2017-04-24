<?php
use My\Web\Lib\App\WebApp;

$app =  WebApp::configure([
    __DIR__ . '/../config',
    __DIR__ . '/../config/web',
], 'di-*.php', array_merge(
    require __DIR__ . '/../config/params.php',
    [
        'env' => 'test',
        'defaultLogLevel' => 'EMERGENCY',
    ]
));

return [$app, 'processMiddlewarePipe'];
