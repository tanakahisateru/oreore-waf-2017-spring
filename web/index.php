<?php
use My\Web\App;

require __DIR__ . '/../config/bootstrap.php';

App::configure(__DIR__ . '/../config', [
    'dependencies.php',
], require __DIR__ . '/../config/params.php')->run();
