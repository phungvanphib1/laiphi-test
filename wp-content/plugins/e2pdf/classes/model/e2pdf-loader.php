<?php

/**
 * E2pdf Loader Helper
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      0.00.01
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Model_E2pdf_Loader extends Model_E2pdf_Model {

    private $errors = array();
    private $e2pdf_admin_pages = array(
        'toplevel_page_e2pdf',
        'e2pdf_page_e2pdf-templates',
        'e2pdf_page_e2pdf-settings',
        'e2pdf_page_e2pdf-license',
        'e2pdf_page_e2pdf-debug'
    );

    /*
     * On init
     * Create instance of Main Helper
     */

    function __construct() {
        parent::__construct();
    }

    /**
     * Main loader of actions / filters / hooks
     */
    public function load() {
        $this->load_translation();
        $this->load_actions();
        $this->load_filters();
        $this->load_extensions();
        $this->load_hooks();
        $this->load_ajax();
        $this->load_shortcodes();
    }

    /**
     * Load translation
     */
    public function load_translation() {
        load_plugin_textdomain('e2pdf', false, '/e2pdf/languages/');
    }

    /**
     * Load ajax
     */
    public function load_ajax() {
        if (is_admin()) {
            add_action('wp_ajax_e2pdf_save_form', array(new Controller_E2pdf_Templates(), 'ajax_save_form'));
            add_action('wp_ajax_e2pdf_auto', array(new Controller_E2pdf_Templates(), 'ajax_auto'));
            add_action('wp_ajax_e2pdf_upload', array(new Controller_E2pdf_Templates(), 'ajax_upload'));
            add_action('wp_ajax_e2pdf_reupload', array(new Controller_E2pdf_Templates(), 'ajax_reupload'));
            add_action('wp_ajax_e2pdf_extension', array(new Controller_E2pdf_Templates(), 'ajax_extension'));
            add_action('wp_ajax_e2pdf_activate_template', array(new Controller_E2pdf_Templates(), 'ajax_activate_template'));
            add_action('wp_ajax_e2pdf_deactivate_template', array(new Controller_E2pdf_Templates(), 'ajax_deactivate_template'));
            add_action('wp_ajax_e2pdf_visual_mapper', array(new Controller_E2pdf_Templates(), 'ajax_visual_mapper'));
            add_action('wp_ajax_e2pdf_get_styles', array(new Controller_E2pdf_Templates(), 'ajax_get_styles'));
            add_action('wp_ajax_e2pdf_license_key', array(new Controller_E2pdf_License(), 'ajax_change_license_key'));
            add_action('wp_ajax_e2pdf_restore_license_key', array(new Controller_E2pdf_License(), 'ajax_restore_license_key'));
            add_action('wp_ajax_e2pdf_deactivate_all_templates', array(new Controller_E2pdf_Templates(), 'ajax_deactivate_all_templates'));
            add_action('wp_ajax_e2pdf_templates', array(new Controller_E2pdf(), 'ajax_templates'));
            add_action('wp_ajax_e2pdf_dataset', array(new Controller_E2pdf(), 'ajax_dataset'));
            add_action('wp_ajax_e2pdf_datasets', array(new Controller_E2pdf(), 'ajax_datasets'));
            add_action('wp_ajax_e2pdf_delete_item', array(new Controller_E2pdf(), 'ajax_delete_item'));
            add_action('wp_ajax_e2pdf_delete_items', array(new Controller_E2pdf(), 'ajax_delete_items'));
            add_action('wp_ajax_e2pdf_delete_font', array(new Controller_E2pdf_Settings(), 'ajax_delete_font'));
            add_action('wp_ajax_e2pdf_email', array(new Controller_E2pdf_Settings(), 'ajax_email'));
        }
    }

    /**
     * Load actions
     */
    public function load_actions() {
        if (is_admin()) {
            add_action('wpmu_new_blog', array(&$this, 'activate_new_network'), 10, 6);
            add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action('admin_init', array(&$this, 'admin_settings'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_js'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_css'));
            add_action('current_screen', array(&$this, 'admin_functions'));
            add_action('plugins_loaded', array(&$this, 'after_load'));
        }
        add_action('wp_enqueue_scripts', array(&$this, 'frontend_js'));
        add_action('wp', array(Helper_E2pdf_View::instance(), 'render_frontend_page'), 5);
        add_action('wp_loaded', array(&$this, 'wp_loaded'));
        add_action('init', array(&$this, 'init'), 0);
    }

    public function init() {

        if ($this->helper->get('page') == 'e2pdf-download') {
            /**
             * Comtatiability fix with Minify HTML
             * https://wordpress.org/plugins/minify-html-markup/
             */
            remove_action('init', 'teckel_init_minify_html', 1);

            /**
             * Compatibility fix with UltimateMember Homepage Redirect
             * https://wordpress.org/plugins/ultimate-member/
             */
            if (class_exists('UM') && method_exists(UM(), 'access')) {
                remove_filter('the_posts', array(UM()->access(), 'filter_protected_posts'), 99);
            }

            /**
             * Weglot Translate – Translate your WordPress website and go multilingual
             * https://wordpress.org/plugins/weglot/
             */
            add_filter('weglot_active_translation_before_treat_page', '__return_false');
        }

        if (get_option('e2pdf_mod_rewrite') && get_option('e2pdf_mod_rewrite_url')) {
            global $wp_rewrite;

            $rewrite_url = rtrim(str_replace('%uid%', '([A-Za-z0-9]+)', get_option('e2pdf_mod_rewrite_url')), '/');

            /**
             * Comtatiability fix with Minify HTML
             * https://wordpress.org/plugins/minify-html-markup/
             */
            if (function_exists('teckel_init_minify_html') && isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] && preg_match('#' . $rewrite_url . '#', $_SERVER['REQUEST_URI'])) {
                remove_action('init', 'teckel_init_minify_html', 1);
            }

            /**
             * Weglot Translate – Translate your WordPress website and go multilingual
             * https://wordpress.org/plugins/weglot/
             */
            if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] && preg_match('#' . $rewrite_url . '#', $_SERVER['REQUEST_URI'])) {
                add_filter('weglot_active_translation_before_treat_page', '__return_false');
            }

            $rules = [
                '^' . $rewrite_url,
                '^' . $wp_rewrite->index . '/' . $rewrite_url,
            ];

            $rewrite_to = 'index.php?e2pdf=1&uid=$matches[1]';
            add_rewrite_rule($rules[0], $rewrite_to, 'top');
            add_rewrite_rule($rules[1], $rewrite_to, 'top');

            $rewrite_rules = get_option('rewrite_rules');
            foreach ($rules as $rule) {
                if (!isset($rewrite_rules[$rule])) {
                    flush_rewrite_rules(false);
                    break;
                }
            }
        }
    }

    public function load_filters() {
        if (get_option('e2pdf_dev_update')) {
            add_filter('pre_set_site_transient_update_plugins', array(&$this, 'filter_pre_set_site_transient_update_plugins'), 10, 1);
        }

        /**
         * SiteGround Optimizer HTML Minify compatibility fix filter 
         * https://wordpress.org/plugins/sg-cachepress/
         */
        add_filter('sgo_html_minify_exclude_urls', array(&$this, 'filter_sgo_html_minify_exclude_urls'), 10, 1);

        if (get_option('e2pdf_mod_rewrite') && get_option('e2pdf_mod_rewrite_url')) {
            add_filter('query_vars', array(&$this, 'filter_query_vars'), 10, 1);
        }
    }

    public function after_load() {
        if (get_option('e2pdf_version') !== $this->helper->get('version')) {
            $this->activate();
        }
    }

    /**
     * Load extensions and its action/filters
     */
    public function load_extensions() {

        $model_e2pdf_extension = new Model_E2pdf_Extension();
        $extensions = $model_e2pdf_extension->extensions();
        if (!empty($extensions)) {
            foreach ($extensions as $extension => $extension_name) {
                $model_e2pdf_extension->load($extension);
                $model_e2pdf_extension->load_actions();
                $model_e2pdf_extension->load_filters();
                $model_e2pdf_extension->load_shortcodes();
            }
        }
    }

    /**
     * Load filters
     */
    public function load_shortcodes() {
        add_shortcode('e2pdf-download', array(new Model_E2pdf_Shortcode(), 'e2pdf_download'));
        add_shortcode('e2pdf-attachment', array(new Model_E2pdf_Shortcode(), 'e2pdf_attachment'));
        add_shortcode('e2pdf-adobesign', array(new Model_E2pdf_Shortcode(), 'e2pdf_adobesign'));
        add_shortcode('e2pdf-save', array(new Model_E2pdf_Shortcode(), 'e2pdf_save'));
        add_shortcode('e2pdf-view', array(new Model_E2pdf_Shortcode(), 'e2pdf_view'));
        add_shortcode('e2pdf-zapier', array(new Model_E2pdf_Shortcode(), 'e2pdf_zapier'));
        add_shortcode('e2pdf-format-number', array(new Model_E2pdf_Shortcode(), 'e2pdf_format_number'));
        add_shortcode('e2pdf-format-date', array(new Model_E2pdf_Shortcode(), 'e2pdf_format_date'));
        add_shortcode('e2pdf-format-output', array(new Model_E2pdf_Shortcode(), 'e2pdf_format_output'));
        add_shortcode('e2pdf-user', array(new Model_E2pdf_Shortcode(), 'e2pdf_user'));
        add_shortcode('e2pdf-wp', array(new Model_E2pdf_Shortcode(), 'e2pdf_wp'));
        add_shortcode('e2pdf-wp-term', array(new Model_E2pdf_Shortcode(), 'e2pdf_wp_term'));
        add_shortcode('e2pdf-wp-posts', array(new Model_E2pdf_Shortcode(), 'e2pdf_wp_posts'));
        add_shortcode('e2pdf-content', array(new Model_E2pdf_Shortcode(), 'e2pdf_content'));
        add_shortcode('e2pdf-exclude', array(new Model_E2pdf_Shortcode(), 'e2pdf_exclude'));
        add_shortcode('e2pdf-filter', array(new Model_E2pdf_Shortcode(), 'e2pdf_filter'));
        add_shortcode('e2pdf-wc-product', array(new Model_E2pdf_Shortcode(), 'e2pdf_wc_product'));
        add_shortcode('e2pdf-wc-order', array(new Model_E2pdf_Shortcode(), 'e2pdf_wc_order'));
        add_shortcode('e2pdf-wc-cart', array(new Model_E2pdf_Shortcode(), 'e2pdf_wc_cart'));
        add_shortcode('e2pdf-wc-customer', array(new Model_E2pdf_Shortcode(), 'e2pdf_wc_customer'));
        add_shortcode('e2pdf-foreach', array(new Model_E2pdf_Shortcode(), 'e2pdf_foreach'));
        add_shortcode('e2pdf-if', array(new Model_E2pdf_Shortcode(), 'e2pdf_if'));
    }

    /**
     * Load hooks
     */
    public function load_hooks() {
        register_activation_hook($this->helper->get('plugin_file_path'), array(&$this, 'activate'));
        register_deactivation_hook($this->helper->get('plugin_file_path'), array(&$this, 'deactivate'));
        register_uninstall_hook($this->helper->get('plugin_file_path'), array('Model_E2pdf_Loader', 'uninstall'));
    }

    /**
     * URL Rewrite Tags Support
     */
    public function filter_query_vars($tags) {
        global $wp;
        if (!empty($_GET['e2pdf']) || strpos($wp->matched_query, 'e2pdf=1') === 0) {
            $tags[] = 'e2pdf';
            $tags[] = 'uid';
        }
        return $tags;
    }

    /**
     * SiteGround Optimizer HTML Minify compatibility fix filter 
     * https://wordpress.org/plugins/sg-cachepress/
     */
    public function filter_sgo_html_minify_exclude_urls($exclude_urls) {
        if (is_array($exclude_urls)) {
            $exclude_urls[] = '/?page=e2pdf-download&uid=*';
            if (get_option('e2pdf_mod_rewrite') && get_option('e2pdf_mod_rewrite_url')) {
                $exclude_urls[] = '/' . rtrim(str_replace('%uid%', '*', get_option('e2pdf_mod_rewrite_url')), '/') . '*';
            }
        }
        return $exclude_urls;
    }

    /**
     * Force Updates from E2Pdf.com
     */
    public function filter_pre_set_site_transient_update_plugins($transient) {
        if (isset($transient->response[$this->helper->get('plugin_slug')]) || isset($transient->no_update[$this->helper->get('plugin_slug')])) {

            $model_e2pdf_api = new Model_E2pdf_Api();
            $model_e2pdf_api->set(array(
                'action' => 'update/info',
            ));
            $request = $model_e2pdf_api->request();

            if (isset($transient->response[$this->helper->get('plugin_slug')])) {
                $update_info = $transient->response[$this->helper->get('plugin_slug')];
            } else {
                $update_info = $transient->no_update[$this->helper->get('plugin_slug')];
            }
            if (isset($request['package']) && isset($request['version']) && isset($request['tested']) && version_compare($this->helper->get('version'), $request['version'], '<')) {
                $update_info->package = $request['package'];
                $update_info->new_version = $request['version'];
                $update_info->tested = $request['tested'];
                $transient->response[$this->helper->get('plugin_slug')] = $update_info;
                if (isset($transient->no_update[$this->helper->get('plugin_slug')])) {
                    unset($transient->no_update[$this->helper->get('plugin_slug')]);
                }
            }
        }

        return $transient;
    }

    /**
     * Load admin menu
     */
    public function admin_menu() {
        ob_start();
        $caps = $this->helper->get_caps();
        if (current_user_can('manage_options')) {
            foreach ($caps as $cap_key => $cap) {
                $caps[$cap_key]['cap'] = 'manage_options';
            }
        }

        add_menu_page('e2pdf', 'E2Pdf', $caps['e2pdf']['cap'], 'e2pdf', array(Helper_E2pdf_View::instance(), 'render_page'), '', '26');
        add_submenu_page('e2pdf', __('Export', 'e2pdf'), __('Export', 'e2pdf'), $caps['e2pdf']['cap'], 'e2pdf', array(Helper_E2pdf_View::instance(), 'render_page'));
        add_submenu_page('e2pdf', __('Templates', 'e2pdf'), __('Templates', 'e2pdf'), $caps['e2pdf_templates']['cap'], 'e2pdf-templates', array(Helper_E2pdf_View::instance(), 'render_page'));
        add_submenu_page('e2pdf', __('Settings', 'e2pdf'), __('Settings', 'e2pdf'), $caps['e2pdf_settings']['cap'], 'e2pdf-settings', array(Helper_E2pdf_View::instance(), 'render_page'));
        add_submenu_page('e2pdf', __('License', 'e2pdf'), __('License', 'e2pdf'), $caps['e2pdf_license']['cap'], 'e2pdf-license', array(Helper_E2pdf_View::instance(), 'render_page'));
        if (get_option('e2pdf_debug') === '1') {
            add_submenu_page('e2pdf', __('Debug', 'e2pdf'), __('Debug', 'e2pdf'), $caps['e2pdf_debug']['cap'], 'e2pdf-debug', array(Helper_E2pdf_View::instance(), 'render_page'));
        }
    }

    /**
     * Setup settings page
     */
    public function admin_settings() {
        register_setting('e2pdf-settings', 'e2pdf_debug');
    }

    /**
     * Load admin css
     */
    public function admin_css($page) {
        if (get_option('e2pdf_debug') === '1') {
            $version = strtotime("now");
        } else {
            $version = $this->helper->get('version');
        }

        wp_register_style('e2pdf.backend', plugins_url('css/e2pdf.backend.css', $this->helper->get('plugin_file_path')), array(), $version);
        wp_enqueue_style('e2pdf.backend');
    }

    /**
     * Load admin javascript
     * 
     * @param string $page - Current page
     */
    public function admin_js($page) {

        if (!in_array($page, $this->e2pdf_admin_pages)) {
            return;
        }

        if (get_option('e2pdf_debug') === '1') {
            $version = strtotime("now");
        } else {
            $version = $this->helper->get('version');
        }

        wp_enqueue_script(
                'js/e2pdf.backend', plugins_url('js/e2pdf.backend.js', $this->helper->get('plugin_file_path')), array('jquery'), $version
        );

        $js_lang = $this->get_js('lang');
        wp_localize_script('js/e2pdf.backend', 'e2pdfLang', $js_lang);

        $js_size = $this->get_js('template_sizes');
        wp_localize_script('js/e2pdf.backend', 'e2pdfTemplateSizes', $js_size);

        $js_extension = $this->get_js('template_extensions');
        wp_localize_script('js/e2pdf.backend', 'e2pdfTemplateExtensions', $js_extension);

        $js_params = $this->get_js('params');
        wp_localize_script('js/e2pdf.backend', 'e2pdfParams', $js_params);
    }

    /**
     * Load admin javascript
     * 
     * @param string $page - Current page
     */
    public function frontend_js() {

        wp_register_script(
                'js/e2pdf.frontend', plugins_url('js/e2pdf.frontend.js', $this->helper->get('plugin_file_path')), array('jquery'), $this->helper->get('version')
        );
        wp_enqueue_script(
                'js/e2pdf.frontend'
        );
    }

    public function get_js($type) {

        $data = array();

        switch ($type) {
            case 'lang':
                $data = array(
                    'Are you sure want to remove page?' => __('Are you sure want to remove page?', 'e2pdf'),
                    'Are you sure want to remove font?' => __('Are you sure want to remove font?', 'e2pdf'),
                    'Saved Template will be overwritten! Are you sure want to continue?' => __('Saved Template will be overwritten! Are you sure want to continue?', 'e2pdf'),
                    'All pages will be removed! Are you sure want to continue?' => __('All pages will be removed! Are you sure want to continue?', 'e2pdf'),
                    'Adding new pages not available in "Uploaded PDF"' => __('Adding new pages not available in "Uploaded PDF"', 'e2pdf'),
                    'Dataset will be removed! Are you sure want to continue?' => __('Dataset will be removed! Are you sure want to continue?', 'e2pdf'),
                    'All datasets will be removed! Are you sure want to continue?' => __('All datasets will be removed! Are you sure want to continue?', 'e2pdf'),
                    'WARNING: Template has changes after last save! Changes will be lost!' => __('WARNING: Template has changes after last save! Changes will be lost!', 'e2pdf'),
                    'Element will be removed! Are you sure want to continue?' => __('Element will be removed! Are you sure want to continue?', 'e2pdf'),
                    'Elements will be removed! Are you sure want to continue?' => __('Elements will be removed! Are you sure want to continue?', 'e2pdf'),
                    'Action will be removed! Are you sure want to continue?' => __('Action will be removed! Are you sure want to continue?', 'e2pdf'),
                    'Condition will be removed! Are you sure want to continue?' => __('Condition will be removed! Are you sure want to continue?', 'e2pdf'),
                    'All Field Values will be overwritten! Are you sure want to continue?' => __('All Field Values will be overwritten! Are you sure want to continue?', 'e2pdf'),
                    'Website will be forced to use "FREE" License Key! Are you sure want to continue?' => __('Website will be forced to use "FREE" License Key! Are you sure want to continue?', 'e2pdf'),
                    'Not Available in Revision Edit Mode' => __('Not Available in Revision Edit Mode', 'e2pdf'),
                    'WYSIWYG Editor is disabled for this HTML Object' => __('WYSIWYG Editor is disabled for this HTML Object', 'e2pdf'),
                    'WYSIWYG can be applied only to HTML Object' => __('WYSIWYG can be applied only to HTML Object', 'e2pdf'),
                    'Only 1 page allowed with "FREE" license type' => __('Only 1 page allowed with "FREE" license type', 'e2pdf'),
                    'Last condition can\'t be removed' => __('Last condition can\'t be removed', 'e2pdf'),
                    'In Progress...' => __('In Progress...', 'e2pdf'),
                    'Delete' => __('Delete', 'e2pdf'),
                    'Properties' => __('Properties', 'e2pdf'),
                    'License Key' => __('License Key', 'e2pdf'),
                    'Empty PDF' => __('Empty PDF', 'e2pdf'),
                    'Upload PDF' => __('Upload PDF', 'e2pdf'),
                    'Auto PDF' => __('Auto PDF', 'e2pdf'),
                    'Create PDF' => __('Create PDF', 'e2pdf'),
                    'Extension' => __('Extension', 'e2pdf'),
                    'Size' => __('Size', 'e2pdf'),
                    'Properties' => __('Properties', 'e2pdf'),
                    'Enter link here' => __('Enter link here', 'e2pdf'),
                    'Z-index' => __('Z-index', 'e2pdf'),
                    'Border' => __('Border', 'e2pdf'),
                    'Background' => __('Background', 'e2pdf'),
                    'Left' => __('Left', 'e2pdf'),
                    'Right' => __('Right', 'e2pdf'),
                    'Top' => __('Top', 'e2pdf'),
                    'Center' => __('Center', 'e2pdf'),
                    'Bottom' => __('Bottom', 'e2pdf'),
                    'Justify' => __('Justify', 'e2pdf'),
                    'Border Color' => __('Border Color', 'e2pdf'),
                    'Line Height' => __('Line Height', 'e2pdf'),
                    'Width' => __('Width', 'e2pdf'),
                    'Height' => __('Height', 'e2pdf'),
                    'Value' => __('Value', 'e2pdf'),
                    'Font' => __('Font', 'e2pdf'),
                    'Options' => __('Options', 'e2pdf'),
                    'Option' => __('Option', 'e2pdf'),
                    'Group' => __('Group', 'e2pdf'),
                    'Check' => __('Check', 'e2pdf'),
                    'Circle' => __('Circle', 'e2pdf'),
                    'Cross' => __('Cross', 'e2pdf'),
                    'Diamond' => __('Diamond', 'e2pdf'),
                    'Square' => __('Square', 'e2pdf'),
                    'Star' => __('Star', 'e2pdf'),
                    'Type' => __('Type', 'e2pdf'),
                    'Scale' => __('Scale', 'e2pdf'),
                    'Width&Height' => __('Width&Height', 'e2pdf'),
                    'Width' => __('Width', 'e2pdf'),
                    'Height' => __('Height', 'e2pdf'),
                    'Choose Image' => __('Choose Image', 'e2pdf'),
                    'PDF Options' => __('PDF Options', 'e2pdf'),
                    'PDF Upload' => __('PDF Upload', 'e2pdf'),
                    'Global Actions' => __('Global Actions', 'e2pdf'),
                    'Item' => __('Item', 'e2pdf'),
                    'Copy' => __('Copy', 'e2pdf'),
                    'Cut' => __('Cut', 'e2pdf'),
                    'Paste' => __('Paste', 'e2pdf'),
                    'Paste in Place' => __('Paste in Place', 'e2pdf'),
                    'Apply' => __('Apply', 'e2pdf'),
                    'Dynamic Height' => __('Dynamic Height', 'e2pdf'),
                    'Text Align' => __('Text Align', 'e2pdf'),
                    'Read Only' => __('Read Only', 'e2pdf'),
                    'Multiline' => __('Multiline', 'e2pdf'),
                    'Required' => __('Required', 'e2pdf'),
                    'Size Preset' => __('Size Preset', 'e2pdf'),
                    'Page Options' => __('Page Options', 'e2pdf'),
                    'Direction' => __('Direction', 'e2pdf'),
                    'RTL' => __('RTL', 'e2pdf'),
                    'LTR' => __('LTR', 'e2pdf'),
                    'Hide' => __('Hide', 'e2pdf'),
                    'Show' => __('Show', 'e2pdf'),
                    'Password' => __('Password', 'e2pdf'),
                    'Map Field' => __('Map Field', 'e2pdf'),
                    'Parent' => __('Parent', 'e2pdf'),
                    '--- Select ---' => __('--- Select ---', 'e2pdf'),
                    'Activated' => __('Activated', 'e2pdf'),
                    'Not Activated' => __('Not Activated', 'e2pdf'),
                    'Page ID' => __('Page ID', 'e2pdf'),
                    'New Position' => __('New Position', 'e2pdf'),
                    'Render New Fields' => __('Render New Fields', 'e2pdf'),
                    'Flush Elements' => __('Flush Elements', 'e2pdf'),
                    'Keep Dimension' => __('Keep Dimension', 'e2pdf'),
                    'Only Image' => __('Only Image', 'e2pdf'),
                    'ID' => __('ID', 'e2pdf'),
                    'Confirmation Code' => __('Confirmation Code', 'e2pdf'),
                    'Code' => __('Code', 'e2pdf'),
                    'Visual Mapper' => __('Visual Mapper', 'e2pdf'),
                    'Auto' => __('Auto', 'e2pdf'),
                    'Actions' => __('Actions', 'e2pdf'),
                    'Save' => __('Save', 'e2pdf'),
                    'Horizontal Align' => __('Horizontal Align', 'e2pdf'),
                    'Vertical Align' => __('Vertical Align', 'e2pdf'),
                    'Middle' => __('Middle', 'e2pdf'),
                    'Apply If' => __('Apply If', 'e2pdf'),
                    'Action' => __('Action', 'e2pdf'),
                    'Property' => __('Property', 'e2pdf'),
                    'If' => __('If', 'e2pdf'),
                    'Condition' => __('Condition', 'e2pdf'),
                    'Change' => __('Change', 'e2pdf'),
                    'Any' => __('Any', 'e2pdf'),
                    'All' => __('All', 'e2pdf'),
                    'Order' => __('Order', 'e2pdf'),
                    'E-Signature' => __('E-Signature', 'e2pdf'),
                    'Contact' => __('Contact', 'e2pdf'),
                    'Location' => __('Location', 'e2pdf'),
                    'Reason' => __('Reason', 'e2pdf'),
                    'Placeholder' => __('Placeholder', 'e2pdf'),
                    'Length' => __('Length', 'e2pdf'),
                    'Comb' => __('Comb', 'e2pdf'),
                    'None' => __('None', 'e2pdf'),
                    'Highlight' => __('Highlight', 'e2pdf'),
                    'Invert' => __('Invert', 'e2pdf'),
                    'Outline' => __('Outline', 'e2pdf'),
                    'Push' => __('Push', 'e2pdf'),
                    'Title' => __('Title', 'e2pdf'),
                    'Status' => __('Status', 'e2pdf'),
                    'Add Action' => __('Add Action', 'e2pdf'),
                    'Shortcodes' => __('Shortcodes', 'e2pdf'),
                    'Labels' => __('Labels', 'e2pdf'),
                    'Field Values' => __('Field Values', 'e2pdf'),
                    'Field Names' => __('Field Names', 'e2pdf'),
                    'Field Name' => __('Field Name', 'e2pdf'),
                    'As Field Name' => __('As Field Name', 'e2pdf'),
                    'Confirm' => __('Confirm', 'e2pdf'),
                    'Cancel' => __('Cancel', 'e2pdf'),
                    'Hide (If Empty)' => __('Hide (If Empty)', 'e2pdf'),
                    'Hide Page (If Empty)' => __('Hide Page (If Empty)', 'e2pdf'),
                    'Replace Value' => __('Replace Value', 'e2pdf'),
                    'Auto-Close' => __('Auto-Close', 'e2pdf'),
                    'E2Pdf License Key' => __('E2Pdf License Key', 'e2pdf'),
                    'New Lines to BR' => __('New Lines to BR', 'e2pdf'),
                    'Disable WYSIWYG Editor' => __('Disable WYSIWYG Editor', 'e2pdf'),
                    'CSS Priority' => __('CSS Priority', 'e2pdf'),
                    'Enabling WYSIWYG can affect "HTML" Source' => __('Enabling WYSIWYG can affect "HTML" Source', 'e2pdf'),
                    'Hidden Fields' => __('Hidden Fields', 'e2pdf'),
                    'Access By URL' => __('Access By URL', 'e2pdf'),
                    'Error Message' => __('Error Message', 'e2pdf'),
                    'Merge' => __('Merge', 'e2pdf'),
                    'Css' => __('Css', 'e2pdf'),
                    'Element' => __('Element', 'e2pdf'),
                    'Position Top' => __('Position Top', 'e2pdf'),
                    'Position Left' => __('Position Left', 'e2pdf'),
                    'Padding Top' => __('Padding Top', 'e2pdf'),
                    'Padding Bottom' => __('Padding Bottom', 'e2pdf'),
                    'Padding Left' => __('Padding Left', 'e2pdf'),
                    'Padding Right' => __('Padding Right', 'e2pdf'),
                    'Border Color' => __('Border Color', 'e2pdf'),
                    'Border Top' => __('Border Top', 'e2pdf'),
                    'Border Bottom' => __('Border Bottom', 'e2pdf'),
                    'Border Left' => __('Border Left', 'e2pdf'),
                    'Border Right' => __('Border Right', 'e2pdf'),
                    'Field' => __('Field', 'e2pdf'),
                    'Style' => __('Style', 'e2pdf'),
                    'Font Color' => __('Font Color', 'e2pdf'),
                    'Font Size' => __('Font Size', 'e2pdf'),
                    'Text Align' => __('Text Align', 'e2pdf'),
                    'Text Rotation' => __('Text Rotation', 'e2pdf'),
                    'Preg Replace Pattern' => __('Preg Replace Pattern', 'e2pdf'),
                    'Preg Replace Replacement' => __('Preg Replace Replacement', 'e2pdf'),
                    'Preg Match All Pattern' => __('Preg Match All Pattern', 'e2pdf'),
                    'Preg Match All Output' => __('Preg Match All Output', 'e2pdf'),
                    'Preg Filters' => __('Preg Filters', 'e2pdf'),
                    'Char Spacing' => __('Char Spacing', 'e2pdf'),
                    'Color' => __('Color', 'e2pdf'),
                    'QR Code' => __('QR Code', 'e2pdf'),
                    'Barcode' => __('Barcode', 'e2pdf'),
                    'Format' => __('Format', 'e2pdf'),
                    'Precision' => __('Precision', 'e2pdf'),
                    'L - Smallest' => __('L - Smallest', 'e2pdf'),
                    'M - Medium' => __('M - Medium', 'e2pdf'),
                    'Q - High' => __('Q - High', 'e2pdf'),
                    'H - Best' => __('H - Best', 'e2pdf'),
                    'UPC-A' => __('UPC-A', 'e2pdf'),
                    'UPC-E' => __('UPC-E', 'e2pdf'),
                    'EAN-8' => __('EAN-8', 'e2pdf'),
                    'EAN-13' => __('EAN-13', 'e2pdf'),
                    'EAN-13 PAD' => __('EAN-13 PAD', 'e2pdf'),
                    'EAN-13 NOPAD' => __('EAN-13 NOPAD', 'e2pdf'),
                    'EAN-128' => __('EAN-128', 'e2pdf'),
                    'CODE-39' => __('CODE-39', 'e2pdf'),
                    'CODE-39 ASCII' => __('CODE-39 ASCII', 'e2pdf'),
                    'CODE-93' => __('CODE-93', 'e2pdf'),
                    'CODE-93 ASCII' => __('CODE-93 ASCII', 'e2pdf'),
                    'CODE-128' => __('CODE-128', 'e2pdf'),
                    'CODEBAR' => __('CODEBAR', 'e2pdf'),
                    'ITF' => __('ITF', 'e2pdf'),
                    'DMTX' => __('DMTX', 'e2pdf'),
                    'DMTX S' => __('DMTX S', 'e2pdf'),
                    'DMTX R' => __('DMTX R', 'e2pdf'),
                    'GS1 DMTX' => __('GS1 DMTX', 'e2pdf'),
                    'GS1 DMTX S' => __('GS1 DMTX S', 'e2pdf'),
                    'GS1 DMTX R' => __('GS1 DMTX R', 'e2pdf'),
                    'All Templates for this Website will be deactivated! Are you sure want to continue?' => __('All Templates for this Website will be deactivated! Are you sure want to continue?', 'e2pdf'),
                    'Quiet Zone Size' => __('Quiet Zone Size', 'e2pdf')
                );
                break;
            case 'params':
                $data = array(
                    'nonce' => wp_create_nonce('e2pdf_ajax'),
                    'plugins_url' => plugins_url('', $this->helper->get('plugin_file_path')),
                    'upload_url' => $this->helper->get_upload_url(),
                    'license_type' => $this->helper->get('license')->get('type')
                );
                break;
            case 'template_sizes':
                $controller_e2pdf_templates = new Controller_E2pdf_Templates();
                $data = $controller_e2pdf_templates->get_sizes_list();
                break;
            case 'template_extensions':
                $model_e2pdf_extension = new Model_E2pdf_Extension();
                $data = $model_e2pdf_extension->extensions();
                break;
            default:
                break;
        }

        return $data;
    }

    /**
     * Check requirenments before activation
     */
    public function requirenments() {
        if (version_compare(PHP_VERSION, '5.3.3', '<')) {
            throw new Exception(
                            sprintf(__("E2Pdf requires PHP version 5.3.3 or later. Your PHP version is %s", 'e2pdf'), PHP_VERSION)
            );
        }

        if (!function_exists('curl_version')) {
            throw new Exception(
                            __("CURL extension is not installed or not enabled in your PHP installation", 'e2pdf')
            );
        }
    }

    public function admin_functions() {
        $current_screen = get_current_screen();
        if (!$current_screen || !in_array($current_screen->id, $this->e2pdf_admin_pages)) {
            return;
        }

        if ($current_screen->id == 'e2pdf_page_e2pdf-templates' && !isset($_GET['action'])) {
            $screen_option = array(
                'label' => __('Templates per page', 'e2pdf') . ':',
                'default' => get_option('e2pdf_templates_screen_per_page') ? get_option('e2pdf_templates_screen_per_page') : '20',
                'option' => 'e2pdf_templates_screen_per_page'
            );
            add_screen_option('per_page', $screen_option);
        }

        $this->helper->set('license', new Model_E2pdf_License());
        if ($this->helper->get('license')->get('error') === 'Site Url Not Found. Please try to "Reactivate" plugin.') {
            update_option('e2pdf_version', '1.00.00');
            $this->after_load();
            $this->helper->set('license', new Model_E2pdf_License());
        }
    }

    public function wp_loaded() {
        if (!get_transient('e2pdf_adobesign_refresh_token')) {
            new Model_E2pdf_AdobeSign();
        }
    }

    /**
     * On plugin activation
     */
    public function activate($network = false) {
        global $wpdb;

        try {
            $this->requirenments();
            if (is_multisite() && $network) {
                foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                    $this->activate_site($blog_id);
                }
            } else {
                $this->activate_site();
            }
        } catch (Exception $e) {
            echo "<div style='line-height: 70px;'>";
            echo $e->getMessage();
            echo "</div>";
            exit();
        }
    }

    public function activate_new_network($blog_id, $user_id, $domain, $path, $site_id, $meta) {
        if (is_plugin_active_for_network('e2pdf/e2pdf.php')) {
            $this->activate_site($blog_id);
        }
    }

    public function activate_site($blog_id = false) {
        global $wpdb;

        $db_prefix = $wpdb->prefix;

        if ($blog_id) {
            switch_to_blog($blog_id);
            $db_prefix = $wpdb->get_blog_prefix($blog_id);
            if (!is_main_site($blog_id)) {
                $this->helper->set('upload_dir', $this->helper->get_wp_upload_dir('basedir') . '/e2pdf/');
                $this->helper->set('tmp_dir', $this->helper->get('upload_dir') . 'tmp/');
                $this->helper->set('pdf_dir', $this->helper->get('upload_dir') . 'pdf/');
                $this->helper->set('fonts_dir', $this->helper->get('upload_dir') . 'fonts/');
                $this->helper->set('tpl_dir', $this->helper->get('upload_dir') . 'tpl/');
                $this->helper->set('viewer_dir', $this->helper->get('upload_dir') . 'viewer/');
            }
        }

        $dirs = array(
            $this->helper->get('upload_dir'),
            $this->helper->get('tmp_dir'),
            $this->helper->get('pdf_dir'),
            $this->helper->get('fonts_dir'),
            $this->helper->get('tpl_dir'),
            $this->helper->get('viewer_dir')
        );

        if (!is_main_site($blog_id)) {
            array_unshift($dirs, $this->helper->get_wp_upload_dir('basedir'));
        }

        foreach ($dirs as $dir) {
            if ($this->helper->create_dir($dir)) {
                if ($dir == $this->helper->get('fonts_dir')) {
                    if (!file_exists($this->helper->get('fonts_dir') . "NotoSans-Regular.ttf") || get_option('e2pdf_version') < '1.10.05') {
                        copy($this->helper->get('plugin_dir') . "data/fonts/NotoSans-Regular.ttf", $this->helper->get('fonts_dir') . "NotoSans-Regular.ttf");
                    }
                } elseif ($dir == $this->helper->get('viewer_dir')) {
                    if (!file_exists($this->helper->get('viewer_dir') . "style.css")) {
                        $this->helper->create_file($this->helper->get('viewer_dir') . "style.css");
                    }
                }
            } else {
                throw new Exception(
                                sprintf(__("Can't create folder %s", 'e2pdf'), $dir)
                );
            }
        }

        $this->helper->init_db($db_prefix);

        if (get_option('e2pdf_version') && get_option('e2pdf_version') < '1.16.14' && get_option('e2pdf_wc_cart_template_id') && get_option('e2pdf_wc_cart_template_id_order') === false) {
            update_option('e2pdf_wc_cart_template_id_order', '99');
        }

        if (get_option('e2pdf_version') !== $this->helper->get('version')) {
            update_option('e2pdf_version', $this->helper->get('version'));
        }

        if (get_option('e2pdf_cache') === false) {
            update_option('e2pdf_cache', '1');
        }

        if (get_option('e2pdf_cache_fonts') === false) {
            update_option('e2pdf_cache_fonts', '1');
        }

        $model_e2pdf_api = new Model_E2pdf_Api();
        $model_e2pdf_api->set(array(
            'action' => 'common/activate'
        ));
        $model_e2pdf_api->request();
        $model_e2pdf_license = new Model_E2pdf_License();

        if ($blog_id) {
            restore_current_blog();
            $this->helper->set('upload_dir', $this->helper->get_wp_upload_dir('basedir') . '/e2pdf/');
            $this->helper->set('tmp_dir', $this->helper->get('upload_dir') . 'tmp/');
            $this->helper->set('pdf_dir', $this->helper->get('upload_dir') . 'pdf/');
            $this->helper->set('fonts_dir', $this->helper->get('upload_dir') . 'fonts/');
            $this->helper->set('tpl_dir', $this->helper->get('upload_dir') . 'tpl/');
            $this->helper->set('viewer_dir', $this->helper->get('upload_dir') . 'viewer/');
        }
    }

    /**
     * On plugin deactivation
     */
    public function deactivate($network = false) {
        
    }

    /**
     * On plugin uninstall
     */
    public static function uninstall() {
        global $wpdb;

        if (is_multisite()) {
            foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) {
                self::uninstall_site($blog_id);
            }
        } else {
            self::uninstall_site();
        }
    }

    public static function uninstall_site($blog_id = false) {
        global $wpdb;

        $db_prefix = $wpdb->prefix;
        $helper_e2pdf_helper = Helper_E2pdf_Helper::instance();

        if ($blog_id) {
            switch_to_blog($blog_id);
            $db_prefix = $wpdb->get_blog_prefix($blog_id);

            if (!is_main_site($blog_id)) {
                $helper_e2pdf_helper->set('upload_dir', $helper_e2pdf_helper->get_wp_upload_dir('basedir') . '/e2pdf/');
            }
        }


        $model_e2pdf_api = new Model_E2pdf_Api();
        $model_e2pdf_api->set(array(
            'action' => 'common/uninstall'
        ));
        $model_e2pdf_api->request();

        $options = Model_E2pdf_Options::get_options();
        foreach ($options as $option_key => $option_value) {
            delete_option($option_key, $option_value);
        }

        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_templates' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_entries' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_datasets' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_pages' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_elements' . '`');
        $wpdb->query('DROP TABLE IF EXISTS `' . $db_prefix . 'e2pdf_revisions' . '`');

        $wpdb->query($wpdb->prepare('DELETE FROM `' . $db_prefix . 'options' . '` WHERE option_name LIKE %s OR option_name LIKE %s', '_transient_e2pdf_%', '_transient_e2pdf_%'));

        $helper_e2pdf_helper->delete_dir($helper_e2pdf_helper->get('upload_dir'));

        $caps = $helper_e2pdf_helper->get_caps();
        $roles = wp_roles()->get_names();
        foreach ($roles as $role_key => $sub_role) {
            $role = get_role($role_key);
            foreach ($caps as $cap_key => $cap) {
                $role->remove_cap($cap_key);
            }
        }

        if ($blog_id) {
            restore_current_blog();
            $helper_e2pdf_helper->set('upload_dir', $helper_e2pdf_helper->get_wp_upload_dir('basedir') . '/e2pdf/');
        }
    }

}
