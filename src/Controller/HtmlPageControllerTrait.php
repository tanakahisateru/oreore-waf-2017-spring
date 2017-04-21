<?php
namespace My\Web\Controller;

use My\Web\Lib\Http\HttpFactoryInjectionTrait;
use My\Web\Lib\Util\Mobile;
use My\Web\Lib\View\ViewInjectionTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait HtmlPageControllerTrait
{
    use HttpFactoryInjectionTrait;
    use ViewInjectionTrait;

    /**
     * @var string
     */
    protected $currentTemplateFolder;

    /**
     * @var callable
     */
    protected $templateFolderModifier;

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
