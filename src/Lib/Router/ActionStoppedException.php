<?php
namespace My\Web\Lib\Router;

class ActionStoppedException extends \Exception
{
    protected $response;

    /**
     * ActionStoppedException constructor.
     *
     * @param mixed $response
     * @param string $message
     * @param int $code
     * @param null $previous
     */
    public function __construct($response, $message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
