<?php
namespace Acme\Controller;

use Acme\Controller\General\HtmlPageControllerInterface;
use Acme\Controller\General\HtmlPageControllerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LogLevel;
use Zend\EventManager\EventInterface;

class SiteController implements HtmlPageControllerInterface
{
    use HtmlPageControllerTrait;

    /**
     * @var \PDO
     */
    protected $db;

    /**
     * SiteController constructor.
     * @param \PDO $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->templateFolder('site');
    }

    /**
     *
     */
    public function attachDefaultListeners()
    {
        $events = $this->getEventManager();

        $events->attach('beforeAction', function (EventInterface $event) {
            $request = $event->getParam('request');
            $this->modifyTemplateFolderForMobile($request);
        });

        $events->attach('afterAction', function (EventInterface $event) {
            $queryParams = $event->getParam('request')->getQueryParams();
            if (!isset($queryParams['stop'])) {
                return;
            }

            $response = $this->textResponse(
                'The action stopped while afterAction because query param "stop" was specified.'
            );
            $event->setParam('response', $response);
            $event->stopPropagation();
        });
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function actionIndex($request)
    {
        $this->logger->log(LogLevel::DEBUG, 'site.index');

        $qp = $request->getQueryParams();
        $greeting = isset($qp['greeting']) ? $qp['greeting'] : 'Hello,';

        return $this->templatedHtmlResponse('current::index.php', [
            'greeting' => $greeting,
        ]);
    }

    /**
     * @param ResponseInterface $response
     */
    public function actionContact($response)
    {
        $this->logger->debug('site.contact');

        echo 'contact page';
        $response->getBody()->write("");
        // no response
    }
}
