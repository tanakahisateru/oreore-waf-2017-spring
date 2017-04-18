<?php
use My\Web\WebApp;

require __DIR__ . '/../config/bootstrap.php';

WebApp::configure([
    __DIR__ . '/../config',
    __DIR__ . '/../config/web',
], 'di-*.php')->run();
