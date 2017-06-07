<?php
namespace Acme\App\Controller;

use Zend\EventManager\EventManagerAwareTrait;

trait ControllerEventManagerAwareTrait
{
    use EventManagerAwareTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['controller'];

    /**
     * Implement default event listeners here.
     */
    public function attachDefaultListeners()
    {
    }
}
