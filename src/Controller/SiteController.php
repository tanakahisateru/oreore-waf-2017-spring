<?php
namespace My\Web\Controller;

use Aura\Sql\PdoInterface;
use My\Web\Lib\Http\HttpFactoryAwareInterface;
use My\Web\Lib\Http\HttpFactoryInjectionTrait;
use My\Web\Lib\Log\LoggerInjectionTrait;
use My\Web\Lib\View\ViewAwareInterface;
use My\Web\Lib\View\ViewInjectionTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

class SiteController implements LoggerAwareInterface, HttpFactoryAwareInterface, ViewAwareInterface
{
    use LoggerInjectionTrait;
    use HttpFactoryInjectionTrait;
    use ViewInjectionTrait;

    use HtmlPageControllerTrait;

    /**
     * @var PdoInterface|\PDO
     */
    protected $db;

    /**
     * SiteController constructor.
     * @param PdoInterface|\PDO $db
     */
    public function __construct($db)
    {
        $this->db = $db;
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

        // TODO Needs event listener to move these cross-cutting concerns
        $this->modifyTemplateFolderForMobile($request);

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
