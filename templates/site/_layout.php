<?php
use My\Web\Lib\View\Template\Template;

/**
 * @var Template $this
 */
$this->layout('_shared/layout.php');

?>
<h2>Site controller</h2>

<div class="content">
    <?= $this->section('content') ?>

    <div>
        Please <a href="<?= $this->view()->routeUrlTo('site.contact') ?>">contact</a> us.
    </div>

    <?php
    // echo 'Missing route displayed as: ' . $router->urlTo('missing.route.name');
    // check also the log.
    ?>
</div>
