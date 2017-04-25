<?php
namespace My\Web\Test\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Framework;
use Codeception\TestInterface;
use Zend\Stratigility\MiddlewareInterface;

class Psr15 extends Framework
{
    protected $config = [
        'processorField' => '',
        'containerField' => '',
    ];

    protected $requiredFields = [
        'applicationFile',
    ];

    /**
     * @var string
     */
        public $applicationFile;

    /**
     * @var \My\Web\Test\Lib\Connector\Psr15
     */
    public $client;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    public $container;


    public function _initialize()
    {
        $this->applicationFile = Configuration::projectDir() . '/' . $this->config['applicationFile'];
        if (!is_file($this->applicationFile)) {
            throw new ModuleConfigException(
                __CLASS__,
                "The objects file does not exist: " . codecept_root_dir() . $this->config['applicationFile']
            );
        }

        /** @noinspection PhpIncludeInspection */
        $objects = require $this->applicationFile;

        if (empty($this->config['processorField'])) {
            if (!(is_callable($objects) || $objects instanceof MiddlewareInterface)) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "PSR-15 app incompatible object was returned from: " . codecept_root_dir() .
                    $this->config['applicationFile']
                );
            }
        } else {
            if (!$this->hasField($objects, $this->config['processorField'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "The " . $this->config['processorField'] . " field does not exist in: " . codecept_root_dir() .
                    $this->config['applicationFile']
                );
            }
            $processor = $this->getFieldValue($objects, $this->config['processorField']);
            if (!(is_callable($processor) || $processor instanceof MiddlewareInterface)) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "PSR-15 app incompatible object in " . $this->config['processorField'] . " was returned from: " . codecept_root_dir() .
                    $this->config['applicationFile']
                );
            }
        }


        if (!empty($this->config['containerField'])) {
            if (!$this->hasField($objects, $this->config['containerField'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "The " . $this->config['containerField'] . " field does not exist in: " . codecept_root_dir() .
                    $this->config['applicationFile']
                );
            }
        }
    }

    public function _before(TestInterface $test)
    {
        $this->client = new \My\Web\Test\Lib\Connector\Psr15();

        /** @noinspection PhpIncludeInspection */
        $objects = require $this->applicationFile;

        if (!empty($this->config['processorField'])) {
            $processor = $this->getFieldValue($objects, $this->config['processorField']);
        } else {
            $processor = $objects;
        }
        $this->client->setProcessor($processor);

        if (!empty($this->config['containerField'])) {
            $this->container = $this->getFieldValue($objects, $this->config['containerField']);
        } else {
            $this->container = null;
        }
    }

    public function _after(TestInterface $test)
    {
        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::_after($test);
    }

    /**
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    private function hasField($bag, $field)
    {
        if (is_array($bag)) {
            return isset($bag[$field]);
        } elseif (is_object($bag)) {
            $getter = 'get' . ucfirst($field);
            return
                method_exists($bag, $getter) ||
                method_exists($bag, $field) ||
                property_exists($bag, $field);
        } else {
            return false;
        }
    }

    private function getFieldValue($bag, $field)
    {
        if (is_array($bag)) {
            return $bag[$field];
        } elseif (is_object($bag)) {
            $getter = 'get' . ucfirst($field);
            if (method_exists($bag, $getter)) {
                return $bag->$getter();
            } elseif (method_exists($bag, $field)) {
                return $bag->$field();
            } elseif (property_exists($bag, $field)) {
                return $bag->$field;
            }
        }

        throw new \InvalidArgumentException("Field " . $field . " does not exist.");
    }
}
