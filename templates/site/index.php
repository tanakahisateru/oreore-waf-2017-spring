<?php
use My\Web\Lib\View\Template\Template;

/**
 * @var Template $this
 * @var string $greeting
 */
$this->layout('current::_layout.php');

$this->view()->setAttribute('title', 'Index - My App');
?>
<div class="site-index">
    <p><?= $this->escape($greeting) ?></p>
</div>

<?php $this->view()->requireAsset('app'); ?>
<?php $this->view()->requireAsset('jquery'); ?>

<?php $this->push('before-end-body'); ?>
<script>
    (function ($) {
        $(function () {
            $('.site-index')
                .css('background', '#ffffee')
                .appendCaption('...modified by jQuery');
        });
    })(jQuery);
</script>
<?php $this->end(); ?>
