<?php
namespace Acme\Controller;

use Acme\App\Controller\PresentationHelperAwareInterface;
use Acme\App\Controller\PresentationHelperAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sumeko\Http\Exception as HttpException;

class ErrorController implements PresentationHelperAwareInterface
{
    use PresentationHelperAwareTrait;

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

        $view = $this->createView();

        return $this->htmlResponse($view->render($template, [
            'error' => $error,
            'request' => $request,
        ]), $statusCode);
    }
}
