<?php
namespace My\Web\Controller;

use My\Web\Lib\Http\HttpFactoryInterface;
use My\Web\Lib\Util\Mobile;
use My\Web\Lib\View\View;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait HtmlPageControllerTrait
{
    /**
     * @var string
     */
    protected $currentTemplateFolder;

    /**
     * @var callable
     */
    protected $templateFolderModifier;

    /**
     * @return View
     */
    abstract public function createView();

    /**
     * @return HttpFactoryInterface
     */
    abstract public function getHttpFactory();

    /**
     * @param string $path
     */
    public function templateFolder($path)
    {
        $this->currentTemplateFolder = $path;
    }

    /**
     * @param RequestInterface $request
     * @param string $name
     */
    public function modifyTemplateFolderForMobile(RequestInterface $request, $name = 'sp')
    {
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
    public function htmlResponse($template, array $data = [], $status = 200, array $headers = [])
    {
        $html = $this->render($template, $data);
        return $this->getHttpFactory()->createHtmlResponse($html, $status, $headers);
    }
}
