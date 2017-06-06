<?php
namespace Acme\Controller;

use Acme\App\Controller\ControllerInterface;
use Acme\App\Controller\ControllerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorController implements ControllerInterface
{
    use ControllerTrait;

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

        $view = $this->responseAgent->createView();

        return $this->responseAgent->htmlResponse($view->render($template, [
            'statusCode' => $statusCode,
            'reasonPhrase' => $reasonPhrase,
            'request' => $request,
        ]));
    }
}
