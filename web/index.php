<?php
use My\Web\Lib\App\WebApp;

require __DIR__ . '/../config/bootstrap.php';

WebApp::configure([
    __DIR__ . '/../config',
    __DIR__ . '/../config/web',
], 'di-*.php', require __DIR__ . '/../config/params.php')->run();
