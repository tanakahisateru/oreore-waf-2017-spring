<?php
namespace My\Web\Controller\General;

use My\Web\Lib\View\ViewAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Zend\EventManager\EventManagerAwareInterface;

interface HtmlPageControllerInterface extends
    LoggerAwareInterface, EventManagerAwareInterface, ViewAwareInterface
{
    /**
     */
    public function attachDefaultListeners();

    /**
     * @param string $path
     */
    public function templateFolder($path);

    /**
     * @param ServerRequestInterface $request
     * @param string $name
     */
    public function modifyTemplateFolderForMobile($request, $name = 'sp');

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render($template, array $data = []);

    /**
     * @param string $template
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function htmlResponse($template, array $data = [], $status = 200, array $headers = []);
}
