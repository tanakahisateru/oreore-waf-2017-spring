<?php
namespace My\Web\Lib\View\Asset;

interface AssetInterface
{
    /**
     * @return AssetInterface[]
     */
    public function collectDependencies();

    /**
     * @param string $stage
     * @return array
     */
    public function collectUrls($stage = null);
}
