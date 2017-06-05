<?php
namespace Acme\Controller;

use Acme\Controller\General\HtmlPageControllerInterface;
use Acme\Controller\General\HtmlPageControllerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorController implements HtmlPageControllerInterface
{
    use HtmlPageControllerTrait;

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
     * @param $statusCode
     * @param $reasonPhrase
     * @param $request
     * @param $response
     * @return ResponseInterface
     */
    public function __invoke($statusCode, $reasonPhrase, ServerRequestInterface $request, ResponseInterface $response)
    {
        if (isset($this->statusToTemplate[$statusCode])) {
            $template = $this->statusToTemplate[$statusCode];
        } else {
            $template = $this->defaultTemplate;
        }

        $response = $response->withHeader('Content-Type', 'text/html');
        $view = $this->createView();
        $response->getBody()->write($view->render($template, [
            'statusCode' => $statusCode,
            'reasonPhrase' => $reasonPhrase,
            'request' => $request,
        ]));

        return $response;
    }
}
