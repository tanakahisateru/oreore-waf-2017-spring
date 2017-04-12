<?php
use My\Web\Lib\View\Template;

/**
 * @var Template $this
 */

$router = $this->view()->getRouter();

$this->layout('_shared/layout.php');
?>
<h2>Site controller</h2>

<div class="container">
    <?= $this->section('content') ?>

    <div>
        Please <a href="<?= $router->urlTo('site.contact') ?>">contact</a> us.
    </div>

    <?php
    // echo 'Missing route displayed as: ' . $router->urlTo('missing.route.name');
    // check also the log.
    ?>
</div>
