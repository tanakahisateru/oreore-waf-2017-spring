<?php
namespace Acme\Controller;

use Acme\App\Presentation\PresentationHelperAwareInterface;
use Acme\App\Presentation\PresentationHelperAwareTrait;
use Acme\App\View\View;
use Acme\Util\Mobile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sumeko\Http\Exception as HttpException;

class ErrorController implements PresentationHelperAwareInterface
{
    use PresentationHelperAwareTrait;

    const TEMPLATE_FOLDER = '_error';

    /**
     * @var array
     */
    protected $statusToTemplate;

    /**
     * @var string
     */
    protected $defaultTemplate;

    /**
     * ErrorController constructor.
     * @param array $statusToTemplate
     * @param string $defaultTemplate
     */
    public function __construct(array $statusToTemplate, $defaultTemplate)
    {
        $this->statusToTemplate = $statusToTemplate;
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * @param ServerRequestInterface $request
     * @return View
     */
    protected function createView(ServerRequestInterface $request)
    {
        $view = $this->createViewPrototype();
        $view->setFolder('current', static::TEMPLATE_FOLDER);

        $mobileDetect = Mobile::detect($request);
        if ($mobileDetect->isMobile()) {
            $view->setFolder('current', static::TEMPLATE_FOLDER . '/sp');
        }

        return $view;
    }

    /**
     * @param HttpException $error
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function __invoke(HttpException $error, ServerRequestInterface $request, ResponseInterface $response)
    {
        $statusCode = $error->getCode();

        if (isset($this->statusToTemplate[$statusCode])) {
            $template = $this->statusToTemplate[$statusCode];
        } else {
            $template = $this->defaultTemplate;
        }

        $view = $this->createView($request);

        return $this->htmlResponse($view->render($template, [
            'error' => $error,
            'request' => $request,
        ]), $statusCode);
    }
}
