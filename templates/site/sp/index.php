<?php
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\Template\ViewAccessExtension;
use League\Plates\Template\Template;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 * @var string $greeting
 */
$this->layout('current::_layout.php');

?>
<div class="site-index">
    <p><?= $this->escapeHtml($greeting) ?> (SP)</p>
</div>
