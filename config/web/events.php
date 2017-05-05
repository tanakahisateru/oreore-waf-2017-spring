<?php
use My\Web\Lib\Container\Container;
use My\Web\Lib\Util\DebugBarInsertion;
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

// Log all
$events->attach('*', '*', function (EventInterface $event) use ($di) {
    $message = 'Event ' . $event->getName() . ' triggered at ' . get_class($event->getTarget());
    $di->get('logger')->debug($message);
});

// DebugBar
$events->attach('view', 'beforeRender', function (EventInterface $event) use ($di) {
    if ($di->has('debugbar')) {
        DebugBarInsertion::placeholder(
            $event->getParam('template'),
            'before-end-head',
            'before-end-body'
        );
    }
});
