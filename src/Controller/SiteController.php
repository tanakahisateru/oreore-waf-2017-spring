<?php
namespace Acme\Controller;

use Acme\App\Controller\ControllerInterface;
use Acme\App\Controller\ControllerTrait;
use Acme\App\Router\Router;
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

class SiteController implements ControllerInterface, LoggerAwareInterface
{
    const TEMPLATE_FOLDER = 'site';

    use ControllerTrait;
    use LoggerAwareTrait;

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

        $events->attach(Router::EVENT_BEFORE_ACTION, function (EventInterface $event) {
            $queryParams = $event->getParam('request')->getQueryParams();
            if (isset($queryParams['stop'])) {
                $response = $this->responseAgent->textResponse(
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
    public function actionIndex($request)
    {
        $this->logger->log(LogLevel::DEBUG, 'site.index');

        $qp = $request->getQueryParams();
        $greeting = isset($qp['greeting']) ? $qp['greeting'] : 'Hello,';

        $view = $this->createView($request);

        return $this->responseAgent->htmlResponse($view->render('current::index.php', [
            'greeting' => $greeting,
        ]));
    }

    /**
     * @param ResponseInterface $response
     */
    public function actionContact($response)
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
    public function actionRedirect($request)
    {
        $params = $request->getQueryParams();
        if (isset($params['route'])) {
            $to = $params['route'];
        } else {
            $to = 'site.index';
        }

        return $this->responseAgent->redirectResponseToRoute($to);
    }

    /**
     * @throws NotFoundException
     */
    public function actionNotFound()
    {
        throw new NotFoundException();
    }

    /**
     * @throws ForbiddenException
     */
    public function actionForbidden()
    {
        throw new ForbiddenException();
    }

    /**
     * @param ServerRequestInterface $request
     * @return View
     */
    protected function createView(ServerRequestInterface $request)
    {
        $view = $this->responseAgent->createView();

        $mobileDetect = Mobile::detect($request);
        if ($mobileDetect->isMobile()) {
            $view->setFolder('current', static::TEMPLATE_FOLDER . '/sp');
        } else {
            $view->setFolder('current', static::TEMPLATE_FOLDER);
        }

        return $view;
    }
}
