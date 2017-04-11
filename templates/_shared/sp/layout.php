<?php
/**
 * @var \My\Web\Lib\View\Template $this
 */

$title = $this->view()->getAttribute('title', 'My App');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $this->escapeHtml($title) ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
<header>
    <h1 class="brand"><?= $this->escapeHtml($title) ?></h1>
</header>
<div class="content">
    <?= $this->section('content') ?>
</div>
<footer>
    <div class="copyright">
        copyright foo bar
    </div>
    <div>
        ... using mobile skin
    </div>
</footer>
</body>
</html>
