<?php
namespace My\Web\Lib;

use Aura\Di\Container;
use Aura\Di\ContainerBuilder;
use Aura\Di\Exception\ServiceNotFound;
use Aura\Includer\Includer;
use My\Web\Lib\Log\LoggerInjectionTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

class App implements LoggerAwareInterface, EventManagerAwareInterface
{
    use LoggerInjectionTrait;
    use EventManagerAwareTrait;

    // Category tag for system-wide event listener
    public $eventIdentifier = ['app'];

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
     * @return static
     */
    public static function configure($dirs, $files)
    {
        $builder = new ContainerBuilder();
        $container = $builder->newInstance();

        $loader = new Includer();
        $loader->setStrict(false);
        $loader->setDirs(is_array($dirs) ? $dirs : array($dirs));
        $loader->setFiles(is_array($files) ? $files : array($files));
        $loader->setVars([
            'di' => $container,
        ]);
        $loader->load();

        try {
            $app = $container->get('app');
        } catch (ServiceNotFound $e) {
            throw new \RuntimeException("Invalid configuration for app", 0, $e);
        }

        if (!($app instanceof App)) {
            throw new \RuntimeException("Invalid configuration for app");
        }

        static::$_instance = $app;

        // debug trace
        if (static::$_instance && static::$_instance->getContainer()->has('logger')) {
            $logger = static::$_instance->getLogger();
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
