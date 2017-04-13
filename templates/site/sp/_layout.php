<?php
use My\Web\Lib\View\Template\Template;

/**
 * @var Template $this
 */
$this->layout('_shared/sp/layout.php');

?>
<h2>Site controller SP</h2>

<div class="container">
    <?= $this->section('content') ?>
</div>
