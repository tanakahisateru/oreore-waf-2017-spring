<?php
namespace Acme\App\Router;

class ControllerProvider
{
    /**
     * @var callable[]
     */
    protected $factories;

    /**
     * ControllerManager constructor.
     * @param array $factories
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * @param string $name
     * @return object
     */
    public function createController($name)
    {
        if (!isset($this->factories[$name])) {
            throw new \LogicException("Controller not defined for: " . $name);
        }

        $factory = $this->factories[$name];
        return call_user_func($factory);
    }
}
