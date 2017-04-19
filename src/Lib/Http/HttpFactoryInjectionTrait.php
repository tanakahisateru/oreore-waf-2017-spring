<?php
namespace My\Web\Lib\Http;

trait HttpFactoryInjectionTrait
{
    /**
     * @var HttpFactoryInterface
     */
    protected $httpFactory;

    /**
     * @param HttpFactoryInterface $httpFactory
     */
    public function setHttpFactory(HttpFactoryInterface $httpFactory)
    {
        $this->httpFactory = $httpFactory;
    }

    /**
     * @return HttpFactoryInterface
     */
    public function getHttpFactory()
    {
        if (!$this->httpFactory) {
            return new DiactorosHttpFactory();
        }

        return $this->httpFactory;
    }
}
