<?php
use Aura\Di\Container;
use Zend\EventManager\EventInterface;
use Zend\EventManager\SharedEventManagerInterface;

/** @var Container $di */
/** @var SharedEventManagerInterface $events */

// Example to monitor a certain event manager behavior:
//
// $events->attach('controller', '*', function (EventInterface $event) use($di) {
//     $message = 'Controller event ' . $event->getName() . ' triggered at ' . get_class($event->getTarget());
//     $di->get('logger')->debug($message);
// });
//
// $events->attach(SiteController::class, '*', function (EventInterface $event) use($di) {
//     $message = 'Controller event ' . $event->getName() . ' triggered at ' . get_class($event->getTarget());
//     $di->get('logger')->debug($message);
// });

$events->attach('*', '*', function (EventInterface $event) use($di) {
    $message = 'Event ' . $event->getName() . ' triggered at ' . get_class($event->getTarget());
    $di->get('logger')->debug($message);
});
