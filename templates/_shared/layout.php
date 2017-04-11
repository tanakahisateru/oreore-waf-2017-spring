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
</footer>
</body>
</html>
