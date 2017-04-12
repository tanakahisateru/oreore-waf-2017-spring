<?php
use My\Web\App;

require __DIR__ . '/../config/bootstrap.php';

$app = App::configure(__DIR__ . '/../config', [
    'dependencies.php',
], require __DIR__ . '/../config/params.php');

$app->getLogger()->info('console kicked');
