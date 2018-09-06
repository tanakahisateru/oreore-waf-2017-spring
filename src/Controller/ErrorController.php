<?php
namespace Acme\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sumeko\Http\Exception as HttpException;

class ErrorController extends AbstractDualHtmlController
{
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
     * @param bool $isMobile
     * @return string
     */
    protected function defaultTemplateFolder($isMobile)
    {
        return $isMobile ? '_error/sp' : '_error';
    }

    /**
     * @param HttpException $error
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(HttpException $error, ServerRequestInterface $request)
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
