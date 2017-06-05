<?php
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\Template\ViewAccessExtension;
use League\Plates\Template\Template;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 */
$this->layout('_shared/sp/layout.php');

?>
<h2>Site controller SP</h2>

<div class="container">
    <?= $this->section('content') ?>
</div>
