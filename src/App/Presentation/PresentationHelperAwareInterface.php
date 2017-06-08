<?php
namespace Acme\App\Presentation;

interface PresentationHelperAwareInterface
{
    /**
     * @param PresentationHelper $agent
     */
    public function setPresentationHelper(PresentationHelper $agent);
}
