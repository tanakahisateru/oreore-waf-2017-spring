<?php
use My\Web\Lib\View\Template\Template;

/**
 * @var Template $this
 */

$this->view()->requireAsset('bootstrap');

$title = $this->view()->getAttribute('title', 'My App');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $this->escapeHtml($title) ?></title>
    <?php foreach ($this->view()->assetUrls('before-end-head-css') as $url): ?>
        <link rel="stylesheet" href="<?= $url ?>">
    <?php endforeach; ?>
    <?= $this->section('before-end-head') ?>
</head>
<body>
<?= $this->section('after-start-body') ?>
<div class="container">
    <header>
        <h1 class="app-title"><?= $this->escapeHtml($title) ?></h1>
    </header>

    <?= $this->section('content') ?>

    <footer>
        <div class="copyright">
            copyright foo bar
        </div>
    </footer>
</div>
<?php foreach ($this->view()->assetUrls('before-end-body-script') as $url): ?>
    <script src="<?= $url ?>"></script>
<?php endforeach; ?>
<?= $this->section('before-end-body') ?>
</body>
</html>
