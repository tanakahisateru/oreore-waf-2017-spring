<?php
use League\Plates\Template\Template;
use My\Web\Lib\View\Template\EscaperExtension;
use My\Web\Lib\View\Template\ViewAccessExtension;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 * @var string $greeting
 */
$this->layout('current::_layout.php');

?>
<div class="site-index">
    <p><?= $this->escape($greeting) ?> (SP)</p>
</div>
