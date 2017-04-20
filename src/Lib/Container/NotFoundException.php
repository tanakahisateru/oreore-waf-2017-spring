<?php
namespace My\Web\Lib\Container;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{

}
