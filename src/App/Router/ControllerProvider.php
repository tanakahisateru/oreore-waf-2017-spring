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
    public function createController(string $name)
    {
        $elements = explode('.', $name);
        $node = $this->factories;

        do {
            $element = array_shift($elements);
            if (!isset($node[$element])) {
                throw new \LogicException("Controller not defined for: " . $name);
            }
            $node = $node[$element];
        } while (!empty($elements));

        $controller = call_user_func($node);

        if ($controller instanceof EventsCapableInterface) {
            $controller->getEventManager()->trigger(static::EVENT_INSTANCE_READY, $controller);
        }

        return $controller;
    }
}
