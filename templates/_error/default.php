<?php
use League\Plates\Template\Template;
use My\Web\Lib\View\Template\EscaperExtension;
use My\Web\Lib\View\Template\ViewAccessExtension;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 * @var int $statusCode
 * @var string $reasonPhrase
 */
$this->layout('_shared/layout.php');
?>

<h2>ERROR</h2>
<p><?= $this->escapeHtml($statusCode) ?> <?= $this->escapeHtml($reasonPhrase) ?></p>
