<?php
use My\Web\App;

require __DIR__ . '/../config/bootstrap.php';

$app = App::configure(__DIR__ . '/../config', [
    'dependencies.php',
]);

$app->getLogger()->info('console kicked');
