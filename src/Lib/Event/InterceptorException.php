<?php
namespace My\Web\Lib\Event;

class InterceptorException extends \Exception
{
    protected $lastResult;

    /**
     * InterceptorException constructor.
     * @param $lastResult
     */
    public function __construct($lastResult)
    {
        parent::__construct();
        $this->lastResult = $lastResult;
    }

    /**
     * @return string
     */
    public function getLastResult()
    {
        return $this->lastResult;
    }
}
