<?php
namespace Acme\App\Http;

use Interop\Http\Factory\StreamFactoryInterface;

interface StreamFactoryAwareInterface
{
    /**
     * @param StreamFactoryInterface $streamFactory
     */
    public function setStreamFactory(StreamFactoryInterface $streamFactory);
}
