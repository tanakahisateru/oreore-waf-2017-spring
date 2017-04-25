<?php
use My\Web\Lib\App\WebApp;

return WebApp::configure([
    __DIR__ . '/..',
    __DIR__ . '/../web',
], 'di-*.php', array_merge(
    require __DIR__ . '/../params.php',
    [
        'env' => 'test',
        'defaultLogLevel' => 'EMERGENCY',
    ]
));
