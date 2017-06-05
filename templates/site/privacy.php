<?php
use Acme\App\View\Template\EscaperExtension;
use Acme\App\View\Template\ViewAccessExtension;
use League\Plates\Template\Template;

/**
 * @var Template|ViewAccessExtension|EscaperExtension $this
 * @var string $greeting
 */
$this->layout('current::_layout.php');

$this->view()->setAttribute('title', 'Privacy Policy - My App');
?>
<div class="site-privacy">
    <p>PLEASE READ THE FOLLOWING TERMS AND CONDITIONS OF USE CAREFULLY BEFORE USING THIS WEBSITE. All users of this site agree that access to and use of this site are subject to the following terms and conditions and other applicable laws. If you do not agree to these terms and conditions, please do not use this site.</p>

    <p>Disclaimer: You understand that it is your responsibility to ensure that the privacy policy you create is complete, accurate, and meets your companies specific privacy needs. We are not liable or responsible for any privacy policies created using our services, and we give no representations or warranties, express or implied, that the privacy policies created using our service are complete, accurate or free from errors or omissions.</p>

    <p>...</p>
</div>
