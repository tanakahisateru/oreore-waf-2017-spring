<?php
namespace My\Web;

use Aura\Di\Container;
use Aura\Di\ContainerBuilder;
use Aura\Includer\Includer;
use My\Web\Lib\Router\Router;
use My\Web\Lib\View\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class App
{
    /**
     * @var static
     */
    protected static $_instance;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $params;

    /**
     * App constructor.
     *
     * @param Container $container
     * @param array $params
     */
    public function __construct(Container $container, array $params)
    {
        $this->container = $container;
        $this->params = $params;
    }

    /**
     * @param string $_filename_
     * @param array $vars
     * @return mixed
     */
    public function runScript($_filename_, array $vars = [])
    {
        foreach ($vars as $k => $v) {
            // if ($v instanceof LazyInterface) {
            //     $vars[$k] = $v->__invoke();
            // }
            $$k = $v;
        }
        unset($k, $v);

        /** @noinspection PhpIncludeInspection */
        return require $_filename_;
    }

    /**
     * @param string|array $dirs
     * @param string|array $files
     * @param array $params
     * @return App
     */
    public static function configure($dirs, $files, $params)
    {
        $builder = new ContainerBuilder();
        $container = $builder->newInstance();

        $app = new static($container, $params);
        $container->set('app', $app);

        static::$_instance = $app;

        $loader = new Includer();
        $loader->setStrict(false);
        $loader->setDirs(is_array($dirs) ? $dirs : array($dirs));
        $loader->setFiles(is_array($files) ? $files : array($files));
        $loader->setVars([
            'di' => $container,
        ]);
        $loader->load();

        // debug trace
        if (static::$_instance && static::$_instance->getContainer()->has('logger')) {
            $logger = static::$_instance->getLogger();
            foreach ($loader->getDebug() as $message) {
                $logger->debug($message);
            }
        }

        return $app;
    }

    /**
     * @return App
     */
    public static function getInstance()
    {
        if (empty(static::$_instance)) {
            throw new \UnexpectedValueException('The app was uninitialized');
            // static::$_instance = new static();
        }
        return static::$_instance;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @param string $class
     * @return mixed
     */
    public function getService($name, $class = null)
    {
        $container = $this->getContainer();
        if (!$container->has($name)) {
            throw new \RuntimeException('Required service not found: ' . $name);
        }

        $object = $container->get($name);

        if ($class && !($object instanceof $class)) {
            throw new \RuntimeException('Service ' . $name . ' is not an instance of ' . $class);
        }

        return $object;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->getService('logger', LoggerInterface::class);
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->getService('router', Router::class);
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->getService('router', View::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request, ResponseInterface $response)
    {
        //try {
            // filter

        ob_start();
        $responseBeforeDispatch = $response;

        $response = $this->getRouter()->dispatch($request, $response);

        if (empty($response)) {
            $response = $responseBeforeDispatch;
            $response->getBody()->write(ob_get_clean());
        } elseif (is_array($response)) {
            $response = $responseBeforeDispatch->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($response));
            ob_end_clean();
        } elseif (is_scalar($response)) {
            $response = $responseBeforeDispatch;
            $response->getBody()->write($response);
            ob_end_clean();
        } elseif ($response instanceof ResponseInterface) {
            $echo = ob_get_clean();
            if (!empty($echo)) {
                $stream = $response->getBody();
                $body = $stream->getContents();
                $stream->rewind();
                $stream->write($body . $echo);
            }
        } else {
            throw new \LogicException('Invalid response returned on: ' . $request->getUri());
        }

            // if response: filter
        //} catch (\Exception)

        return $response;
    }

    /**
     *
     */
    public function run()
    {
        $request = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        $response = $this->handle($request, new Response());

        if ($response->getStatusCode() != 200) {
            @header(sprintf('HTTP/%s %d %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
        }

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                @header(sprintf('%s: %s', $name, $value), false);
            }
        }

        echo $response->getBody();
    }
}
