<?php
if (!defined('ABSPATH')) {
    die('Access denied.');
}
?>
<?php foreach ($this->get_notifications() as $key => $notify) { ?>
    <?php if ($notify['type'] === 'update') { ?>
        <div id="message" class="updated e2pdf-notice">
            <?php echo $notify['text']; ?>
        </div>
    <?php } elseif ($notify['type'] === 'error') { ?>
        <div id="message" class="error e2pdf-notice">
            <b><?php echo __('[ERROR]', 'e2pdf'); ?></b> <?php echo $notify['text']; ?>
        </div>
    <?php } elseif ($notify['type'] === 'notice') { ?>
        <div id="message" class="notice e2pdf-notice">
            <?php echo $notify['text']; ?>
        </div>
    <?php } ?>
<?php } ?>