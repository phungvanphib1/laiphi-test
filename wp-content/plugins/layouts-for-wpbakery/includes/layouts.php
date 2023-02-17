<div class="lfw-body">
    <div class="lfw-header">
        <h1 class="wp-heading-inline"><?php _e('Layouts for WPBakery', LFW_TEXTDOMAIN); ?></h1>
    </div>
    <div id="lfw-wrap" class="lfw-wrap">
        <div class="lfw-header">
            <div class="lfw-title lfw-is-inline"><h2 class="lfw-title"><?php _e('WPBakery Template Kits:', LFW_TEXTDOMAIN); ?></h2></div>
            <div class="lfw-sync lfw-is-inline">
                <a href="javascript:void(0);" class="lfw-sync-btn"><?php _e('Sync Now', LFW_TEXTDOMAIN); ?></a>
            </div>
        </div>
        <?php
        $categories = Layouts_WPB_Remote::lfw_get_instance()->categories_list();

        if (!empty($categories['category']) && $categories != "") {
            ?>
            <div class="collection-bar">
                <h4><?php _e('Browse by Industry', LFW_TEXTDOMAIN); ?></h4>
                <ul class="collection-list">
                    <li><a class="lfw-category-filter active" data-filter="all" href="javascript:void(0)"><?php _e('All', LFW_TEXTDOMAIN); ?></a></li>
                    <?php
                    foreach ($categories['category'] as $cat) {
                        ?>
                        <li><a href="javascript:void(0);" class="lfw-category-filter" data-filter="<?php echo $cat['slug']; ?>" ><?php echo $cat['title']; ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>

        <div class="lfw_wrapper">
            <?php
            $data = Layouts_WPB_Remote::lfw_get_instance()->templates_list();
            $i = 0;
            if (!empty($data['templates']) && $data !== "") {
                foreach ($data['templates'] as $key => $val) {
                    $categories = "";
                    foreach ($val['category'] as $ckey => $cval) {
                        $categories .= sanitize_title($cval) . " ";
                    }
                    ?>
                    <div class="lfw_box lfw_filter <?php echo $categories; ?>">
                        <div class="lfw_box_widget">
                            <div class="lfw-media">
                                <img src="<?php echo $val['thumbnail']; ?>" alt="screen 1">
                                <?php if ($val['is_premium'] == true) { ?>
                                    <span class="pro-btn"><?php echo _e('PRO', LFW_TEXTDOMAIN); ?></span>
                                <?php } else { ?>
                                    <span class="free-btn"><?php echo _e('FREE', LFW_TEXTDOMAIN); ?></span>
                                <?php } ?>
                            </div>
                            <div class="lfw-template-title"><?php _e($val['title'], LFW_TEXTDOMAIN); ?></div>
                            <div class="lfw-btn">
                                <a href="javascript:void(0);" data-url="<?php echo esc_url($val['url']); ?>" title="<?php _e('Preview', LFW_TEXTDOMAIN); ?>" class="btn pre-btn previewbtn"><?php _e('Preview', LFW_TEXTDOMAIN); ?></a>
                                <a href="javascript:void(0);" title="<?php _e('Install', LFW_TEXTDOMAIN); ?>" class="btn ins-btn installbtn"><?php _e('Install', LFW_TEXTDOMAIN); ?></a>
                            </div>
                        </div>
                    </div>

                    <!-- Preview popup div start -->
                    <div class="lfw-preview-popup" id="preview-in-<?php echo $i; ?>">
                        <div class="lfw-preview-container">
                            <div class="lfw-preview-header">
                                <div class="lfw-preview-title"><?php echo $val['title']; ?></div>
                                <?php
                                if ($val['is_premium'] == true) {

                                    $current_user = wp_get_current_user();
                                    if (!is_wp_error($current_user) && !empty($current_user->user_email)) {

                                    }
                                    ?>
                                    <div class="lfw-buy">
                                        <p class="lfw-buy-msg"><?php _e('This template is premium version', LFW_TEXTDOMAIN); ?></p>
                                        <span class="lfw-buy-loader"></span>

                                        <a href="javascript:void(0);" class="btn ins-btn lfw-buy-btn" disabled data-template-id="<?php echo $val['id']; ?>" ><?php _e('Buy Now', LFW_TEXTDOMAIN); ?></a>
                                        <a href="javascript:void(0);" class="btn ins-btn lfw-buy-template" style="display:none" target="_blank"><?php _e('Edit Template', LFW_TEXTDOMAIN); ?></a>
                                    </div>
                                <?php } else { ?>
                                    <div class="lfw-import">
                                        <p class="lfw-msg"><?php _e('Import this template via one click', LFW_TEXTDOMAIN); ?></p>
                                        <span class="lfw-loader"></span>

                                        <a href="javascript:void(0);" class="btn ins-btn lfw-import-btn" disabled data-template-id="<?php echo $val['id']; ?>" ><?php _e('Import Template', LFW_TEXTDOMAIN); ?></a>
                                        <a href="javascript:void(0);" class="btn ins-btn lfw-edit-template" style="display:none" target="_blank"><?php _e('Edit Template', LFW_TEXTDOMAIN); ?></a>
                                    </div>

                                    <span><?php _e('OR', LFW_TEXTDOMAIN); ?></span>

                                    <div class="lfw-import lfw-page-create">
                                        <p><?php _e('Create a new page from this template', LFW_TEXTDOMAIN); ?></p>
                                        <input type="text" class="lfw-page-name-<?php echo $val['id']; ?>" placeholder="Enter a Page Name" />
                                        <a href="javascript:void(0);" class="btn ins-btn lfw-create-page-btn" data-template-id="<?php echo $val['id']; ?>" ><?php _e('Create New Page', LFW_TEXTDOMAIN); ?></a>
                                    </div>

                                    <span class="lfw-loader-page"></span>

                                    <div class="lfw-import lfw-page-edit" style="display:none" >
                                        <p><?php _e('Your template is successfully imported!', LFW_TEXTDOMAIN); ?></p>
                                        <a href="javascript:void(0);" class="btn ins-btn lfw-edit-page" target="_blank" ><?php _e('Edit Page', LFW_TEXTDOMAIN); ?></a>
                                    </div>
                                    <div class="lfw-import lfw-page-error" style="display:none" >
                                        <p class="lfw-error"><?php _e('Something went wrong!', LFW_TEXTDOMAIN); ?></p>
                                    </div>
                                <?php } ?>
                                <span class="lfw-close-icon"></span>

                                <a href="<?php echo esc_url($val['url']); ?>" class="lfw-dashicons-link" title="<?php _e('Open Preview in New Tab', LFW_TEXTDOMAIN); ?>" rel="noopener noreferrer" target="_blank">
                                    <span class="lfw-dashicons"></span>
                                </a>
                            </div>
                            <iframe width="100%" height="100%" src=""></iframe>
                        </div>
                    </div>
                    <!-- Preview popup div end -->

                    <!-- Install popup div start -->
                    <div class="lfw-install-popup" id="content-in-<?php echo $i; ?>">
                        <div class="lfw-container">
                            <div class="lfw-install-header">
                                <div class="lfw-install-title"><?php echo $val['title']; ?></div>
                                <span class="lfw-close-icon"></span>
                            </div>
                            <div class="lfw-install-content">

                                <?php
                                if ($val['is_premium'] == true) {

                                    $current_user = wp_get_current_user();
                                    if (!is_wp_error($current_user) && !empty($current_user->user_email)) {

                                    }
                                    ?>
                                    <p class="lfw-msg"><?php _e('This template is premium version', LFW_TEXTDOMAIN); ?></p>
                                    <div class="lfw-btn">
                                        <span class="lfw-loader"></span>
                                        <a href="javascript:void(0);" class="btn ins-btn lfw-buy-btn" data-template-id="<?php echo $val['id']; ?>" ><?php _e('Buy Now', LFW_TEXTDOMAIN); ?></a>
                                        <a href="javascript:void(0);" class="btn ins-btn lfw-buy-template" style="display:none" target="_blank"><?php _e('Edit Template', LFW_TEXTDOMAIN); ?></a>
                                    </div>

                                <?php } else { ?>

                                    <p class="lfw-msg"><?php _e('Import this template via one click', LFW_TEXTDOMAIN); ?></p>
                                    <div class="lfw-btn">
                                        <span class="lfw-loader"></span>
                                        <a href="javascript:void(0);" class="btn ins-btn lfw-import-btn" data-template-id="<?php echo $val['id']; ?>" ><?php _e('Import Template', LFW_TEXTDOMAIN); ?></a>
                                        <a href="javascript:void(0);" class="btn ins-btn lfw-edit-template" style="display:none" target="_blank"><?php _e('Edit Template', LFW_TEXTDOMAIN); ?></a>
                                    </div>

                                    <p class="lfw-horizontal"><?php _e('OR', LFW_TEXTDOMAIN); ?></p>

                                    <div class="lfw-page-create">
                                        <p><?php _e('Create a new page from this template', LFW_TEXTDOMAIN); ?></p>
                                        <input type="text" class="lfw-page-<?php echo $val['id']; ?>" placeholder="Enter a Page Name" />
                                        <div class="lfw-btn">
                                            <a href="javascript:void(0);" style="padding: 0;" class="btn pre-btn lfw-create-page-btn" data-name="crtbtn" data-template-id="<?php echo $val['id']; ?>" ><?php _e('Create New Page', LFW_TEXTDOMAIN); ?></a>
                                            <span class="lfw-loader-page"></span>
                                        </div>
                                    </div>
                                    <div class="lfw-create-div lfw-page-edit" style="display:none" >
                                        <p style="color: #000;"><?php _e('Your page is successfully imported!', LFW_TEXTDOMAIN); ?></p>
                                        <div class="lfw-btn">
                                            <a href="javascript:void(0);" class="btn pre-btn lfw-edit-page" target="_blank" ><?php _e('Edit Page', LFW_TEXTDOMAIN); ?></a>
                                        </div>
                                    </div>
                                    <div class="lfw-import lfw-page-error" style="display:none;" >
                                        <p class="lfw-error" style="color: #444;"><?php _e('Something went wrong!', LFW_TEXTDOMAIN); ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <!-- Install popup div end -->
                    <?php
                    $i++;
                }
            } else {
                echo $data['message'];
            }
            ?>
        </div>
    </div>
</div>
