<?php
namespace My\Web\Controller;

use My\Web\Controller\General\DefaultListenerAttachableInterface;
use My\Web\Controller\General\HtmlPageControllerInterface;
use My\Web\Controller\General\HtmlPageControllerTrait;
use My\Web\Lib\Log\LoggerInjectionTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

class SiteController implements
    LoggerAwareInterface,
    EventManagerAwareInterface,
    DefaultListenerAttachableInterface,
    HtmlPageControllerInterface
{
    use LoggerInjectionTrait;
    use EventManagerAwareTrait;
    use HtmlPageControllerTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['controller'];

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
            if (!isset($event->getParam('request')->getQueryParams()['stop'])) {
                return;
            }

            $response = $this->httpFactory->createTextResponse(
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
        $this->getLogger()->log(LogLevel::DEBUG, 'site.index');

        $qp = $request->getQueryParams();
        $greeting = isset($qp['greeting']) ? $qp['greeting'] : 'Hello,';

        return $this->htmlResponse('current::index.php', [
            'greeting' => $greeting,
        ]);
    }

    /**
     * @param ResponseInterface $response
     */
    public function actionContact($response)
    {
        $this->getLogger()->debug('site.contact');

        echo 'contact page';
        $response->getBody()->write("");
        // no response
    }
}
