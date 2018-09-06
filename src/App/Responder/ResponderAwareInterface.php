<?php
namespace Acme\App\Responder;

interface ResponderAwareInterface
{
    /**
     * @param Responder $agent
     */
    public function setResponder(Responder $agent);
}
