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

<h2>Sorry, <?= $this->escapeHtml($error->getStatus()) ?></h2>
<p>
    This URL and/or METHOD is not available.
</p>
