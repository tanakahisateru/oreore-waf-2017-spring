<?php
use League\Plates\Template\Template;

/**
 * @var Template $this
 * @var string $param
 */
?>
<h1>Foo</h1>
<p><?= $this->escape($param) ?></p>
