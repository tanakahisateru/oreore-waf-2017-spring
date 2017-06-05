#!/usr/bin/env php
<?php
use Acme\App\App;

require __DIR__ . '/../config/bootstrap.php';

$app = App::configure([
    __DIR__ . '/../config/shared',
    __DIR__ . '/../config/cli',
], 'di-*.php', require __DIR__ . '/../config/params.php');

$app->getEventManager()->attach('exit', function () {
    echo "bye-bye\n";
});

$logger = $app->getContainer()->get('logger');
assert($logger instanceof \Psr\Log\LoggerInterface);

$logger->info('console kicked');
fprintf(STDOUT, "This is STDOUT\n");
fprintf(STDERR, "This is STDERR\n");
$logger->info('console done');

$app->getEventManager()->trigger('exit', $app);
