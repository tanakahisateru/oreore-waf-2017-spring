<?php
namespace Acme\App\Controller;

use Zend\EventManager\EventManagerAwareTrait;

trait ControllerTrait
{
    use EventManagerAwareTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['controller'];

    /**
     * @var ResponseAgent
     */
    protected $responseAgent;

    /**
     * @param ResponseAgent $responseAgent
     */
    public function setResponseAgent(ResponseAgent $responseAgent)
    {
        $this->responseAgent = $responseAgent;
    }

    /**
     * Implement default event listeners here.
     */
    public function attachDefaultListeners()
    {
    }
}
