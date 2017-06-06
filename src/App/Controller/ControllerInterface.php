<?php
namespace Acme\App\Controller;

use Zend\EventManager\EventManagerAwareInterface;

interface ControllerInterface extends EventManagerAwareInterface
{
    /**
     * @param ResponseAgent $agent
     */
    public function setResponseAgent(ResponseAgent $agent);
}
