<?php
use My\Web\Lib\Container\Container;
use Zend\EventManager\EventInterface;
use Zend\EventManager\SharedEventManagerInterface;

/** @var Container $di */
/** @var SharedEventManagerInterface $events */

$events->attach('*', '*', function (EventInterface $event) use($di) {
    $message = 'Event ' . $event->getName() . ' triggered at ' . get_class($event->getTarget());
    $di->get('logger')->debug($message);
});
