<?php
namespace Acme\App\Http;

use Interop\Http\Factory\StreamFactoryInterface;

trait StreamFactoryAwareTrait
{
    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param StreamFactoryInterface $streamFactory
     */
    public function setStreamFactory(StreamFactoryInterface $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }
}
