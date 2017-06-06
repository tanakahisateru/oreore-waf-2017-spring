<?php
namespace Acme\App\Router;

trait RouterAwareTrait
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }
}
