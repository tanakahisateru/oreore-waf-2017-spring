<?php
namespace Acme\Controller\General;

use Acme\App\Http\StreamFactoryAwareTrait;
use Acme\App\Router\RouterAwareTrait;
use Acme\App\View\ViewFactoryAwareTrait;
use Acme\Util\Mobile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareTrait;
use Zend\EventManager\EventManagerAwareTrait;

trait HtmlPageControllerTrait
{
    use LoggerAwareTrait;
    use EventManagerAwareTrait;
    use StreamFactoryAwareTrait;
    use RouterAwareTrait;
    use ViewFactoryAwareTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['controller'];

    /**
     * @var string
     */
    protected $currentTemplateFolder;

    /**
     * @var callable
     */
    protected $templateFolderModifier;

    /**
     * @var ResponseInterface
     */
    protected $responsePrototype;

    /**
     * Implement default event listeners here.
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
     * @param ResponseInterface $responsePrototype
     */
    public function setResponsePrototype(ResponseInterface $responsePrototype)
    {
        $this->responsePrototype = $responsePrototype;
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

        $view = $this->createView();
        $view->setFolder('current', $path);

        return $view->render($template, $data);
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
        $response = $this->responsePrototype->withStatus($status)
            ->withHeader('Content-Type', 'text/html; charset=utf-8');

        foreach ($headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }

        $response = $response->withBody($this->streamFactory->createStream($html));

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
        $response = $this->responsePrototype->withStatus($status)
            ->withHeader('Content-Type', 'text/plain; charset=utf-8');

        foreach ($headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }

        $response = $response->withBody($this->streamFactory->createStream($text));

        return $response;
    }

    /**
     * @param string $url
     * @return ResponseInterface
     */
    public function redirectResponse($url)
    {
        return $this->responsePrototype->withStatus(302)->withHeader('Location', $url);
    }

    /**
     * @param string $route
     * @param array $data
     * @return ResponseInterface
     */
    public function redirectResponseToRoute($route, $data = [])
    {
        return $this->redirectResponse($this->router->urlTo($route, $data));
    }
}
