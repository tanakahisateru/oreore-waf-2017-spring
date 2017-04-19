<?php
namespace My\Web\Lib\Log;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait LoggerInjectionTrait
{
    use LoggerAwareTrait;

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (!$this->logger) {
            return new NullLogger();
        }

        return $this->logger;
    }
}
