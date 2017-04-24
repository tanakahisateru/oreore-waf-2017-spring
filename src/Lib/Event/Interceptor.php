<?php
namespace My\Web\Lib\Event;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\ResponseCollection;

class Interceptor implements InterceptorInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var callable
     */
    protected $lastResultCallback;

    /**
     * Interceptor constructor.
     * @param EventManagerInterface $events
     * @param callable $lastResultCallback
     */
    public function __construct(EventManagerInterface $events, callable $lastResultCallback)
    {
        $this->events = $events;
        $this->lastResultCallback = $lastResultCallback;
    }

    /**
     * @param object $object
     * @param callable $lastResultCallback
     * @return InterceptorInterface
     */
    public static function createForEventCapable($object, callable $lastResultCallback)
    {
        if (!($object instanceof EventsCapableInterface)) {
            return new NullInterceptor();
        }

        return new static($object->getEventManager(), $lastResultCallback);
    }

    /**
     * @inheritDoc
     */
    public function trigger($eventName, $target = null, $argv = [])
    {
        $results = $this->events->trigger($eventName, $target, $argv);
        $this->handleResults($results, $argv);
    }

    /**
     * @inheritDoc
     */
    public function triggerUntil(callable $callback, $eventName, $target = null, $argv = [])
    {
        $results = $this->events->triggerUntil($callback, $eventName, $target, $argv);
        $this->handleResults($results, $argv);
    }

    /**
     * @inheritDoc
     */
    public function triggerEvent(EventInterface $event)
    {
        $results = $this->events->triggerEvent($event);
        $this->handleEventResults($results, $event);
    }

    /**
     * @inheritDoc
     */
    public function triggerEventUntil(callable $callback, EventInterface $event)
    {
        $results = $this->events->triggerEventUntil($callback, $event);
        $this->handleEventResults($results, $event);
    }

    /**
     * @param ResponseCollection $results
     * @param array|object $argv
     * @throws InterceptorException
     */
    private function handleResults($results, $argv)
    {
        if ($results->stopped()) {
            throw new InterceptorException(call_user_func($this->lastResultCallback, $results->last(), $argv));
        }
    }

    /**
     * @param ResponseCollection $results
     * @param EventInterface $event
     * @throws InterceptorException
     */
    private function handleEventResults($results, $event)
    {
        if ($results->stopped()) {
            throw new InterceptorException(call_user_func($this->lastResultCallback, $results->last(), $event));
        }
    }
}
