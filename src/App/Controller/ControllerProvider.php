<?php
namespace Acme\App\Controller;

use Acme\App\Router\ControllerProviderInterface;

class ControllerProvider implements ControllerProviderInterface
{
    /**
     * @var array
     */
    protected $controllerFactories;

    /**
     * ControllerManager constructor.
     * @param array $controllerFactories
     */
    public function __construct(array $controllerFactories)
    {
        $this->controllerFactories = $controllerFactories;
    }

    /**
     * @param string $name
     * @return object
     */
    public function createController($name)
    {
        if (!isset($this->controllerFactories[$name])) {
            throw new \LogicException("Controller not defined for: " . $name);
        }

        $factory = $this->controllerFactories[$name];
        return call_user_func($factory);
    }
}
