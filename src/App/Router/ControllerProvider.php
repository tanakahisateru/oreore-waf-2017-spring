<?php
namespace Acme\App\Router;

use Zend\EventManager\EventsCapableInterface;

class ControllerProvider
{
    const EVENT_INSTANCE_READY = 'instanceReady';

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
        $controller = call_user_func($factory);

        if ($controller instanceof EventsCapableInterface) {
            $controller->getEventManager()->trigger(static::EVENT_INSTANCE_READY, $controller);
        }

        return $controller;
    }
}
