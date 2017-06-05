<?php
namespace Acme\App\Router;

use PHPUnit\Framework\TestCase;

class RoutingExceptionTest extends TestCase
{
    public function testStatusCode()
    {
        $exception = new RoutingException(400);
        $this->assertEquals(400, $exception->getStatus());
    }
}
