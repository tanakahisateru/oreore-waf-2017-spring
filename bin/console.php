<?php
use My\Web\App;

require __DIR__ . '/../config/bootstrap.php';

$app = App::configure([
    __DIR__ . '/../config',
    __DIR__ . '/../config/cli',
], [
    'di-*.php',
], require __DIR__ . '/../config/params.php');

$app->getLogger()->info('console kicked');
fprintf(STDOUT, "This is STDOUT\n");
fprintf(STDERR, "This is STDERR\n");
$app->getLogger()->info('console done');
