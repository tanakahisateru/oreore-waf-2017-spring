<?php
namespace Acme\App\Controller;

interface PresentationHelperAwareInterface
{
    /**
     * @param PresentationHelper $agent
     */
    public function setPresentationHelper(PresentationHelper $agent);
}
