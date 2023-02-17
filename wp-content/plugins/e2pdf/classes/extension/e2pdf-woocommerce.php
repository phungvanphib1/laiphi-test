<?php

/**
 * E2pdf Wordpress Extension
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      1.09.07
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Extension_E2pdf_Woocommerce extends Model_E2pdf_Model {

    private $options;
    private $info = array(
        'key' => 'woocommerce',
        'title' => 'WooCommerce'
    );

    function __construct() {
        parent::__construct();
    }

    /**
     * Get info about extension
     * 
     * @param string $key - Key to get assigned extension info value
     * 
     * @return array|string - Extension Key and Title or Assigned extension info value
     */
    public function info($key = false) {
        if ($key && isset($this->info[$key])) {
            return $this->info[$key];
        } else {
            return array(
                $this->info['key'] => $this->info['title']
            );
        }
    }

    /**
     * Check if needed plugin active
     * 
     * @return bool - Activated/Not Activated plugin
     */
    public function active() {
        if (!function_exists('is_plugin_active')) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        if (is_plugin_active('woocommerce/woocommerce.php') || defined('E2PDF_WOOCOMMERCE_EXTENSION')) {
            return true;
        }
        return false;
    }

    /**
     * Set option
     * 
     * @param string $key - Key of option
     * @param string $value - Value of option
     * 
     * @return bool - Status of setting option
     */
    public function set($key, $value) {
        if (!isset($this->options)) {
            $this->options = new stdClass();
        }

        $this->options->$key = $value;
    }

    /**
     * Get option by key
     * 
     * @param string $key - Key to get assigned option value
     * 
     * @return mixed
     */
    public function get($key) {
        if (isset($this->options->$key)) {
            $value = $this->options->$key;
            return $value;
        } elseif ($key == 'args') {
            return array();
        } else {
            return false;
        }
    }

    /**
     * Get items to work with
     * 
     * @return array() - List of available items
     */
    public function items() {

        $content = array();

        $items = array(
            'product',
            'product_variation',
            'shop_order',
            'cart'
        );

        foreach ($items as $item) {
            $content[] = $this->item($item);
        }

        return $content;
    }

    /**
     * Get entries for export
     * 
     * @param string $item - Item
     * @param string $name - Entries names
     * 
     * @return array() - Entries list
     */
    public function datasets($item = false, $name = false) {

        $datasets = array();
        $datasets_tmp = array();
        if ($item) {
            if ($item == 'cart') {
                if (function_exists('wc_get_page_id') && wc_get_page_id('cart')) {
                    $datasets_tmp = get_posts(
                            array(
                                'post_type' => 'any',
                                'post__in' => array(wc_get_page_id('cart')),
                                'numberposts' => -1,
                                'post_status' => 'any'
                    ));
                }
            } else {
                $datasets_tmp = get_posts(
                        array(
                            'post_type' => $item,
                            'numberposts' => -1,
                            'post_status' => 'any'
                ));
            }

            if ($datasets_tmp) {
                foreach ($datasets_tmp as $key => $dataset) {
                    $this->set('item', $item);
                    $this->set('dataset', $dataset->ID);

                    $dataset_title = $this->render($name);
                    if (!$dataset_title) {
                        $dataset_title = isset($dataset->post_title) && $dataset->post_title ? $dataset->post_title : $dataset->ID;
                    }
                    $datasets[] = array(
                        'key' => $dataset->ID,
                        'value' => $dataset_title
                    );
                }
            }
        }

        return $datasets;
    }

    /**
     * Get item
     * 
     * @param string $item - Item
     * 
     * @return object - Item
     */
    public function item($item = false) {

        if (!$item && $this->get('item')) {
            $item = $this->get('item');
        }

        $form = new stdClass();
        $post = get_post_type_object($item);
        if ($post) {
            $form->id = $item;
            $form->name = $post->label ? $post->label : $item;
            $form->url = $this->helper->get_url(array('post_type' => $item), 'edit.php?');
        } elseif ($item == 'cart' && function_exists('wc_get_page_id')) {
            $form->id = $item;
            $form->name = __('Cart', 'e2pdf');
            $form->url = $this->helper->get_url(array('post' => wc_get_page_id('cart'), 'action' => 'edit'), 'post.php?');
        } else {
            $form->id = '';
            $form->name = '';
            $form->url = 'javascript:void(0);';
        }

        return $form;
    }

    /**
     * Get dataset
     * 
     * @param int $dataset - Dataset ID
     * 
     * @return object - Dataset
     */
    public function dataset($dataset = false) {

        $dataset = (int) $dataset;
        if (!$dataset) {
            return;
        }

        $data = new stdClass();
        $data->url = $this->helper->get_url(array('post' => $dataset, 'action' => 'edit'), 'post.php?');

        return $data;
    }

    public function load_actions() {
        $email_actions = apply_filters(
                'woocommerce_email_actions',
                array(
                    'woocommerce_low_stock',
                    'woocommerce_no_stock',
                    'woocommerce_product_on_backorder',
                    'woocommerce_order_status_pending_to_processing',
                    'woocommerce_order_status_pending_to_completed',
                    'woocommerce_order_status_processing_to_cancelled',
                    'woocommerce_order_status_pending_to_failed',
                    'woocommerce_order_status_pending_to_on-hold',
                    'woocommerce_order_status_failed_to_processing',
                    'woocommerce_order_status_failed_to_completed',
                    'woocommerce_order_status_failed_to_on-hold',
                    'woocommerce_order_status_cancelled_to_processing',
                    'woocommerce_order_status_cancelled_to_completed',
                    'woocommerce_order_status_cancelled_to_on-hold',
                    'woocommerce_order_status_on-hold_to_processing',
                    'woocommerce_order_status_on-hold_to_cancelled',
                    'woocommerce_order_status_on-hold_to_failed',
                    'woocommerce_order_status_completed',
                    'woocommerce_order_fully_refunded',
                    'woocommerce_order_partially_refunded',
                    'woocommerce_new_customer_note',
                    'woocommerce_created_customer',
                )
        );

        foreach ($email_actions as $email_action) {
            add_action($email_action . '_notification', array($this, 'action_after_email'), 99, 2);
        }
        add_action('woocommerce_after_resend_order_email', array($this, 'action_after_email'), 99, 2);

        if (get_option('e2pdf_wc_cart_template_id')) {
            $e2pdf_wc_cart_template_id_order = '10';
            if (get_option('e2pdf_wc_cart_template_id_order') !== false) {
                $e2pdf_wc_cart_template_id_order = (int) get_option('e2pdf_wc_cart_template_id_order');
            }
            add_action('woocommerce_proceed_to_checkout', array($this, 'action_e2pdf_wc_cart_template_id'), $e2pdf_wc_cart_template_id_order);
        }

        if (get_option('e2pdf_wc_checkout_template_id')) {
            $e2pdf_wc_checkout_template_id_order = '10';
            if (get_option('e2pdf_wc_checkout_template_id_order') !== false) {
                $e2pdf_wc_checkout_template_id_order = (int) get_option('e2pdf_wc_checkout_template_id_order');
            }
            $e2pdf_wc_checkout_template_id_hook = 'woocommerce_review_order_before_submit';
            if (get_option('e2pdf_wc_checkout_template_id_hook') == 'woocommerce_review_order_after_submit') {
                $e2pdf_wc_checkout_template_id_hook = 'woocommerce_review_order_after_submit';
            }
            add_action($e2pdf_wc_checkout_template_id_hook, array($this, 'action_e2pdf_wc_checkout_template_id'), $e2pdf_wc_checkout_template_id_order);
        }

        /**
         * Compatibility fix with Enforcing Rules enabled
         */
        add_action('woocommerce_settings_products', array($this, 'action_enforcing_rules_enabled_check'), -10);
        add_action('woocommerce_admin_process_product_object', array($this, 'action_enforcing_rules_enabled_check'), 10);
    }

    public function load_filters() {
        add_filter('woocommerce_product_file_download_path', array($this, 'filter_woocommerce_product_file_download_path'), 10, 3);
        add_filter('woocommerce_short_description', array($this, 'filter_content_custom'), 10, 1);
        add_filter('the_content', array($this, 'filter_the_content'), 10, 2);
        add_filter('woocommerce_email_attachments', array($this, 'filter_woocommerce_email_attachments'), 10, 4);
        add_filter('woocommerce_mail_content', array($this, 'filter_woocommerce_mail_content'), 99, 1);
        add_filter('woocommerce_display_product_attributes', array($this, 'filter_woocommerce_display_product_attributes'), 10, 2);
        add_filter('e2pdf_model_shortcode_wc_product_get_attribute_value', array($this, 'filter_e2pdf_model_shortcode_wc_product_get_attribute_value'), 10, 2);
        add_filter('e2pdf_model_options_get_options_options', array($this, 'filter_e2pdf_model_options_get_options_options'), 10, 1);
        add_filter('woocommerce_customer_available_downloads', array($this, 'filter_download_urls'), 10, 1);
        add_filter('woocommerce_order_get_downloadable_items', array($this, 'filter_download_urls'), 10, 1);

        if (get_option('e2pdf_wc_invoice_template_id')) {
            add_filter('woocommerce_my_account_my_orders_actions', array($this, 'filter_woocommerce_my_account_my_orders_actions'), 10, 2);
        }

        /*
         * Flatsome theme global tab content
         */
        add_filter('theme_mod_tab_content', array($this, 'filter_content_custom'), 10, 1);

        /*
         * Variable Product Short Description Dynamic ID
         */
        add_filter('woocommerce_available_variation', array($this, 'filter_woocommerce_available_variation'), 10, 3);

        /*
         * PDF download link with E2Pdf shortcodes
         */
        add_filter('e2pdf_model_shortcode_e2pdf_wc_product_description', array($this, 'filter_the_content'), 10, 2);

        /**
         * Compatibility fix with Enforcing Rules enabled
         */
        add_filter('woocommerce_product_downloads_approved_directory_validation_for_shortcodes', array($this, 'filter_woocommerce_product_downloads_approved_directory_validation_for_shortcodes'), 99, 1);
    }

    /**
     * Delete attachments that were sent by email
     */
    public function action_after_email($order_id, $order = false) {
        $files = $this->helper->get('woocommerce_attachments');
        if (is_array($files) && !empty($files)) {
            foreach ($files as $key => $file) {
                $this->helper->delete_dir(dirname($file) . '/');
            }
            $this->helper->deset('woocommerce_attachments');
        }
    }

    public function action_e2pdf_wc_cart_template_id() {
        if (!is_cart() || WC()->cart->is_empty()) {
            return;
        }
        echo do_shortcode('[e2pdf-download id="' . get_option('e2pdf_wc_cart_template_id') . '" dataset="' . wc_get_page_id('cart') . '" class="button e2pdf-wc-download-button e2pdf-wc-download-cart-button"]');
    }

    public function action_e2pdf_wc_checkout_template_id() {
        if (!is_checkout() || WC()->cart->is_empty()) {
            return;
        }
        echo do_shortcode('[e2pdf-download id="' . get_option('e2pdf_wc_cart_template_id') . '" dataset="' . wc_get_page_id('cart') . '" class="button e2pdf-wc-download-button e2pdf-wc-download-checkout-button"]');
    }

    public function action_enforcing_rules_enabled_check() {
        if (get_option('wc_downloads_approved_directories_mode') === 'enabled') {
            if (class_exists("Automattic\WooCommerce\Internal\ProductDownloads\ApprovedDirectories\Register")) {
                try {
                    $download_directories = wc_get_container()->get(Automattic\WooCommerce\Internal\ProductDownloads\ApprovedDirectories\Register::class);
                    $directory_id = $download_directories->add_approved_directory("file://./");
                    if ($directory_id) {
                        $download_directories->enable_by_id($directory_id);
                    }
                } catch (Exception $e) {
                    
                }
            }
        }
    }

    public function filter_woocommerce_available_variation($data, $wc_product_variable, $variation) {
        $description = $variation->get_description();
        if ($description && false !== strpos($description, '[e2pdf-download') || false !== strpos($description, '[e2pdf-save') || false !== strpos($description, '[e2pdf-view') || false !== strpos($description, '[e2pdf-adobesign') || false !== strpos($description, '[e2pdf-zapier')) {
            $description = $this->filter_the_content($description, $variation->get_variation_id());
            $data['variation_description'] = wc_format_content($description);
        }
        return $data;
    }

    public function filter_woocommerce_mail_content($message) {
        if ($message) {
            if (false !== strpos($message, '[e2pdf-attachment') || false !== strpos($message, '[e2pdf-save') || false !== strpos($message, '[e2pdf-zapier')) {
                $message = preg_replace('~(?:\[(e2pdf-attachment|e2pdf-save|e2pdf-zapier)/?)\s[^\]]+/?\]~s', "", $message);
            }
        }

        return $message;
    }

    public function filter_woocommerce_email_attachments($attachments, $wc_email_id, $order, $wc_email) {

        if ($order && is_object($order) && method_exists($order, 'get_id') && $wc_email && is_object($wc_email) && method_exists($wc_email, 'get_additional_content')) {

            $additional_content = $wc_email->get_additional_content();

            if (false !== strpos($additional_content, '[')) {
                $shortcode_tags = array(
                    'e2pdf-attachment',
                    'e2pdf-save',
                    'e2pdf-zapier'
                );

                preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $additional_content, $matches);
                $tagnames = array_intersect($shortcode_tags, $matches[1]);

                if (!empty($tagnames)) {

                    $items = array();
                    $items_variation = array();
                    $items_product = array();
                    if (method_exists($order, 'get_items')) {
                        $order_items = $order->get_items();
                        foreach ($order_items as $item_id => $order_item) {
                            $items[$item_id] = $order_item->get_product_id();
                            if ($order_item->get_variation_id()) {
                                $items_variation[$item_id] = $order_item->get_variation_id();
                            } else {
                                $items_product[$item_id] = $order_item->get_product_id();
                            }
                        }
                    }

                    $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                    preg_match_all("/$pattern/", $additional_content, $shortcodes);

                    foreach ($shortcodes[0] as $key => $shortcode_value) {

                        $shortcode = array();
                        $shortcode[1] = $shortcodes[1][$key];
                        $shortcode[2] = $shortcodes[2][$key];
                        $shortcode[3] = $shortcodes[3][$key];
                        $shortcode[4] = $shortcodes[4][$key];
                        $shortcode[5] = $shortcodes[5][$key];
                        $shortcode[6] = $shortcodes[6][$key];

                        $atts = shortcode_parse_atts($shortcode[3]);
                        $file = false;
                        $template = new Model_E2pdf_Template();
                        if (isset($atts['id']) && $atts['id']) {
                            $template->load($atts['id']);
                        }

                        $attributes = array();
                        foreach ($atts as $att_key => $att) {
                            if (strpos($att_key, 'attribute_') === 0) {
                                $attributes[substr($att_key, 10)] = $att;
                            }
                        }

                        if ($template->get('item') == 'product') {
                            if (isset($atts['dataset'])) {
                                foreach ($items_product as $item_id => $item) {
                                    if ($item == $atts['dataset']) {
                                        $attachment = true;
                                        if (!empty($attributes)) {
                                            $product = wc_get_product($item);
                                            if ($product) {
                                                foreach ($attributes as $attribute_key => $attribute) {
                                                    if ($product->get_attribute($attribute_key) != $attribute) {
                                                        $attachment = false;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $attachment = false;
                                            }
                                        }

                                        if ($attachment) {
                                            $item_shortcode = $shortcode;
                                            if (!isset($atts['apply'])) {
                                                $item_shortcode[3] .= ' apply="true"';
                                            }
                                            if (!isset($atts['filter'])) {
                                                $item_shortcode[3] .= ' filter="true"';
                                            }
                                            if (!isset($atts['wc_order_id'])) {
                                                $item_shortcode[3] .= ' wc_order_id="' . $order->get_id() . '"';
                                            }
                                            if (!isset($atts['wc_product_item_id'])) {
                                                $item_shortcode[3] .= ' wc_product_item_id="' . $item_id . '"';
                                            }

                                            if (($item_shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $item_shortcode[2] === 'e2pdf-attachment') {
                                                $file = do_shortcode_tag($item_shortcode);
                                                if ($file) {
                                                    if ($item_shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                                                        $this->helper->add('woocommerce_attachments', $file);
                                                    }
                                                    $attachments[] = $file;
                                                }
                                            } elseif ($item_shortcode === 'e2pdf-zapier') {
                                                do_shortcode_tag($item_shortcode);
                                            }
                                        }
                                    }
                                }
                            } elseif (!isset($atts['dataset'])) {
                                foreach ($items_product as $item_id => $item) {
                                    $attachment = true;
                                    if (!empty($attributes)) {
                                        $product = wc_get_product($item);
                                        if ($product) {
                                            foreach ($attributes as $attribute_key => $attribute) {
                                                if ($product->get_attribute($attribute_key) != $attribute) {
                                                    $attachment = false;
                                                    break;
                                                }
                                            }
                                        } else {
                                            $attachment = false;
                                        }
                                    }

                                    if ($attachment) {
                                        if (isset($atts['product_ids']) && $atts['product_ids']) {
                                            $attachment = false;
                                            $product_ids = explode(',', $atts['product_ids']);
                                            if (in_array($item, $product_ids)) {
                                                $attachment = true;
                                            }
                                        }

                                        if ($attachment) {
                                            $item_shortcode = $shortcode;
                                            if (!isset($atts['apply'])) {
                                                $item_shortcode[3] .= ' apply="true"';
                                            }
                                            if (!isset($atts['filter'])) {
                                                $item_shortcode[3] .= ' filter="true"';
                                            }
                                            if (!isset($atts['wc_order_id'])) {
                                                $item_shortcode[3] .= ' wc_order_id="' . $order->get_id() . '"';
                                            }
                                            if (!isset($atts['wc_product_item_id'])) {
                                                $item_shortcode[3] .= ' wc_product_item_id="' . $item_id . '"';
                                            }

                                            $item_shortcode[3] .= ' dataset="' . $item . '"';

                                            if (($item_shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $item_shortcode[2] === 'e2pdf-attachment') {
                                                $file = do_shortcode_tag($item_shortcode);
                                                if ($file) {
                                                    if ($item_shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                                                        $this->helper->add('woocommerce_attachments', $file);
                                                    }
                                                    $attachments[] = $file;
                                                }
                                            } elseif ($item_shortcode === 'e2pdf-zapier') {
                                                do_shortcode_tag($item_shortcode);
                                            }
                                        }
                                    }
                                }
                            }
                        } elseif ($template->get('item') == 'product_variation') {
                            if (isset($atts['dataset'])) {
                                foreach ($items_variation as $item_id => $item) {
                                    if ($item == $atts['dataset']) {
                                        $attachment = true;
                                        if (!empty($attributes)) {
                                            $product = wc_get_product($item);
                                            if ($product) {
                                                foreach ($attributes as $attribute_key => $attribute) {
                                                    if ($product->get_attribute($attribute_key) != $attribute) {
                                                        $attachment = false;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $attachment = false;
                                            }
                                        }

                                        if ($attachment) {
                                            $item_shortcode = $shortcode;
                                            if (!isset($atts['apply'])) {
                                                $item_shortcode[3] .= ' apply="true"';
                                            }
                                            if (!isset($atts['filter'])) {
                                                $item_shortcode[3] .= ' filter="true"';
                                            }
                                            if (!isset($atts['wc_order_id'])) {
                                                $item_shortcode[3] .= ' wc_order_id="' . $order->get_id() . '"';
                                            }
                                            if (!isset($atts['wc_product_item_id'])) {
                                                $item_shortcode[3] .= ' wc_product_item_id="' . $item_id . '"';
                                            }

                                            if (($item_shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $item_shortcode[2] === 'e2pdf-attachment') {
                                                $file = do_shortcode_tag($item_shortcode);
                                                if ($file) {
                                                    if ($item_shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                                                        $this->helper->add('woocommerce_attachments', $file);
                                                    }
                                                    $attachments[] = $file;
                                                }
                                            } elseif ($item_shortcode === 'e2pdf-zapier') {
                                                do_shortcode_tag($item_shortcode);
                                            }
                                        }
                                    }
                                }
                            } elseif (!isset($atts['dataset'])) {
                                foreach ($items_variation as $item_id => $item) {
                                    $attachment = true;
                                    if (!empty($attributes)) {
                                        $product = wc_get_product($item);
                                        if ($product) {
                                            foreach ($attributes as $attribute_key => $attribute) {
                                                if ($product->get_attribute($attribute_key) != $attribute) {
                                                    $attachment = false;
                                                    break;
                                                }
                                            }
                                        } else {
                                            $attachment = false;
                                        }
                                    }

                                    if ($attachment && isset($atts['product_ids']) && $atts['product_ids']) {
                                        $attachment = false;
                                        $product_ids = explode(',', $atts['product_ids']);
                                        if (in_array($item, $product_ids)) {
                                            $attachment = true;
                                        } else {
                                            $product = wc_get_product($item);
                                            if ($product && method_exists($product, 'get_parent_id')) {
                                                if (in_array($product->get_parent_id(), $product_ids)) {
                                                    $attachment = true;
                                                }
                                            }
                                        }
                                    }

                                    if ($attachment) {
                                        $item_shortcode = $shortcode;
                                        if (!isset($atts['apply'])) {
                                            $item_shortcode[3] .= ' apply="true"';
                                        }
                                        if (!isset($atts['filter'])) {
                                            $item_shortcode[3] .= ' filter="true"';
                                        }
                                        if (!isset($atts['wc_order_id'])) {
                                            $item_shortcode[3] .= ' wc_order_id="' . $order->get_id() . '"';
                                        }
                                        if (!isset($atts['wc_product_item_id'])) {
                                            $item_shortcode[3] .= ' wc_product_item_id="' . $item_id . '"';
                                        }

                                        $item_shortcode[3] .= ' dataset="' . $item . '"';

                                        if (($item_shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $item_shortcode[2] === 'e2pdf-attachment') {
                                            $file = do_shortcode_tag($item_shortcode);
                                            if ($file) {
                                                if ($item_shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                                                    $this->helper->add('woocommerce_attachments', $file);
                                                }
                                                $attachments[] = $file;
                                            }
                                        } elseif ($item_shortcode === 'e2pdf-zapier') {
                                            do_shortcode_tag($item_shortcode);
                                        }
                                    }
                                }
                            }
                        } elseif ($template->get('item') == 'shop_order') {
                            if (!isset($atts['dataset'])) {
                                $attachment = true;
                                if (isset($atts['product_ids']) && $atts['product_ids']) {
                                    $attachment = false;
                                    $product_ids = explode(',', $atts['product_ids']);
                                    foreach ($product_ids as $product_id) {
                                        if (in_array($product_id, $items) || in_array($product_id, $items_variation)) {
                                            $attachment = true;
                                            break;
                                        }
                                    }
                                }

                                if ($attachment) {
                                    if (!isset($atts['apply'])) {
                                        $shortcode[3] .= ' apply="true"';
                                    }
                                    if (!isset($atts['filter'])) {
                                        $shortcode[3] .= ' filter="true"';
                                    }
                                    $shortcode[3] .= ' dataset="' . $order->get_id() . '"';

                                    if (($shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $shortcode[2] === 'e2pdf-attachment') {
                                        $file = do_shortcode_tag($shortcode);
                                        if ($file) {
                                            if ($shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                                                $this->helper->add('woocommerce_attachments', $file);
                                            }
                                            $attachments[] = $file;
                                        }
                                    } elseif ($shortcode[2] === 'e2pdf-zapier') {
                                        do_shortcode_tag($shortcode);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $attachments;
    }

    public function filter_gform_merge_tag_filter($value, $merge_tag, $modifier, $field, $raw_value) {

        if ($field && $value) {
            if ($field->type == 'consent') {
                $mod = explode('.', $merge_tag);
                if (isset($mod[1]) && $mod[1] == '1') {
                    $value = '1';
                }
            } elseif ($field->type == 'list') {

                if ($modifier && $modifier != 'text') {

                    $list_id = false;
                    $field_id = false;

                    if (false !== strpos($modifier, '_')) {
                        $mod = explode('_', $modifier);
                        if (isset($mod[0]) && is_numeric($mod[0])) {
                            $list_id = $mod[0] - 1;
                        }
                        if (isset($mod[1]) && is_numeric($mod[1])) {
                            $field_id = $mod[1] - 1;
                        }
                    } elseif (is_numeric($modifier)) {
                        $list_id = $modifier - 1;
                    }

                    if ($list_id !== false) {
                        $value = '';
                        $list = maybe_unserialize($raw_value);
                        if (is_array($list)) {
                            if (isset($list[$list_id])) {
                                if ($field_id !== false) {
                                    if (is_array($list[$list_id]) && isset(array_values($list[$list_id])[$field_id])) {
                                        $value = array_values($list[$list_id])[$field_id];
                                    }
                                } else {
                                    if (is_array($list[$list_id])) {
                                        $value = implode(',', $list[$list_id]);
                                    } else {
                                        $value = $list[$list_id];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $value;
    }

    public function filter_woocommerce_display_product_attributes($product_attributes, $product) {
        if ($product_attributes) {
            foreach ($product_attributes as $product_attribute_key => $product_attribute) {
                if (isset($product_attribute['value']) && $product_attribute['value'] && false !== strpos($product_attribute['value'], '[e2pdf-download')) {
                    $product_attributes[$product_attribute_key]['value'] = $this->filter_content(htmlspecialchars_decode($product_attribute['value']), $product->get_id());
                }
            }
        }
        return $product_attributes;
    }

    public function filter_e2pdf_model_shortcode_wc_product_get_attribute_value($product_attribute, $product) {
        if ($product_attribute && false !== strpos($product_attribute, '[e2pdf-download') && $product && is_object($product) && method_exists($product, 'get_id')) {
            $product_attribute = $this->filter_content(htmlspecialchars_decode($product_attribute), $product->get_id());
        }
        return $product_attribute;
    }

    public function filter_woocommerce_product_file_download_path($file_path, $product, $download_id) {
        $file_path = $this->filter_content($file_path, $product->get_id(), true);
        return $file_path;
    }

    public function filter_download_urls($downloads) {
        if (!empty($downloads)) {

            $items = array();

            foreach ($downloads as $key => $download) {
                if (isset($download['file']['file']) && $download['file']['file'] && strpos($download['file']['file'], '[e2pdf-download') !== false) {
                    global $wpdb;

                    if (isset($items[$download['download_id']])) {
                        $item_ids = $items[$download['download_id']];
                    } else {
                        $item_ids = array();
                    }

                    $item_id = $wpdb->get_var($wpdb->prepare("SELECT `items`.`order_item_id` FROM " . $wpdb->prefix . "woocommerce_order_items `items` INNER JOIN " . $wpdb->prefix . "woocommerce_order_itemmeta `itemmeta` ON `itemmeta`.`order_item_id` = `items`.`order_item_id` AND `itemmeta`.`meta_key` = '_product_id' AND `itemmeta`.`meta_value` = '" . $download['product_id'] . "' WHERE `items`.`order_id` = '" . $download['order_id'] . "' and `items`.`order_item_type` = 'line_item' and `items`.`order_item_id` NOT IN ( '" . implode("','", $item_ids) . "' ) ORDER BY `items`.`order_item_id` ASC"));
                    if ($item_id) {
                        $downloads[$key]['download_url'] = add_query_arg(
                                array('item_id' => $item_id), $download['download_url']
                        );
                        $items[$download['download_id']][] = $item_id;
                    }
                }
            }
        }
        return $downloads;
    }

    /**
     * Render value according to content
     * 
     * @param string $value - Content
     * @param string $type - Type of rendering value
     * @param array $field - Field details
     * 
     * @return string - Fully rendered value
     */
    public function render($value, $field = array(), $convert_shortcodes = true) {

        $html = false;
        if (isset($field['type']) && $field['type'] == 'e2pdf-html') {
            $html = true;
        }

        $value = $this->render_shortcodes($value, $field);
        $value = $this->strip_shortcodes($value);
        $value = $this->convert_shortcodes($value, $convert_shortcodes, $html);

        if (isset($field['type']) && $field['type'] === 'e2pdf-checkbox' && isset($field['properties']['option'])) {
            $option = $this->render($field['properties']['option']);
            $options = explode(', ', $value);
            $option_options = explode(', ', $option);
            if (is_array($options) && is_array($option_options) && !array_diff($option_options, $options)) {
                $value = $option;
            } else {
                $value = "";
            }
        }

        return $value;
    }

    /**
     * Render shortcodes which available in this extension
     * 
     * @param string $value - Content
     * @param string $type - Type of rendering value
     * @param array $field - Field details
     * 
     * @return string - Value with rendered shortcodes
     */
    public function render_shortcodes($value, $field = array()) {

        $dataset = $this->get('dataset');
        $item = $this->get('item');
        $args = $this->get('args');
        $user_id = $this->get('user_id');
        $template_id = $this->get('template_id') ? $this->get('template_id') : '0';
        $element_id = isset($field['element_id']) ? $field['element_id'] : '0';

        if ($this->verify()) {

            $args = apply_filters('e2pdf_extension_render_shortcodes_args', $args, $element_id, $template_id, $item, $dataset, false, false);

            $post = get_post($dataset);

            if (false !== strpos($value, '[')) {

                $replace = array(
                    '[id]' => isset($post->ID) && $post->ID ? $post->ID : '',
                    '[e2pdf-dataset]' => $dataset,
                    '[pdf_url]' => '[e2pdf-url]'
                );
                $value = str_replace(array_keys($replace), $replace, $value);

                if (false !== strpos($value, '[e2pdf-url]')) {
                    $pdf_url = '';
                    if (!$this->get('uid') && $this->get('uid_params')) {
                        $entry = new Model_E2pdf_Entry();
                        $entry->set('entry', $this->get('uid_params'));
                        if (!$entry->load_by_uid($entry->get('uid'))) {
                            $entry->save();
                        }
                        $this->set('uid', $entry->get('uid'));
                    }

                    if ($this->get('uid')) {
                        $url_data = array(
                            'page' => 'e2pdf-download',
                            'uid' => $this->get('uid')
                        );

                        $pdf_url = esc_url_raw(
                                $this->helper->get_frontend_pdf_url($url_data, false, array(
                                    'e2pdf_extension_render_shortcodes_site_url',
                                    'e2pdf_extension_woocommerce_render_shortcodes_site_url'
                                ))
                        );
                    }

                    $replace = array(
                        '[e2pdf-url]' => $pdf_url
                    );
                    $value = str_replace(array_keys($replace), $replace, $value);
                }

                $shortcode_tags = array(
                    'e2pdf-foreach',
                );

                preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $value, $matches);
                $tagnames = array_intersect($shortcode_tags, $matches[1]);

                foreach ($matches[1] as $key => $shortcode) {
                    if (strpos($shortcode, ':') !== false) {
                        $shortcode_tags[] = $shortcode;
                    }
                }

                if (!empty($tagnames)) {

                    $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                    preg_match_all("/$pattern/", $value, $shortcodes);

                    foreach ($shortcodes[0] as $key => $shortcode_value) {
                        $shortcode = array();
                        $shortcode[1] = $shortcodes[1][$key];
                        $shortcode[2] = $shortcodes[2][$key];
                        $shortcode[3] = $shortcodes[3][$key];
                        $shortcode[4] = $shortcodes[4][$key];
                        $shortcode[5] = $shortcodes[5][$key];
                        $shortcode[6] = $shortcodes[6][$key];

                        $atts = shortcode_parse_atts($shortcode[3]);
                        if ($shortcode['2'] == 'e2pdf-foreach') {
                            if ($this->get('item') == 'product' || $this->get('item') == 'product_variation') {
                                if (isset($atts['shortcode']) && $atts['shortcode'] == 'e2pdf-wc-customer') {
                                    
                                } elseif (isset($atts['shortcode']) && $atts['shortcode'] == 'e2pdf-wc-order') {
                                    if (!isset($atts['id']) && $this->get('wc_order_id')) {
                                        $shortcode[3] .= ' id="' . $this->get('wc_order_id') . '"';
                                    }
                                } else {
                                    if (!isset($atts['id']) && isset($post->ID) && $post->ID) {
                                        $shortcode[3] .= ' id="' . $post->ID . '"';
                                    }
                                    if (!isset($atts['wc_order_id']) && $this->get('wc_order_id')) {
                                        $shortcode[3] .= ' wc_order_id="' . $this->get('wc_order_id') . '"';
                                    }

                                    if (!isset($atts['wc_product_item_id']) && $this->get('wc_product_item_id')) {
                                        $shortcode[3] .= ' wc_product_item_id="' . $this->get('wc_product_item_id') . '"';
                                    }
                                }
                            } elseif ($this->get('item') == 'shop_order') {
                                if (isset($atts['shortcode']) && $atts['shortcode'] == 'e2pdf-wc-customer') {
                                    
                                } elseif (isset($atts['shortcode']) && $atts['shortcode'] == 'e2pdf-wc-product') {
                                    if (!isset($atts['wc_order_id']) && isset($post->ID) && $post->ID) {
                                        $shortcode[3] .= ' wc_order_id="' . $post->ID . '"';
                                    }
                                } else {
                                    if (!isset($atts['id']) && isset($post->ID) && $post->ID) {
                                        $shortcode[3] .= ' id="' . $post->ID . '"';
                                    }
                                }
                            } elseif ($this->get('item') == 'cart') {
                                if (isset($atts['shortcode']) && $atts['shortcode'] == 'e2pdf-wc-customer') {
                                    
                                } elseif (isset($atts['shortcode']) && $atts['shortcode'] == 'e2pdf-wc-product') {
                                    if (!isset($atts['wc_order_id']) && isset($post->ID) && $post->ID) {
                                        $shortcode[3] .= ' wc_order_id="cart"';
                                    }
                                } else {
                                    if (!isset($atts['id']) && isset($post->ID) && $post->ID) {
                                        $shortcode[3] .= ' id="' . $post->ID . '"';
                                    }
                                }
                            }
                            $value = str_replace($shortcode_value, do_shortcode_tag($shortcode), $value);
                        }
                    }
                }

                $shortcode_tags = array(
                    'e2pdf-wc-product',
                    'e2pdf-wc-order',
                    'e2pdf-wc-cart',
                    'e2pdf-content',
                    'e2pdf-user',
                    'e2pdf-arg',
                );

                preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $value, $matches);
                $tagnames = array_intersect($shortcode_tags, $matches[1]);

                foreach ($matches[1] as $key => $shortcode) {
                    if (strpos($shortcode, ':') !== false) {
                        $shortcode_tags[] = $shortcode;
                    }
                }

                if (!empty($tagnames)) {

                    $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                    preg_match_all("/$pattern/", $value, $shortcodes);

                    foreach ($shortcodes[0] as $key => $shortcode_value) {
                        $shortcode = array();
                        $shortcode[1] = $shortcodes[1][$key];
                        $shortcode[2] = $shortcodes[2][$key];
                        $shortcode[3] = $shortcodes[3][$key];
                        $shortcode[4] = $shortcodes[4][$key];
                        $shortcode[5] = $shortcodes[5][$key];
                        $shortcode[6] = $shortcodes[6][$key];

                        $atts = shortcode_parse_atts($shortcode[3]);

                        if ($shortcode[2] === 'e2pdf-user') {
                            if ((!isset($atts['id']) && $user_id) || (isset($atts['id']) && $atts['id'] == 'dynamic')) {
                                if (!isset($atts['id'])) {
                                    $shortcode[3] .= ' id="' . $user_id . '"';
                                }
                                if (substr($shortcode_value, -13) === '[/e2pdf-user]') {
                                    $sub_value = '';
                                    if ($shortcode['5']) {
                                        if (isset($field['type']) && ($field['type'] === 'e2pdf-image' || $field['type'] === 'e2pdf-signature' || $field['type'] === 'e2pdf-qrcode' || $field['type'] === 'e2pdf-barcode' || ($field['type'] === 'e2pdf-checkbox' && isset($field['properties']['option'])))) {
                                            $sub_value = $this->render($shortcode['5'], array(), false);
                                        } else {
                                            $sub_value = $this->render($shortcode['5'], $field, false);
                                        }
                                    }
                                    $value = str_replace($shortcode_value, "[e2pdf-user" . $shortcode['3'] . "]" . $sub_value . "[/e2pdf-user]", $value);
                                } else {
                                    $value = str_replace($shortcode_value, "[" . $shortcode['2'] . $shortcode['3'] . "]", $value);
                                }
                            }
                        } else if ($shortcode['2'] == 'e2pdf-wc-product') {
                            if ($this->get('item') == 'product' || $this->get('item') == 'product_variation') {
                                if (!isset($atts['id']) && !isset($atts['index']) && isset($post->ID) && $post->ID) {
                                    $shortcode[3] .= ' id="' . $post->ID . '"';
                                }
                                if (!isset($atts['wc_order_id']) && $this->get('wc_order_id')) {
                                    $shortcode[3] .= ' wc_order_id="' . $this->get('wc_order_id') . '"';
                                }

                                if (!isset($atts['wc_product_item_id']) && $this->get('wc_product_item_id')) {
                                    $shortcode[3] .= ' wc_product_item_id="' . $this->get('wc_product_item_id') . '"';
                                }

                                $value = str_replace($shortcode_value, "[e2pdf-wc-product" . $shortcode['3'] . "]", $value);
                            } elseif ($this->get('item') == 'shop_order') {
                                if (!isset($atts['wc_order_id']) && isset($post->ID) && $post->ID) {
                                    $shortcode[3] .= ' wc_order_id="' . $post->ID . '"';
                                }
                                $value = str_replace($shortcode_value, "[e2pdf-wc-product" . $shortcode['3'] . "]", $value);
                            } elseif ($this->get('item') == 'cart') {
                                $shortcode[3] .= ' wc_order_id="cart"';
                                $value = str_replace($shortcode_value, "[e2pdf-wc-product" . $shortcode['3'] . "]", $value);
                            }
                        } else if ($shortcode['2'] == 'e2pdf-wc-order') {
                            if ($this->get('item') == 'product' || $this->get('item') == 'product_variation') {
                                if (!isset($atts['id']) && $this->get('wc_order_id')) {
                                    $shortcode[3] .= ' id="' . $this->get('wc_order_id') . '"';
                                    $value = str_replace($shortcode_value, "[e2pdf-wc-order" . $shortcode['3'] . "]", $value);
                                }
                            }
                            if ($this->get('item') == 'shop_order') {
                                if (!isset($atts['id']) && isset($post->ID) && $post->ID) {
                                    $shortcode[3] .= ' id="' . $post->ID . '"';
                                }
                                $value = str_replace($shortcode_value, "[e2pdf-wc-order" . $shortcode['3'] . "]", $value);
                            }
                        } else if ($shortcode['2'] == 'e2pdf-wc-cart') {
                            if ($this->get('item') == 'cart') {
                                if (!isset($atts['id']) && isset($post->ID) && $post->ID) {
                                    $shortcode[3] .= ' id="' . $post->ID . '"';
                                }
                                $value = str_replace($shortcode_value, "[e2pdf-wc-cart" . $shortcode['3'] . "]", $value);
                            }
                        } elseif ($shortcode['2'] == 'e2pdf-arg') {
                            if (isset($atts['key']) && isset($args[$atts['key']])) {
                                $sub_value = $this->strip_shortcodes($args[$atts['key']]);
                                $value = str_replace($shortcode_value, $sub_value, $value);
                            } else {
                                $value = str_replace($shortcode_value, '', $value);
                            }
                        }
                    }
                }

                $shortcode_tags = array(
                    'e2pdf-format-number',
                    'e2pdf-format-date',
                    'e2pdf-format-output',
                );
                $shortcode_tags = apply_filters('e2pdf_extension_render_shortcodes_tags', $shortcode_tags);
                preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $value, $matches);
                $tagnames = array_intersect($shortcode_tags, $matches[1]);

                if (!empty($tagnames)) {

                    $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                    preg_match_all("/$pattern/", $value, $shortcodes);
                    foreach ($shortcodes[0] as $key => $shortcode_value) {
                        $shortcode = array();
                        $shortcode[1] = $shortcodes[1][$key];
                        $shortcode[2] = $shortcodes[2][$key];
                        $shortcode[3] = $shortcodes[3][$key];
                        $shortcode[4] = $shortcodes[4][$key];
                        $shortcode[5] = $shortcodes[5][$key];
                        $shortcode[6] = $shortcodes[6][$key];

                        if (!$shortcode['5']) {
                            $sub_value = '';
                        } elseif (isset($field['type']) && ($field['type'] === 'e2pdf-image' || $field['type'] === 'e2pdf-signature' || $field['type'] === 'e2pdf-qrcode' || $field['type'] === 'e2pdf-barcode' || ($field['type'] === 'e2pdf-checkbox' && isset($field['properties']['option'])))) {
                            $sub_value = $this->render($shortcode['5'], array(), false);
                        } else {
                            $sub_value = $this->render($shortcode['5'], $field, false);
                        }
                        $value = str_replace($shortcode_value, "[" . $shortcode['2'] . $shortcode['3'] . "]" . $sub_value . "[/" . $shortcode['2'] . "]", $value);
                    }
                }
            }

            $value = apply_filters('e2pdf_extension_render_shortcodes_pre_do_shortcode', $value, $element_id, $template_id, $item, $dataset, false, false);
            $value = do_shortcode($value);
            $value = apply_filters('e2pdf_extension_render_shortcodes_after_do_shortcode', $value, $element_id, $template_id, $item, $dataset, false, false);
            $value = apply_filters('e2pdf_extension_render_shortcodes_pre_value', $value, $element_id, $template_id, $item, $dataset, false, false);

            // Process Gravity Forms Connected Entry
            if (false !== strpos($value, '{') && class_exists('WC_GFPA_Compatibility') && class_exists('GFCommon') && class_exists('GFFormsModel')) {

                if (($this->get('item') == 'product' || $this->get('item') == 'product_variation') && $this->get('wc_order_id')) {

                    $order = wc_get_order($this->get('wc_order_id'));
                    $order_items = $order->get_items();

                    foreach ($order_items as $order_item) {
                        if ($order_item->get_product_id() == $dataset) {
                            $gravity_forms_history = null;
                            $entry_id = false;

                            $meta_data = $order_item->get_meta_data();
                            if (WC_GFPA_Compatibility::is_wc_version_gte_3_2()) {
                                foreach ($meta_data as $meta_data_item) {
                                    $d = $meta_data_item->get_data();
                                    if ($d['key'] == '_gravity_forms_history') {
                                        $gravity_forms_history = array($meta_data_item);
                                        break;
                                    }
                                }
                            } else {
                                $gravity_forms_history = wp_list_filter($meta_data, array('key' => '_gravity_forms_history'));
                            }

                            if ($gravity_forms_history) {
                                $gravity_forms_history_value = array_pop($gravity_forms_history);
                                $entry_id = isset($gravity_forms_history_value->value['_gravity_form_linked_entry_id']) && !empty($gravity_forms_history_value->value['_gravity_form_linked_entry_id']) ?
                                        $gravity_forms_history_value->value['_gravity_form_linked_entry_id'] : false;
                                $form_data = $gravity_forms_history_value->value['_gravity_form_data'];

                                if ($entry_id && !is_wp_error($entry_id)) {
                                    $entry = GFFormsModel::get_entry($entry_id);
                                    if ($entry && isset($entry['form_id'])) {
                                        $form = GFFormsModel::get_form_meta($entry['form_id']);
                                        add_filter('gform_merge_tag_filter', array($this, 'filter_gform_merge_tag_filter'), 30, 5);
                                        $value = GFCommon::replace_variables($value, $form, $entry, false, false, false, 'text');
                                        remove_filter('gform_merge_tag_filter', array($this, 'filter_gform_merge_tag_filter'), 30, 5);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (isset($field['type']) && ($field['type'] === 'e2pdf-image' || $field['type'] === 'e2pdf-signature')) {
                $esig = isset($field['properties']['esig']) && $field['properties']['esig'] ? true : false;
                if ($esig) {
                    //process e-signature
                    $value = "";
                } else {
                    $value = $this->helper->load('properties')->apply($field, $value);
                    if (!$this->helper->load('image')->get_image($value)) {
                        $only_image = isset($field['properties']['only_image']) && $field['properties']['only_image'] ? true : false;
                        $value = $this->strip_shortcodes($value);
                        if (
                                $value &&
                                trim($value) != "" &&
                                extension_loaded('gd') &&
                                function_exists('imagettftext') &&
                                !$only_image
                        ) {
                            if (isset($field['properties']['text_color']) && $field['properties']['text_color']) {
                                $penColour = $this->helper->load('convert')->to_hex_color($field['properties']['text_color']);
                            } else {
                                $penColour = array(0x14, 0x53, 0x94);
                            }

                            $default_options = array(
                                'imageSize' => array(isset($field['width']) ? $field['width'] : '400', isset($field['height']) ? $field['height'] : '150'),
                                'bgColour' => 'transparent',
                                'penColour' => $penColour
                            );

                            $options = array();
                            $options = apply_filters('e2pdf_image_sig_output_options', $options, $element_id, $template_id);
                            $options = array_merge($default_options, $options);

                            $model_e2pdf_font = new Model_E2pdf_Font();

                            $font = false;
                            if (isset($field['properties']['text_font']) && $field['properties']['text_font']) {
                                $font = $model_e2pdf_font->get_font_path($field['properties']['text_font']);
                            }
                            if (!$font) {
                                $font = $model_e2pdf_font->get_font_path('Noto Sans Regular');
                            }
                            if (!$font) {
                                $font = $model_e2pdf_font->get_font_path('Noto Sans');
                            }

                            $size = 150;
                            if (isset($field['properties']['text_font_size']) && $field['properties']['text_font_size']) {
                                $size = $field['properties']['text_font_size'];
                            }

                            $model_e2pdf_signature = new Model_E2pdf_Signature();
                            $value = $model_e2pdf_signature->ttf_signature($value, $size, $font, $options);
                        } else {
                            $value = "";
                        }
                    }
                }
            } elseif (isset($field['type']) && $field['type'] === 'e2pdf-qrcode') {
                $value = $this->strip_shortcodes($value);
                if ($value) {
                    $options = array(
                        'w' => $field['width'],
                        'h' => $field['height'],
                        'wq' => isset($field['properties']['wq']) ? $field['properties']['wq'] : '1'
                    );

                    $precision = isset($field['properties']['precision']) && $field['properties']['precision'] ? $field['properties']['precision'] : 'qrl';
                    $color = isset($field['properties']['color']) && $field['properties']['color'] ? $field['properties']['color'] : false;
                    $background = isset($field['properties']['background']) && $field['properties']['background'] ? $field['properties']['background'] : false;

                    if ($color) {
                        $options['cm'] = $color;
                    }

                    if ($background) {
                        $options['bc'] = $background;
                        $options['cs'] = $background;
                    }

                    $value = $this->helper->load('qrcode')->qrcode($value, $precision, $options);
                }
            } elseif (isset($field['type']) && $field['type'] === 'e2pdf-barcode') {
                $value = $this->strip_shortcodes($value);
                if ($value) {
                    $options = array(
                        'w' => $field['width'],
                        'h' => $field['height'],
                        'wq' => isset($field['properties']['wq']) ? $field['properties']['wq'] : '1'
                    );

                    $format = isset($field['properties']['format']) && $field['properties']['format'] ? $field['properties']['format'] : 'upc-a';
                    $color = isset($field['properties']['color']) && $field['properties']['color'] ? $field['properties']['color'] : false;
                    $text_color = isset($field['properties']['text_color']) && $field['properties']['text_color'] ? $field['properties']['text_color'] : false;
                    $background = isset($field['properties']['background']) && $field['properties']['background'] ? $field['properties']['background'] : false;

                    if ($color) {
                        $options['cm'] = $color;
                    }

                    if ($text_color) {
                        $options['tc'] = $text_color;
                    }

                    if ($background) {
                        $options['bc'] = $background;
                        $options['cs'] = $background;
                    }

                    $value = $this->helper->load('qrcode')->barcode($value, $format, $options);
                }
            } else {
                $value = $this->convert_shortcodes($value);
                $value = $this->helper->load('properties')->apply($field, $value);
            }
        }

        $value = apply_filters('e2pdf_extension_render_shortcodes_value', $value, $element_id, $template_id, $item, $dataset, false, false);

        return $value;
    }

    /**
     * Strip unused shortcodes
     * 
     * @param string $value - Content
     * 
     * @return string - Value with removed unused shortcodes
     */
    public function strip_shortcodes($value) {
        $value = preg_replace('~(?:\[/?)[^/\]]+/?\]~s', "", $value);
        return $value;
    }

    /**
     * Convert "shortcodes" inside value string
     * 
     * @param string $value - Value string
     * @param bool $to - Convert From/To
     * 
     * @return string - Converted value
     */
    public function convert_shortcodes($value, $to = false, $html = false) {
        if ($value) {
            if ($to) {
                $value = str_replace("&#91;", "[", $value);
                if (!$html) {
                    $value = wp_specialchars_decode($value, ENT_QUOTES);
                }
            } else {
                $value = str_replace("[", "&#91;", $value);
            }
        }
        return $value;
    }

    public function auto() {
        $response = array();
        $elements = array();

        $line_height = (int) $this->get('line_height');

        if ($this->get('item') == 'cart') {

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '<strong>Billing:</strong>'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '<strong>Shipping:</strong>'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => $line_height * 7,
                    'value' => '[e2pdf-wc-customer key="get_formatted_billing_address"]'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => $line_height * 7,
                    'value' => '[e2pdf-wc-customer key="get_formatted_shipping_address"]'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '10',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '<strong>Email:</strong>'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '10',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '<strong>Phone:</strong>'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '[e2pdf-wc-customer key="get_billing_email"]'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '[e2pdf-wc-customer key="get_billing_phone"]'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'wysiwyg_disable' => '1',
                    'css' => 'td {' . "\r\n"
                    . 'text-align: center;' . "\r\n"
                    . 'vertical-align: top;' . "\r\n"
                    . '}' . "\r\n"
                    . '.header {' . "\r\n"
                    . 'background: #eeeeee;' . "\r\n"
                    . '}' . "\r\n"
                    . '.item {' . "\r\n"
                    . 'text-align: left;' . "\r\n"
                    . 'width: 40%;' . "\r\n"
                    . '}' . "\r\n"
                    . '.total-label {' . "\r\n"
                    . 'text-align: right;' . "\r\n"
                    . '}' . "\r\n"
                    . '.total-value {' . "\r\n"
                    . 'text-align: right;' . "\r\n"
                    . '}' . "\r\n"
                    . '.total-order {' . "\r\n"
                    . 'font-weight: bold;' . "\r\n"
                    . '}' . "\r\n"
                    . '.formatted-meta-data {' . "\r\n"
                    . 'color: #555555;' . "\r\n"
                    . 'font-size: 8px;' . "\r\n"
                    . '}' . "\r\n"
                    . '.hr {' . "\r\n"
                    . 'background: #eeeeee;' . "\r\n"
                    . '}' . "\r\n"
                    . '.attachment-32x32 {' . "\r\n"
                    . 'margin-top: 5px;' . "\r\n"
                    . '}' . "\r\n"
                    . 'ul {' . "\r\n"
                    . 'padding-left: 10px;' . "\r\n"
                    . '}' . "\r\n"
                    . 'li {' . "\r\n"
                    . 'padding-left: 5px;' . "\r\n"
                    . '}' . "\r\n"
                    ,
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '100%',
                    'height' => 'max',
                    'value' => '<table cellpadding="5">' . "\r\n"
                    . '<tr class="header"><td></td><td class="item">Item</td><td align="center">Quantity</td><td align="center">Unit Price</td><td align="center">Subtotal</td></tr>' . "\r\n"
                    . '[e2pdf-foreach shortcode="e2pdf-wc-cart" key="get_cart"]' . "\r\n"
                    . '<tr>' . "\r\n"
                    . '<td>[e2pdf-wc-product key="get_image" size="32x32" index="[e2pdf-foreach-index]" order="true" wc_filter="true"]' . "\r\n"
                    . '</td>' . "\r\n"
                    . '<td class="item">' . "\r\n"
                    . '[e2pdf-wc-product key="get_name" index="[e2pdf-foreach-index]" order="true" wc_filter="true"]' . "\r\n"
                    . '[e2pdf-foreach-1 shortcode="e2pdf-wc-product" key="get_formatted_meta_data" index="[e2pdf-foreach-index]" order="true"]' . "\r\n"
                    . '<div class="formatted-meta-data">[e2pdf-wc-product key="get_formatted_meta_data" index="[e2pdf-foreach-index]" path="[e2pdf-foreach-key-1].display_key"]: [e2pdf-wc-product key="get_formatted_meta_data" index="[e2pdf-foreach-index]" path="[e2pdf-foreach-key-1].display_value"]</div>' . "\r\n"
                    . '[/e2pdf-foreach-1]' . "\r\n"
                    . '</td>' . "\r\n"
                    . '<td align="center">[e2pdf-wc-product key="get_quantity" index="[e2pdf-foreach-index]" order="true"]</td>' . "\r\n"
                    . '<td align="center">[e2pdf-wc-product key="get_product_price" index="[e2pdf-foreach-index]" order="true" wc_filter="true"]</td>' . "\r\n"
                    . '<td align="center">[e2pdf-wc-product key="get_subtotal" index="[e2pdf-foreach-index]" order="true" wc_filter="true"]</td>' . "\r\n"
                    . '</tr>' . "\r\n"
                    . '[/e2pdf-foreach]' . "\r\n"
                    . '<tr><td colspan="5"><hr class="hr"></td></tr>' . "\r\n"
                    . '[e2pdf-foreach shortcode="e2pdf-wc-cart" key="get_formatted_cart_totals"]' . "\r\n"
                    . '<tr><td colspan="3" class="total-label">[e2pdf-wc-cart key="get_formatted_cart_totals" path="[e2pdf-foreach-key].label"]:</td><td colspan="2" class="total-value">[e2pdf-wc-cart key="get_formatted_cart_totals" path="[e2pdf-foreach-key].value"]</td></tr>' . "\r\n"
                    . '[/e2pdf-foreach]' . "\r\n"
                    . '</table>' . "\r\n"
                )
            );
        } elseif ($this->get('item') == 'shop_order') {
            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '100%',
                    'height' => 'auto',
                    'value' => '<h2>[e2pdf-wc-order key="get_order_number"]</h2>' . "\r\n",
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '100%',
                    'height' => 'auto',
                    'value' => 'Payment via [e2pdf-wc-order key="get_payment_method_title"]' . "\r\n",
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '100%',
                    'height' => $line_height * 3,
                    'value' => 'Order ID: [e2pdf-wc-order key="get_id"]<br>'
                    . 'Date Created: [e2pdf-wc-order key="get_date_created"]<br>' . "\r\n"
                    . 'Status: [e2pdf-wc-order key="get_status" wc_get_order_status_name="true"]' . "\r\n"
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '<strong>Billing:</strong>'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '<strong>Shipping:</strong>'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => $line_height * 7,
                    'value' => '[e2pdf-wc-order key="get_formatted_billing_address"]'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => $line_height * 7,
                    'value' => '[e2pdf-wc-order key="get_formatted_shipping_address"]'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '10',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '<strong>Email:</strong>'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '10',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '<strong>Phone:</strong>'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '[e2pdf-wc-order key="get_billing_email"]'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '5',
                    'left' => '20',
                    'right' => '20',
                    'width' => '50%',
                    'height' => 'auto',
                    'value' => '[e2pdf-wc-order key="get_billing_phone"]'
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'properties' => array(
                    'wysiwyg_disable' => '1',
                    'css' => 'td {' . "\r\n"
                    . 'text-align: center;' . "\r\n"
                    . 'vertical-align: top;' . "\r\n"
                    . '}' . "\r\n"
                    . '.header {' . "\r\n"
                    . 'background: #eeeeee;' . "\r\n"
                    . '}' . "\r\n"
                    . '.item {' . "\r\n"
                    . 'text-align: left;' . "\r\n"
                    . 'width: 40%;' . "\r\n"
                    . '}' . "\r\n"
                    . '.total-label {' . "\r\n"
                    . 'text-align: right;' . "\r\n"
                    . '}' . "\r\n"
                    . '.total-value {' . "\r\n"
                    . 'text-align: right;' . "\r\n"
                    . '}' . "\r\n"
                    . '.total-order {' . "\r\n"
                    . 'font-weight: bold;' . "\r\n"
                    . '}' . "\r\n"
                    . '.formatted-meta-data {' . "\r\n"
                    . 'color: #555555;' . "\r\n"
                    . 'font-size: 8px;' . "\r\n"
                    . '}' . "\r\n"
                    . '.hr {' . "\r\n"
                    . 'background-color: #eeeeee;' . "\r\n"
                    . '}' . "\r\n"
                    . '.attachment-32x32 {' . "\r\n"
                    . 'margin-top: 5px;' . "\r\n"
                    . '}' . "\r\n"
                    . 'ul {' . "\r\n"
                    . 'padding-left: 10px;' . "\r\n"
                    . '}' . "\r\n"
                    . 'li {' . "\r\n"
                    . 'padding-left: 5px;' . "\r\n"
                    . '}' . "\r\n"
                    ,
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '100%',
                    'height' => 'max',
                    'value' => '<table cellpadding="5">' . "\r\n"
                    . '<tr class="header"><td class="item">Item</td><td align="center">Quantity</td><td align="center">Unit Price</td><td align="center">Subtotal</td><td align="center">Total</td></tr>' . "\r\n"
                    . '[e2pdf-foreach shortcode="e2pdf-wc-order" key="get_items"]' . "\r\n"
                    . '<tr>' . "\r\n"
                    . '<td class="item">' . "\r\n"
                    . '[e2pdf-wc-product key="get_name" index="[e2pdf-foreach-index]" order="true"]' . "\r\n"
                    . '[e2pdf-foreach-1 shortcode="e2pdf-wc-product" key="get_formatted_meta_data" index="[e2pdf-foreach-index]"]' . "\r\n"
                    . '<div class="formatted-meta-data">[e2pdf-wc-product key="get_formatted_meta_data" index="[e2pdf-foreach-index]" path="[e2pdf-foreach-key-1].display_key"]: [e2pdf-wc-product key="get_formatted_meta_data" index="[e2pdf-foreach-index]" path="[e2pdf-foreach-key-1].display_value"]</div>' . "\r\n"
                    . '[/e2pdf-foreach-1]' . "\r\n"
                    . '</td>' . "\r\n"
                    . '<td align="center">[e2pdf-wc-product key="get_quantity" index="[e2pdf-foreach-index]" order="true"]</td>' . "\r\n"
                    . '<td align="center">[e2pdf-wc-product key="get_item_subtotal" index="[e2pdf-foreach-index]" order="true" wc_price="true"]</td>' . "\r\n"
                    . '<td align="center">[e2pdf-wc-product key="get_subtotal" index="[e2pdf-foreach-index]" order="true" wc_price="true"]</td>' . "\r\n"
                    . '<td align="center">[e2pdf-wc-product key="get_total" index="[e2pdf-foreach-index]" order="true" wc_price="true"]</td>' . "\r\n"
                    . '</tr>' . "\r\n"
                    . '[/e2pdf-foreach]' . "\r\n"
                    . '<tr><td colspan="5"><hr class="hr"></td></tr>' . "\r\n"
                    . '[e2pdf-foreach shortcode="e2pdf-wc-order" key="get_order_item_totals"]' . "\r\n"
                    . '<tr>' . "\r\n"
                    . '<td colspan="3" class="total-label">[e2pdf-wc-order key="get_order_item_totals" path="[e2pdf-foreach-key].label"]</td>' . "\r\n"
                    . '<td colspan="2" class="total-value">[e2pdf-wc-order key="get_order_item_totals" path="[e2pdf-foreach-key].value"]</td>' . "\r\n"
                    . '</tr>' . "\r\n"
                    . '[/e2pdf-foreach]' . "\r\n"
                    . '</table>' . "\r\n"
                )
            );
        } elseif ($this->get('item') == 'product' || $this->get('item') == 'product_variation') {

            $elements[] = array(
                'type' => 'e2pdf-html',
                'block' => true,
                'float' => true,
                'properties' => array(
                    'top' => '20',
                    'left' => '20',
                    'right' => '20',
                    'width' => '100%',
                    'height' => 'auto',
                    'value' => '<hr>',
                )
            );

            $elements[] = array(
                'type' => 'e2pdf-image',
                'properties' => array(
                    'top' => '20',
                    'width' => '110',
                    'height' => '110',
                    'value' => '[e2pdf-wc-product key="get_image"]',
                    'dimension' => '1',
                    'vertical' => 'top',
                    'horizontal' => 'center'
                )
            );

            if ($this->get('item') == 'product_variation') {

                $elements[] = array(
                    'type' => 'e2pdf-html',
                    'float' => true,
                    'properties' => array(
                        'left' => '20',
                        'right' => '110',
                        'height' => '110',
                        'width' => '100%',
                        'value' => '<h2>[e2pdf-wc-product key="get_name"]</h2><br>' . "\r\n"
                        . '<div>Price: [e2pdf-wc-product key="get_price" wc_price="true"]</div>' . "\r\n"
                    )
                );

                $elements[] = array(
                    'type' => 'e2pdf-html',
                    'block' => true,
                    'float' => true,
                    'properties' => array(
                        'top' => '20',
                        'left' => '20',
                        'right' => '20',
                        'width' => '100%',
                        'height' => 'max',
                        'wysiwyg_disable' => '1',
                        'value' =>
                        '<table cellpadding="5">' . "\r\n"
                        . '<tr><td colspan="2"><b>Additional Information:</b></td></tr>' . "\r\n"
                        . '[e2pdf-foreach shortcode="e2pdf-wc-product" key="get_attributes" wc_filter="true"]' . "\r\n"
                        . '<tr>' . "\r\n"
                        . '<td>[e2pdf-wc-product key="get_attributes" path="[e2pdf-foreach-key].label" wc_filter="true"]:</td>' . "\r\n"
                        . '<td>[e2pdf-wc-product key="get_attributes" path="[e2pdf-foreach-key].value" wc_filter="true"]</td>' . "\r\n"
                        . '</tr>' . "\r\n"
                        . '[/e2pdf-foreach]' . "\r\n"
                        . '</table>' . "\r\n"
                        . '<table cellpadding="5">' . "\r\n"
                        . '<tr><td><b>Description</b></td></tr>' . "\r\n"
                        . '<tr><td>[e2pdf-wc-product key="get_description" wc_format_content="true"]</td></tr>' . "\r\n"
                        . '</table>' . "\r\n"
                    )
                );
            } else {

                $elements[] = array(
                    'type' => 'e2pdf-html',
                    'float' => true,
                    'properties' => array(
                        'left' => '20',
                        'right' => '110',
                        'height' => '110',
                        'width' => '100%',
                        'value' => '<h2>[e2pdf-wc-product key="get_name"]</h2><br>' . "\r\n"
                        . '<div>Price: [e2pdf-wc-product key="get_price" wc_price="true"]</div>' . "\r\n"
                    )
                );

                $elements[] = array(
                    'type' => 'e2pdf-html',
                    'block' => true,
                    'float' => true,
                    'properties' => array(
                        'top' => '20',
                        'left' => '20',
                        'right' => '20',
                        'width' => '100%',
                        'height' => 'max',
                        'wysiwyg_disable' => '1',
                        'value' =>
                        '<table cellpadding="5">' . "\r\n"
                        . '<tr><td colspan="2"><b>Additional Information:</b></td></tr>' . "\r\n"
                        . '[e2pdf-foreach shortcode="e2pdf-wc-product" key="get_attributes" wc_filter="true"]' . "\r\n"
                        . '<tr>' . "\r\n"
                        . '<td>[e2pdf-wc-product key="get_attributes" path="[e2pdf-foreach-key].label" wc_filter="true"]:</td>' . "\r\n"
                        . '<td>[e2pdf-wc-product key="get_attributes" path="[e2pdf-foreach-key].value" wc_filter="true"]</td>' . "\r\n"
                        . '</tr>' . "\r\n"
                        . '[/e2pdf-foreach]' . "\r\n"
                        . '</table>' . "\r\n"
                        . '<table cellpadding="5">' . "\r\n"
                        . '<tr><td><b>Short Description</b></td></tr>' . "\r\n"
                        . '<tr><td>[e2pdf-wc-product key="get_short_description" wc_format_content="true"]</td></tr>' . "\r\n"
                        . '</table>' . "\r\n"
                    )
                );
            }
        }

        $response['page'] = array(
            'bottom' => '20',
            'top' => '20',
            'left' => '20',
            'right' => '20'
        );

        $response['elements'] = $elements;
        return $response;
    }

    public function filter_content_custom($content) {
        $content = $this->filter_content($content);
        return $content;
    }

    public function filter_the_content($content, $post_id = false) {
        $content = $this->filter_content($content, $post_id);
        return $content;
    }

    /**
     * Search and update shortcodes for this extension inside content
     * Auto set of dataset id
     * 
     * @param string $content - Content
     * @param string $post_id - Custom Post ID
     * 
     * @return string - Content with updated shortcodes
     */
    public function filter_content($content, $post_id = false, $download = false) {
        global $post;

        if (!is_string($content) || false === strpos($content, '[')) {
            return $content;
        }

        $shortcode_tags = array(
            'e2pdf-download',
            'e2pdf-save',
            'e2pdf-view',
            'e2pdf-adobesign',
            'e2pdf-zapier'
        );

        preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
        $tagnames = array_intersect($shortcode_tags, $matches[1]);

        if (!empty($tagnames)) {

            $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

            preg_match_all("/$pattern/", $content, $shortcodes);

            foreach ($shortcodes[0] as $key => $shortcode_value) {

                wp_reset_postdata();

                $shortcode = array();
                $shortcode[1] = $shortcodes[1][$key];
                $shortcode[2] = $shortcodes[2][$key];
                $shortcode[3] = $shortcodes[3][$key];
                $shortcode[4] = $shortcodes[4][$key];
                $shortcode[5] = $shortcodes[5][$key];
                $shortcode[6] = $shortcodes[6][$key];

                $atts = shortcode_parse_atts($shortcode[3]);

                if (($shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $shortcode[2] === 'e2pdf-attachment') {
                    
                } else {

                    if (isset($atts['id'])) {
                        $template = new Model_E2pdf_Template();
                        $template->load($atts['id']);
                        if ($template->get('extension') === 'wordpress') {
                            continue;
                        } elseif ($template->get('extension') === 'woocommerce') {

                            if (!isset($atts['dataset']) && $template->get('item') == 'shop_order' && function_exists('wc_get_order') && isset($_GET['order'])) {
                                $order_id = wc_get_order_id_by_order_key(wc_clean(wp_unslash($_GET['order']))); // WPCS: input var ok, CSRF ok.
                                if ($order_id) {
                                    $atts['dataset'] = $order_id;
                                    $shortcode[3] .= ' dataset="' . $order_id . '"';
                                }
                            }

                            if (!isset($atts['dataset']) && ($post_id || isset($post->ID))) {
                                $dataset = $post_id ? $post_id : $post->ID;
                                $atts['dataset'] = $dataset;
                                $shortcode[3] .= ' dataset="' . $dataset . '"';
                            }

                            if (($template->get('item') == 'product' || $template->get('item') == 'product_variation') && function_exists('wc_get_order') && isset($_GET['order'])) {
                                $order_id = wc_get_order_id_by_order_key(wc_clean(wp_unslash($_GET['order']))); // WPCS: input var ok, CSRF ok.
                                if ($order_id) {
                                    $atts['wc_order_id'] = $order_id;
                                    $shortcode[3] .= ' wc_order_id="' . $order_id . '"';

                                    $item_id = isset($_GET['item_id']) && $_GET['item_id'] ? wc_clean(wp_unslash($_GET['item_id'])) : false;
                                    if ($item_id) {
                                        $order = wc_get_order($order_id);
                                        if ($order && method_exists($order, 'get_items')) {
                                            $order_items = $order->get_items();
                                            if ($order_items && isset($order_items[$item_id])) {
                                                $atts['wc_product_item_id'] = $item_id;
                                                $shortcode[3] .= ' wc_product_item_id="' . $item_id . '"';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!isset($atts['apply'])) {
                        $shortcode[3] .= ' apply="true"';
                    }

                    if (!isset($atts['filter'])) {
                        $shortcode[3] .= ' filter="true"';
                    }

                    if ($download) {
                        $site_url = str_replace(array('http:', 'https:'), array('', ''), $this->helper->get_frontend_site_url());
                        $shortcode[3] .= ' output="url" site_url="' . $site_url . '" esc_url_raw="true" wc_product_download="true"';
                    }

                    $content = str_replace($shortcode_value, do_shortcode_tag($shortcode), $content);
                }
            }
        }

        return $content;
    }

    /**
     * Add options for WooCommerce extension
     * 
     * @param array $options - List of options 
     * 
     * @return array - Updated options list
     */
    public function filter_e2pdf_model_options_get_options_options($options = array()) {
        global $wpdb;

        $model_e2pdf_template = new Model_E2pdf_Template();

        $invoice_templates = array(
            '0' => __('--- Select ---', 'e2pdf'),
        );
        $templates = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $model_e2pdf_template->get_table() . "` WHERE extension = %s AND item = %s AND activated = %s ORDER BY ID ASC", 'woocommerce', 'shop_order', '1'), ARRAY_A);
        if (!empty($templates)) {
            foreach ($templates as $template) {
                $invoice_templates[$template['ID']] = $template['title'];
            }
        }

        $cart_templates = array(
            '0' => __('--- Select ---', 'e2pdf'),
        );
        $templates = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $model_e2pdf_template->get_table() . "` WHERE extension = %s AND item = %s AND activated = %s ORDER BY ID ASC", 'woocommerce', 'cart', '1'), ARRAY_A);

        if (!empty($templates)) {
            foreach ($templates as $template) {
                $cart_templates[$template['ID']] = $template['title'];
            }
        }

        $invoice_statuses = !is_array(get_option('e2pdf_wc_invoice_statuses')) ? array() : get_option('e2pdf_wc_invoice_statuses');
        $order_statuses = array();
        if (function_exists('wc_get_order_statuses')) {
            $order_statuses = wc_get_order_statuses();
        }

        foreach ($order_statuses as $order_status_key => $order_status) {
            $invoice_statuses_options[] = array(
                'name' => $order_status,
                'key' => 'e2pdf_wc_invoice_statuses[]',
                'value' => in_array($order_status_key, $invoice_statuses) ? $order_status_key : '',
                'checkbox_value' => $order_status_key,
                'placeholder' => $order_status,
                'type' => 'checkbox',
                'default_value' => ''
            );
        }

        $invoice_statuses_options[] = array(
            'name' => $order_status,
            'key' => 'e2pdf_wc_invoice_statuses[]',
            'value' => in_array('any', $invoice_statuses) || get_option('e2pdf_wc_invoice_statuses') === false ? 'any' : '',
            'checkbox_value' => 'any',
            'placeholder' => __('Any', 'e2pdf'),
            'type' => 'checkbox',
            'default_value' => ''
        );

        $options['woocommerce_group'] = array(
            'name' => __('WooCommerce', 'e2pdf'),
            'action' => 'extension',
            'group' => 'woocommerce_group',
            'options' => array(
                array(
                    'name' => __('Invoice PDF Template', 'e2pdf'),
                    'key' => 'e2pdf_wc_invoice_template_id',
                    'value' => get_option('e2pdf_wc_invoice_template_id') === false ? '0' : get_option('e2pdf_wc_invoice_template_id'),
                    'default_value' => '0',
                    'type' => 'select',
                    'options' => $invoice_templates
                ),
                array(
                    'name' => __('Status to Show Invoice', 'e2pdf'),
                    'key' => 'e2pdf_wc_invoice_statuses',
                    'type' => 'checkbox_list',
                    'options' => $invoice_statuses_options,
                ),
                array(
                    'name' => __('Cart PDF Button Template', 'e2pdf'),
                    'key' => 'e2pdf_wc_cart_template_id',
                    'value' => get_option('e2pdf_wc_cart_template_id') === false ? '0' : get_option('e2pdf_wc_cart_template_id'),
                    'default_value' => '0',
                    'type' => 'select',
                    'options' => $cart_templates
                ),
                array(
                    'name' => __('Cart PDF Button Order', 'e2pdf'),
                    'key' => 'e2pdf_wc_cart_template_id_order',
                    'value' => get_option('e2pdf_wc_cart_template_id_order') === false ? '10' : get_option('e2pdf_wc_cart_template_id_order'),
                    'default_value' => '10',
                    'type' => 'text',
                    'class' => 'e2pdf-numbers',
                    'placeholder' => '0'
                ),
                array(
                    'name' => __('Checkout PDF Button Template', 'e2pdf'),
                    'key' => 'e2pdf_wc_checkout_template_id',
                    'value' => get_option('e2pdf_wc_checkout_template_id') === false ? '0' : get_option('e2pdf_wc_checkout_template_id'),
                    'default_value' => '0',
                    'type' => 'select',
                    'options' => $cart_templates
                ),
                array(
                    'name' => __('Checkout PDF Button Hook', 'e2pdf'),
                    'key' => 'e2pdf_wc_checkout_template_id_hook',
                    'value' => get_option('e2pdf_wc_checkout_template_id_hook') === false ? 'woocommerce_review_order_before_submit' : get_option('e2pdf_wc_checkout_template_id_hook'),
                    'default_value' => 'woocommerce_review_order_before_submit',
                    'type' => 'select',
                    'options' => array(
                        'woocommerce_review_order_before_submit' => __('Before Submit Button', 'e2pdf'),
                        'woocommerce_review_order_after_submit' => __('After Submit Button', 'e2pdf')
                    )
                ),
                array(
                    'name' => __('Checkout PDF Button Order', 'e2pdf'),
                    'key' => 'e2pdf_wc_checkout_template_id_order',
                    'value' => get_option('e2pdf_wc_checkout_template_id_order') === false ? '10' : get_option('e2pdf_wc_checkout_template_id_order'),
                    'default_value' => '10',
                    'type' => 'text',
                    'class' => 'e2pdf-numbers',
                    'placeholder' => '0'
                ),
            )
        );
        return $options;
    }

    function filter_woocommerce_my_account_my_orders_actions($actions, $order) {
        $invoice_statuses = !is_array(get_option('e2pdf_wc_invoice_statuses')) ? array() : get_option('e2pdf_wc_invoice_statuses');
        if (in_array($order->get_status(), $invoice_statuses) || in_array("wc-" . $order->get_status(), $invoice_statuses) || in_array('any', $invoice_statuses) || get_option('e2pdf_wc_invoice_statuses') === false) {
            $actions['e2pdf_invoice'] = array(
                'url' => do_shortcode('[e2pdf-download id="' . get_option('e2pdf_wc_invoice_template_id') . '" dataset="' . $order->get_id() . '" output="url"]'),
                'name' => apply_filters('e2pdf_wc_my_account_my_orders_actions_invoice_title', do_shortcode('[e2pdf-download id="' . get_option('e2pdf_wc_invoice_template_id') . '" dataset="' . $order->get_id() . '" output="button_title"]'))
            );
        }
        return apply_filters('e2pdf_wc_filter_woocommerce_my_account_my_orders_actions', $actions, $order);
    }

    function filter_woocommerce_product_downloads_approved_directory_validation_for_shortcodes($enabled) {
        if (get_option('wc_downloads_approved_directories_mode') === 'enabled') {
            return false;
        }
        return $enabled;
    }

    /**
     * Verify if item and dataset exists
     * 
     * @return bool - item and dataset exists
     */
    public function verify() {
        $item = $this->get('item');
        $dataset = $this->get('dataset');

        if ($item && $dataset && get_post($dataset) && ($item == get_post_type($dataset) || ($item == 'cart' && $dataset == wc_get_page_id('cart')))) {
            return true;
        }

        return false;
    }

    /**
     * Init Visual Mapper data
     * 
     * @return bool|string - HTML data source for Visual Mapper
     */
    public function visual_mapper() {

        $vc = "";

        if ($this->get('item') == 'cart') {

            $vc .= "<h3>" . __('Cart', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart", "e2pdf"), 'e2pdf-wc-cart key="cart"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Products", "e2pdf"), 'e2pdf-wc-cart key="get_cart"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Applied Coupons", "e2pdf"), 'e2pdf-wc-cart key="get_applied_coupons"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Total", "e2pdf"), 'e2pdf-wc-cart key="get_cart_total"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart Subtotal", "e2pdf"), 'e2pdf-wc-cart key="get_cart_subtotal"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Tax", "e2pdf"), 'e2pdf-wc-cart key="get_cart_tax"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart Hash", "e2pdf"), 'e2pdf-wc-cart key="get_cart_hash"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Contents Total", "e2pdf"), 'e2pdf-wc-cart key="get_cart_contents_total"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart Contents Tax", "e2pdf"), 'e2pdf-wc-cart key="get_cart_contents_tax"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Contents Taxes", "e2pdf"), 'e2pdf-wc-cart key="get_cart_contents_taxes"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart Contents Count", "e2pdf"), 'e2pdf-wc-cart key="get_cart_contents_count"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Contents Weight", "e2pdf"), 'e2pdf-wc-cart key="get_cart_contents_weight"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart Item Quantities", "e2pdf"), 'e2pdf-wc-cart key="get_cart_item_quantities"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Item Tax Classes", "e2pdf"), 'e2pdf-wc-cart key="get_cart_item_tax_classes"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart Item Tax Classes For Shipping", "e2pdf"), 'e2pdf-wc-cart key="get_cart_item_tax_classes_for_shipping"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Shipping Total", "e2pdf"), 'e2pdf-wc-cart key="get_cart_shipping_total"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Coupon Discount Totals", "e2pdf"), 'e2pdf-wc-cart key="get_coupon_discount_totals"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Coupon Discount Tax Totals", "e2pdf"), 'e2pdf-wc-cart key="get_coupon_discount_tax_totals"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Totals", "e2pdf"), 'e2pdf-wc-cart key="get_totals"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Total", "e2pdf"), 'e2pdf-wc-cart key="get_total"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Total Tax", "e2pdf"), 'e2pdf-wc-cart key="get_total_tax"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Total Ex Tax", "e2pdf"), 'e2pdf-wc-cart key="get_total_ex_tax"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Total Discount", "e2pdf"), 'e2pdf-wc-cart key="get_total_discount"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Subtotal", "e2pdf"), 'e2pdf-wc-cart key="get_subtotal"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Subtotal Tax", "e2pdf"), 'e2pdf-wc-cart key="get_subtotal_tax"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Discount Total", "e2pdf"), 'e2pdf-wc-cart key="get_discount_total"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Discount Tax", "e2pdf"), 'e2pdf-wc-cart key="get_discount_tax"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Total", "e2pdf"), 'e2pdf-wc-cart key="get_shipping_total"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Tax", "e2pdf"), 'e2pdf-wc-cart key="get_shipping_tax"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Taxes", "e2pdf"), 'e2pdf-wc-cart key="get_shipping_taxes"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Fees", "e2pdf"), 'e2pdf-wc-cart key="get_fees"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Fee Total", "e2pdf"), 'e2pdf-wc-cart key="get_fee_total"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Fee Tax", "e2pdf"), 'e2pdf-wc-cart key="get_fee_tax"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Fee Taxes", "e2pdf"), 'e2pdf-wc-cart key="get_fee_taxes"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Displayed Subtotal", "e2pdf"), 'e2pdf-wc-cart key="get_displayed_subtotal"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Tax Price Display Mode", "e2pdf"), 'e2pdf-wc-cart key="get_tax_price_display_mode"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Taxes", "e2pdf"), 'e2pdf-wc-cart key="get_taxes"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Taxes Total", "e2pdf"), 'e2pdf-wc-cart key="get_taxes_total"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Taxes Total", "e2pdf"), 'e2pdf-wc-cart key="get_shipping_method_title"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Taxes Total", "e2pdf"), 'e2pdf-wc-cart key="get_payment_method_title"')}</div>";
            $vc .= "</div>";

            $vc .= "<h3>" . __('Cart Common', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("ID", "e2pdf"), 'e2pdf-wc-cart key="id"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Author", "e2pdf"), 'e2pdf-wc-cart key="post_author"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Date", "e2pdf"), 'e2pdf-wc-cart key="post_date"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Date (GMT)", "e2pdf"), 'e2pdf-wc-cart key="post_date_gmt"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Content", "e2pdf"), 'e2pdf-wc-cart key="post_content"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Title", "e2pdf"), 'e2pdf-wc-cart key="post_title"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Excerpt", "e2pdf"), 'e2pdf-wc-cart key="post_excerpt"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Status", "e2pdf"), 'e2pdf-wc-cart key="post_status"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Comment Status", "e2pdf"), 'e2pdf-wc-cart key="comment_status"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Ping Status", "e2pdf"), 'e2pdf-wc-cart key="ping_status"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Password", "e2pdf"), 'e2pdf-wc-cart key="post_password"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Name", "e2pdf"), 'e2pdf-wc-cart key="post_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("To Ping", "e2pdf"), 'e2pdf-wc-cart key="to_ping"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Ping", "e2pdf"), 'e2pdf-wc-cart key="pinged"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Modified Date", "e2pdf"), 'e2pdf-wc-cart key="post_modified"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Modified Date (GMT)", "e2pdf"), 'e2pdf-wc-cart key="post_modified_gmt"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Filtered Content", "e2pdf"), 'e2pdf-wc-cart key="post_content_filtered"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Parent ID", "e2pdf"), 'e2pdf-wc-cart key="post_parent"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("GUID", "e2pdf"), 'e2pdf-wc-cart key="guid"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Menu Order", "e2pdf"), 'e2pdf-wc-cart key="menu_order"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Type", "e2pdf"), 'e2pdf-wc-cart key="post_type"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Mime Type", "e2pdf"), 'e2pdf-wc-cart key="post_mime_type"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Comments Count", "e2pdf"), 'e2pdf-wc-cart key="comment_count"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Filter", "e2pdf"), 'e2pdf-wc-cart key="filter"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Post Thumbnail", "e2pdf"), 'e2pdf-wc-cart key="get_the_post_thumbnail"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Post Thumbnail URL", "e2pdf"), 'e2pdf-wc-cart key="get_the_post_thumbnail_url"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Permalink", "e2pdf"), 'e2pdf-wc-cart key="permalink"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Post Permalink", "e2pdf"), 'e2pdf-wc-cart key="get_post_permalink"')}</div>";
            $vc .= "</div>";

            $vc .= "<h3>" . __('Cart Special Shortcodes', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart Products", "e2pdf"), 'e2pdf-foreach shortcode="e2pdf-wc-cart" key="get_cart"][e2pdf-wc-product key="get_name" index="[e2pdf-foreach-index]" order="true" wc_filter="true"]' . "\r\n" . '[/e2pdf-foreach')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart Totals", "e2pdf"), 'e2pdf-foreach shortcode="e2pdf-wc-cart" key="get_formatted_cart_totals"][e2pdf-wc-cart key="get_formatted_cart_totals" path="[e2pdf-foreach-key].label"]:[e2pdf-wc-cart key="get_formatted_cart_totals" path="[e2pdf-foreach-key].value"]' . "\r\n" . '[/e2pdf-foreach')}</div>";
            $vc .= "</div>";

            if (function_exists('wc_get_page_id')) {
                $meta_keys = $this->get_post_meta_keys(false, wc_get_page_id('cart'));
                if (!empty($meta_keys)) {
                    $vc .= "<h3>" . __('Cart Meta Keys', 'e2pdf') . "</h3>";
                    $vc .= "<div class='e2pdf-grid'>";
                    $i = 0;
                    foreach ($meta_keys as $meta_key) {
                        $pr = $i % 2 ? 'e2pdf-pl10' : 'e2pdf-pr10';
                        $vc .= "<div class='e2pdf-ib e2pdf-w50 {$pr}'>{$this->get_vm_element($meta_key, 'e2pdf-wc-cart key="' . $meta_key . '" meta="true"')}</div>";
                        $i++;
                    }
                    $vc .= "</div>";
                }
            }
            $vc .= $this->get_vm_product_order();
            $vc .= $this->get_vm_product();

            $vc .= "<h3>" . __('Customer', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Taxable Address", "e2pdf"), 'e2pdf-wc-customer key="get_taxable_address"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Is Vat Exempt", "e2pdf"), 'e2pdf-wc-customer key="is_vat_exempt"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Is Vat Exempt", "e2pdf"), 'e2pdf-wc-customer key="get_is_vat_exempt"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Has Calculated Shipping", "e2pdf"), 'e2pdf-wc-customer key="has_calculated_shipping"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Calculated Shipping", "e2pdf"), 'e2pdf-wc-customer key="get_calculated_shipping"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Vatar URL", "e2pdf"), 'e2pdf-wc-customer key="get_avatar_url"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Username", "e2pdf"), 'e2pdf-wc-customer key="get_username"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Email", "e2pdf"), 'e2pdf-wc-customer key="get_email"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("First Name", "e2pdf"), 'e2pdf-wc-customer key="get_first_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Last Name", "e2pdf"), 'e2pdf-wc-customer key="get_last_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Display Name", "e2pdf"), 'e2pdf-wc-customer key="get_display_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Role", "e2pdf"), 'e2pdf-wc-customer key="get_role"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Date Created", "e2pdf"), 'e2pdf-wc-customer key="get_date_created"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Date Modified", "e2pdf"), 'e2pdf-wc-customer key="get_date_modified"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing", "e2pdf"), 'e2pdf-wc-customer key="get_billing"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing First Name", "e2pdf"), 'e2pdf-wc-customer key="get_billing_first_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing Last Name", "e2pdf"), 'e2pdf-wc-customer key="get_billing_last_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing Company", "e2pdf"), 'e2pdf-wc-customer key="get_billing_company"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing Address", "e2pdf"), 'e2pdf-wc-customer key="get_billing_address"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing Address 1", "e2pdf"), 'e2pdf-wc-customer key="get_billing_address_1"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing Address 2", "e2pdf"), 'e2pdf-wc-customer key="get_billing_address_2"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing City", "e2pdf"), 'e2pdf-wc-customer key="get_billing_city"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing State", "e2pdf"), 'e2pdf-wc-customer key="get_billing_state"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing Postcode", "e2pdf"), 'e2pdf-wc-customer key="get_billing_postcode"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing Email", "e2pdf"), 'e2pdf-wc-customer key="get_billing_email"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing Phone", "e2pdf"), 'e2pdf-wc-customer key="get_billing_phone"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping", "e2pdf"), 'e2pdf-wc-customer key="get_shipping"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping First Name", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_first_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Last Name", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_last_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Company", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_company"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Address", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_address"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Address 1", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_address_1"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Address 2", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_address_2"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping City", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_city"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping State", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_state"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Postcode", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_postcode"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Country", "e2pdf"), 'e2pdf-wc-customer key="get_shipping_country"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Is Paying Customer", "e2pdf"), 'e2pdf-wc-customer key="get_is_paying_customer"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Formatted Shipping Address", "e2pdf"), 'e2pdf-wc-customer key="get_formatted_shipping_address"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Formatted Billing Address", "e2pdf"), 'e2pdf-wc-customer key="get_formatted_billing_address"')}</div>";
            $vc .= "</div>";
        }

        if ($this->get('item') == 'shop_order') {

            $vc .= $this->get_vm_order();
            $vc .= "<h3>" . __('Order Common', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("ID", "e2pdf"), 'e2pdf-wc-order key="id"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Author", "e2pdf"), 'e2pdf-wc-order key="post_author"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Date", "e2pdf"), 'e2pdf-wc-order key="post_date"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Date (GMT)", "e2pdf"), 'e2pdf-wc-order key="post_date_gmt"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Content", "e2pdf"), 'e2pdf-wc-order key="post_content"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Title", "e2pdf"), 'e2pdf-wc-order key="post_title"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Excerpt", "e2pdf"), 'e2pdf-wc-order key="post_excerpt"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Status", "e2pdf"), 'e2pdf-wc-order key="post_status"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Comment Status", "e2pdf"), 'e2pdf-wc-order key="comment_status"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Ping Status", "e2pdf"), 'e2pdf-wc-order key="ping_status"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Password", "e2pdf"), 'e2pdf-wc-order key="post_password"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Name", "e2pdf"), 'e2pdf-wc-order key="post_name"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("To Ping", "e2pdf"), 'e2pdf-wc-order key="to_ping"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Ping", "e2pdf"), 'e2pdf-wc-order key="pinged"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Modified Date", "e2pdf"), 'e2pdf-wc-order key="post_modified"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Modified Date (GMT)", "e2pdf"), 'e2pdf-wc-order key="post_modified_gmt"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Filtered Content", "e2pdf"), 'e2pdf-wc-order key="post_content_filtered"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Parent ID", "e2pdf"), 'e2pdf-wc-order key="post_parent"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("GUID", "e2pdf"), 'e2pdf-wc-order key="guid"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Menu Order", "e2pdf"), 'e2pdf-wc-order key="menu_order"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Type", "e2pdf"), 'e2pdf-wc-order key="post_type"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Mime Type", "e2pdf"), 'e2pdf-wc-order key="post_mime_type"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Comments Count", "e2pdf"), 'e2pdf-wc-order key="comment_count"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Filter", "e2pdf"), 'e2pdf-wc-order key="filter"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Post Thumbnail", "e2pdf"), 'e2pdf-wc-order key="get_the_post_thumbnail"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Post Thumbnail URL", "e2pdf"), 'e2pdf-wc-order key="get_the_post_thumbnail_url"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Permalink", "e2pdf"), 'e2pdf-wc-order key="permalink"')}</div>";
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Post Permalink", "e2pdf"), 'e2pdf-wc-order key="get_post_permalink"')}</div>";
            $vc .= "</div>";

            $vc .= $this->get_vm_product_order();
            $vc .= $this->get_vm_product();
        }

        if ($this->get('item') == 'product' || $this->get('item') == 'product_variation') {

            $vc .= $this->get_vm_product();
            $vc .= $this->get_vm_product_order();
            $vc .= $this->get_vm_product_order_item_meta();
            $vc .= $this->get_vm_order();
        }

        return $vc;
    }

    private function get_item_type_keys_subkeys($item_type = false) {
        global $wpdb;

        $meta_data = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT `meta`.`meta_key`, `item`.`order_item_type` FROM " . $wpdb->prefix . "woocommerce_order_itemmeta `meta` LEFT JOIN " . $wpdb->prefix . "woocommerce_order_items `item` ON (`meta`.`order_item_id` = `item`.`order_item_id`)"), ARRAY_A);

        $meta_keys = array();
        if ($meta_data) {
            foreach ($meta_data as $key => $meta) {
                if ($item_type) {
                    if ($meta['order_item_type'] == $item_type) {
                        $meta_keys[] = $meta['meta_key'];
                    }
                } else {
                    $field_key = array_search($meta['order_item_type'], array_column($meta_keys, 'meta_key'));
                    if ($field_key === false) {
                        $meta_keys[] = array(
                            'meta_key' => $meta['order_item_type'],
                            'sub_keys' => array(
                                $meta['meta_key']
                            )
                        );
                    } else {
                        $meta_keys[$field_key]['sub_keys'][] = $meta['meta_key'];
                    }
                }
            }
        }

        return $meta_keys;
    }

    private function get_post_taxonomy_keys() {
        global $wpdb;

        $meta_keys = array();
        if ($this->get('item')) {
            $order_condition = array(
                'orderby' => 'taxonomy',
                'order' => 'desc',
            );
            $orderby = $this->helper->load('db')->prepare_orderby($order_condition);
            $meta_keys = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT `taxonomy` FROM " . $wpdb->term_taxonomy . " `t` " . $orderby . "", ""));
        }

        return $meta_keys;
    }

    private function get_post_meta_keys($item_key = false, $post_id = false) {
        global $wpdb;

        if (!$item_key) {
            $item_key = $this->get('item');
        }

        $meta_keys = array();
        if ($item_key || $post_id) {

            if ($post_id) {
                $condition = array(
                    'p.ID' => array(
                        'condition' => '=',
                        'value' => $post_id,
                        'type' => '%d'
                    ),
                );
            } else {
                $condition = array(
                    'p.post_type' => array(
                        'condition' => '=',
                        'value' => $item_key,
                        'type' => '%s'
                    ),
                );
            }

            $order_condition = array(
                'orderby' => 'meta_key',
                'order' => 'desc',
            );

            $where = $this->helper->load('db')->prepare_where($condition);
            $orderby = $this->helper->load('db')->prepare_orderby($order_condition);

            $meta_keys = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT `meta_key` FROM " . $wpdb->postmeta . " `pm` LEFT JOIN " . $wpdb->posts . " `p` ON (`p`.`ID` = `pm`.`post_ID`) " . $where['sql'] . $orderby . "", $where['filter']));
        }

        return $meta_keys;
    }

    private function get_product_attribute_keys() {
        global $wpdb;

        $meta_keys = array();
        $attributes = wc_get_attribute_taxonomies();
        foreach ($attributes as $attribute) {
            $meta_keys[] = array(
                'id' => 'pa_' . $attribute->attribute_name,
                'name' => $attribute->attribute_label
            );
        }

        $condition = array(
            'pm.meta_key' => array(
                'condition' => '=',
                'value' => '_product_attributes',
                'type' => '%s'
            ),
        );

        $order_condition = array(
            'orderby' => 'pm.meta_key',
            'order' => 'desc',
        );

        $where = $this->helper->load('db')->prepare_where($condition);
        $orderby = $this->helper->load('db')->prepare_orderby($order_condition);

        $custom_meta_keys = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT `meta_value` FROM " . $wpdb->postmeta . " `pm`" . $where['sql'] . $orderby . "", $where['filter']));
        foreach ($custom_meta_keys as $custom_meta_key) {
            $custom_metas = @unserialize($custom_meta_key);
            if ($custom_metas && is_array($custom_metas)) {
                foreach ($custom_metas as $custom_metas_key => $custom_metas_value) {
                    if (isset($custom_metas_value['is_taxonomy']) && $custom_metas_value['is_taxonomy']) {
                        
                    } else {
                        $in_array = array_search($custom_metas_key, array_column($meta_keys, 'id'));
                        if ($in_array === false && isset($custom_metas_value['name'])) {
                            $meta_keys[] = array(
                                'id' => $custom_metas_key,
                                'name' => $custom_metas_value['name']
                            );
                        }
                    }
                }
            }
        }

        return $meta_keys;
    }

    private function get_vm_element($name, $id) {
        $element = "<div>";
        $element .= "<label>{$name}:</label>";
        $element .= "<input type='text' name='[{$id}]' value='[{$id}]' class='e2pdf-w100'>";
        $element .= "</div>";
        return $element;
    }

    private function get_vm_product() {

        $index = '';
        if ($this->get('item') == 'shop_order' || $this->get('item') == 'cart') {
            $index = ' index="0"';
        }

        $vc = '';

        $vc .= "<h3>" . __('Product', 'e2pdf') . "</h3>";
        $vc .= "<div class='e2pdf-grid'>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Name", "e2pdf"), 'e2pdf-wc-product key="get_name"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Type", "e2pdf"), 'e2pdf-wc-product key="get_type"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Slug", "e2pdf"), 'e2pdf-wc-product key="get_slug"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Date Created", "e2pdf"), 'e2pdf-wc-product key="get_date_created"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Date Modified", "e2pdf"), 'e2pdf-wc-product key="get_date_modified"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Status", "e2pdf"), 'e2pdf-wc-product key="get_status"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Featured", "e2pdf"), 'e2pdf-wc-product key="get_featured"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Catalog Visibility", "e2pdf"), 'e2pdf-wc-product key="get_catalog_visibility"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Description", "e2pdf"), 'e2pdf-wc-product key="get_description" wc_format_content="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Short Description", "e2pdf"), 'e2pdf-wc-product key="get_short_description" wc_format_content="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Sku", "e2pdf"), 'e2pdf-wc-product key="get_sku"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Price", "e2pdf"), 'e2pdf-wc-product key="get_price"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Regular Price", "e2pdf"), 'e2pdf-wc-product key="get_regular_price"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Sale Price", "e2pdf"), 'e2pdf-wc-product key="get_sale_price"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Date Sale From", "e2pdf"), 'e2pdf-wc-product key="get_date_on_sale_from"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Date Sale To", "e2pdf"), 'e2pdf-wc-product key="get_date_on_sale_to"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Total Sales", "e2pdf"), 'e2pdf-wc-product key="get_total_sales"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Tax Status", "e2pdf"), 'e2pdf-wc-product key="get_tax_status"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Tax Class", "e2pdf"), 'e2pdf-wc-product key="get_tax_class"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Manage Stock", "e2pdf"), 'e2pdf-wc-product key="get_manage_stock"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Stock Quantity", "e2pdf"), 'e2pdf-wc-product key="get_stock_quantity"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Stock Status", "e2pdf"), 'e2pdf-wc-product key="get_stock_status"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Backorders", "e2pdf"), 'e2pdf-wc-product key="get_backorders"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Low Stock Amount", "e2pdf"), 'e2pdf-wc-product key="get_low_stock_amount"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Sold Individually", "e2pdf"), 'e2pdf-wc-product key="get_sold_individually"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Weight", "e2pdf"), 'e2pdf-wc-product key="get_weight"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Length", "e2pdf"), 'e2pdf-wc-product key="get_length"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Width", "e2pdf"), 'e2pdf-wc-product key="get_width"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Height", "e2pdf"), 'e2pdf-wc-product key="get_height"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Dimensions", "e2pdf"), 'e2pdf-wc-product key="get_dimensions"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Upsell IDs", "e2pdf"), 'e2pdf-wc-product key="get_upsell_ids"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cross Sell IDs", "e2pdf"), 'e2pdf-wc-product key="get_cross_sell_ids"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Parent ID", "e2pdf"), 'e2pdf-wc-product key="get_parent_id"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Reviews Allowed", "e2pdf"), 'e2pdf-wc-product key="get_reviews_allowed"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Purchase Note", "e2pdf"), 'e2pdf-wc-product key="get_purchase_note"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Attributes", "e2pdf"), 'e2pdf-wc-product key="get_attributes"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Default Attributes", "e2pdf"), 'e2pdf-wc-product key="get_default_attributes"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Menu Order", "e2pdf"), 'e2pdf-wc-product key="get_menu_order"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Password", "e2pdf"), 'e2pdf-wc-product key="get_post_password"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Category IDs", "e2pdf"), 'e2pdf-wc-product key="get_category_ids"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Tag IDs", "e2pdf"), 'e2pdf-wc-product key="get_tag_ids"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Virtual", "e2pdf"), 'e2pdf-wc-product key="get_virtual"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Gallery Image Ids", "e2pdf"), 'e2pdf-wc-product key="get_gallery_image_ids"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Class ID", "e2pdf"), 'e2pdf-wc-product key="get_shipping_class_id"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Downloads", "e2pdf"), 'e2pdf-wc-product key="get_downloads"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Download Expiry", "e2pdf"), 'e2pdf-wc-product key="get_download_expiry"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Downloadable", "e2pdf"), 'e2pdf-wc-product key="get_downloadable"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Download Limit", "e2pdf"), 'e2pdf-wc-product key="get_download_limit"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Image ID", "e2pdf"), 'e2pdf-wc-product key="get_image_id"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Rating Counts", "e2pdf"), 'e2pdf-wc-product key="get_rating_counts"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Average Rating", "e2pdf"), 'e2pdf-wc-product key="get_average_rating"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Review Count", "e2pdf"), 'e2pdf-wc-product key="get_review_count"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Title", "e2pdf"), 'e2pdf-wc-product key="get_title"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Product Permalink", "e2pdf"), 'e2pdf-wc-product key="get_permalink"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Children", "e2pdf"), 'e2pdf-wc-product key="get_children"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Stock Managed ID", "e2pdf"), 'e2pdf-wc-product key="get_stock_managed_by_id"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Price (HTML)", "e2pdf"), 'e2pdf-wc-product key="get_price_html"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Formatted Name", "e2pdf"), 'e2pdf-wc-product key="get_formatted_name"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Min Purchase Quantity", "e2pdf"), 'e2pdf-wc-product key="get_min_purchase_quantity"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Max Purchase Quantity", "e2pdf"), 'e2pdf-wc-product key="get_max_purchase_quantity"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Image", "e2pdf"), 'e2pdf-wc-product key="get_image"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Class", "e2pdf"), 'e2pdf-wc-product key="get_shipping_class"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Rating Count", "e2pdf"), 'e2pdf-wc-product key="get_rating_count"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("File", "e2pdf"), 'e2pdf-wc-product key="get_file"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("File Download Path", "e2pdf"), 'e2pdf-wc-product key="get_file_download_path"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Price Suffix", "e2pdf"), 'e2pdf-wc-product key="get_price_suffix"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Availability", "e2pdf"), 'e2pdf-wc-product key="get_availability"' . $index)}</div>";
        if ($this->get('item') == 'product_variation') {
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Product Variation Attributes", "e2pdf"), 'e2pdf-wc-product key="get_variation_attributes"' . $index)}</div>";
        }
        $vc .= "</div>";

        $vc .= "<h3>" . __('Product Common', 'e2pdf') . "</h3>";
        $vc .= "<div class='e2pdf-grid'>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("ID", "e2pdf"), 'e2pdf-wc-product key="id"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Author", "e2pdf"), 'e2pdf-wc-product key="post_author"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Date", "e2pdf"), 'e2pdf-wc-product key="post_date"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Date (GMT)", "e2pdf"), 'e2pdf-wc-product key="post_date_gmt"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Content", "e2pdf"), 'e2pdf-wc-product key="post_content"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Title", "e2pdf"), 'e2pdf-wc-product key="post_title"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Excerpt", "e2pdf"), 'e2pdf-wc-product key="post_excerpt"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Status", "e2pdf"), 'e2pdf-wc-product key="post_status"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Comment Status", "e2pdf"), 'e2pdf-wc-product key="comment_status"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Ping Status", "e2pdf"), 'e2pdf-wc-product key="ping_status"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Password", "e2pdf"), 'e2pdf-wc-product key="post_password"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Name", "e2pdf"), 'e2pdf-wc-product key="post_name"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("To Ping", "e2pdf"), 'e2pdf-wc-product key="to_ping"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Ping", "e2pdf"), 'e2pdf-wc-product key="pinged"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Modified Date", "e2pdf"), 'e2pdf-wc-product key="post_modified"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Modified Date (GMT)", "e2pdf"), 'e2pdf-wc-product key="post_modified_gmt"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Filtered Content", "e2pdf"), 'e2pdf-wc-product key="post_content_filtered"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Parent ID", "e2pdf"), 'e2pdf-wc-product key="post_parent"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("GUID", "e2pdf"), 'e2pdf-wc-product key="guid"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Menu Order", "e2pdf"), 'e2pdf-wc-product key="menu_order"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Type", "e2pdf"), 'e2pdf-wc-product key="post_type"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Mime Type", "e2pdf"), 'e2pdf-wc-product key="post_mime_type"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Comments Count", "e2pdf"), 'e2pdf-wc-product key="comment_count"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Filter", "e2pdf"), 'e2pdf-wc-product key="filter"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Post Thumbnail", "e2pdf"), 'e2pdf-wc-product key="get_the_post_thumbnail"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Post Thumbnail URL", "e2pdf"), 'e2pdf-wc-product key="get_the_post_thumbnail_url"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Permalink", "e2pdf"), 'e2pdf-wc-product key="permalink"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Post Permalink", "e2pdf"), 'e2pdf-wc-product key="get_post_permalink"')}</div>";
        $vc .= "</div>";

        $meta_keys = $this->get_product_attribute_keys();
        $vc .= "<h3>" . __('Product Attributes', 'e2pdf') . "</h3>";
        $vc .= "<div class='e2pdf-grid'>";
        $i = 0;
        foreach ($meta_keys as $meta_key) {
            $pr = $i % 2 ? 'e2pdf-pl10' : 'e2pdf-pr10';
            $vc .= "<div class='e2pdf-ib e2pdf-w50 {$pr}'>{$this->get_vm_element($meta_key['name'], 'e2pdf-wc-product key="get_attribute" attribute="' . $meta_key['id'] . '" show="value"' . $index)}</div>";

            $i++;
        }
        $vc .= "</div>";

        $vc .= "<h3>" . __('Product Special Shortcodes', 'e2pdf') . "</h3>";
        $vc .= "<div class='e2pdf-grid'>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Product Attributes", "e2pdf"), 'e2pdf-foreach shortcode="e2pdf-wc-product" key="get_attributes"' . $index . ' wc_filter="true"][e2pdf-wc-product key="get_attributes" path="[e2pdf-foreach-key].label" wc_filter="true"' . $index . ']: [e2pdf-wc-product key="get_attributes" path="[e2pdf-foreach-key].value" wc_filter="true"' . $index . ']' . "\r\n" . '[/e2pdf-foreach')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Formatted Meta Data", "e2pdf"), 'e2pdf-foreach shortcode="e2pdf-wc-product" key="get_formatted_meta_data"' . $index . '][e2pdf-wc-product key="get_formatted_meta_data" path="[e2pdf-foreach-key].display_key"' . $index . ']:[e2pdf-wc-product key="get_formatted_meta_data" path="[e2pdf-foreach-key].display_value"' . $index . ']' . "\r\n" . '[/e2pdf-foreach')}</div>";
        if ($this->get('item') == 'product_variation') {
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Product Variation Attributes", "e2pdf"), 'e2pdf-foreach shortcode="e2pdf-wc-product" key="get_variation_attributes"' . $index . ' wc_filter="true"][e2pdf-wc-product key="get_variation_attributes" path="[e2pdf-foreach-key].label" wc_filter="true"' . $index . ']: [e2pdf-wc-product key="get_variation_attributes" path="[e2pdf-foreach-key].value" wc_filter="true"' . $index . ']' . "\r\n" . '[/e2pdf-foreach')}</div>";
        }

        $vc .= "</div>";

        $meta_keys = $this->get_post_meta_keys('product');
        $meta_keys2 = $this->get_post_meta_keys('product_variation');

        if (!empty($meta_keys2)) {
            foreach ($meta_keys2 as $meta_key) {
                if (!in_array($meta_key, $meta_keys)) {
                    $meta_keys[] = $meta_key;
                }
            }
        }

        if (!empty($meta_keys)) {
            $vc .= "<h3>" . __('Product Meta Keys', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $i = 0;
            foreach ($meta_keys as $meta_key) {
                $pr = $i % 2 ? 'e2pdf-pl10' : 'e2pdf-pr10';
                $vc .= "<div class='e2pdf-ib e2pdf-w50 {$pr}'>{$this->get_vm_element($meta_key, 'e2pdf-wc-product key="' . $meta_key . '" meta="true"' . $index)}</div>";
                $i++;
            }
            $vc .= "</div>";
        }

        $meta_keys = $this->get_post_taxonomy_keys();
        if (!empty($meta_keys)) {
            $vc .= "<h3>" . __('Product Taxonomy', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $i = 0;
            foreach ($meta_keys as $meta_key) {
                $pr = $i % 2 ? 'e2pdf-pl10' : 'e2pdf-pr10';
                $vc .= "<div class='e2pdf-ib e2pdf-w50 {$pr}'>{$this->get_vm_element($meta_key, 'e2pdf-wc-product key="' . $meta_key . '" terms="true"' . $index)}</div>";
                $i++;
            }
            $vc .= "</div>";
        }

        return $vc;
    }

    private function get_vm_product_order() {
        $index = '';
        if ($this->get('item') == 'shop_order' || $this->get('item') == 'cart') {
            $index = ' index="0"';
        }

        $vc = '';

        if ($this->get('item') == 'cart') {
            $vc .= "<h3>" . __('Cart Product', 'e2pdf') . "</h3>";
        } else {
            $vc .= "<h3>" . __('Order Product', 'e2pdf') . "</h3>";
        }
        $vc .= "<div class='e2pdf-grid'>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Name", "e2pdf"), 'e2pdf-wc-product key="get_name" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Type", "e2pdf"), 'e2pdf-wc-product key="get_type" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Quantity", "e2pdf"), 'e2pdf-wc-product key="get_quantity" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Tax Status", "e2pdf"), 'e2pdf-wc-product key="get_tax_status" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Tax Class", "e2pdf"), 'e2pdf-wc-product key="get_tax_class" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Formatted Meta Data", "e2pdf"), 'e2pdf-wc-product key="get_formatted_meta_data" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Product ID", "e2pdf"), 'e2pdf-wc-product key="get_product_id" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Variation ID", "e2pdf"), 'e2pdf-wc-product key="get_variation_id" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Subtotal", "e2pdf"), 'e2pdf-wc-product key="get_subtotal" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Subtotal Tax", "e2pdf"), 'e2pdf-wc-product key="get_subtotal_tax" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Total", "e2pdf"), 'e2pdf-wc-product key="get_total" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Total Tax", "e2pdf"), 'e2pdf-wc-product key="get_total_tax" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Taxes", "e2pdf"), 'e2pdf-wc-product key="get_taxes" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Item Download URL", "e2pdf"), 'e2pdf-wc-product key="get_item_download_url" order="true" index="0" download_order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Downloads", "e2pdf"), 'e2pdf-wc-product key="get_item_downloads" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Tax Status", "e2pdf"), 'e2pdf-wc-product key="get_tax_status" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Item Subtotal", "e2pdf"), 'e2pdf-wc-product key="get_item_subtotal" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Item Total", "e2pdf"), 'e2pdf-wc-product key="get_item_total" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Item Tax", "e2pdf"), 'e2pdf-wc-product key="get_item_tax" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Line Total", "e2pdf"), 'e2pdf-wc-product key="get_line_total" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Line Tax", "e2pdf"), 'e2pdf-wc-product key="get_line_tax" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Formatted Line Subtotal", "e2pdf"), 'e2pdf-wc-product key="get_formatted_line_subtotal" order="true"' . $index)}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Order Response Hook", "e2pdf"), 'e2pdf-wc-product key="order_response_hook" order="true"' . $index)}</div>";

        if ($this->get('item') == 'cart') {
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Item Cart Response Hook", "e2pdf"), 'e2pdf-wc-product key="item_cart_response_hook"' . $index)}</div>";
        } else {
            $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Item Response Hook", "e2pdf"), 'e2pdf-wc-product key="item_response_hook" order="true"' . $index)}</div>";
        }

        $vc .= "</div>";

        return $vc;
    }

    private function get_vm_product_order_item_meta() {

        $vc = '';
        $meta_keys = $this->get_item_type_keys_subkeys('line_item');
        if (!empty($meta_keys)) {
            $vc .= "<h3>" . __('Order Product Item Meta Keys', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $i = 0;
            foreach ($meta_keys as $meta_key) {
                $pr = $i % 2 ? 'e2pdf-pl10' : 'e2pdf-pr10';
                $vc .= "<div class='e2pdf-ib e2pdf-w50 {$pr}'>{$this->get_vm_element($meta_key, 'e2pdf-wc-product key="' . $meta_key . '" order_item_meta="true"')}</div>";
                $i++;
            }
            $vc .= "</div>";
        }

        return $vc;
    }

    private function get_vm_order() {

        $vc = '';
        $vc .= "<h3>" . __('Order', 'e2pdf') . "</h3>";
        $vc .= "<div class='e2pdf-grid'>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Order ID", "e2pdf"), 'e2pdf-wc-order key="get_id"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Order Key", "e2pdf"), 'e2pdf-wc-order key="get_order_key"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Order Number", "e2pdf"), 'e2pdf-wc-order key="get_order_number"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Formatted Order Total", "e2pdf"), 'e2pdf-wc-order key="get_formatted_order_total"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cart Tax", "e2pdf"), 'e2pdf-wc-order key="get_cart_tax"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Currency", "e2pdf"), 'e2pdf-wc-order key="get_currency"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Discount Tax", "e2pdf"), 'e2pdf-wc-order key="get_discount_tax"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Discount to Display", "e2pdf"), 'e2pdf-wc-order key="get_discount_to_display"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Discount Total", "e2pdf"), 'e2pdf-wc-order key="get_discount_total"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Tax", "e2pdf"), 'e2pdf-wc-order key="get_shipping_tax"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Total", "e2pdf"), 'e2pdf-wc-order key="get_shipping_total"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Subtotal", "e2pdf"), 'e2pdf-wc-order key="get_subtotal"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Subtotal to Display", "e2pdf"), 'e2pdf-wc-order key="get_subtotal_to_display"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Get Total", "e2pdf"), 'e2pdf-wc-order key="get_total"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Total Discount", "e2pdf"), 'e2pdf-wc-order key="get_total_discount"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Total Tax", "e2pdf"), 'e2pdf-wc-order key="get_total_tax"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Total Refunded", "e2pdf"), 'e2pdf-wc-order key="get_total_refunded"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Total Tax Refunded", "e2pdf"), 'e2pdf-wc-order key="get_total_tax_refunded"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Total Shipping Refunded", "e2pdf"), 'e2pdf-wc-order key="get_total_shipping_refunded"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Item Count Refunded", "e2pdf"), 'e2pdf-wc-order key="get_item_count_refunded"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Total QTY Refunded", "e2pdf"), 'e2pdf-wc-order key="get_total_qty_refunded"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Remaining Refund Amount", "e2pdf"), 'e2pdf-wc-order key="get_remaining_refund_amount"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Item Count", "e2pdf"), 'e2pdf-wc-order key="get_item_count"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Method", "e2pdf"), 'e2pdf-wc-order key="get_shipping_method"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping to Display", "e2pdf"), 'e2pdf-wc-order key="get_shipping_to_display"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Date Created", "e2pdf"), 'e2pdf-wc-order key="get_date_created"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Date Modified", "e2pdf"), 'e2pdf-wc-order key="get_date_modified"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Date Completed", "e2pdf"), 'e2pdf-wc-order key="get_date_completed"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Date Paid", "e2pdf"), 'e2pdf-wc-order key="get_date_paid"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Custom ID", "e2pdf"), 'e2pdf-wc-order key="get_customer_id"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("User ID", "e2pdf"), 'e2pdf-wc-order key="get_user_id"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Customer IP Address", "e2pdf"), 'e2pdf-wc-order key="get_customer_ip_address"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Customer User Agent", "e2pdf"), 'e2pdf-wc-order key="get_customer_user_agent"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Created Via", "e2pdf"), 'e2pdf-wc-order key="get_created_via"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Customer Note", "e2pdf"), 'e2pdf-wc-order key="get_customer_note"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing First Name", "e2pdf"), 'e2pdf-wc-order key="get_billing_first_name"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing Last Name", "e2pdf"), 'e2pdf-wc-order key="get_billing_last_name"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing Company", "e2pdf"), 'e2pdf-wc-order key="get_billing_company"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing Address 1", "e2pdf"), 'e2pdf-wc-order key="get_billing_address_1"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing Address 2", "e2pdf"), 'e2pdf-wc-order key="get_billing_address_2"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing City", "e2pdf"), 'e2pdf-wc-order key="get_billing_city"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing State", "e2pdf"), 'e2pdf-wc-order key="get_billing_state"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing Postcode", "e2pdf"), 'e2pdf-wc-order key="get_billing_postcode"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing Country", "e2pdf"), 'e2pdf-wc-order key="get_billing_country"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Billing Email", "e2pdf"), 'e2pdf-wc-order key="get_billing_email"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Billing Phone", "e2pdf"), 'e2pdf-wc-order key="get_billing_phone"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shopping First Name", "e2pdf"), 'e2pdf-wc-order key="get_shipping_first_name"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Last Name", "e2pdf"), 'e2pdf-wc-order key="get_shipping_last_name"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Company", "e2pdf"), 'e2pdf-wc-order key="get_shipping_company"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Address 1", "e2pdf"), 'e2pdf-wc-order key="get_shipping_address_1"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Address 2", "e2pdf"), 'e2pdf-wc-order key="get_shipping_address_2"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping City", "e2pdf"), 'e2pdf-wc-order key="get_shipping_city"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping State", "e2pdf"), 'e2pdf-wc-order key="get_shipping_state"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Postcode", "e2pdf"), 'e2pdf-wc-order key="get_shipping_postcode"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Country", "e2pdf"), 'e2pdf-wc-order key="get_shipping_country"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Shipping Address Map URL", "e2pdf"), 'e2pdf-wc-order key="get_shipping_address_map_url"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Formatted Billing Full Name", "e2pdf"), 'e2pdf-wc-order key="get_formatted_billing_full_name"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Formatted Shipping Full Name", "e2pdf"), 'e2pdf-wc-order key="get_formatted_shipping_full_name"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Formatted Billing Address", "e2pdf"), 'e2pdf-wc-order key="get_formatted_billing_address"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Formatted Shipping Address", "e2pdf"), 'e2pdf-wc-order key="get_formatted_shipping_address"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Payment Method", "e2pdf"), 'e2pdf-wc-order key="get_payment_method"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Payment Method Title", "e2pdf"), 'e2pdf-wc-order key="get_payment_method_title"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Transaction ID", "e2pdf"), 'e2pdf-wc-order key="get_transaction_id"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Checkout Payment URL", "e2pdf"), 'e2pdf-wc-order key="get_checkout_payment_url"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Checkout Order Received URL", "e2pdf"), 'e2pdf-wc-order key="get_checkout_order_received_url"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cancel Order URL", "e2pdf"), 'e2pdf-wc-order key="get_cancel_order_url"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Cancel Order URL (raw)", "e2pdf"), 'e2pdf-wc-order key="get_cancel_order_url_raw"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cancel Endpoint", "e2pdf"), 'e2pdf-wc-order key="get_cancel_endpoint"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("View Order URL", "e2pdf"), 'e2pdf-wc-order key="get_view_order_url"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Edit Order URL", "e2pdf"), 'e2pdf-wc-order key="get_edit_order_url"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Status", "e2pdf"), 'e2pdf-wc-order key="get_status"')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Cart", "e2pdf"), 'e2pdf-wc-order key="cart"')}</div>";
        $vc .= "</div>";

        $vc .= "<h3>" . __('Order Special Shortcodes', 'e2pdf') . "</h3>";
        $vc .= "<div class='e2pdf-grid'>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Order Products", "e2pdf"), 'e2pdf-foreach shortcode="e2pdf-wc-order" key="get_items"][e2pdf-wc-product key="get_name" order="true" index="[e2pdf-foreach-index]"]' . "\r\n" . '[/e2pdf-foreach')}</div>";
        $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pl10'>{$this->get_vm_element(__("Order Totals", "e2pdf"), 'e2pdf-foreach shortcode="e2pdf-wc-order" key="get_order_item_totals"][e2pdf-wc-order key="get_order_item_totals" path="[e2pdf-foreach-key].label"][e2pdf-wc-order key="get_order_item_totals" path="[e2pdf-foreach-key].value"]' . "\r\n" . '[/e2pdf-foreach')}</div>";

        /* Iteration Shortcodes */
        /*
          $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Taxes", "e2pdf"), 'e2pdf-wc-order key="get_taxes"')}</div>";
          $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Taxes Total", "e2pdf"), 'e2pdf-wc-order key="get_tax_totals"')}</div>";
          $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Taxes Classes", "e2pdf"), 'e2pdf-wc-order key="get_items_tax_classes"')}</div>";
          $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Coupons", "e2pdf"), 'e2pdf-wc-order key="get_coupons"')}</div>";
          $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Coupon Codes", "e2pdf"), 'e2pdf-wc-order key="get_coupon_codes"')}</div>";
          $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Fees", "e2pdf"), 'e2pdf-wc-order key="get_fees"')}</div>";
          $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Fees Total", "e2pdf"), 'e2pdf-wc-order key="get_total_fees"')}</div>";
          $vc .= "<div class='e2pdf-ib e2pdf-w50 e2pdf-pr10'>{$this->get_vm_element(__("Shipping Methods", "e2pdf"), 'e2pdf-wc-order key="get_shipping_methods"')}</div>";
         * 
         */

        $vc .= "</div>";

        $meta_keys = $this->get_post_meta_keys('shop_order');
        if (!empty($meta_keys)) {
            $vc .= "<h3>" . __('Order Meta Keys', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $i = 0;
            foreach ($meta_keys as $meta_key) {
                $pr = $i % 2 ? 'e2pdf-pl10' : 'e2pdf-pr10';
                $vc .= "<div class='e2pdf-ib e2pdf-w50 {$pr}'>{$this->get_vm_element($meta_key, 'e2pdf-wc-order key="' . $meta_key . '" meta="true"')}</div>";
                $i++;
            }
            $vc .= "</div>";
        }

        $meta_keys = $this->get_post_taxonomy_keys();
        if (!empty($meta_keys)) {
            $vc .= "<h3>" . __('Order Taxonomy', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";
            $i = 0;
            foreach ($meta_keys as $meta_key) {
                $pr = $i % 2 ? 'e2pdf-pl10' : 'e2pdf-pr10';
                $vc .= "<div class='e2pdf-ib e2pdf-w50 {$pr}'>{$this->get_vm_element($meta_key, 'e2pdf-wc-order key="' . $meta_key . '" terms="true"')}</div>";
                $i++;
            }
            $vc .= "</div>";
        }


        $meta_keys = $this->get_item_type_keys_subkeys();
        if (!empty($meta_keys)) {
            $vc .= "<h3>" . __('Order Item Meta Keys', 'e2pdf') . "</h3>";
            $vc .= "<div class='e2pdf-grid'>";

            $i = 0;
            foreach ($meta_keys as $meta_key) {
                foreach ($meta_key['sub_keys'] as $sub_key) {
                    $pr = $i % 2 ? 'e2pdf-pl10' : 'e2pdf-pr10';
                    $vc .= "<div class='e2pdf-ib e2pdf-w50 {$pr}'>{$this->get_vm_element($sub_key . ' (' . $meta_key['meta_key'] . ')', 'e2pdf-wc-order key="' . $meta_key['meta_key'] . '" subkey="' . $sub_key . '" index="0" order_item_meta="true"')}</div>";

                    $i++;
                }
            }
            $vc .= "</div>";
        }

        return $vc;
    }

}
