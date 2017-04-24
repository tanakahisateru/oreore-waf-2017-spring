<?php
namespace My\Web\Lib\Event;

use Zend\EventManager\EventInterface;

interface InterceptorInterface
{
    /**
     * @param string $eventName
     * @param object $target
     * @param array|object $argv
     * @throws InterceptorException
     */
    public function trigger($eventName, $target = null, $argv = []);

    /**
     * @param callable $callback
     * @param string $eventName
     * @param object $target
     * @param array|object $argv
     * @throws InterceptorException
     */
    public function triggerUntil(callable $callback, $eventName, $target = null, $argv = []);

    /**
     * @param EventInterface $event
     * @throws InterceptorException
     */
    public function triggerEvent(EventInterface $event);

    /**
     * @param callable $callback
     * @param EventInterface $event
     * @throws InterceptorException
     */
    public function triggerEventUntil(callable $callback, EventInterface $event);
}
