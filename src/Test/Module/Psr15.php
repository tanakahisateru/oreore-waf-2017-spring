<?php
namespace My\Web\Test\Module;

use Codeception\Configuration;
use Codeception\Lib\Framework;
use Codeception\TestInterface;

class Psr15 extends Framework
{
    protected $config = [
        'pipeline' => '_pipeline-processor.php',
    ];

    /**
     * @var \My\Web\Test\Lib\Connector\Psr15
     */
    public $client;

    protected $pipelineProcessor;

    public function _initialize()
    {
    }

    public function _before(TestInterface $test)
    {
        $this->client = new \My\Web\Test\Lib\Connector\Psr15();
        /** @noinspection PhpIncludeInspection */
        $processor = require Configuration::testsDir() . '/' . $this->config['pipeline'];
        $this->client->setPipelineProcessor($processor);
    }

    public function _after(TestInterface $test)
    {
        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::_after($test);
    }
}
