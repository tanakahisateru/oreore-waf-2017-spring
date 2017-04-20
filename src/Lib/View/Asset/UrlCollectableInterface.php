<?php
namespace My\Web\Lib\View\Asset;

interface UrlCollectableInterface
{
    /**
     * @param string $stage
     * @return array
     */
    public function collectUrls($stage = null);
}
