<?php
use My\Web\Lib\View\Template\Template;

/**
 * @var Template $this
 * @var int $statusCode
 * @var string $reasonPhrase
 */
$this->layout('_shared/layout.php');
?>

<h2>ERROR</h2>
<p><?= $this->escapeHtml($statusCode) ?> <?= $this->escapeHtml($reasonPhrase) ?></p>
