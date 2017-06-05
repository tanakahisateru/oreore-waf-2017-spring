<?php
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\Template\ViewAccessExtension;
use League\Plates\Template\Template;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 * @var string $greeting
 */
$this->layout('current::_layout.php');

$this->view()->setAttribute('title', 'Index - My App');
?>
<div class="site-index">
    <p><?= $this->escapeHtml($greeting) ?></p>

    <p>jquery.js mapped to url: <?= $this->view()->resourceUrlTo('/assets/vendor/jquery/dist/jquery.js') ?></p>
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
