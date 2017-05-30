<?php
namespace My\Web\Controller\General;

use Interop\Http\Factory\ResponseFactoryInterface;
use My\Web\Lib\View\ViewEngineAwareInterface;
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
