<?php
namespace My\Web\Controller\General;

use Interop\Http\Factory\ResponseFactoryInterface;
use My\Web\Lib\Util\Mobile;
use My\Web\Lib\View\ViewEngineAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareTrait;
use Zend\EventManager\EventManagerAwareTrait;

trait HtmlPageControllerTrait
{
    use LoggerAwareTrait;
    use EventManagerAwareTrait;
    use ViewEngineAwareTrait;

    /**
     * @var string
     */
    protected $currentTemplateFolder;

    /**
     * @var callable
     */
    protected $templateFolderModifier;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     *
     */
    public function attachDefaultListeners()
    {
    }

    /**
     * @param string $path
     */
    public function templateFolder($path)
    {
        $this->currentTemplateFolder = $path;
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $name
     */
    public function modifyTemplateFolderForMobile($request, $name = 'sp')
    {
        assert($request instanceof ServerRequestInterface);
        $agent = Mobile::detect($request);
        if ($agent->isMobile()) {
            $this->templateFolderModifier = function ($base) use ($name) {
                return rtrim($base, '/') . '/' . trim($name, '/');
            };
        }
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render($template, array $data = [])
    {
        $path = $this->currentTemplateFolder;
        if ($this->templateFolderModifier) {
            $path = call_user_func($this->templateFolderModifier, $path);
        }

        $view = $this->viewEngine->createView();
        $view->setFolder('current', $path);

        return $view->render($template, $data);
    }

    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function setResponseFactory(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param string $template
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function templatedHtmlResponse($template, array $data = [], $status = 200, array $headers = [])
    {
        $html = $this->render($template, $data);
        return $this->htmlResponse($html, $status, $headers);
    }

    /**
     * @param string $html
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function htmlResponse($html, $status = 200, array $headers = [])
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', 'text/html; charset=utf-8');

        foreach ($headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }

        $response->getBody()->write($html);

        return $response;
    }

    /**
     * @param string $text
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function textResponse($text, $status = 200, array $headers = [])
    {
        $response = $this->responseFactory->createResponse($status)
            ->withHeader('Content-Type', 'text/plain; charset=utf-8');

        foreach ($headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }

        $response->getBody()->write($text);

        return $response;
    }
}
