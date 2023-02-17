<?php
if (!defined('ABSPATH')) {
    die('Access denied.');
}
?>
<div class="wrap <?php echo $this->page; ?>">
    <h1><?php _e('Debug', 'e2pdf') ?></h1>
    <hr class="wp-header-end">
    <?php $this->render('blocks', 'notifications'); ?>
    <h3 class="nav-tab-wrapper wp-clearfix">
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf-debug')); ?>" class="nav-tab <?php if (!$this->get->get('action')) { ?>nav-tab-active<?php } ?>"><?php echo _e('Debug', 'e2pdf'); ?></a>
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf-debug', 'action' => 'connection')); ?>" class="nav-tab <?php if ($this->get->get('action') == 'connection') { ?>nav-tab-active<?php } ?>"><?php _e('Connection', 'e2pdf'); ?></a>
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf-debug', 'action' => 'db')); ?>" class="nav-tab <?php if ($this->get->get('action') == 'db') { ?>nav-tab-active<?php } ?>"><?php _e('DB', 'e2pdf'); ?></a>
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf-debug', 'action' => 'phpinfo')); ?>" class="nav-tab <?php if ($this->get->get('action') == 'phpinfo') { ?>nav-tab-active<?php } ?>"><?php _e('PHP Info', 'e2pdf'); ?></a>
    </h3>

    <div class="wrap">
        <?php if (!$this->get->get('action')) { ?>
            <ul class="e2pdf-view-area">
                <li><h2><?php _e('Common', 'e2pdf') ?></h2></li>
                <li><span class="e2pdf-bold"><?php _e('Domain', 'e2pdf') ?>:</span> <?php echo $this->view->api->get_domain(); ?></li>
                <li><span class="e2pdf-bold"><?php _e('Plugin Version', 'e2pdf') ?>:</span> <?php echo $this->helper->get('version'); ?></li>
                <li><span class="e2pdf-bold"><?php _e('DB Version', 'e2pdf') ?>:</span> <?php echo get_option('e2pdf_version'); ?></li>
                <li><span class="e2pdf-bold"><?php _e('PHP Version', 'e2pdf') ?>:</span> <?php echo phpversion(); ?></li>
                <li><span class="e2pdf-bold"><?php _e('WP Version', 'e2pdf') ?>:</span> <?php echo get_bloginfo('version'); ?></li>
                <li><span class="e2pdf-bold"><?php _e('Multisite', 'e2pdf') ?>:</span> <?php is_multisite() ? _e('Yes', 'e2pdf') : _e('No', 'e2pdf'); ?></span></li>
                <li><span class="e2pdf-bold"><?php _e('Is Main Site', 'e2pdf') ?>:</span> <?php is_main_site() ? _e('Yes', 'e2pdf') : _e('No', 'e2pdf'); ?></span></li>
                <li><h2><?php _e('Folders', 'e2pdf') ?></h2></li>
                <li><span class="e2pdf-bold"><?php _e('WP Folder', 'e2pdf') ?>:</span></li>
                <li><?php echo ABSPATH ?></li>
                <li><span class="e2pdf-bold"><?php _e('Upload Folder', 'e2pdf') ?>:</span></li>
                <li><?php echo $this->helper->get('upload_dir'); ?></li>
                <li><span class="e2pdf-bold"><?php _e('Plugin Folder', 'e2pdf') ?>:</span></li>
                <li><?php echo $this->helper->get('plugin_dir'); ?></li>
                <li><span class="e2pdf-bold"><?php _e('Folders permission', 'e2pdf') ?>:</span></li>
                <li><?php if (is_writable($this->helper->get('tmp_dir'))) { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span>
                    <?php } else { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span>
                    <?php } ?> <?php echo $this->helper->get('tmp_dir'); ?>
                </li>
                <li><?php if (is_writable($this->helper->get('pdf_dir'))) { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span>
                    <?php } else { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span>
                    <?php } ?> <?php echo $this->helper->get('pdf_dir'); ?>
                </li>
                <li><?php if (is_writable($this->helper->get('fonts_dir'))) { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span>
                    <?php } else { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span>
                    <?php } ?> <?php echo $this->helper->get('fonts_dir'); ?>
                </li>
                <li><?php if (is_writable($this->helper->get('tpl_dir'))) { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span>
                    <?php } else { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span>
                    <?php } ?> <?php echo $this->helper->get('tpl_dir'); ?>
                </li>
                <li><?php if (is_writable($this->helper->get('viewer_dir'))) { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span>
                    <?php } else { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span>
                    <?php } ?> <?php echo $this->helper->get('viewer_dir'); ?>
                </li>
                <li><h2><?php _e('PHP Extensions', 'e2pdf') ?></h2></li>
                <li>
                    <?php if (!function_exists('curl_version')) { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span> <?php _e('CURL', 'e2pdf') ?><br><small><?php _e('curl_version not found', 'e2pdf'); ?></small>
                    <?php } elseif (in_array('curl_exec', $this->view->disabled_functions)) { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span> <?php _e('CURL', 'e2pdf') ?><br><small><?php _e('curl_exec disabled', 'e2pdf'); ?></small>
                    <?php } else { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span> <?php _e('CURL', 'e2pdf') ?>
                    <?php } ?> 
                </li>
                <li>
                    <?php if (extension_loaded('simplexml')) { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span>
                    <?php } else { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span>
                    <?php } ?> <?php _e('SIMPLEXML', 'e2pdf') ?>
                </li>
                <li>
                    <?php if (extension_loaded('libxml')) { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span>
                    <?php } else { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span>
                    <?php } ?> <?php _e('LIBXML', 'e2pdf') ?>
                </li>
                <li>
                    <?php if (extension_loaded('Dom')) { ?>
                        <span class="e2pdf-color-green"><?php _e('[OK]', 'e2pdf'); ?></span>
                    <?php } else { ?>
                        <span class="e2pdf-color-red"><?php _e('[ERROR]', 'e2pdf'); ?></span>
                    <?php } ?> <?php _e('DOM', 'e2pdf') ?>
                </li>
                <?php
                if (function_exists('get_locale') && function_exists('get_available_languages')) {
                    $locales = get_available_languages();
                    if (!in_array(get_locale(), $locales)) {
                        array_unshift($locales, get_locale());
                    }
                    ?>
                    <li><h2><?php _e('Locales', 'e2pdf') ?>:</h2></li>
                    <li><?php echo implode(', ', $locales); ?></span></li>
                    <?php
                }
                ?>
                <?php
                if (function_exists('get_intermediate_image_sizes')) {
                    $image_sizes = get_intermediate_image_sizes();
                    ?>
                    <li><h2><?php _e('Image Sizes', 'e2pdf') ?>:</h2></li>
                    <li><?php echo implode(', ', $image_sizes); ?></span></li>
                    <?php
                }
                ?>
                <li><h2><?php _e('Plugins', 'e2pdf') ?>:</h2></li>
                <li>
                    <?php echo implode(", ", get_option('active_plugins')); ?>
                </li>
            </ul>
        <?php } elseif ($this->get->get('action') == 'db') { ?>
            <div class="e2pdf-view-area">
                <?php foreach ($this->view->db_structure as $table_key => $table) { ?>
                    <ul>
                        <li><span class="e2pdf-bold <?php echo isset($table['check']) && $table['check'] ? 'e2pdf-color-green' : 'e2pdf-color-red' ?>"><?php echo $table_key; ?></span></li>
                        <li>
                            <?php foreach ($table['columns'] as $column_key => $column) { ?>
                                <span class="<?php echo isset($column['check']) && $column['check'] ? 'e2pdf-color-green' : 'e2pdf-color-red' ?>"><?php echo $column_key; ?></span>
                            <?php } ?>
                        </li>

                    </ul>
                <?php } ?>
            </div>
        <?php } elseif ($this->get->get('action') == 'phpinfo') { ?>
            <div class="e2pdf-view-area">
                <div class="phpinfo_wrapper">
                    <?php echo $this->view->phpinfo; ?>
                </div>
            </div>
        <?php } elseif ($this->get->get('action') == 'connection') { ?>
            <div class="e2pdf-view-area">
                <?php echo $this->view->connection; ?>
            </div>
        <?php } ?>
    </div>
</div>
<?php $this->render('blocks', 'debug-panel'); ?>


