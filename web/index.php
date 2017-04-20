<?php
use My\Web\Lib\WebApp;

require __DIR__ . '/../config/bootstrap.php';

WebApp::configure([
    __DIR__ . '/../config',
    __DIR__ . '/../config/web',
], 'di-*.php')->run();
