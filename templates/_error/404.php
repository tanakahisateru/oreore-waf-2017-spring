<?php
use My\Web\Lib\View\Template\Template;

/**
 * @var Template $this
 * @var int $statusCode
 * @var string $reasonPhrase
 */
$this->layout('_shared/layout.php');
?>

<h2>Sorry, <?= $this->escapeHtml($statusCode) ?> <?= $this->escapeHtml($reasonPhrase) ?></h2>
<p>
    This URL and/or METHOD is not available.
</p>
