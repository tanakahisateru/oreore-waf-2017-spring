<?php
use My\Web\Lib\View\Template;

/**
 * @var Template $this
 */

$this->layout('_shared/layout.php');
?>
<h2>Site controller</h2>

<div class="container">
    <?= $this->section('content') ?>
</div>
