<?php
namespace My\Web\Lib\Http;

interface HttpFactoryAwareInterface
{
    /**
     * @param HttpFactoryInterface $httpFactory
     */
    public function setHttpFactory(HttpFactoryInterface $httpFactory);
}
