<?php
namespace Acme\App;

use Aura\Di\Container;
use Aura\Di\ContainerBuilder;
use Aura\Di\Exception\ServiceNotFound;
use Aura\Includer\Includer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

class App implements LoggerAwareInterface, EventManagerAwareInterface
{
    use LoggerAwareTrait;
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
     * App constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|array $dirs
     * @param string|array $files
     * @param array $params
     * @return static
     */
    public static function configure($dirs, $files, array $params = [])
    {
        $builder = new ContainerBuilder();
        $container = $builder->newInstance();

        $loader = new Includer();
        $loader->setStrict(false);
        $loader->setDirs(is_array($dirs) ? $dirs : array($dirs));
        $loader->setFiles(is_array($files) ? $files : array($files));
        $loader->setVars([
            'di' => $container,
            'params' => $params,
        ]);
        $loader->load();

        try {
            $app = $container->get('app');
        } catch (ServiceNotFound $e) {
            throw new \RuntimeException("Invalid configuration: " . $e->getMessage(), 0, $e);
        }

        if (!($app instanceof App)) {
            throw new \RuntimeException("Invalid configuration for app");
        }

        // debug trace
        if ($app && $app->getContainer()->has('logger')) {
            foreach ($loader->getDebug() as $message) {
                $app->logger->debug('App::configure | ' . $message);
            }
        }

        static::$_instance = $app;

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
}
