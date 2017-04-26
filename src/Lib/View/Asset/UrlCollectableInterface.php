<?php
namespace My\Web\Lib\View\Asset;

interface UrlCollectableInterface
{
    /**
     * @param string $section
     * @return array
     */
    public function collectUrls($section = null);
}
