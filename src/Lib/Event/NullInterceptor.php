<?php
namespace My\Web\Lib\Event;

use Zend\EventManager\EventInterface;

class NullInterceptor implements InterceptorInterface
{
    /**
     * @inheritDoc
     */
    public function trigger($eventName, $target = null, $argv = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function triggerUntil(callable $callback, $eventName, $target = null, $argv = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function triggerEvent(EventInterface $event)
    {
    }

    /**
     * @inheritDoc
     */
    public function triggerEventUntil(callable $callback, EventInterface $event)
    {
    }
}
