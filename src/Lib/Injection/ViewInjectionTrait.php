<?php
namespace My\Web\Lib\Injection;

use My\Web\Lib\Util\Mobile;
use My\Web\Lib\View\View;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait ViewInjectionTrait
{
    /**
     * @var View
     */
    protected $view;

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
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param View $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return string
     */
    public function getCurrentTemplateFolder()
    {
        return $this->currentTemplateFolder;
    }

    /**
     * @param string $path
     */
    public function setCurrentTemplateFolder($path)
    {
        $this->currentTemplateFolder = $path;
    }

    /**
     * @param RequestInterface $request
     * @param string $name
     */
    public function modifyTemplateFolderForMobile($request, $name = 'sp')
    {
        $agent = Mobile::detect($request);
        if ($agent->isMobile()) {
            $this->templateFolderModifier = function ($base) use ($name) {
                return rtrim($base, '/') . '/' . trim($name, '/');
            };
        }
    }

    /**
     * @param ResponseInterface $response
     * @param string $template
     * @param array $data
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, $template, array $data = [])
    {
        $path = $this->getCurrentTemplateFolder();
        if ($this->templateFolderModifier) {
            $path = call_user_func($this->templateFolderModifier, $path);
        }

        $view = $this->getView();
        $view->setTemplateFolder('current', $path);
        return $view->render($response, $template, $data);
    }
}
