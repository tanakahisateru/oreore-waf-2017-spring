<?php
namespace Acme\Controller;

use Acme\App\Controller\ControllerEventManagerAwareTrait;
use Acme\App\Controller\PresentationHelperAwareInterface;
use Acme\App\Controller\PresentationHelperAwareTrait;
use Acme\App\Router\ActionDispatcher;
use Acme\App\View\View;
use Acme\Util\Mobile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Sumeko\Http\Exception\ForbiddenException;
use Sumeko\Http\Exception\NotFoundException;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerAwareInterface;

class SiteController implements PresentationHelperAwareInterface, EventManagerAwareInterface, LoggerAwareInterface
{
    use PresentationHelperAwareTrait;
    use ControllerEventManagerAwareTrait;
    use LoggerAwareTrait;

    const TEMPLATE_FOLDER = 'site';

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

        $events->attach(ActionDispatcher::EVENT_BEFORE_ACTION, function (EventInterface $event) {
            $queryParams = $event->getParam('request')->getQueryParams();
            if (isset($queryParams['stop'])) {
                $response = $this->textResponse(
                    'The action stopped while afterAction because query param "stop" was specified.'
                );
                $event->setParam('response', $response);
                $event->stopPropagation();
            }
        });
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function index($request)
    {
        $this->logger->log(LogLevel::DEBUG, 'site.index');

        $qp = $request->getQueryParams();
        $greeting = isset($qp['greeting']) ? $qp['greeting'] : 'Hello,';

        $view = $this->createView($request);

        return $this->htmlResponse($view->render('current::index.php', [
            'greeting' => $greeting,
        ]));
    }

    /**
     * @param ResponseInterface $response
     */
    public function contact($response)
    {
        $this->logger->debug('site.contact');

        echo 'echo contact page';
        $response->getBody()->write("(not shown because this response not returned)");
        // no response returned
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function redirect($request)
    {
        $params = $request->getQueryParams();
        if (isset($params['route'])) {
            $to = $params['route'];
        } else {
            $to = 'site.index';
        }

        return $this->redirectResponseToRoute($to);
    }

    /**
     * @throws NotFoundException
     */
    public function notFound()
    {
        throw new NotFoundException();
    }

    /**
     * @throws ForbiddenException
     */
    public function forbidden()
    {
        throw new ForbiddenException();
    }

    /**
     * @param ServerRequestInterface $request
     * @return View
     */
    protected function createView(ServerRequestInterface $request)
    {
        $view = $this->presentationHelper->createView();

        $mobileDetect = Mobile::detect($request);
        if ($mobileDetect->isMobile()) {
            $view->setFolder('current', static::TEMPLATE_FOLDER . '/sp');
        } else {
            $view->setFolder('current', static::TEMPLATE_FOLDER);
        }

        return $view;
    }
}
