<?php
namespace Acme\App\Router;

interface ControllerProviderInterface
{


    public function createController($controller);
}
