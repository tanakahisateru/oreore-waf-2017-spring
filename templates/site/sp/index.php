<?php
use My\Web\Lib\View\Template\Template;

/**
 * @var Template $this
 * @var string $greeting
 */
$this->layout('current::_layout.php');

?>
<div class="site-index">
    <p><?= $this->escape($greeting) ?> (SP)</p>
</div>
