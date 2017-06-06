<?php
namespace Acme\App\Router;

interface RouterAwareInterface
{
    /**
     * @param Router $router
     */
    public function setRouter(Router $router);
}
