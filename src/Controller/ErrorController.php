<?php
namespace My\Web\Controller;

use My\Web\Controller\General\HtmlPageControllerInterface;
use My\Web\Controller\General\HtmlPageControllerTrait;
use Psr\Http\Message\ResponseInterface;

class ErrorController implements
    HtmlPageControllerInterface
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
    public function actionIndex($statusCode, $reasonPhrase, $request, ResponseInterface $response)
    {
        if (isset($this->statusToTemplate[$statusCode])) {
            $template = $this->statusToTemplate[$statusCode];
        } else {
            $template = $this->defaultTemplate;
        }

        $response = $response->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($this->createView()->render($template, [
            'statusCode' => $statusCode,
            'reasonPhrase' => $reasonPhrase,
            'request' => $request,
        ]));

        return $response;
    }
}
