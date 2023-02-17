<?php
if (!defined('ABSPATH')) {
    die('Access denied.');
}
?>
<?php if (get_option('e2pdf_memory_time')) { ?>
    <div class="e2pdf-debug-panel">
        <?php _e('Load Time', 'e2pdf') ?>: <?php echo microtime(true) - $this->helper->get('time_debug'); ?> | <?php _e('Total Memory Usage', 'e2pdf') ?>: <?php echo $this->helper->load('convert')->from_bytes(memory_get_usage()); ?> | <?php _e('E2pdf Memory Usage', 'e2pdf') ?>: <?php echo $this->helper->load('convert')->from_bytes(memory_get_usage() - $this->helper->get('memory_debug')); ?>
    </div> 
<?php } ?>


