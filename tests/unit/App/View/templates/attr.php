<?php
use Acme\App\View\Template\ViewAccessExtension;
use League\Plates\Template\Template;

/**
 * @var Template|ViewAccessExtension $this
 */
?>
<h1>Attr</h1>
<p><?= $this->escape($this->view()->getAttribute('alpha')) ?></p>
