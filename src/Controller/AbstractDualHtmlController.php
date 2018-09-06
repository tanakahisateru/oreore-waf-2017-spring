<?php
namespace Acme\Controller;

use Acme\App\Responder\ResponderAwareInterface;
use Acme\App\Responder\ResponderAwareTrait;
use Acme\App\View\View;
use Acme\Util\Mobile;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

abstract class AbstractDualHtmlController implements
    ResponderAwareInterface,
    EventManagerAwareInterface,
    LoggerAwareInterface
{
    use ResponderAwareTrait;
    use EventManagerAwareTrait;
    use LoggerAwareTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['controller'];

    /**
     * @param bool $isMobile
     * @return string
     */
    abstract protected function defaultTemplateFolder(bool $isMobile): string;

    /**
     *
     */
    public function attachDefaultListeners()
    {

    }

    /**
     * @param ServerRequestInterface $request
     * @return View
     */
    protected function createView(ServerRequestInterface $request): View
    {
        $view = $this->createViewPrototype();

        $mobileDetect = Mobile::detect($request);
        $view->setFolder('current', $this->defaultTemplateFolder($mobileDetect->isMobile()));

        return $view;
    }
}
