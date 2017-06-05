<?php
use Acme\App\App;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Stratigility\NoopFinalHandler;

require __DIR__ . '/../config/bootstrap.php';

$app = App::configure([
    __DIR__ . '/../config/shared',
    __DIR__ . '/../config/web',
], 'di-*.php', require __DIR__ . '/../config/params.php');

$request = ServerRequestFactory::fromGlobals();
$server = Server::createServerFromRequest($app->getService('middlewarePipe'), $request);
$server->setEmitter(new SapiEmitter());
$server->listen(new NoopFinalHandler());
