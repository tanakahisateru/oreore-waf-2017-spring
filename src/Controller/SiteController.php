<?php
namespace My\Web\Controller;

use Aura\Sql\PdoInterface;
use My\Web\Lib\Injection\LoggerInjectionTrait;
use My\Web\Lib\Injection\ViewInjectionTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LogLevel;

class SiteController
{
    use ViewInjectionTrait;
    use LoggerInjectionTrait;

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
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function actionIndex($request, $response)
    {
        $this->log(LogLevel::DEBUG, 'site.index');

        $qp = $request->getQueryParams();
        $greeting = isset($qp['greeting']) ? $qp['greeting'] : 'Hello,';

        $this->modifyTemplateFolderForMobile($request);
        return $this->render($response, 'current::index.php', [
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
