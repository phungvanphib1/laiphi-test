<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols

/*
    Plugin Name: Print, PDF & Email by PrintFriendly
    Plugin URI: https://www.printfriendly.com
    Description: PrintFriendly & PDF button for your website. Optimizes your pages and brand for print, pdf, and email.
    Name and URL are included to ensure repeat visitors and new visitors when printed versions are shared.
    Version: 5.5.0
    Author: Print, PDF, & Email by PrintFriendly
    Author URI: https://www.printfriendly.com
    Domain Path: /languages
    Text Domain: printfriendly
*/

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
// phpcs:disable PSR2.Classes.PropertyDeclaration.VarUsed
// phpcs:disable PSR2.Classes.PropertyDeclaration.ScopeMissing
// phpcs:disable Squiz.Scope.MethodScope.Missing
// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * PrintFriendly WordPress plugin. Allows easy embedding of printfriendly.com buttons.
 *
 * @package   PrintFriendly_WordPress
 * @author    PrintFriendly <support@printfriendly.com>
 * @copyright Copyright (C) 2023, PrintFriendly
 */

if (! class_exists('PrintFriendly_WordPress')) {
    /**
     * Class containing all the plugins functionality.
     *
     * @package PrintFriendly_WordPress
     */
    class PrintFriendly_WordPress
    {
        /**
         * Current plugin version.
         *
         * @var string
         */
        var $plugin_version = '5.5.0';
        /**
         * The hook, used for text domain as well as hooks on pages and in get requests for admin.
         *
         * @var string
         */
        var $hook = 'printfriendly';
        /**
         * The option name, used throughout to refer to the plugins option and option group.
         *
         * @var string
         */
        var $option_name = 'printfriendly_option';
        /**
         * The plugins options, loaded on init containing all the plugins settings.
         *
         * @var array
         */
        var $options = array();
        /**
         * Database version, used to allow for easy upgrades to / additions in plugin options between plugin versions.
         *
         * @var int
         */
        var $db_version = 21;
        /**
         * Settings page, used within the plugin to reliably load the plugins admin JS and CSS files only on the admin page.
         *
         * @var string
         */
        var $settings_page = '';
        /**
         * List of all buttons with their attributes.
         *
         * @var array
         */
        private static $_buttons = array( // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore
            'buttons/printfriendly-pdf-email-button.png' => array( 'width' => 170, 'height' => 24 ),
            'buttons/printfriendly-pdf-email-button-md.png' => array( 'width' => 194, 'height' => 30 ),
            'buttons/printfriendly-pdf-email-button-notext.png' => array( 'width' => 110, 'height' => 30 ),
            'buttons/printfriendly-pdf-button.png' => array( 'width' => 112, 'height' => 24 ),
            'buttons/printfriendly-pdf-button-nobg.png' => array( 'width' => 112, 'height' => 24 ),
            'buttons/printfriendly-pdf-button-nobg-md.png' => array( 'width' => 124, 'height' => 30 ),
            'buttons/printfriendly-button.png' => array( 'width' => 112, 'height' => 24 ),
            'buttons/printfriendly-button-nobg.png' => array( 'width' => 112, 'height' => 24 ),
            'buttons/printfriendly-button-md.png' => array( 'width' => 124, 'height' => 30 ),
            'buttons/printfriendly-button-lg.png' => array( 'width' => 154, 'height' => 28 ),
            'buttons/print-button.png' => array( 'width' => 66, 'height' => 24 ),
            'buttons/print-button-nobg.png' => array( 'width' => 66, 'height' => 24 ),
            'buttons/print-button-gray.png' => array( 'width' => 66, 'height' => 24 ),
            'icons/printfriendly-icon-sm.png' => array( 'width' => 17, 'height' => 17 ),
            'icons/printfriendly-icon-md.png' => array( 'width' => 16, 'height' => 16 ),
            'icons/printfriendly-icon-lg.png' => array( 'width' => 25, 'height' => 25 ),
        );

        /**
         * Constructor
         *
         * @since 3.0
         */
        function __construct()
        {
            define('PRINTFRIENDLY_BASEPATH', dirname(__FILE__));
            define('PRINTFRIENDLY_BASEURL', plugins_url('/', __FILE__));
            // Retrieve the plugin options
            $this->options = get_option($this->option_name);
            // If the options array is empty, set defaults
            if (! is_array($this->options)) {
                $this->set_defaults();
            }

            /**
             * Set page content selection option "WordPress Standard/Strict" to "WP Template"
             */
            if (isset($this->options['pf_algo']) && $this->options['pf_algo'] === 'ws') {
                $this->options['pf_algo'] = 'wp';
                update_option($this->option_name, $this->options);
            }

            // If the version number doesn't match, upgrade
            if ($this->db_version > $this->options['db_version']) {
                $this->upgrade();
            }

            $this->public_hooks();
            if (is_admin()) {
                $this->admin_hooks();
            }
        }

        /**
         * All public hooks.
         */
        function public_hooks()
        {
            add_action('wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ));
            add_action('wp_head', array(&$this, 'front_head'));

            // automaticaly add the link
            add_filter('the_content', array(&$this, 'show_link_on_content'));
            add_filter('the_excerpt', array(&$this, 'show_link_on_excerpt'));

            add_action('the_content', array(&$this, 'add_pf_content_class_around_content_hook'));
        }

        /**
         * Load front end dependencies.
         */
        function wp_enqueue_scripts()
        {
            wp_enqueue_script('jquery');
        }

        /**
         * All admin hooks.
         */
        function admin_hooks()
        {
            // Hook into init for registration of the option and the language files
            add_action('admin_init', array(&$this, 'init'));
            // Register the settings page
            add_action('admin_menu', array(&$this, 'add_config_page'));
            // Enqueue the needed scripts and styles
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
            // Register a link to the settings page on the plugins overview page
            add_filter('plugin_action_links', array(&$this, 'filter_plugin_actions'), 10, 2);
            add_filter('plugin_row_meta', array(&$this, 'additional_links'), 10, 2);
            add_filter('wp_dropdown_cats', array( &$this, 'wp_dropdown_cats_multiple' ), 10, 2);
        }

        /**
         * Adds multiple select support for category dropdown.
         *
         * @since 4.2
         **/
        function wp_dropdown_cats_multiple($output, $attributes)
        {
            if (isset($attributes['multiple']) && $attributes['multiple']) {
                $output = preg_replace('/^<select/i', '<select multiple', $output);
                $output = str_replace("name='{$attributes['name']}'", "name='{$attributes['name']}[]'", $output);
                foreach (array_map('trim', explode(',', $attributes['selected'])) as $value) {
                    $output = str_replace("value=\"{$value}\"", "value=\"{$value}\" selected", $output);
                }
            }
            return $output;
        }

        /**
         * Adds wraps content in pf-content class to help Printfriendly algo determine the content
         *
         * @since 3.2.8
         **/
        function add_pf_content_class_around_content_hook($content = false)
        {
            if ($this->is_enabled() && $this->is_wp_algo_on($content)) {
                add_action('wp_footer', array(&$this, 'print_script_footer'));
                return '<div class="pf-content">' . $content . '</div>';
            } else {
                return $content;
            }
        }

        /**
         *  Override to check if print-only command is being used
         *
         * @since 3.3.0
         **/
        function print_only_override($content)
        {
            $pattern = '/class\s*?=\s*?(["\']|["\']([^"\']*?)\s)print-only(["\']|\s([^"\']*?)["\'])/';
            $pf_pattern = '/class\s*?=\s*?(["\']|["\']([^"\']*?)\s)pf-content(["\']|\s([^"\']*?)["\'])/';
            return ( preg_match($pattern, $content) || preg_match($pf_pattern, $content) );
        }

        /**
         *  Check if WP Algorithm is selected and content doesn't use print-only
         *
         * @since 3.5.4
         **/
        function is_wp_algo_on($content)
        {
            return ! class_exists('WooCommerce') && $content && $this->getVal('pf_algo') === 'wp' && ! $this->print_only_override($content);
        }

        /**
         * PHP 4 Compatible Constructor
         *
         * @since 3.0
         */
        function PrintFriendly_WordPress()
        {
            $this->__construct();
        }

        /**
         * Check if button should be visible on particular page
         */
        function is_enabled()
        {
            if (
                ( is_page() && 'on' === $this->getVal('show_on_pages') )
                || ( is_home() && 'on' === $this->getVal('show_on_homepage') )
                || ( is_tax() && 'on' === $this->getVal('show_on_taxonomies') && $this->category_included() )
                || ( is_category() && 'on' === $this->getVal('show_on_categories')  && $this->category_included() )
                || ( is_single() && 'on' === $this->getVal('show_on_posts')  && $this->category_included() )
            ) {
                return true;
            }
            return false;
        }

        /**
         * Determines the CSS rules to return for the `printfriendly` class.
         *
         * @since 5.2.5
         */
        function get_css_rules_for_button()
        {
            $css = array( 'z-index: 1000' );

            if ($this->getVal('button_alignment_method') === 'default') {
                $css[] = 'display: flex';
                $css[] = sprintf(
                    'margin: %dpx %dpx %dpx %dpx',
                    $this->getVal('margin_top', 0),
                    $this->getVal('margin_right', 0),
                    $this->getVal('margin_bottom', 0),
                    $this->getVal('margin_left', 0)
                );
            } else {
                $css[] = 'position: relative';
            }

            return implode('; ', $css);
        }

        /**
         * Determines the CSS rules to return for the front end.
         *
         * @since 5.2.5
         */
        function get_main_css()
        {
            if ($this->getVal('button_alignment_method') === 'css') {
                return '
					@media print {
						.printfriendly {
							display: none;
						}
					}
				';
            }

            return sprintf(
                '
				@media screen {
					.printfriendly {
						%s
					}
					.printfriendly a, .printfriendly a:link, .printfriendly a:visited, .printfriendly a:hover, .printfriendly a:active {
						font-weight: 600;
						cursor: pointer;
						text-decoration: none;
						border: none;
						-webkit-box-shadow: none;
						-moz-box-shadow: none;
						box-shadow: none;
						outline:none;
						font-size: %dpx !important;
						color: %s !important;
					}
					.printfriendly.pf-alignleft {
						%s
					}
					.printfriendly.pf-alignright {
						%s
					}
					.printfriendly.pf-aligncenter {
						justify-content: center;
						%s
					}
				}
				
				.pf-button-img {
					border: none;
					-webkit-box-shadow: none; 
					-moz-box-shadow: none; 
					box-shadow: none; 
					padding: 0; 
					margin: 0;
					display: inline; 
					vertical-align: middle;
				}
			  
				img.pf-button-img + .pf-button-text {
					margin-left: 6px;
				}

				@media print {
					.printfriendly {
						display: none;
					}
				}
				',
                $this->get_css_rules_for_button(),
                $this->getVal('text_size'),
                $this->getVal('text_color'),
                $this->getVal('button_alignment_method') === 'default' ? 'justify-content: start;' : 'float: left;',
                $this->getVal('button_alignment_method') === 'default' ? 'justify-content: end;' : 'float: right;',
                $this->getVal('button_alignment_method') === 'default' ? '' : 'display: flex; align-items: center;'
            );
        }

        /**
         * Returns the custom CSS (including the style tag) for content positioning.
         *
         * @since 5.2.5
         */
        function get_content_position_css_tag()
        {
            $css = $this->getVal('content_position_css');
            if (! empty($css)) {
                return sprintf('<style type="text/css" id="pf-button-css">%s</style>', $css);
            }
            return null;
        }

        /**
         * Prints the PrintFriendly button CSS, in the header.
         *
         * @since 3.0
         */
        function front_head()
        {
            if (! $this->is_enabled()) {
                return;
            }

            if ($this->getVal('enable_css') !== 'on') {
                return;
            }
            ?>
        <style type="text/css" id="pf-main-css">
            <?php echo $this->get_main_css(); ?>
        </style>

            <?php echo $this->get_content_position_css_tag(); ?>

        <style type="text/css" id="pf-excerpt-styles">
          .pf-button.pf-button-excerpt {
              display: none;
           }
        </style>

            <?php
        }

        /**
         * Prints the PrintFriendly JavaScript, in the footer, and loads it asynchronously.
         *
         * @since 3.0
         */
        function print_script_footer()
        {
            $tagline = $this->getVal('tagline');
            $image_url = $this->getVal('image_url');
            if ($this->getVal('logo') === 'favicon') {
                $tagline = '';
                $image_url = '';
            }

            echo $this->getSelectorsFromCustomCSS();

            // Currently we use v3 for both: normal and password protected sites
            ?>
     <script type="text/javascript" id="pf_script">
            <?php echo $this->get_analytics_code(); ?>
          var pfHeaderImgUrl = '<?php echo esc_js(esc_url($image_url)); ?>';
          var pfHeaderTagline = '<?php echo esc_js($tagline); ?>';
          var pfdisableClickToDel = '<?php echo esc_js($this->getVal('click_to_delete')); ?>';
          var pfImagesSize = '<?php echo esc_js($this->getVal('images-size')); ?>';
          var pfImageDisplayStyle = '<?php echo esc_js($this->getVal('image-style')); ?>';
          var pfEncodeImages = '<?php echo esc_js($this->getVal('password_protected') === 'yes' ? 1 : 0); ?>';
          var pfShowHiddenContent  = '<?php echo esc_js($this->getVal('show_hidden_content') === 'yes' ? 1 : 0); ?>';
          var pfDisableEmail = '<?php echo esc_js($this->getVal('email')); ?>';
          var pfDisablePDF = '<?php echo esc_js($this->getVal('pdf')); ?>';
          var pfDisablePrint = '<?php echo esc_js($this->getVal('print')); ?>';

            <?php
                echo $this->get_custom_css_js_var();
            ?>

          var pfPlatform = 'WordPress';

        (function($){
            $(document).ready(function(){
                if($('.pf-button-content').length === 0){
                    $('style#pf-excerpt-styles').remove();
                }
            });
        })(jQuery);
        </script>
      <script defer src='https://cdn.printfriendly.com/printfriendly.js'></script>
            <?php
            echo $this->get_custom_css_tag();
            ?>

            <?php
        }

        /**
         * Used as a filter for the_content.
         *
         * @since ?
         *
         * @param string $content The content of the post, when the function is used as a filter.
         *
         * @return string The content with the button added to the content.
         */
        function show_link_on_content($content)
        {
            $content = $this->show_link($content, true);
            return $content;
        }

        /**
         * Used as a filter for the_excerpt.
         *
         * @since ?
         *
         * @param string $content The content of the post, when the function is used as a filter.
         *
         * @return string The content with the button added to the content.
         */
        function show_link_on_excerpt($content)
        {
            return $this->show_link($content, false);
        }

        /**
         * Used to generate the the final content as per the required placement.
         *
         * @since 3.0
         *
         * @param string $content    The content of the post, when the function is used as a filter.
         * @param bool   $on_content True if showing on the_content and False if showing on the_excerpt.
         *
         * @return string $button or $content with the button added to the content when appropriate, just the content when button shouldn't be added or just button when called manually.
         */
        private function show_link($content = false, $on_content = true)
        {
            if ($this->is_enabled() && $content) {
                $button = $this->getButton(false, $on_content ? 'pf-button-content' : 'pf-button-excerpt');
                // Hook the script call now, so it only get's loaded when needed, and need is determined by the user calling pf_button
                add_action('wp_footer', array(&$this, 'print_script_footer'));
                if ($this->getVal('content_placement') === 'before') {
                    return $button . $content;
                } else {
                    return $content . $button;
                }
            }
            return $content;
        }

        /**
         * Returns the analytics function to be included in the front end.
         *
         * @since 5.1
         *
         * @return string
         */
        private function get_analytics_code()
        {
            $return = null;
            if ($this->google_analytics_enabled()) {
                $code = apply_filters(
                    'printfriendly_analytics_code',
                    "
                        if(typeof(_gaq) === 'function') {
                            _gaq.push(['_trackEvent','PRINTFRIENDLY', 'print', title]);
                        }else if(typeof(ga) === 'function') {
                            ga('send', 'event','PRINTFRIENDLY', 'print', title); 
                        }else if(typeof(gtag) === 'function') { 
                            gtag('event', 'printfriendly_button_click', {'event_category': 'printfriendly', 'event_label': title})
                        }else if(typeof(dataLayer) === 'object') { 
                            dataLayer.push({
                                'event': 'printfriendly_button_click',
                                'pageTitle': title
                            })
                        }
					"
                );
                $return = "
					function pfTrackEvent(title) {
						$code
					}
				";
            }
            return $return;
        }

        /**
         * Returns the HTML of the button.
         *
         * This can be called publicly from `pf_show_link()`.
         *
         * @since 3.3.8
         *
         * @param bool   $add_footer_script Whether to add the script in the footer.
         * @param string $add_class         Additional class for the HTML button div.
         * @param bool   $print_whole_page  Whether to print the whole page or follow default behaviour.
         *
         * @return Printfriendly Button HTML
         */
        public function getButton($add_footer_script = false, $add_class = '', $print_whole_page = false)
        {
            if ($add_footer_script) {
                add_action('wp_footer', array(&$this, 'print_script_footer'));
            }

            $onclick = array();
            $href = '#';

            if ($print_whole_page) {
                $onclick[] = 'window.print()';
            } else {
                if (is_singular()) {
                    $onclick[] = 'window.print()';
                } else {
                    $href = add_query_arg('pfstyle', 'wp', get_permalink());
                }
            }

            if ($this->google_analytics_enabled()) {
                $onclick[] = "pfTrackEvent('" . esc_attr(get_the_title()) . "')";
            }

            if (! empty($onclick)) {
                $onclick = 'onClick="' . implode('; ', $onclick) . '; return false;"';
            } else {
                $onclick = '';
            }

            $align = '';
            $position = $this->getVal('content_position');

            // none option has been removed and translates to left
            if ('none' === $position) {
                $position = 'left';
            }
            $align = ' pf-align' . $position;
            $href = str_replace('&', '&amp;', $href);
            $button = apply_filters('printfriendly_button', '<div class="printfriendly pf-button ' . $add_class . $align . '"><a href="' . $href . '" rel="nofollow" ' . $onclick . ' title="Printer Friendly, PDF & Email">' . $this->button() . '</a></div>');
            return $button;
        }

        /**
         * Returns the custom selector CSS to be added.
         *
         * @since   5.0
         * @returns string
         */
        private function getSelectorsFromCustomCSS()
        {
            $css = '';
            if ($this->getVal('pf_algo') === 'css') {
                $attributes = array();
                $names = array( 'author', 'date', 'content', 'title', 'primaryImage' );
                $data = '';
                foreach ($names as $name) {
                    $selector = $this->getVal("css-{$name}");
                    $suffix = $name === 'content' ? 'Selectors' : 'Selector';
                    if (! empty($selector)) {
                            $data .= "{$name}{$suffix}=$selector;";
                    }
                }
                $fallback = $this->getVal('pf_algo_css_content');
                if ($data) {
                    $css = sprintf('<printfriendly-options data-selectors="%s" data-fallback-strategy="%s"></printfriendly-options>', esc_attr($data), esc_attr($fallback));
                }
            }
            return $css;
        }


        /**
         * Checks if GA is enabled.
         *
         * @since   3.2.9
         * @returns if google analytics enabled
         */
        function google_analytics_enabled()
        {
            return $this->getVal('enable_google_analytics') === 'yes';
        }

        /**
         * Filter posts by category.
         *
         * @since  3.2.2
         * @return boolean true if post belongs to category selected for button display
         */
        function category_included()
        {
            return !empty($this->getVal('show_on_cat')) ? in_category(explode(',', $this->getVal('show_on_cat'))) : true;
        }

        /**
         * Register the textdomain and the options array along with the validation function
         *
         * @since 3.0
         */
        function init()
        {
            // Allow for localization
            load_plugin_textdomain('printfriendly', false, basename(dirname(__FILE__)) . '/languages');
            // Register our option array
            register_setting($this->option_name, $this->option_name, array( 'sanitize_callback' => array(&$this, 'options_validate') ));
        }

        /**
         * Validate the saved options.
         *
         * @since  3.0
         * @param  array $input with unvalidated options.
         * @return array $valid_input with validated options.
         */
        function options_validate($input)
        {
            // Prevent CSRF attack
            check_admin_referer('pf-options', 'pf-nonce');
            $valid_input = $input;

            // Section 1 options
            if (
                ! isset($input['button_type']) || ! in_array(
                    $input['button_type'],
                    array(
                    'buttons/printfriendly-pdf-email-button.png',
                    'buttons/printfriendly-pdf-email-button-md.png',
                    'buttons/printfriendly-pdf-email-button-notext.png', // buttongroup1
                    'buttons/printfriendly-pdf-button.png',
                    'buttons/printfriendly-pdf-button-nobg.png',
                    'buttons/printfriendly-pdf-button-nobg-md.png', // buttongroup2
                    'buttons/printfriendly-button.png',
                    'buttons/printfriendly-button-nobg.png',
                    'buttons/printfriendly-button-md.png',
                    'buttons/printfriendly-button-lg.png', // buttongroup3
                    'buttons/print-button.png',
                    'buttons/print-button-nobg.png',
                    'buttons/print-button-gray.png', // buttongroup3
                    'custom-button', // custom
                    ),
                    true
                )
            ) {
                $valid_input['button_type'] = 'printfriendly-pdf-button.png';
            }

            if (
                ! isset($input['custom_button_icon']) || ! in_array(
                    $input['custom_button_icon'],
                    array(
                    'https://cdn.printfriendly.com/icons/printfriendly-icon-sm.png',
                    'https://cdn.printfriendly.com/icons/printfriendly-icon-md.png',
                    'https://cdn.printfriendly.com/icons/printfriendly-icon-lg.png',
                    'custom-image',
                    'no-image',
                    ),
                    true
                )
            ) {
                $valid_input['custom_button_icon'] = 'https://cdn.printfriendly.com/icons/printfriendly-icon-md.png';
            }

            // if a custom image is not being chosen, reset it in case it existed in the past.
            if (isset($valid_input['custom_button_icon']) && $valid_input['custom_button_icon'] !== 'custom-image') {
                $valid_input['custom_image'] = '';
            }

            // @todo custom image url validation
            if (! isset($input['custom_image']) || empty($input['custom_image'])) {
                $valid_input['custom_image'] = '';
            }

            if (! isset($input['custom_button_text'])) {
                $valid_input['custom_button_text'] = 'custom-text';
            }

            // @todo validate optional custom text
            if (! isset($input['custom_text'])) {
                $valid_input['custom_text'] = 'Print Friendly';
            }

            // Custom button selected, but no url nor text given, reset button type to default
            if ('custom-button' === $valid_input['button_type'] && ( '' === $valid_input['custom_image'] && '' === $input['custom_text'] )) {
                $valid_input['button_type'] = 'buttons/printfriendly-pdf-button.png';
                add_settings_error($this->option_name, 'invalid_custom_image', __('No valid custom image url received, please enter a valid url to use a custom image.', 'printfriendly'));
            }

            $valid_input['text_size'] = (int) $input['text_size'];
            if (! isset($valid_input['text_size']) || 0 === $valid_input['text_size']) {
                $valid_input['text_size'] = 14;
            } elseif (25 < $valid_input['text_size'] || 9 > $valid_input['text_size']) {
                $valid_input['text_size'] = 14;
                add_settings_error($this->option_name, 'invalid_text_size', __('The text size you entered is invalid, please stay between 9px and 25px', 'printfriendly'));
            }

            if (! isset($input['text_color'])) {
                $valid_input['text_color'] = $this->getVal('text_color');
            } elseif (! preg_match('/^#[a-f0-9]{3,6}$/i', $input['text_color'])) {
                // Revert to previous setting and throw error.
                $valid_input['text_color'] = $this->getVal('text_color');
                add_settings_error($this->option_name, 'invalid_color', __('The color you entered is not valid, it must be a valid hexadecimal RGB font color.', 'printfriendly'));
            }

            /* Section 2 options */
            if (! isset($input['enable_css']) || 'off' !== $input['enable_css']) {
                $valid_input['enable_css'] = 'on';
            }

            if (! isset($input['content_position']) || ! in_array($input['content_position'], array('left', 'center', 'right'), true)) {
                $valid_input['content_position'] = 'left';
            }

            if (! isset($input['button_alignment_method']) || ! in_array($input['button_alignment_method'], array('default', 'css'), true)) {
                $valid_input['button_alignment_method'] = 'default';
            }

            if (! isset($input['button_alignment_method']) || 'css' !== $input['button_alignment_method']) {
                $valid_input['content_position_css'] = '';
            }

            if (! isset($input['content_placement']) || ! in_array($input['content_placement'], array('before', 'after'), true)) {
                $valid_input['content_placement'] = 'after';
            }

            foreach (array('margin_top', 'margin_right', 'margin_bottom', 'margin_left') as $opt) {
                // if margin is not defined, don't throw a PHP notice
                if (isset($input[ $opt ])) {
                    $valid_input[ $opt ] = (int) $input[ $opt ];
                }
            }

            unset($opt);

            /* Section 3 options */
            foreach (array('show_on_posts', 'show_on_pages', 'show_on_homepage', 'show_on_categories', 'show_on_taxonomies') as $opt) {
                if (! isset($input[ $opt ]) || 'on' !== $input[ $opt ]) {
                    unset($valid_input[ $opt ]);
                }
            }
            unset($opt);

            if (isset($input['category_ids'])) {
                unset($valid_input['category_ids']);
            }

            /* Section 4 options */
            if (! isset($input['logo']) || ! in_array($input['logo'], array('favicon', 'upload-an-image'), true)) {
                $valid_input['logo'] = 'favicon';
            }

            // @todo custom logo url validation
            if (! isset($input['image_url']) || empty($input['image_url'])) {
                $valid_input['image_url'] = '';
            }

            // @todo validate optional tagline text
            if (! isset($input['tagline'])) {
                $valid_input['tagline'] = '';
            }

            // Custom logo selected, but no valid url given, reset logo to default
            if ('upload-an-image' === $valid_input['logo'] && '' === $valid_input['image_url']) {
                $valid_input['logo'] = 'favicon';
                add_settings_error($this->option_name, 'invalid_custom_logo', __('No valid custom logo url received, please enter a valid url to use a custom logo.', 'printfriendly'));
            }

            if (! isset($input['image-style']) || ! in_array($input['image-style'], array('right', 'left', 'none', 'block'), true)) {
                $valid_input['image-style'] = 'block';
            }

            foreach (array('click_to_delete', 'email', 'pdf', 'print') as $opt) {
                // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
                if (! isset($input[ $opt ]) || ! in_array($input[ $opt ], array('0', '1'))) {
                    $valid_input[ $opt ] = '0';
                }
            }
            unset($opt);

            if (! isset($input['images-size']) || ! in_array($input['images-size'], array('full-size', 'remove-images', 'large', 'medium', 'small'), true)) {
                $valid_input['images-size'] = 'full-size';
            }

            if (! isset($input['css_include_via']) || ! in_array($input['css_include_via'], array('file', 'inline_tag'), true)) {
                $valid_input['css_include_via'] = 'inline_tag';
            }

            $css = $input['custom_css'];

            // remove the <style> </style> tags.
            $css = str_replace(array( '<style>', '</style>' ), '', $css);
            $valid_input['custom_css'] = sanitize_textarea_field($css);


            // if empty CSS is provided in the box
            // check if this version is upgraded from the version that was using url instead of textbox
            // and reuse it
            if (empty($valid_input['custom_css']) && isset($this->options['custom_css_url'])) {
                if (empty($this->getVal('custom_css_url'))) {
                    unset($valid_input['custom_css_url']);
                } else {
                    $valid_input['custom_css_url'] = $this->getVal('custom_css_url');
                }
            } else {
                if (empty($valid_input['custom_css'])) {
                    unset($valid_input['custom_css_url_pro']);
                } else {
                    if ($valid_input['css_include_via'] === 'file') {
                        $file = $this->generate_custom_css_file($valid_input['custom_css']);
                        $valid_input['custom_css_url_pro'] = $file;
                    }
                }
            }

            /* Section 5 options */
            if (! isset($input['password_protected']) || ! in_array($input['password_protected'], array('no', 'yes'), true)) {
                $valid_input['password_protected'] = 'no';
            }
            if (! isset($input['show_hidden_content']) || ! in_array($input['show_hidden_content'], array('no', 'yes'), true)) {
                $valid_input['show_hidden_content'] = 'no';
            }

            /*Analytics Options */
            if (! isset($input['enable_google_analytics']) || ! in_array($input['enable_google_analytics'], array('no', 'yes'), true)) {
                $valid_input['enable_google_analytics'] = 'no';
            }

            if (! isset($input['pf_algo']) || ! in_array($input['pf_algo'], array('wp', 'pf', 'css'), true)) {
                $valid_input['pf_algo'] = 'wp';
            }

            if ($valid_input['pf_algo'] === 'css' && empty($valid_input['pf_algo_css_content']) && ! empty($valid_input['css-content'])) {
                $valid_input['pf_algo_css_content'] = 'original';
            }

            if (! isset($valid_input['custom_image_width'])) {
                $valid_input['custom_image_width'] = 0;
            }

            if (! isset($valid_input['custom_image_height'])) {
                $valid_input['custom_image_height'] = 0;
            }

            if (empty($valid_input['custom_image'])) {
                unset($valid_input['custom_image_width']);
                unset($valid_input['custom_image_height']);
            }

            /* Database version */
            $valid_input['db_version'] = $this->db_version;

            // set the current tab from where the settings were saved from
            if (isset($_POST['tab'])) {
                set_transient('pf-tab', $_POST['tab'], 5);
            }

            // save the categories as comma-separated
            if (isset($valid_input['show_on_cat']) && ! empty($valid_input['show_on_cat'])) {
                $valid_input['show_on_cat'] = implode(',', $valid_input['show_on_cat']);
            }

            return $valid_input;
        }

        /**
         * Register the config page for all users that have the manage_options capability
         *
         * @since 3.0
         */
        function add_config_page()
        {
            $this->settings_page = add_options_page(__('PrintFriendly Options', 'printfriendly'), __('Print Friendly & PDF', 'printfriendly'), 'manage_options', $this->hook, array(&$this, 'config_page'));

            // register  callback gets call prior your own page gets rendered
            add_action('load-' . $this->settings_page, array(&$this, 'on_load_printfriendly'));
        }

        /**
         * Enqueue the scripts for the admin settings page
         *
         * @since 3.0
         * @param string $screen_id check whether the current page is the PrintFriendly settings page.
         */
        function admin_enqueue_scripts($screen_id)
        {
            if ($this->settings_page === $screen_id) {
                if (! did_action('wp_enqueue_media')) {
                    wp_enqueue_media();
                }

                if (! wp_script_is('clipboard')) {
                    wp_enqueue_script('clipboard', plugins_url('assets/js/lib/clipboard.min.js', __FILE__));
                }

                if (! wp_script_is('select2')) {
                    wp_enqueue_script('select2', plugins_url('assets/js/lib/select2.min.js', __FILE__));
                }

                wp_register_script('pf-admin', plugins_url('assets/js/admin.js', __FILE__), array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-accordion', 'media-upload', 'wp-color-picker', 'clipboard', 'select2' ), $this->plugin_version);
                wp_localize_script(
                    'pf-admin',
                    'pf_config',
                    array(
                        'i10n' => array(
                            'upload_window_title' => __('Custom Image', 'printfriendly'),
                            'upload_window_button_title' => __('Use Image', 'printfriendly'),
                            'invalid_image_url' => __('Invalid Image URL', 'printfriendly'),
                        ),
                    )
                );

                wp_register_script('pf-admin-pro', plugins_url('assets/js/admin_pro.js', __FILE__), array( 'pf-admin' ), $this->plugin_version);

                wp_localize_script(
                    'pf-admin-pro',
                    'pf_config',
                    array(
                        'nonce' => wp_create_nonce($this->hook . $this->plugin_version),
                        'action' => $this->hook,
                        'i10n' => array(
                            'activation' => __('Activation', 'printfriendly'),
                            'check_status' => __('Checking status', 'printfriendly'),
                            'activate' => __('Activate', 'printfriendly'),
                            'active_trial' => __('Active Trial', 'printfriendly'),
                            'active' => __('Active', 'printfriendly'),
                            'expired' => __('Expired', 'printfriendly'),
                            'connection' => __('Please check Internet connection.', 'printfriendly'),
                        ),
                    )
                );
                wp_enqueue_script('pf-admin-pro');

                wp_enqueue_style('pf-bulma', plugins_url('assets/css/lib/bulma.prefixed.min.css', __FILE__), array( 'wp-color-picker' ), $this->plugin_version);
                wp_enqueue_style('pf-select2', plugins_url('assets/css/lib/select2.min.css', __FILE__), array(), $this->plugin_version);
                wp_enqueue_style('pf-admin', plugins_url('assets/css/admin.css', __FILE__), array( 'pf-select2', 'pf-bulma' ), $this->plugin_version);
            }
        }

        /**
         * Register the settings link for the plugins page
         *
         * @since  3.0
         * @param  array  $links the links for the plugins.
         * @param  string $file  filename to check against plugins filename.
         * @return array $links the links with the settings link added to it if appropriate.
         */
        function filter_plugin_actions($links, $file)
        {
            // Static so we don't call plugin_basename on every plugin row.
            static $this_plugin;
            if (! $this_plugin) {
                $this_plugin = plugin_basename(__FILE__);
            }

            if ($file === $this_plugin) {
                $settings_link = '<a href="options-general.php?page=' . $this->hook . '">' . __('Settings', 'printfriendly') . '</a>';
                array_unshift($links, $settings_link); // before other links
            }

            return $links;
        }

        /**
         * Register the additional link for the plugins page in the plugin description column.
         *
         * @since  ?
         * @param  array  $links the links for the plugins.
         * @param  string $file  filename to check against plugins filename.
         * @return array $links the links with the links added to it if appropriate.
         */
        function additional_links($links, $file)
        {
            // Static so we don't call plugin_basename on every plugin row.
            static $this_plugin;
            if (! $this_plugin) {
                $this_plugin = plugin_basename(__FILE__);
            }

            if ($file === $this_plugin) {
                $new_links = array( '<a href="https://support.printfriendly.com/wordpress/customize-content-selection/" target="_new">' . __('Documentation', 'printfriendly') . '</a>' );
                $links = array_merge($links, $new_links);
            }
            return $links;
        }

        /**
         * Set default values for the plugin. If old, as in pre 1.0, settings are there, use them and then delete them.
         *
         * @since 3.0
         */
        function set_defaults()
        {
            // Set some defaults
            $this->options = array(
                'button_type' => 'buttons/printfriendly-pdf-button.png',
                'content_position' => 'left',
                'button_alignment_method' => 'default',
                'content_placement' => 'after',
                'custom_button_icon' => 'https://cdn.printfriendly.com/icons/printfriendly-icon-md.png',
                'custom_button_text' => 'custom-text',
                'custom_image' => '',
                'custom_text' => 'PrintFriendly',
                'enable_css' => 'on',
                'margin_top' => '12',
                'margin_right' => '12',
                'margin_bottom' => '12',
                'margin_left' => '12',
                'show_on_posts' => 'on',
                'show_on_pages' => 'on',
                'text_color' => '#3AAA11',
                'text_size' => 14,
                'logo' => 'favicon',
                'image_url' => '',
                'tagline' => '',
                'click_to_delete' => '0', // 0 - allow, 1 - do not allow
                'hide-images' => '0', // 0 - show images, 1 - hide images
                'image-style' => 'block', // 'right', 'left', 'none', 'block'
                'email' => '0', // 0 - allow, 1 - do not allow
                'pdf' => '0', // 0 - allow, 1 - do not allow
                'print' => '0', // 0 - allow, 1 - do not allow
                'password_protected' => 'no',
                'enable_google_analytics' => 'no',
                'enable_error_reporting' => 'yes',
                'pf_algo' => 'wp',
                'images-size' => 'full-size',
                'show_hidden_content' => 'no',
                'css_include_via' => 'inline_tag',
            );

            // Check whether the old badly named singular options are there, if so, use the data and delete them.
            foreach (array_keys($this->options) as $opt) {
                $old_opt = get_option('pf_' . $opt);
                if ($old_opt !== false) {
                    $this->options[ $opt ] = $old_opt;
                    delete_option('pf_' . $opt);
                }
            }

            // This should always be set to the latest immediately when defaults are pushed in.
            $this->options['db_version'] = $this->db_version;

            update_option($this->option_name, $this->options);
        }

        /**
         * Upgrades the stored options, used to add new defaults if needed etc.
         *
         * @since 3.0
         */
        function upgrade()
        {
            // update options to version 2
            if ($this->options['db_version'] < 2) {
                $additional_options = array(
                    'enable_css' => 'on',
                    'logo' => 'favicon',
                    'image_url' => '',
                    'tagline' => '',
                    'click_to_delete' => '0',
                    'password_protected' => 'no',
                );

                // correcting badly named option
                if (isset($this->options['disable_css'])) {
                    $additional_options['enable_css'] = $this->options['disable_css'];
                    unset($this->options['disable_css']);
                }

                // check whether image we do not list any more was used
                if (in_array($this->options['button_type'], array('button-print-whgn20.png', 'pf_button_sq_qry_m.png', 'pf_button_sq_qry_l.png', 'pf_button_sq_grn_m.png', 'pf_button_sq_grn_l.png'), true)) {
                    // previous version had a bug with button name
                    if (in_array($this->options['button_type'], array('pf_button_sq_qry_m.png', 'pf_button_sq_qry_l.png'), true)) {
                        $this->options['button_type'] = str_replace('qry', 'gry', $this->options['button_type']);
                    }

                    $image_address = 'https://cdn.printfriendly.com/' . $this->options['button_type'];
                    $this->options['button_type'] = 'custom-image';
                    $this->options['custom_text'] = '';
                    $this->options['custom_image'] = $image_address;
                }

                $this->options = array_merge($this->options, $additional_options);
            }

            // update options to version 3
            if ($this->options['db_version'] < 3) {
                $old_show_on = $this->options['show_list'];
                // 'manual' setting
                $additional_options = array('custom_css_url' => '');

                if ($old_show_on === 'all') {
                    $additional_options = array(
                        'show_on_pages' => 'on',
                        'show_on_posts' => 'on',
                        'show_on_homepage' => 'on',
                        'show_on_categories' => 'on',
                        'show_on_taxonomies' => 'on',
                    );
                }

                if ($old_show_on === 'single') {
                    $additional_options = array(
                        'show_on_pages' => 'on',
                        'show_on_posts' => 'on',
                    );
                }

                if ($old_show_on === 'posts') {
                    $additional_options = array(
                        'show_on_posts' => 'on',
                    );
                }

                unset($this->options['show_list']);

                $this->options = array_merge($this->options, $additional_options);
            }

            // update options to version 4
            if ($this->options['db_version'] < 4) {
                $additional_options = array(
                    'email' => '0',
                    'pdf' => '0',
                    'print' => '0',
                );

                $this->options = array_merge($this->options, $additional_options);
            }

            // update options to version 6
            // Replacement for db version 5 - should also be run for those already upgraded
            if ($this->options['db_version'] < 6) {
                unset($this->options['category_ids']);
            }

            if ($this->options['db_version'] < 7) {
                $additional_options = array(
                    'hide-images' => '0',
                    'image-style' => 'right',
                );

                $this->options = array_merge($this->options, $additional_options);
            }

            if ($this->options['db_version'] < 8) {
                $this->options['enable_google_analytics'] = 'no';
            }

            if ($this->options['db_version'] < 9) {
                $this->options['pf_algo'] = 'wp';
            }

            if ($this->options['db_version'] < 10) {
                $this->options['enable_error_reporting'] = 'yes';
            }

            if ($this->options['db_version'] < 11) {
                if (! isset($this->options['custom_css_url'])) {
                    $this->options['custom_css_url'] = '';
                }
            }

            if ($this->options['db_version'] < 12) {
               // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
                $this->options['images-size'] = $this->options['hide-images'] == '1' ? 'remove-images' : 'full-size';
            }

            if ($this->options['db_version'] < 13) {
                switch ($this->options['button_type']) {
                    case 'pf-button.gif':
                        $this->options['button_type'] = 'buttons/printfriendly-button.png';
                        break;
                    case 'pf-button-both.gif':
                        $this->options['button_type'] = 'buttons/printfriendly-pdf-button.png';
                        break;
                    case 'pf-button-big.gif':
                        $this->options['button_type'] = 'buttons/printfriendly-button-lg.png';
                        break;
                    case 'pf-button-print-pdf-mail.png':
                        $this->options['button_type'] = 'buttons/printfriendly-pdf-email-button-notext.png';
                        break;
                    case 'button-print-grnw20.png':
                        $this->options['button_type'] = 'buttons/print-button.png';
                        break;
                    case 'button-print-blu20.png':
                        $this->options['button_type'] = 'buttons/print-button-nobg.png';
                        break;
                    case 'button-print-gry20.png':
                        $this->options['button_type'] = 'buttons/print-button-gray.png';
                        break;
                    case 'pf-icon-small.gif':
                    case 'pf-icon.gif':
                        $this->options['button_type'] = 'custom-button';
                        $this->options['custom_button_icon'] = 'icons/printfriendly-icon-md.png';
                        $this->options['custom_button_text'] = 'custom-text';
                        $this->options['custom_image'] = 'icons/printfriendly-icon-md.png';
                        break;
                    case 'text-only':
                        $this->options['button_type'] = 'custom-button';
                        $this->options['custom_button_icon'] = 'no-image';
                        $this->options['custom_button_text'] = 'custom-text';
                        break;
                }
            }

            if ($this->options['db_version'] < 14) {
                if ($this->options['button_type'] === 'pf-icon-both.gif') {
                    $this->options['button_type'] = 'buttons/printfriendly-pdf-button-nobg.png';
                }
            }

            if ($this->options['db_version'] < 15) {
                if ($this->options['button_type'] === 'custom-image') {
                    $this->options['button_type'] = 'custom-button';

                    switch ($this->options['custom_image']) {
                        case 'icons/printfriendly-icon-sm.png':
                        case 'icons/printfriendly-icon-md.png':
                        case 'icons/printfriendly-icon-lg.png':
                            $this->options['custom_button_icon'] = $this->options['custom_image'];
                            break;
                        case '':
                            $this->options['custom_button_icon'] = 'no-image';
                            break;
                        default:
                            $this->options['custom_button_icon'] = 'custom-image';
                    }

                    if ($this->options['custom_text'] === '') {
                        $this->options['custom_button_text'] = 'no-text';
                    } else {
                        $this->options['custom_button_text'] = 'custom-text';
                    }
                }
            }

            if ($this->options['db_version'] < 16) {
                if ($this->options['custom_button_icon'] === 'icons/printfriendly-icon-md.png') {
                    $this->options['custom_button_icon'] = 'https://cdn.printfriendly.com/icons/printfriendly-icon-md.png';
                }
            }

            if ($this->options['db_version'] < 17) {
                $this->options['pro_email'] = get_bloginfo('admin_email');

                $url = get_bloginfo('url');
                $parsed_url = parse_url($url);
                $this->options['pro_domain'] = $parsed_url['host'];
            }

            if ($this->options['db_version'] < 21) {
                if (empty($this->getVal('custom_css'))) {
                    $this->options['css_include_via'] = 'inline_tag';
                } else {
                    $this->options['css_include_via'] = 'file';
                }
            }

            $this->options['db_version'] = $this->db_version;

            update_option($this->option_name, $this->options);
        }

        /**
         * Displays radio button in the admin area
         *
         * @since 3.0
         * @param string  $name  the name of the radio button to generate.
         * @param boolean $br    whether or not to add an HTML <br> tag, defaults to true.
         * @param boolean $value if this is null, will have the same value as $name.
         */
        function radio($name, $br = false, $value = null)
        {
            if (is_null($value)) {
                $value = $name;
            }

            $var = '<input id="' . $name . '" class="radio" name="' . $this->option_name . '[button_type]" type="radio" value="' . $value . '" ' . $this->checked('button_type', $value, false) . '/>';
            $button = $this->button($name);
            if (! empty($button)) {
                echo '<label for="' . $name . '">' . $var . $button . '</label>';
            } else {
                echo $var;
            }

            if ($br) {
                echo '<br>';
            }
        }

        /**
         * Displays radio button in the admin area
         *
         * @since 3.12.0
         * @param string $value the value of the radio button to generate.
         */
        function radio_custom_image($value)
        {
            $button = $this->button($value);
            $value = "https://cdn.printfriendly.com/{$value}";
            ?>
      <label for="<?php echo esc_attr($value); ?>">
        <input type="radio" id="<?php echo esc_attr($value); ?>" name="<?php echo $this->option_name; ?>[custom_button_icon]" value="<?php echo $value; ?>" <?php $this->checked('custom_button_icon', $value); ?>>
            <?php echo $button; ?>
      </label>
            <?php
        }

        /**
         * Displays button image in the admin area
         *
         * @since 3.0
         * @param string $name the name of the button to generate.
         */
        function button($name = false)
        {
            if (! $name) {
                $name = $this->getVal('button_type');
            }

            $img_path = 'https://cdn.printfriendly.com/';

            $return = '';
            $imgStyle = '';

            if ($name === 'custom-button') {
                if ($this->getVal('custom_button_icon') === 'custom-image' && '' !== trim($this->getVal('custom_image', ''))) {
                    $width = $this->getVal('custom_image_width');
                    $height = $this->getVal('custom_image_height');
                    if (! empty($width)) {
                        $imgStyle .= sprintf('width: %dpx;', $width);
                    }
                    if (! empty($height)) {
                        $imgStyle .= sprintf('height: %dpx;', $height);
                    }

                    $return = '<img src="' . esc_url($this->getVal('custom_image')) . '" alt="Print Friendly, PDF & Email" class="pf-button-img" style="' . $imgStyle . '"  />';
                } elseif ($this->getVal('custom_button_icon') !== 'no-image') {
                    $icon = str_replace($img_path, '', $this->getVal('custom_button_icon'));
                    $attributes = self::$_buttons[ $icon ];
                    $atts = '';
                    $width = $attributes['width'];
                    $height = $attributes['height'];
                    if (! empty($width)) {
                        $imgStyle .= sprintf('width: %dpx;', $width);
                    }
                    if (! empty($height)) {
                        $imgStyle .= sprintf('height: %dpx;', $height);
                    }

                    $return = '<img src="' . esc_url($this->getVal('custom_button_icon')) . '" alt="Print Friendly, PDF & Email" class="pf-button-img" style="' . $imgStyle . '"  />';
                }

                if ($this->getVal('custom_button_text') === 'custom-text') {
                    $return .= sprintf('<span id="printfriendly-text2" class="pf-button-text">%s</span>', $this->esc_html_if_needed($this->getVal('custom_text')));
                }

                return $return;
            } elseif ($name === 'custom-btn') {
                return __('Custom Button', 'printfriendly');
            } else {
                $attributes = self::$_buttons[ $name ];
                $atts = '';
                $width = $attributes['width'];
                $height = $attributes['height'];
                if (! empty($width)) {
                    $imgStyle .= sprintf('width: %dpx;', $width);
                }
                if (! empty($height)) {
                    $imgStyle .= sprintf('height: %dpx;', $height);
                }

                return '<img class="pf-button-img" src="' . $img_path . $name . '" alt="Print Friendly, PDF & Email" style="' . $imgStyle . '"  />';
            }
        }

        /**
         * Convenience function to output a value custom button preview elements
         *
         * @since 3.0.9
         */
        function custom_button_preview()
        {
            $img = $url = $button_text = $style = $width = $height = '';
            switch ($this->getVal('custom_button_icon')) {
                case 'no-image':
                    break;
                case 'custom-image':
                    $url = $this->getVal('custom_image');
                    $width = $this->getVal('custom_image_width');
                    $height = $this->getVal('custom_image_height');
                    break;
                default:
                    $url = $this->getVal('custom_button_icon');
                    break;
            }

            if (! empty($url)) {
                $imgStyle = '';
                if (! empty($width)) {
                    $imgStyle .= sprintf('width: %dpx;', $width);
                }
                if (! empty($height)) {
                    $imgStyle .= sprintf('height: %dpx;', $height);
                }
                $img = sprintf('<img src="%s" style="%s" alt="Print Friendly, PDF & Email">', esc_url($url), $imgStyle);
            }

            if ($this->getVal('custom_button_text') !== 'no-text') {
                $button_text = $this->getVal('custom_text');
            }

            if ('' !== $this->getVal('text_color', '')) {
                $style .= 'color: ' . $this->getVal('text_color') . ';';
            }

            $button_preview = sprintf('<span><span id="pf-custom-button-preview" class="pf-button-img">%s</span><span id="printfriendly-text2" class="pf-button-text" style="%s">%s</span></span>', $img, $style, $this->esc_html_if_needed($button_text));

            echo $button_preview;
        }

        function echoVal($option, $default = '')
        {
            echo $this->getVal($option, $default);
        }

        function getVal($option, $default = null)
        {
            $value = $default;
            if (isset($this->options[ $option ])) {
                $value = $this->options[ $option ];
            }

            return $value;
        }


        /**
         * Like the WordPress checked() function but it doesn't throw notices when the array key isn't set and uses the plugins options array.
         *
         * @since  3.0
         * @param  mixed   $val           value to check.
         * @param  mixed   $check_against value to check against.
         * @param  boolean $echo          whether or not to echo the output.
         * @return string checked, when true, empty, when false.
         */
        function checked($val, $check_against = true, $echo = true)
        {
            $result = '';
            if (! isset($this->options[ $val ])) {
                return $result;
            }

            // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
            if ($this->options[ $val ] == $check_against) {
                $result = ' checked="checked" ';
                if ($echo) {
                    echo $result;
                }
            }
            return $result;
        }

        /**
         * Helper for creating checkboxes.
         *
         * @since 3.1.5
         * @param string $name string used for various parts of checkbox.
         */
        function create_checkbox($name, $label = '', $labelid = '')
        {
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
            $label = ( ! empty($label) ? $label : __(ucfirst($name), 'printfriendly') );
            echo '<label' . ( ! empty($labelid) ? ' id=' . $labelid : '' ) . '><input type="checkbox" class="show_list" name="' . $this->option_name . '[show_on_' . $name . ']" value="on" ';
            $this->checked('show_on_' . $name, 'on');
            echo ' />' . $label . "</label>\r\n";
        }


        /**
         * Like the WordPress selected() function but it doesn't throw notices when the array key isn't set and uses the plugins options array.
         *
         * @since  3.0.9
         * @param  mixed $val           value to check.
         * @param  mixed $check_against value to check against.
         * @return string checked, when true, empty, when false.
         */
        function selected($val, $check_against = true)
        {
            if (! isset($this->options[ $val ])) {
                return;
            }

            return selected($this->options[ $val ], $check_against);
        }

        /**
         * For use with page metabox.
         *
         * @since 3.2.2
         */
        function get_page_post_type()
        {
            $post_types = get_post_types(array('name' => 'page'), 'object');
            // echo '<pre>'.print_r($post_types,1).'</pre>';
            // die;

            return $post_types['page'];
        }


        /**
         * Helper that checks if wp versions is above 3.0.
         *
         * @since  3.2.2
         * @return boolean true wp version is above 3.0
         */
        function wp_version_gt30()
        {
            global $wp_version;
            return version_compare($wp_version, '3.0', '>=');
        }


        /**
         * Create box for picking individual categories.
         *
         * @since 3.2.2
         */
        function create_category_metabox()
        {
            $obj = new stdClass();
            $obj->ID = 0;
            do_meta_boxes('settings_page_' . $this->hook, 'normal', $obj);
        }


        /**
         * Load metaboxes advanced button display settings.
         *
         * @since 3.2.2
         */
        function on_load_printfriendly()
        {
            global $wp_version;
            if ($this->wp_version_gt30()) {
                // require_once(dirname(__FILE__).'/includes/meta-boxes.php');
                // require_once(dirname(__FILE__).''includes/nav-menu.php');
                wp_enqueue_script('post');

                add_meta_box('categorydiv', __('Only display when post is in:', 'printfriendly'), 'post_categories_meta_box', 'settings_page_' . $this->hook, 'normal', 'core');
            }
        }

        function esc_html_if_needed($input)
        {
            if (current_user_can('unfiltered_html')) {
                return $input;
            } else {
                return esc_html($input);
            }
        }

        /**
         * Returns if the user is a pro user.
         */
        function is_pro($feature = null)
        {
            $licensed = true;//$this->getVal('license_status') === 'pro';

            switch ($feature) {
                case 'custom-css':
                    // custom css needs to be available for all irrespective of the license.
                    return true;
            }

            return $licensed;
        }

        function get_custom_css_tag()
        {
            $custom_css = $this->getVal('custom_css');
            if (!empty($custom_css) && 'inline_tag' === $this->getVal('css_include_via')) {
                return sprintf('<printfriendly-css style="display: none;">%s</printfriendly-css>', html_entity_decode($custom_css));
            }
        }

        function get_custom_css_js_var()
        {
            $css_url = $this->get_custom_css_url();
            if (!empty($css_url)) {
                return sprintf('var pfCustomCSS = "%s";', esc_js(esc_url($css_url)));
            }
        }

        function get_custom_css_url()
        {
            $css_url = $this->getVal('custom_css_url', '');

            if (empty($css_url) && 'file' === $this->getVal('css_include_via')) {
                // It could be unset because custom css is empty
                $css_url = $this->getVal('custom_css_url_pro');
                if (!empty($css_url)) {
                    $dirs = wp_get_upload_dir();
                    $css_url = $dirs['baseurl'] . '/' . $css_url;
                }
            }
            return $css_url;
        }


        /**
         * Generates the custom css file from the CSS block.
         */
        function generate_custom_css_file($css)
        {
            $file = $this->getVal('custom_css_url_pro');

            $custom_css_old = html_entity_decode($this->getVal('custom_css'));

            // return the old file if the CSS has not changed.
            if ($custom_css_old === $css && ! empty($file)) {
                return $file;
            }

            $dirs = wp_get_upload_dir();

            // delete old file, if it exists
            if (! empty($file)) {
                wp_delete_file($dirs['basedir'] . '/' . $file);
            }

            // add a comment so that users know whence this file came.
            $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'));
            $comment = sprintf('/* DO NOT EDIT - FILE AUTO-GENERATED BY PRINTFRIENDLY v%s ON %s */', $this->plugin_version, $date);
            $css = $comment . PHP_EOL . PHP_EOL . $css;

            // create new file, suffixed with the current time.
            $file = sprintf('%s_%s.css', $this->hook, time());
            include_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
            global $wp_filesystem;
            $wp_filesystem->put_contents(
                $dirs['basedir'] . '/' . $file,
                $css,
                FS_CHMOD_FILE
            );

            // return the new file name
            return $file;
        }

        /**
         * If upgrading from a previous version that was using urls instead of the textarea
         * it will return an appropriate message for the user.
         */
        function get_custom_css_upgrade_message()
        {
            // upgrading from a version that was using urls instead of the textarea?
            $css_url = $this->getVal('custom_css_url');
            if (! empty($css_url)) {
                return sprintf(__('You are currently using %1$s%2$s%3$s. You can copy its contents into the textbox if you want to update the styles.', 'printfriendly'), '<a href="' . $css_url . '" target="_blank">', $css_url, '</a>');
            }

            return null;
        }

        /**
         * Output the config page
         *
         * @since 3.0
         */
        function config_page()
        {

            // Since WP 3.2 outputs these errors by default, only display them when we're on versions older than 3.2 that do support the settings errors.
            global $wp_version;
            if (version_compare($wp_version, '3.2', '<') && $this->wp_version_gt30()) {
                settings_errors();
            }

            $customCssOptionCode = $this->getSelectorsFromCustomCSS();
            include_once PRINTFRIENDLY_BASEPATH . '/views/settings.php';
        }

        /**
         * Returns the current tab to activate.
         *
         * @since 4.0.1
         */
        function is_tab($tab_id)
        {
            $tab = get_transient('pf-tab');
			// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
            return empty($tab) && $tab_id == 0 ? true : $tab == $tab_id;
        }
    }
    $printfriendly = new PrintFriendly_WordPress();
}

// Add shortcode for printfriendly button
add_shortcode(
    'printfriendly',
    /**
     * Show the printfriendly button.
     *
     * @param array $atts The attributes of the shortcode.
     * If current="yes", the whole page will be printed (pf_current_page_button is called).
     * The default behaviour is to call pf_default_button.
     *
     * @since 3.0
     *
     * @return string The button.
     */
    function ($atts) {
        $atts = shortcode_atts(
            array(
                'current' => '', // yes => print the whole page
            ),
            $atts,
            'printfriendly'
        );

        if (empty($atts['current'])) {
            return pf_default_button();
        } else {
            return pf_current_page_button();
        }

        return null;
    }
);


/**
 * Convenience function for use in templates.
 *
 * @deprecated Use pf_default_button instead.
 *
 * @since 3.0
 *
 * @return string returns a button to be printed.
 */
function pf_show_link()
{
    return pf_default_button();
}

/**
 * Convenience function for use in templates.
 *
 * @since 5.1
 *
 * @return string returns a button to be printed.
 */
function pf_default_button()
{
    global $printfriendly;
    return $printfriendly->getButton(true);
}

/**
 * Convenience function for use in templates.
 *
 * @since 5.1
 *
 * @return string returns a button that prints the entire page it is on.
 */
function pf_current_page_button()
{
    global $printfriendly;
    return $printfriendly->getButton(true, '', true);
}
