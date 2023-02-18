        <div id="pf-tabs">
            <div class="pf-bu-tabs pf-bu-is-centered">
                <ul>
                    <li class="<?php echo $this->is_tab(0) ? 'pf-bu-is-active' : ''; ?>" data-id="0"><a href="#tab-standard"><?php _e('Standard', 'printfriendly'); ?></a></li>
                    <li class="<?php echo $this->is_tab(1) ? 'pf-bu-is-active' : ''; ?>" data-id="1"><a href="#tab-advanced"><?php _e('Advanced', 'printfriendly'); ?></a></li>
                    <?php if (WP_DEBUG) { ?>
                        <li><a href="#tab-debug"><?php _e('Debug', 'printfriendly'); ?></a></li>
                    <?php } ?>
                </ul>
            </div>

            <section id="tab-standard">
                <div class="pf-bu-container">

                    <?php include_once PRINTFRIENDLY_BASEPATH . '/views/pro.php'; ?>

                    <div class="pf-bu-block pf-bu-card">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Select content using', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content">
                            <?php
                            $disabled = $message = '';
                            if (class_exists('WooCommerce')) {
                                $disabled = 'disabled';
                                $message = '(' . __('Not available for WooCommerce', 'printfriendly') . ')';
                            }
                            ?>

                            <div>
                                <div>
                                    <label for="pf-algo-wp">
                                        <input id="pf-algo-wp" type="radio" name="<?php echo $this->option_name; ?>[pf_algo]" value="wp" <?php echo $this->options['pf_algo'] === 'wp' ? 'checked' : ''; ?> <?php echo $disabled; ?>>
                                        <?php _e('WP Template', 'printfriendly'); ?> <?php echo $message; ?>
                                    </label>
                                </div>

                                <div>
                                    <label for="pf-algo-pf">
                                        <input id="pf-algo-pf" type="radio" name="<?php echo $this->option_name; ?>[pf_algo]" value="pf" <?php echo $this->options['pf_algo'] === 'pf' ? 'checked' : ''; ?>>
                                        <?php _e('Content Algorithm', 'printfriendly'); ?>
                                    </label>
                                </div>

                                <div>
                                    <label for="pf-algo-css">
                                        <input id="pf-algo-css" type="radio" name="<?php echo $this->option_name; ?>[pf_algo]" value="css" <?php echo $this->options['pf_algo'] === 'css' ? 'checked' : ''; ?>>
                                        <?php _e('Custom CSS Selectors', 'printfriendly'); ?>
                                    </label>
                                    <div class="pf-algo-usage pf-algo-usage-css" style="display: none" data-tag-template="<?php echo esc_attr('<printfriendly-options data-selectors="#1" data-fallback-strategy="#2"></printfriendly-options>'); ?>">
                                        <div class="pf-bu-columns">
                                            <div class="pf-bu-column pf-bu-one-sixth pf-algo-usage-css-fields">
                                                <div>
                                                    <label for="pf-algo-usage-css-author"><?php _e('Author Selector', 'printfriendly'); ?></label>
                                                    <input type="text" id="pf-algo-usage-css-author" name="<?php echo $this->option_name; ?>[css-author]" value="<?php $this->echoVal('css-author'); ?>" data-selector-name="authorSelector">
                                                </div>
                                                <div>
                                                    <label for="pf-algo-usage-css-content"><?php _e('Content Selector', 'printfriendly'); ?></label>
                                                    <input type="text" id="pf-algo-usage-css-content" name="<?php echo $this->option_name; ?>[css-content]" value="<?php $this->echoVal('css-content'); ?>" data-selector-name="contentSelectors">
                                                </div>
                                                <div>
                                                    <label for="pf-algo-usage-css-date"><?php _e('Date Selector', 'printfriendly'); ?></label>
                                                    <input type="text" id="pf-algo-usage-css-date" name="<?php echo $this->option_name; ?>[css-date]" value="<?php $this->echoVal('css-date'); ?>" data-selector-name="dateSelector">
                                                </div>
                                                <div>
                                                    <label for="pf-algo-usage-css-title"><?php _e('Title Selector', 'printfriendly'); ?></label>
                                                    <input type="text" id="pf-algo-usage-css-title" name="<?php echo $this->option_name; ?>[css-title]" value="<?php $this->echoVal('css-title'); ?>" data-selector-name="titleSelector">
                                                </div>
                                                <div>
                                                    <label for="pf-algo-usage-css-image"><?php _e('Image Selector', 'printfriendly'); ?></label>
                                                    <input type="text" id="pf-algo-usage-css-image" name="<?php echo $this->option_name; ?>[css-primaryImage]" value="<?php $this->echoVal('css-primaryImage'); ?>" data-selector-name="primaryImageSelector">
                                                </div>
                                            </div>
                                            <div class="pf-bu-column pf-bu-one-sixth pf-algo-usage-css-strategy">
                                                <label><?php _e('Content Fallback Strategy', 'printfriendly'); ?></label>
                                                <label>
                                                    <input type="radio" id="pf_algo_css_fallback_original" name="<?php echo $this->option_name; ?>[pf_algo_css_content]" value="original" <?php echo ( empty($this->options['pf_algo_css_content']) || $this->options['pf_algo_css_content'] === 'original' ) ? 'checked' : ''; ?>>
                                                    <?php _e('Use existing rules to find content', 'printfriendly'); ?>
                                                </label>
                                                <label>
                                                    <input type="radio" id="pf_algo_css_fallback_error" name="<?php echo $this->option_name; ?>[pf_algo_css_content]" value="error-message" <?php echo $this->options['pf_algo_css_content'] === 'error-message' ? 'checked' : ''; ?>>
                                                    <?php _e('Show an error message', 'printfriendly'); ?>
                                                </label>
                                                <p class="description">
                                                <?php _e('This setting can be used to configure the behaviour of the plugin, when content CSS selector does not match any content.', 'printfriendly'); ?>
                                                <br/>
                                                <?php _e('We recommend setting this to "Use existing rules to find content" in production and to "Show an error message" in development.', 'printfriendly'); ?></p>
                                                <div class="pf-bu-mt-5"></div>
                                                <div class="pf-accordion">
                                                    <label><?php _e('Show Options Code', 'printfriendly'); ?></label>
                                                    <div>
                                                        <code class="pf-custom-css-code"><?php echo esc_html($customCssOptionCode); ?></code>
                                                        <a class="pf-clipboard pf-custom-css-code-snippet" href="javascript:;" data-clipboard-text="<?php echo esc_attr($customCssOptionCode); ?>"><span class="pf-bu-tag pf-bu-is-info pf-bu-is-light"><?php _e('Copy Code', 'printfriendly'); ?></span></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pf-bu-card-footer">
                            <p class="pf-bu-card-footer-item"><?php _e('Change this setting if your content is not showing in the preview.', 'printfriendly'); ?></p>
                            <p class="pf-bu-card-footer-item"><a href="https://support.printfriendly.com/wordpress/customize-content-selection/" target="_new"><?php _e('Documentation', 'printfriendly'); ?></a></p>
                        </div>
                    </div>

                    <div class="pf-bu-block pf-bu-card">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Button Style', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content">

                            <div class="pf-bu-columns pf-bu-is-multiline" id="button-style">
                            <?php
                                // if any images are added here, add to $__buttons tooo
                                $buttons = array(
                                    'buttongroup1' => array(
                                        'printfriendly-pdf-email-button.png',
                                        'printfriendly-pdf-email-button-md.png',
                                        'printfriendly-pdf-email-button-notext.png',
                                    ),
                                    'buttongroup2' => array(
                                        'printfriendly-pdf-button.png',
                                        'printfriendly-pdf-button-nobg.png',
                                        'printfriendly-pdf-button-nobg-md.png',
                                    ),
                                    'buttongroup3' => array(
                                        'printfriendly-button.png',
                                        'printfriendly-button-nobg.png',
                                        'printfriendly-button-md.png',
                                        'printfriendly-button-lg.png',
                                    ),
                                    'buttongroup4' => array(
                                        'print-button.png',
                                        'print-button-nobg.png',
                                        'print-button-gray.png',
                                    ),
                                );

                                foreach ($buttons as $class => $array) {
                                    foreach ($array as $button) {
                                        ?>
                                <div class="pf-bu-column pf-bu-is-one-quarter">
                                        <?php $this->radio('buttons/' . $button); ?>
                                </div>
                                        <?php
                                    }
                                }
                                ?>

                            </div>

                            <div class="pf-bu-columns">
                                <div class="pf-bu-column">
                                    <label>
                                        <input id="custom-btn" class="radio" name="<?php echo $this->option_name; ?>[button_type]" type="radio" value="custom-button" <?php echo $this->checked('button_type', 'custom-button', false); ?> >
                                        <?php _e('Custom Button', 'printfriendly'); ?>
                                    </label>

                                    <div class="pf-bu-tile pf-bu-is-ancestor">
                                        <div class="custom-btn pf-bu-tile pf-bu-is-4 pf-bu-is-flex-direction-column" id="custom-img">
                                            <h2><?php _e('Image', 'printfriendly'); ?></h2>
                                            <?php
                                                // if any images are added here, add to $__buttons tooo
                                                $icons = array(
                                                    'icons/printfriendly-icon-sm.png',
                                                    'icons/printfriendly-icon-md.png',
                                                    'icons/printfriendly-icon-lg.png',
                                                );
                                                foreach ($icons as $icon) {
                                                    $this->radio_custom_image($icon);
                                                }
                                                ?>
                                            <label for="custom-image-rb" class="radio-custom-btn">
                                                <input id="custom-image-rb" type="radio" name="<?php echo $this->option_name; ?>[custom_button_icon]" value="custom-image" <?php $this->checked('custom_button_icon', 'custom-image'); ?>>
                                                <?php _e('Use Your Image', 'printfriendly'); ?>
                                        
                                                <div id="enter-image-url">
                                                    <input type="button" class="pf_upload_image_button button button-secondary" data-pf-element="#custom_image" value="<?php _e('Select Image', 'printfriendly'); ?>">
                                                    <input id="custom_image" type="hidden" name="<?php echo $this->option_name; ?>[custom_image]" value="<?php $this->echoVal('custom_image'); ?>" />
                                                    <span id="custom_image_label">
                                                        <?php if (! empty($this->getVal('custom_image'))) { ?>
                                                        <img src="<?php $this->echoVal('custom_image'); ?>">
                                                        <?php } ?>
                                                    </span>
                                                    <div id="custom-img-sizes">
                                                        <?php _e('Width', 'printfriendly'); ?>
                                                        <input type="number" id="custom-img-width" min="0" class="small-text" name="<?php echo $this->option_name; ?>[custom_image_width]" value="<?php $this->echoVal('custom_image_width'); ?>" />px
                                                        <?php _e('Height', 'printfriendly'); ?>
                                                        <input type="number" id="custom-img-height" min="0" class="small-text" name="<?php echo $this->option_name; ?>[custom_image_height]" value="<?php $this->echoVal('custom_image_height'); ?>" />px
                                                    </div>
                                                    <div id="pf-custom-button-error"></div>
                                                </div>
                                            </label>

                                            <label class="radio-custom-btn">
                                                <input type="radio" name="<?php echo $this->option_name; ?>[custom_button_icon]" value="no-image" <?php $this->checked('custom_button_icon', 'no-image'); ?>>
                                                <?php _e('No Image', 'printfriendly'); ?>
                                            </label>
                                        </div>

                                        <div class="custom-btn  pf-bu-tile pf-bu-is-4 pf-bu-is-flex-direction-column" id="custom-txt">
                                            <h2><?php _e('Text', 'printfriendly'); ?></h2>
                                            <div id="txt-enter">
                                                <div class="pf-form-element">
                                                    <input id="custom-text-rb" type="radio" name="<?php echo $this->option_name; ?>[custom_button_text]" value="custom-text" <?php $this->checked('custom_button_text', 'custom-text'); ?>>
                                                    <input id="custom_text" type="text" size="10" class="clear regular-text" name="<?php echo $this->option_name; ?>[custom_text]" value="<?php $this->echoVal('custom_text'); ?>">

                                                    <div id="pf-txt-attributes">
                                                        <div id="txt-size">
                                                            <?php _e('Text Size', 'printfriendly'); ?>
                                                            <input type="number" id="text_size" min="9" max="25" class="small-text" name="<?php echo $this->option_name; ?>[text_size]" value="<?php $this->echoVal('text_size'); ?>" />
                                                            <input type="text" class="pf-color-picker" name="<?php echo $this->option_name; ?>[text_color]" id="text_color" value="<?php $this->echoVal('text_color'); ?>" />
                                                        </div>
                                                    </div>

                                                </div>
                                                
                                                <label>
                                                    <input type="radio" id="custom-text-no" name="<?php echo $this->option_name; ?>[custom_button_text]" value="no-text" <?php $this->checked('custom_button_text', 'no-text'); ?>>
                                                    <?php _e('No Text', 'printfriendly'); ?>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="custom-btn  pf-bu-tile pf-bu-is-4 pf-bu-is-flex-direction-column" id="custom-button-preview">
                                            <h2><?php _e('Preview', 'printfriendly'); ?></h2>
                                            <?php $this->custom_button_preview(); ?>
                                      </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pf-bu-block pf-bu-card">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Button Position', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content pf-features">
                            <div class="pf-label-inline">
                                <label for="pf_content_placement" class="pf-bu-label"><?php _e('Placement', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" id="pf_content_placement" name="<?php echo $this->option_name; ?>[content_placement]">
                                        <option value="before" <?php selected($this->options['content_placement'], 'before'); ?>><?php _e('Above Content', 'printfriendly'); ?></option>
                                        <option value="after" <?php selected($this->options['content_placement'], 'after'); ?>><?php _e('Below Content', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="pf-label-inline">
                                <label for="pf_button_alignment_method" class="pf-bu-label"><?php _e('Alignment Method', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" id="pf_button_alignment_method" name="<?php echo $this->option_name; ?>[button_alignment_method]">
                                        <option value="default" <?php selected($this->options['button_alignment_method'], 'default'); ?>><?php _e('Default', 'printfriendly'); ?></option>
                                        <option value="css" <?php selected($this->options['button_alignment_method'], 'css'); ?>><?php _e('CSS', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="pf-label-inline" id="pf-content-position">
                                <label for="pf_content_position" class="pf-bu-label"><?php _e('Alignment', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" id="pf_content_position" name="<?php echo $this->option_name; ?>[content_position]">
                                        <option value="left" <?php selected($this->options['content_position'], 'left'); ?>><?php _e('Left Align', 'printfriendly'); ?></option>
                                        <option value="right" <?php selected($this->options['content_position'], 'right'); ?>><?php _e('Right Align', 'printfriendly'); ?></option>
                                        <option value="center" <?php selected($this->options['content_position'], 'center'); ?>><?php _e('Center', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="pf-label-inline" id="pf-content-position-css">
                                <label for="pf_content_position_css" class="pf-bu-label"><?php _e('CSS', 'printfriendly'); ?></label>
                                <div>
                                    <textarea id="pf_content_position_css" rows="5" cols="80" name="<?php echo $this->option_name; ?>[content_position_css]"><?php echo esc_textarea($this->options['content_position_css']); ?></textarea>
                                    <p>Add your custom CSS (without the <code style="display:inline;padding:3px 2px">&lt;style&gt;</code> tags) in the box above.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="pf-bu-block pf-bu-card" id="button-display-setting">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Button Display', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content">
                            <label for="pages" class="pf-bu-label"><?php _e('Pages to show on', 'printfriendly'); ?></label>
                            <div id="pages">
                                <?php $this->create_checkbox('posts', __('Posts', 'printfriendly')); ?>
                                <?php $this->create_checkbox('pages', __('Pages', 'printfriendly')); ?>
                                <?php $this->create_checkbox('homepage', __('Homepage', 'printfriendly')); ?>
                                <?php $this->create_checkbox('categories', __('Category Index Pages', 'printfriendly')); ?>
                                <?php $this->create_checkbox('taxonomies', __('Taxonomy Index Pages', 'printfriendly')); ?>
                            </div>

                            <hr/>
                            <label for="categories" class="pf-bu-label"><?php _e('Restrict to specific categories', 'printfriendly'); ?></label>
                            <?php
                                wp_dropdown_categories(
                                    apply_filters(
                                        'printfriendly_category_args',
                                        array(
                                            'show_count'       => 0,
                                            'orderby'          => 'name',
                                            'hide_empty'        => false, // show a category even if it has no posts assigned
                                            'class'             => 'pf-select2',
                                            'name'              => 'printfriendly_option[show_on_cat]',
                                            'id'                => 'show_on_cat',
                                            'selected'          => isset($this->options['show_on_cat']) ? $this->options['show_on_cat'] : '',
                                            'multiple'          => true,
                                        )
                                    )
                                );
                                ?>

                            <hr/>

                            <div class="pf-accordion">
                                <label class="pf-bu-label"><?php echo _e('Add direct to template', 'printfriendly'); ?></label>
                                <div>
                                    <div>
                                        <span class="small-text">Use the following if you want the buttons in archive/category page to link to individual posts</span>
                                        <code>
                                        &lt;?php if( function_exists( 'pf_default_button' ) ){ echo pf_default_button(); } ?&gt;

                                        <a class="pf-clipboard" href="javascript:;" data-clipboard-text="<?php echo '&lt;?php if( function_exists( \'pf_default_button\' ) ) { echo pf_default_button(); } ?&gt;'; ?>"><span class="pf-bu-tag pf-bu-is-info pf-bu-is-dark"><?php _e('Copy Code', 'printfriendly'); ?></span></a>
                                        </code>
                                    </div>
                                    <div>
                                        <span class="small-text">Use the following if you want the button to always print the current page</span>
                                        <code>
                                        &lt;?php if( function_exists( 'pf_current_page_button' ) ){ echo pf_current_page_button(); } ?&gt;

                                        <a class="pf-clipboard" href="javascript:;" data-clipboard-text="<?php echo '&lt;?php if( function_exists( \'pf_current_page_button\' ) ) { echo pf_current_page_button(); } ?&gt;'; ?>"><span class="pf-bu-tag pf-bu-is-info pf-bu-is-dark"><?php _e('Copy Code', 'printfriendly'); ?></span></a>
                                        </code>
                                    </div>
                                </div>
                            </div>

                            <div class="pf-accordion">
                                <label class="pf-bu-label"><?php echo _e('Use the shortcode', 'printfriendly'); ?></label>
                                <div>
                                    <div>
                                        <span class="small-text">Use the following if you want the buttons in archive/category page to link to individual posts</span>
                                        <code>
                                        [printfriendly]

                                        <a class="pf-clipboard" href="javascript:;" data-clipboard-text="<?php echo '[printfriendly]'; ?>"><span class="pf-bu-tag pf-bu-is-info pf-bu-is-dark"><?php _e('Copy Code', 'printfriendly'); ?></span></a>
                                        </code>
                                    </div>
                                    <div>
                                        <span class="small-text">Use the following if you want the button to always print the current page</span>
                                        <code>
                                        [printfriendly current="yes"]

                                        <a class="pf-clipboard" href="javascript:;" data-clipboard-text="<?php echo '[printfriendly current=\'yes\']'; ?>"><span class="pf-bu-tag pf-bu-is-info pf-bu-is-dark"><?php _e('Copy Code', 'printfriendly'); ?></span></a>
                                        </code>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="pf-bu-block pf-bu-card">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Pro Exclusive Features', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content pf-features">
                            <div class="pf-label-inline">
                                <label for="password_protected" class="pf-bu-label"><?php _e('Encode images', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" id="password_protected" name="<?php echo $this->option_name; ?>[password_protected]">
                                        <option value="no" <?php selected($this->getVal('password_protected'), 'no'); ?>><?php _e('No', 'printfriendly'); ?></option>
                                        <option value="yes" <?php selected($this->getVal('password_protected'), 'yes'); ?>><?php _e('Yes', 'printfriendly'); ?></option>
                                    </select>
                                    <p class="description"><?php _e('Select "Yes" if your site is not publicly accessible or if your provider blocks image requests from third parties or if images are not present in PDF', 'printfriendly'); ?></p>
                                </div>
                            </div>

                            <div class="pf-label-inline">
                                <label for="show_hidden_content" class="pf-bu-label"><?php _e('Show Hidden Content', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" id="show_hidden_content" name="<?php echo $this->option_name; ?>[show_hidden_content]">
                                        <option value="no" <?php selected($this->getVal('show_hidden_content'), 'no'); ?>><?php _e('No', 'printfriendly'); ?></option>
                                        <option value="yes" <?php selected($this->getVal('show_hidden_content'), 'yes'); ?>><?php _e('Yes', 'printfriendly'); ?></option>
                                    </select>
                                    <p class="description"><?php _e('By default PrintFriendly Pro will only show the visible content on the page. Select "Yes", if you want PrintFriendly to show hidden content. (Ex. Content in hidden tabs)', 'printfriendly'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="pf-bu-card-footer">
                            <p class="pf-bu-card-footer-item"><?php _e('These features require a Pro subscription.', 'printfriendly'); ?>&nbsp;<a href="https://www.printfriendly.com/pro" target="_blank"><?php _e('Learn More', 'printfriendly'); ?></a></p>
                        </div>
                    </div>

                    <div class="pf-bu-container">
                        <div class="pf-bu-is-flex pf-bu-is-justify-content-center">
                            <input type="submit" class="button-primary pf-bu-is-medium pf-bu-button" value="<?php esc_attr_e('Save Options', 'printfriendly'); ?>" />
                            <input type="reset" style="margin-left: 10px" class="button-secondary pf-bu-is-medium pf-bu-button" value="<?php esc_attr_e('Cancel', 'printfriendly'); ?>" />
                        </div>
                    </div>
                </div>
            </section>

            <section id="tab-advanced">
                <div class="pf-bu-container">

                    <div class="pf-bu-block pf-bu-card">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Page header', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content">
                            <div class="pf-bu-columns">
                                <div class="pf-bu-column pf-bu-is-6">
                                    <label for="icon_favicon">
                                        <input id="icon_favicon" type="radio" name="<?php echo $this->option_name; ?>[logo]" value="favicon" <?php echo $this->options['logo'] === 'favicon' ? 'checked' : ''; ?>>
                                        <?php _e('My Website Icon', 'printfriendly'); ?>
                                    </label>
                                </div>

                                <div class="pf-bu-column pf-bu-is-6">
                                    <label for="icon-upload-an-image">
                                        <input id="icon-upload-an-image" type="radio" name="<?php echo $this->option_name; ?>[logo]" value="upload-an-image" <?php echo $this->options['logo'] === 'upload-an-image' ? 'checked' : ''; ?>>
                                        <?php _e('Custom Image', 'printfriendly'); ?>

                                        <div id="custom-logo"><div class="pf-bu-tile pf-bu-is-ancestor">
                                            <div class="pf-bu-tile pf-bu-is-vertical">
                                                <div class="pf-bu-tile pf-bu-is-4 pf-bu-is-flex-direction-column">
                                                    <div>
                                                        <input type="button" class="pf_upload_image_button button button-secondary" data-pf-element="#custom_logo" value="<?php _e('Select Image', 'printfriendly'); ?>">
                                                        <input id="custom_logo" type="hidden" name="<?php echo $this->option_name; ?>[image_url]" value="<?php $this->echoVal('image_url'); ?>" />
                                                        <span id="custom_logo_label">
                                                            <?php if (! empty($this->getVal('image_url'))) { ?>
                                                            <img src="<?php $this->echoVal('image_url'); ?>">
                                                            <?php } ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="pf-bu-tile pf-bu-is-4 pf-bu-is-flex-direction-column">
                                                    <div>
                                                        <div class="pf-bu-field pf-bu-has-addons">
                                                          <p class="pf-bu-control">
                                                            <a class="pf-bu-button pf-bu-is-static">
                                                              <?php _e('Tagline (optional)', 'printfriendly'); ?>
                                                            </a>
                                                          </p>
                                                          <p class="pf-bu-control">
                                                            <input id="image-tagline" type="text" class="pf-bu-input regular-text pf-regular-text" name="<?php echo $this->option_name; ?>[tagline]" value="<?php $this->echoVal('tagline'); ?>" />
                                                          </p>
                                                        </div>                                                  
                                                    </div>
                                                </div>
                                            </div>
                                        </div></div>

                                    </label>
                                </div>
                            </div>
                            <div id="pf-image-error"></div>
                            <div id="pf-image-preview"></div>
                        </div>

                        <div class="pf-bu-card-footer">
                            <p class="pf-bu-card-footer-item"><a href="https://support.printfriendly.com/wordpress/custom-header-footer/" target="_new"><?php _e('Documentation', 'printfriendly'); ?></a></p>
                        </div>
                    </div>

                    <div class="pf-bu-block pf-bu-card">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Features', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content pf-features">
                            <div class="pf-label-inline">
                                <label for="pf-analytics-tracking" class="pf-bu-label"><?php _e('Track in Google Analytics', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" id="pf-analytics-tracking" name="<?php echo $this->option_name; ?>[enable_google_analytics]">
                                        <option value="yes" <?php $this->selected('enable_google_analytics', 'yes'); ?>> <?php _e('Yes', 'printfriendly'); ?></option>
                                        <option value="no" <?php $this->selected('enable_google_analytics', 'no'); ?>> <?php _e('No', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="pf-label-inline">
                                <label for="click-to-delete" class="pf-bu-label"><?php _e('Click to delete', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" name="<?php echo $this->option_name; ?>[click_to_delete]" id="click-to-delete">
                                        <option value="0" <?php selected($this->options['click_to_delete'], '0'); ?>><?php _e('Allow', 'printfriendly'); ?></option>
                                        <option value="1" <?php selected($this->options['click_to_delete'], '1'); ?>><?php _e('Not Allow', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="pf-label-inline">
                                <label for="images-size" class="pf-bu-label"><?php _e('Image size', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" name="<?php echo $this->option_name; ?>[images-size]" id="images-size">
                                        <option value="full-size" <?php selected($this->options['images-size'], 'full-size'); ?>><?php _e('Full Size', 'printfriendly'); ?></option>
                                        <option value="large" <?php selected($this->options['images-size'], 'large'); ?>><?php _e('Large', 'printfriendly'); ?></option>
                                        <option value="medium" <?php selected($this->options['images-size'], 'medium'); ?>><?php _e('Medium', 'printfriendly'); ?></option>
                                        <option value="small" <?php selected($this->options['images-size'], 'small'); ?>><?php _e('Small', 'printfriendly'); ?></option>
                                        <option value="remove-images" <?php selected($this->options['images-size'], 'remove-images'); ?>><?php _e('Remove Images', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="pf-label-inline">
                                <label for="image-style" class="pf-bu-label"><?php _e('Image style', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" name="<?php echo $this->option_name; ?>[image-style]" id="image-style">
                                        <option value="block" <?php selected($this->options['image-style'], 'block'); ?>><?php _e('Center/Block', 'printfriendly'); ?></option>
                                        <option value="right" <?php selected($this->options['image-style'], 'right'); ?>><?php _e('Align Right', 'printfriendly'); ?></option>
                                        <option value="left" <?php selected($this->options['image-style'], 'left'); ?>><?php _e('Align Left', 'printfriendly'); ?></option>
                                        <option value="none" <?php selected($this->options['image-style'], 'none'); ?>><?php _e('Align None', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="pf-label-inline">
                                <label for="email" class="pf-bu-label"><?php _e('Email', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" name="<?php echo $this->option_name; ?>[email]" id="email">
                                        <option value="0" <?php selected($this->options['email'], '0'); ?>><?php _e('Allow', 'printfriendly'); ?></option>
                                        <option value="1" <?php selected($this->options['email'], '1'); ?>><?php _e('Not Allow', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="pf-label-inline">
                                <label for="pdf" class="pf-bu-label"><?php _e('PDF', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" name="<?php echo $this->option_name; ?>[pdf]" id="pdf">
                                        <option value="0" <?php selected($this->options['pdf'], '0'); ?>><?php _e('Allow', 'printfriendly'); ?></option>
                                        <option value="1" <?php selected($this->options['pdf'], '1'); ?>><?php _e('Not Allow', 'printfriendly'); ?></option>
                                    </select>
                                    <p class="description"><strong><?php _e('Developer Note', 'printfriendly'); ?></strong>: <?php _e('On localhost the images can not be included in the PDF. Once the website is live/public images will be included in the PDF.', 'printfriendly'); ?></p>
                                </div>
                            </div>

                            <div class="pf-label-inline">
                                <label for="print" class="pf-bu-label"><?php _e('Print', 'printfriendly'); ?></label>
                                <div>
                                    <select class="pf-bu-select" name="<?php echo $this->option_name; ?>[print]" id="print">
                                        <option value="0" <?php selected($this->options['print'], '0'); ?>><?php _e('Allow', 'printfriendly'); ?></option>
                                        <option value="1" <?php selected($this->options['print'], '1'); ?>><?php _e('Not Allow', 'printfriendly'); ?></option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="pf-bu-block pf-bu-card">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Custom CSS', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content pf-features">
                            <?php if ($this->is_pro('custom-css')) { ?>
                                <div class="pf-label-inline">
                                    <label for="custom_css" class="pf-bu-label"><?php _e('CSS', 'printfriendly'); ?></label>
                                    <div>
                                        <textarea id="custom_css" class="regular-text pf-bu-textarea" rows="5" cols="80" name="<?php echo $this->option_name; ?>[custom_css]"><?php echo html_entity_decode(esc_textarea($this->getVal('custom_css'))); ?></textarea>
                                        <p class="desc"><?php echo $this->get_custom_css_upgrade_message(); ?></p>
                                    </div>
                                </div>
                                <div class="pf-label-inline" style="margin-top: 30px">
                                    <label for="css_include_via" class="pf-bu-label"><?php _e('Include Via', 'printfriendly'); ?></label>
                                    <div>
                                        <select class="pf-bu-select" name="<?php echo $this->option_name; ?>[css_include_via]" id="css_include_via">
                                            <option value="inline_tag" <?php selected($this->options['css_include_via'], 'inline_tag'); ?>><?php _e('Inline Tag', 'printfriendly'); ?></option>
                                            <option value="file" <?php selected($this->options['css_include_via'], 'file'); ?>><?php _e('File', 'printfriendly'); ?></option>
                                        </select>
                                        <?php if ($this->getVal('css_include_via') == 'file') { ?>
                                        <p class="desc pf-bu-has-background-warning-light pf-bu-py-1 pf-bu-px-2">We recommend setting this to Inline Tag</p>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <label for="custom_css_url">
                                  <?php _e('Custom CSS URL', 'printfriendly'); ?>
                                  <input id="custom_css_url" type="url" class="regular-text" name="<?php echo $this->option_name; ?>[custom_css_url]" value="<?php $this->echoVal('custom_css_url'); ?>" />
                                </label>
                            <?php } ?>
                        </div>

                        <div class="pf-bu-card-footer">
                            <div class="pf-bu-card-footer-item">
                                <div>
                                    <p>Customize the styles of the printed/pdf page using your own CSS</p>
                                    <p>Add your custom CSS (without the <code style="display:inline;padding:3px 2px">&lt;style&gt;</code> tags) in the box above.
                                    <a href="https://support.printfriendly.com/wordpress/custom-css/" target="_new">Learn More</a>
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="pf-bu-container">
                        <div class="pf-bu-is-flex pf-bu-is-justify-content-center">
                            <input type="submit" class="button-primary pf-bu-is-medium pf-bu-button" value="<?php esc_attr_e('Save Options', 'printfriendly'); ?>" />
                            <input type="reset" style="margin-left: 10px" class="button-secondary pf-bu-is-medium pf-bu-button" value="<?php esc_attr_e('Cancel', 'printfriendly'); ?>" />
                        </div>
                    </div>

                </div>
            </section>


            <?php if (WP_DEBUG) { ?>
            <section id="tab-debug">
                <div class="pf-bu-container">
                    <div class="pf-bu-block pf-bu-card">
                        <header class="pf-bu-card-header">
                            <p class="pf-bu-card-header-title">
                                <?php _e('Debug options', 'printfriendly'); ?>
                            </p>
                        </header>

                        <div class="pf-bu-card-content">
                            <pre><?php echo print_r($this->options, 1); ?></pre>
                        </div>

                        <div class="pf-bu-card-footer">
                            <p class="pf-bu-card-footer-item"><?php _e('Currently in Debug Mode. This tab is visible in debug mode only', 'printfriendly'); ?></p>
                        </div>
                    </div>
                </div>
            </section>
            <?php } ?>

          </div>
