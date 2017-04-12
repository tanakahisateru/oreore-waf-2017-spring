<?php
namespace My\Web;

use Aura\Di\Container;
use Aura\Di\ContainerBuilder;
use Aura\Includer\Includer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
     * @param string|array $dirs
     * @param string|array $files
     * @param array $params
     * @return static
     */
    public static function configure($dirs, $files, $params)
    {
        $startedAt = microtime(true);

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

        $elapsed = microtime(true) - $startedAt;

        // debug trace
        if (static::$_instance && static::$_instance->getContainer()->has('logger')) {
            $logger = static::$_instance->getLogger();

            $logger->debug(sprintf("App::configure took %0.3fms", $elapsed * 1000));

            foreach ($loader->getDebug() as $message) {
                $logger->debug('App::configure | ' . $message);
            }
        }

        return $app;
    }

    /**
     * @return static
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
        if (!$this->getContainer()->has('logger')) {
            return new NullLogger();
        }

        return $this->getService('logger', LoggerInterface::class);
    }
}
