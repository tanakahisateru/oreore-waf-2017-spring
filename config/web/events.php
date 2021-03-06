<?php
use Acme\App\Debug\Middleware\DebugBarInsertion;
use Aura\Di\Container;
use Zend\EventManager\EventInterface;
use Zend\EventManager\SharedEventManagerInterface;

/** @var SharedEventManagerInterface $this */
/** @var Container $di */
/** @var array $params */

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
$this->attach('*', '*', function (EventInterface $event) use ($di) {
    $message = 'Event ' . $event->getName() . ' triggered at ' . get_class($event->getTarget());
    $di->get('logger')->debug($message);
});

// DebugBar
$this->attach('view', 'beforeRender', function (EventInterface $event) use ($di) {
    if ($di->has('debugbar')) {
        DebugBarInsertion::placeholder(
            $event->getParam('template'),
            'before-end-head',
            'before-end-body'
        );
    }
});
