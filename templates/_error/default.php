<?php
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\Template\ViewAccessExtension;
use League\Plates\Template\Template;
use Sumeko\Http;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 * @var Http\Exception|Http\ExceptionInterface $error
 */
$this->layout('_shared/layout.php');
?>

<h2>ERROR</h2>
<p><?= $this->escapeHtml($error->getStatus()) ?></p>
