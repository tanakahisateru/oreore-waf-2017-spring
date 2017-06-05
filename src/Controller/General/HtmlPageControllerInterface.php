<?php
namespace Acme\Controller\General;

use Acme\App\View\ViewEngineAwareInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Zend\EventManager\EventManagerAwareInterface;

interface HtmlPageControllerInterface extends
    LoggerAwareInterface, EventManagerAwareInterface, ViewEngineAwareInterface
{
    /**
     */
    public function attachDefaultListeners();

    /**
     * @param string $path
     */
    public function templateFolder($path);

    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function setResponseFactory(ResponseFactoryInterface $responseFactory);
}
