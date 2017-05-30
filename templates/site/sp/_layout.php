<?php
use League\Plates\Template\Template;
use My\Web\Lib\View\Template\EscaperExtension;
use My\Web\Lib\View\Template\ViewAccessExtension;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 */
$this->layout('_shared/sp/layout.php');

?>
<h2>Site controller SP</h2>

<div class="container">
    <?= $this->section('content') ?>
</div>
