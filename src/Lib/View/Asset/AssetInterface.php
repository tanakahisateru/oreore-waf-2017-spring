<?php
namespace My\Web\Lib\View\Asset;

interface AssetInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getBaseUrl();

    /**
     * @return AssetInterface[]
     */
    public function getDependencies();

    /**
     * @return AssetInterface[]
     */
    public function collectDependencies();

    /**
     * @param string $stage
     * @return array
     */
    public function getElements($stage = null);

    /**
     * @param string $stage
     * @return array
     */
    public function collectUrls($stage = null);
}
