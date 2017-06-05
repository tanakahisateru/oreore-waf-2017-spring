<?php
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\Template\ViewAccessExtension;
use League\Plates\Template\Template;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 * @var int $statusCode
 * @var string $reasonPhrase
 */
$this->layout('_shared/layout.php');
?>

<h2>ERROR</h2>
<p><?= $this->escapeHtml($statusCode) ?> <?= $this->escapeHtml($reasonPhrase) ?></p>
