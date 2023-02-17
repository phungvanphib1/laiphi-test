<?php
if (!defined('ABSPATH')) {
    die('Access denied.');
}
?>
<div class="wrap">
    <h1><?php _e('Export', 'e2pdf'); ?></h1>
    <hr class="wp-header-end">
    <?php $this->render('blocks', 'notifications'); ?>
    <h3 class="nav-tab-wrapper wp-clearfix">
        <a href="<?php echo $this->helper->get_url(array('page' => 'e2pdf')); ?>" class="nav-tab <?php if (!($this->get->get('action'))) { ?>nav-tab-active<?php } ?>"><?php echo _e('Export', 'e2pdf'); ?></a>
    </h3>

    <div class="wrap e2pdf-view-area e2pdf-rel">
        <form method="post" target="_blank" id="e2pdf-export-form" action="<?php echo $this->helper->get_url(array('page' => 'e2pdf', 'action' => 'export')); ?>" class="e2pdf-export-form">
            <input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('e2pdf_post') ?>">
            <div class="e2pdf-form-loader"><span class="spinner"></span></div>
            <div class="e2pdf-grid">
                <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                    <?php _e('Select Template to use', 'e2pdf'); ?>:
                </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10">
                    <?php
                    $this->render('field', 'select', array(
                        'field' => array(
                            'id' => 'e2pdf-export-template',
                            'name' => 'template',
                            'class' => 'e2pdf-w100'
                        ),
                        'value' => 0,
                        'options' => $this->controller->get_active_templates(),
                    ));
                    ?>
                </div>
            </div>
            <div id="e2pdf-single-item" class="e2pdf-grid">
                <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                    <?php _e('Select Dataset to export', 'e2pdf'); ?>:
                </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10">
                    <?php
                    $datasets = $this->controller->get_datasets();

                    $this->render('field', 'select', array(
                        'field' => array(
                            'id' => 'e2pdf-export-dataset',
                            'name' => 'dataset',
                            'disabled' => 'disabled',
                            'class' => 'e2pdf-w100'
                        ),
                        'value' => 0,
                        'options' => !empty($datasets) ? $datasets : array(
                            '' => __('--- Select ---', 'e2pdf')
                                ),
                    ));
                    ?>
                </div>
            </div>

            <div id="e2pdf-merged-items" class="e2pdf-grid e2pdf-hide">
                <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                    <?php _e('Select Datasets to export', 'e2pdf'); ?>:
                </div><div class="e2pdf-ib e2pdf-w35 e2pdf-pl10">
                    <?php
                    $datasets = $this->controller->get_datasets();

                    $this->render('field', 'select', array(
                        'field' => array(
                            'id' => 'e2pdf-export-dataset1',
                            'name' => 'dataset1',
                            'disabled' => 'disabled',
                            'class' => 'e2pdf-w100 e2pdf-export-datasets'
                        ),
                        'value' => 0,
                        'options' => !empty($datasets) ? $datasets : array(
                            '' => __('--- Select ---', 'e2pdf')
                                ),
                    ));
                    ?>
                </div><div class="e2pdf-ib e2pdf-w35 e2pdf-pl10">
                    <?php
                    $datasets = $this->controller->get_datasets();

                    $this->render('field', 'select', array(
                        'field' => array(
                            'id' => 'e2pdf-export-dataset2',
                            'name' => 'dataset2',
                            'disabled' => 'disabled',
                            'class' => 'e2pdf-w100 e2pdf-export-datasets'
                        ),
                        'value' => 0,
                        'options' => !empty($datasets) ? $datasets : array(
                            '' => __('--- Select ---', 'e2pdf')
                                ),
                    ));
                    ?>
                </div>
            </div>

            <div class="e2pdf-grid">
                <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10 e2pdf-inline-radio e2pdf-align-right e2pdf-export-disposition">
                    <?php
                    $this->render('field', 'radio', array(
                        'field' => array(
                            'name' => 'disposition',
                        ),
                        'value' => 'inline',
                        'options' => array(
                            'attachment' => __('Download', 'e2pdf'),
                            'inline' => __('View', 'e2pdf')
                        )
                    ));
                    ?>
                </div>
            </div>

            <div class="e2pdf-grid">
                <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">

                </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10">
                    <div class="e2pdf-grid e2pdf-options">
                        <div class="e2pdf-grid">
                            <div class="e2pdf-ib e2pdf-w100 e2pdf-align-right">
                                <ul class="e2pdf-inline-links e2pdf-item-actions">
                                    <li><a id="e2pdf-delete-item" disabled="disabled" class="e2pdf-link e2pdf-delete-item e2pdf-color-red" href="javascript:void(0);" target="_blank"><?php _e('Delete Dataset', 'e2pdf'); ?></a></li>
                                    <li><a id="e2pdf-delete-items" disabled="disabled" class="e2pdf-link e2pdf-delete-items e2pdf-color-red" href="javascript:void(0);" target="_blank"><?php _e('Delete All Datasets', 'e2pdf'); ?></a></li>
                                    <li><a id="e2pdf-item-link" disabled="disabled" class="e2pdf-link e2pdf-item-link" href="javascript:void(0);" target="_blank"><?php _e('View Dataset', 'e2pdf'); ?></a></li>
                                    <li><a id="e2pdf-template-link" disabled="disabled" class="e2pdf-link e2pdf-template-link" href="javascript:void(0);" target="_blank"><?php _e('Edit Template', 'e2pdf'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="e2pdf-grid e2pdf-export-shortcodes">
                            <div class='e2pdf-ib e2pdf-w100'>
                                <h4 class="e2pdf-center"><?php _e('Shortcodes', 'e2pdf'); ?></h4>
                            </div>
                            <div id="e2pdf-template-shortcode-wr" class='e2pdf-ib e2pdf-w100 e2pdf-template-shortcode-wr'>
                                <div class="e2pdf-w100 e2pdf-center"><?php _e("Shortcode for Download Link with dynamic dataset", 'e2pdf'); ?></div>
                                <input id="e2pdf-template-shortcode" readonly="readonly" name="e2pdf-template-shortcode" class="e2pdf-center e2pdf-w100" type="text" value=''>
                            </div>
                            <div id="e2pdf-dataset-shortcode-wr" class='e2pdf-ib e2pdf-w100 e2pdf-dataset-shortcode-wr'>
                                <div class="e2pdf-w100 e2pdf-center"><?php _e("Shortcode for Download Link with current dataset", 'e2pdf'); ?></div>
                                <input id="e2pdf-dataset-shortcode" readonly="readonly" name="e2pdf-dataset-shortcode" class="e2pdf-center e2pdf-w100" type="text" value=''>
                            </div>
                        </div>

                        <div class="e2pdf-grid">
                            <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                                <?php _e('PDF Name', 'e2pdf'); ?>:
                            </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10">
                                <?php
                                $this->render('field', 'text', array(
                                    'field' => array(
                                        'name' => 'options[name]',
                                        'placeholder' => __('PDF Name', 'e2pdf'),
                                        'disabled' => 'disabled',
                                        'class' => 'e2pdf-w100'
                                    ),
                                    'value' => '',
                                ));
                                ?>
                            </div>
                        </div>


                        <div class="e2pdf-grid">
                            <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                                <?php _e('Password', 'e2pdf'); ?>:
                            </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10">
                                <?php
                                $this->render('field', 'text', array(
                                    'field' => array(
                                        'name' => 'options[password]',
                                        'placeholder' => __('Password', 'e2pdf'),
                                        'disabled' => 'disabled',
                                        'class' => 'e2pdf-w100'
                                    ),
                                    'value' => '',
                                ));
                                ?>
                            </div>
                        </div>

                        <div class="e2pdf-grid">
                            <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                                <?php _e('User', 'e2pdf'); ?>:
                            </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10">
                                <?php
                                $this->render('field', 'select', array(
                                    'field' => array(
                                        'name' => 'options[user_id]',
                                        'class' => 'e2pdf-w100'
                                    ),
                                    'value' => '0',
                                    'options' => $this->view->users,
                                ));
                                ?>
                            </div>
                        </div>

                        <div class="e2pdf-grid">
                            <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                                <?php _e('Flatten', 'e2pdf'); ?>:
                            </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10">
                                <?php
                                $this->render('field', 'select', array(
                                    'field' => array(
                                        'name' => 'options[flatten]',
                                        'class' => 'e2pdf-w100'
                                    ),
                                    'value' => '0',
                                    'options' => array(
                                        '0' => __('No', 'e2pdf'),
                                        '1' => __('Yes', 'e2pdf'),
                                        '2' => __('Full', 'e2pdf'),
                                    ),
                                ));
                                ?>
                            </div>
                        </div>
                        <div class="e2pdf-grid">
                            <div class="e2pdf-ib e2pdf-w30 e2pdf-pr10">
                                <?php _e('Format', 'e2pdf'); ?>:
                            </div><div class="e2pdf-ib e2pdf-w70 e2pdf-pl10">
                                <?php
                                $this->render('field', 'select', array(
                                    'field' => array(
                                        'name' => 'options[format]',
                                        'class' => 'e2pdf-w100'
                                    ),
                                    'value' => '0',
                                    'options' => array(
                                        'pdf' => __('pdf', 'e2pdf'),
                                        'jpg' => __('jpg', 'e2pdf')
                                    ),
                                ));
                                ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="e2pdf-center">
                <input id="e2pdf-export-form-submit" type="submit" form-id="e2pdf-export-form" disabled="disabled" class="button-primary button-large" value="<?php _e('Export', 'e2pdf'); ?>">
            </div>
        </form>
    </div>
</div>
<?php $this->render('blocks', 'debug-panel'); ?>



