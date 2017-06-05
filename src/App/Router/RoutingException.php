<?php
namespace Acme\App\Router;

class RoutingException extends \Exception
{
    /**
     * @var int
     */
    protected $status;

    /**
     * RoutingException constructor.
     * @param int $status
     * @param string $message
     * @param int $code
     * @param mixed $previous
     */
    public function __construct($status, $message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
