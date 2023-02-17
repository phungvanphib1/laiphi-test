<?php

/**
 * E2pdf Caldera Extension
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      0.01.47
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Extension_E2pdf_Caldera extends Model_E2pdf_Model {

    private $options;
    private $info = array(
        'key' => 'caldera',
        'title' => 'Caldera Forms'
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

        if (is_plugin_active('caldera-forms/caldera-core.php') || defined('E2PDF_CALDERA_EXTENSION')) {
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
        return true;
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
        if (class_exists("Caldera_Forms_Forms")) {
            $forms = Caldera_Forms_Forms::get_forms(true);
            if ($forms) {
                foreach ($forms as $key => $value) {
                    $content[] = $this->item($value['ID']);
                }
            }
        }
        return $content;
    }

    /**
     * Get entries for export
     * 
     * @param int $item - Form ID
     * @param string $name - Entries names
     * 
     * @return array() - Entries list
     */
    public function datasets($item = false, $name = false) {

        $datasets = array();

        if (class_exists("Caldera_Forms_Admin") && $item) {

            $form = Caldera_Forms_Admin::get_entries($item, 1, 9999999999);

            if ($form && isset($form['entries']) && is_array($form['entries'])) {

                foreach ($form['entries'] as $key => $dataset) {
                    $this->set('item', $item);
                    $this->set('dataset', $dataset['_entry_id']);

                    $dataset_title = $this->render($name);
                    if (!$dataset_title) {
                        $dataset_title = $dataset['_date'];
                    }
                    $datasets[] = array(
                        'key' => $dataset['_entry_id'],
                        'value' => $dataset_title
                    );
                }
            }
        }

        return $datasets;
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
            return false;
        }

        $data = new stdClass();
        $data->url = false;

        return $data;
    }

    /**
     * Get item
     * 
     * @param string $item - Item ID
     * 
     * @return object - Item
     */
    public function item($item = false) {

        $form = new stdClass();

        if (!$item && $this->get('item')) {
            $item = $this->get('item');
        }

        $caldera_form = false;
        if (class_exists('Caldera_Forms_Forms')) {
            $caldera_form = Caldera_Forms_Forms::get_form($item);
        }

        if ($caldera_form) {
            $form->id = (string) $caldera_form['ID'];
            $form->url = $this->helper->get_url(array('page' => 'caldera-forms', 'edit' => $caldera_form['ID']));
            $form->name = $caldera_form['name'];
        } else {
            $form->id = '';
            $form->url = 'javascript:void(0);';
            $form->name = '';
        }
        return $form;
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
        $user_id = $this->get('user_id');
        $args = $this->get('args');
        $template_id = $this->get('template_id') ? $this->get('template_id') : '0';
        $element_id = isset($field['element_id']) ? $field['element_id'] : '0';

        if ($this->verify()) {

            $args = apply_filters('e2pdf_extension_render_shortcodes_args', $args, $element_id, $template_id, $item, $dataset, false, false);

            if (false !== strpos($value, '[')) {

                $replace = array(
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
                                    'e2pdf_extension_caldera_render_shortcodes_site_url'
                                ))
                        );
                    }

                    $replace = array(
                        '[e2pdf-url]' => $pdf_url
                    );
                    $value = str_replace(array_keys($replace), $replace, $value);
                }

                $shortcode_tags = array(
                    'e2pdf-user',
                    'e2pdf-arg'
                );
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

                        $atts = shortcode_parse_atts($shortcode[3]);

                        if ($shortcode['2'] === 'e2pdf-user') {
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
                        } elseif ($shortcode['2'] === 'e2pdf-arg') {
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

            add_filter('caldera_forms_magic_form', array($this, 'filter_caldera_forms_magic_form'), 30, 2);
            add_filter('caldera_forms_render_get_field', array($this, 'filter_caldera_forms_render_get_field'), 30, 2);
            add_filter('caldera_forms_pre_do_field_magic', array($this, 'filter_caldera_forms_pre_do_field_magic'), 30, 5);
            add_filter('caldera_forms_get_field_entry', array($this, 'filter_caldera_forms_get_field_entry'), 30, 4);
            add_filter('caldera_forms_pre_check_condition', array($this, 'filter_caldera_forms_pre_check_condition'), 30, 4);

            global $form;
            $tmp_form = false;
            if ($form && isset($form['ID'])) {
                $tmp_form = $form;
                if ($form['ID'] != $this->get('item')) {
                    $form = Caldera_Forms_Forms::get_form($this->get('item'));
                }
                if (!empty($form['is_connected_form'])) {
                    $form['processors']['_connected_form'] = array(
                        'type' => 'form-connector',
                        'runtimes' => array(
                            'insert' => true
                        ),
                        'config' => array()
                    );

                    // setup fields
                    $form_fields = array();
                    // get meta
                    $meta = Caldera_Forms::get_entry_meta((int) $dataset, $form);

                    if (!empty($meta['form-connector']['data']['_connected_form']['entry']['form']['meta_value'])) {
                        foreach ((array) $meta['form-connector']['data']['_connected_form']['entry']['form']['meta_value'] as $form_meta) {
                            $form_fields = array_merge($form_fields, $form_meta);
                        }
                    }

                    if (!empty($form['condition_points']['forms'])) {
                        $form['fields'] = array();
                        foreach ($form['condition_points']['forms'] as $connected_id => $connected_form) {
                            if (!empty($form_fields[$connected_id]) && !empty($connected_form['fields'])) {
                                $form['fields'] = array_merge($form['fields'], $connected_form['fields']);
                            }
                        }

                        if (function_exists('cf_form_connector_setup_processors_meta')) {
                            add_filter('caldera_forms_get_entry_detail', 'cf_form_connector_setup_processors_meta', 10, 3);
                        }
                    }

                    if (isset($form['node']) && function_exists('cf_from_connector_merge_fields')) {
                        $form['fields'] = cf_from_connector_merge_fields($form);
                    }
                }
            }

            $value = Caldera_Forms::do_magic_tags($value, $dataset);

            if ($tmp_form) {
                $form = $tmp_form;
            }

            $value = apply_filters('e2pdf_extension_render_shortcodes_pre_value', $value, $element_id, $template_id, $item, $dataset, false, false);

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

            remove_filter('caldera_forms_magic_form', array($this, 'filter_caldera_forms_magic_form'), 30);
            remove_filter('caldera_forms_render_get_field', array($this, 'filter_caldera_forms_render_get_field'), 30);
            remove_filter('caldera_forms_pre_do_field_magic', array($this, 'filter_caldera_forms_pre_do_field_magic'), 30);
            remove_filter('caldera_forms_get_field_entry', array($this, 'filter_caldera_forms_get_field_entry'), 30);
            remove_filter('caldera_forms_pre_check_condition', array($this, 'filter_caldera_forms_pre_check_condition'), 30);
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
        $value = preg_replace('~%[a-z0-9_]*%~', "", $value);
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
                $value = stripslashes_deep($value);
                $value = str_replace('&#91;', '[', $value);
                if (!$html) {
                    $value = wp_specialchars_decode($value, ENT_QUOTES);
                }
            } else {
                $value = str_replace('[', '&#91;', $value);
            }
        }
        return $value;
    }

    /**
     * Verify if item and dataset exists
     * 
     * @return bool - item and dataset exists
     */
    public function verify() {
        $item = $this->get('item');
        $dataset = $this->get('dataset');

        if ($item && $dataset && class_exists('Caldera_Forms_Entry') && class_exists('Caldera_Forms_Forms') && class_exists("Caldera_Forms")) {
            $form = Caldera_Forms_Forms::get_form($item);
            if ($form && isset($form['_last_updated'])) {
                $entry = new Caldera_Forms_Entry($form, $dataset);
                if ($entry->found()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Create Form based on uploaded PDF
     * 
     * @param object $template - Template Object to work with
     * @param array $data - Settings to create labels/shortcodes 
     * 
     * @return object - Mapped Template Object
     */
    public function auto_form($template, $data = array()) {

        if ($template->get('ID')) {

            $auto_form_label = isset($data['auto_form_label']) && $data['auto_form_label'] ? $data['auto_form_label'] : false;
            $auto_form_shortcode = isset($data['auto_form_shortcode']) ? true : false;

            $form = array(
                '_last_updated' => date('r'),
                'success' => sprintf(__('Success. [e2pdf-download id="%s"]', 'e2pdf'), $template->get('ID')),
                'name' => $template->get('title'),
                'db_support' => '1',
                'hide_form' => '1',
                'form_ajax' => '1',
                'custom_callback' => '',
                'fields' => array(),
                'layout_grid' => array(
                    'fields' => array(),
                    'strucutre' => ''
                ),
                'settings' => array(
                    'responsive' => array(
                        'break_point' => 'sm',
                    ),
                ),
            );

            $pages = $template->get('pages');
            $checkboxes = array();
            $radios = array();

            foreach ($pages as $page_key => $page) {
                if (isset($page['elements']) && !empty($page['elements'])) {
                    foreach ($page['elements'] as $element_key => $element) {
                        $type = false;
                        $label = '';
                        if ($element['type'] == 'e2pdf-input' || $element['type'] == 'e2pdf-signature') {
                            $type = 'text';
                            $label = __('Text', 'e2pdf');
                        } elseif ($element['type'] == 'e2pdf-textarea') {
                            $type = 'paragraph';
                            $label = __('Textarea', 'e2pdf');
                        } elseif ($element['type'] == 'e2pdf-select') {
                            $type = 'dropdown';
                            $label = __('Select', 'e2pdf');
                            $options = array();
                            $field_options = array();

                            if (isset($element['properties']['options'])) {
                                $field_options = explode("\n", $element['properties']['options']);
                                foreach ($field_options as $option) {
                                    $option_id = 'opt' . $this->random();
                                    $options[$option_id] = array(
                                        'calc_value' => '',
                                        'label' => $option,
                                        'value' => $option
                                    );
                                }
                            }
                        } elseif ($element['type'] == 'e2pdf-checkbox') {
                            $field_key = array_search($element['name'], array_column($checkboxes, 'name'));
                            if ($field_key !== false) {
                                $option_id = 'opt' . $this->random();
                                $checkboxes[$field_key]['options'][$option_id] = array(
                                    'calc_value' => $element['properties']['option'],
                                    'label' => $element['properties']['option'],
                                    'value' => $element['properties']['option'],
                                );
                                $pages[$page_key]['elements'][$element_key]['value'] = '%element_' . $checkboxes[$field_key]['element_id'] . '%';
                            } else {
                                $type = 'checkbox';
                                $label = __('Checkbox', 'e2pdf');
                            }
                        } elseif ($element['type'] == 'e2pdf-radio') {
                            if (isset($element['properties']['group']) && $element['properties']['group']) {
                                $element['name'] = $element['properties']['group'];
                            } else {
                                $element['name'] = $element['element_id'];
                            }

                            $field_key = array_search($element['name'], array_column($radios, 'name'));
                            if ($field_key !== false) {
                                $option_id = 'opt' . $this->random();
                                $radios[$field_key]['options'][$option_id] = array(
                                    'calc_value' => $element['properties']['option'],
                                    'label' => $element['properties']['option'],
                                    'value' => $element['properties']['option'],
                                );
                                $pages[$page_key]['elements'][$element_key]['value'] = '%element_' . $radios[$field_key]['element_id'] . '%';
                            } else {
                                $type = 'radio';
                                $label = __('Radio', 'e2pdf');
                            }
                        }

                        if ($type) {
                            $labels = array();
                            if ($auto_form_shortcode) {
                                $labels[] = '%element_' . $element['element_id'] . '%';
                            }

                            if ($auto_form_label && $auto_form_label == 'value' && isset($element['value']) && $element['value']) {
                                $labels[] = $element['value'];
                            } elseif ($auto_form_label && $auto_form_label == 'name' && isset($element['name']) && $element['name']) {
                                $labels[] = $element['name'];
                            }

                            if ($type == 'checkbox' || $type == 'radio') {
                                $field_id = 'opt' . $this->random();
                                $field_data = array(
                                    'name' => $element['name'],
                                    'element_id' => $element['element_id'],
                                    'label' => !empty($labels) ? implode(' ', $labels) : $label,
                                    'options' => array(
                                        $field_id => array(
                                            'calc_value' => $element['properties']['option'],
                                            'label' => $element['properties']['option'],
                                            'value' => $element['properties']['option'],
                                        ),
                                    )
                                );
                                if ($type == 'checkbox') {
                                    $checkboxes[] = $field_data;
                                } else {
                                    $radios[] = $field_data;
                                }
                            } else {
                                $field_id = 'fld_' . $this->random();
                                $form['fields'][$field_id] = array(
                                    'ID' => $field_id,
                                    'slug' => 'element_' . $element['element_id'],
                                    'type' => $type,
                                    'label' => !empty($labels) ? implode(' ', $labels) : $label
                                );

                                if ($type == 'select') {
                                    $form['fields'][$field_id]['config'] = array(
                                        'option' => $options
                                    );
                                }
                            }

                            $pages[$page_key]['elements'][$element_key]['value'] = '%element_' . $element['element_id'] . '%';
                            if (isset($element['properties']['esig'])) {
                                unset($pages[$page_key]['elements'][$element_key]['properties']['esig']);
                            }
                        }
                    }
                }
            }

            foreach ($checkboxes as $element) {

                $field_id = 'fld_' . $this->random();
                $form['fields'][$field_id] = array(
                    'ID' => $field_id,
                    'slug' => 'element_' . $element['element_id'],
                    'type' => 'checkbox',
                    'label' => $element['label'],
                    'config' => array(
                        'option' => $element['options']
                    )
                );
            }

            foreach ($radios as $element) {
                $field_id = 'fld_' . $this->random();
                $form['fields'][$field_id] = array(
                    'ID' => $field_id,
                    'slug' => 'element_' . $element['element_id'],
                    'type' => 'radio',
                    'label' => $element['label'],
                    'config' => array(
                        'option' => $element['options']
                    )
                );
            }

            $field_id = 'fld_' . $this->random();
            $form['fields'][$field_id] = array(
                'ID' => $field_id,
                'slug' => 'submit',
                'type' => 'button',
                'label' => __('Submit', 'e2pdf'),
                'class' => 'btn btn-default'
            );

            $fields = array_keys($form['fields']);
            $x = 1;
            foreach ($fields as $field) {
                $form['layout_grid']['fields'][$field] = $x . ':1';
                if ($x == 1) {
                    $form['layout_grid']['structure'] .= '12';
                } else {
                    $form['layout_grid']['structure'] .= '|12';
                }
                $x++;
            }

            if ($item = Caldera_Forms_Forms::create_form($form)) {
                $template->set('item', $item['ID']);
                $template->set('pages', $pages);
            }
        }
        return $template;
    }

    /**
     * Generate Random number for Field ID
     * 
     * @return int - Generated number
     */
    private function random() {
        return round((float) rand() / (float) getrandmax() * 10000000);
    }

    /**
     * Init Visual Mapper data
     * 
     * @return bool|string - HTML data source for Visual Mapper
     */
    public function visual_mapper() {

        $item = $this->get('item');
        $forms = array();
        $source = '';
        $html = '';

        if ($item && class_exists("Caldera_Forms") && class_exists("Caldera_Forms_Forms") && class_exists("Caldera_Forms_Field_Util")) {

            add_filter('caldera_forms_render_get_form', array($this, 'filter_caldera_forms_render_get_form'), 10, 1);

            $form = Caldera_Forms_Forms::get_form($item);
            if (!empty($form['is_connected_form']) && isset($form['node']) && !empty($form['node'])) {
                foreach ($form['node'] as $node) {
                    if (isset($node['form']) && $node['form']) {
                        $source .= Caldera_Forms::render_form($node['form']);
                        $forms[] = Caldera_Forms_Forms::get_form($node['form']);
                    }
                }
            } else {
                $source = Caldera_Forms::render_form($item);
            }

            remove_filter('caldera_forms_render_get_form', array($this, 'filter_caldera_forms_render_get_form'), 10);

            if ($source) {
                libxml_use_internal_errors(true);
                $dom = new DOMDocument();
                if (function_exists('mb_convert_encoding')) {
                    $html = $dom->loadHTML(mb_convert_encoding($source, 'HTML-ENTITIES', 'UTF-8'));
                } else {
                    $html = $dom->loadHTML('<?xml encoding="UTF-8">' . $source);
                }
                libxml_clear_errors();
            }

            if (!$source) {
                return __('Form source is empty', 'e2pdf');
            } elseif (!$html) {
                return __('Form could not be parsed due incorrect HTML', 'e2pdf');
            } else {

                $xml = $this->helper->load('xml');
                $xml->set('dom', $dom);
                $xpath = new DomXPath($dom);

                $remove_by_class = array(
                    'live-gravatar',
                );
                foreach ($remove_by_class as $key => $class) {
                    $elements = $xpath->query("//*[contains(@class, '{$class}')]");
                    foreach ($elements as $element) {
                        $element->parentNode->removeChild($element);
                    }
                }

                // Convert Conditional Fields
                $conditional_fields = $xpath->query("//*[contains(@class, 'caldera-forms-conditional-field')]");
                foreach ($conditional_fields as $element) {
                    $conditional_field_id = $xml->get_node_value($element, 'data-field-id');
                    if ($conditional_field_id) {
                        preg_match('/<script type="text\/html" id="conditional-' . $conditional_field_id . '-tmpl">(.*?)<\/script>/s', $source, $matches);
                        if (isset($matches[1])) {
                            $conditional_dom = new DOMDocument();
                            if (function_exists('mb_convert_encoding')) {
                                $conditional_dom->loadHTML(mb_convert_encoding($matches[1], 'HTML-ENTITIES', 'UTF-8'));
                            } else {
                                $conditional_dom->loadHTML('<?xml encoding="UTF-8">' . $matches[1]);
                            }
                            $element->appendChild($dom->importNode($conditional_dom->documentElement, true));
                        }
                    }
                }

                // Remove scripts
                $remove_by_tag = array(
                    'script'
                );
                foreach ($remove_by_tag as $key => $tag) {
                    $elements = $xpath->query("//{$tag}");
                    foreach ($elements as $element) {
                        $element->parentNode->removeChild($element);
                    }
                }

                // Replace names
                $fields = $xpath->query("//*[contains(@name, 'fld_')]");
                foreach ($fields as $element) {

                    $field_id = false;
                    if ($xml->get_node_value($element, 'type') == 'checkbox') {
                        preg_match("/(.*?)\[(.*?)\]/i", $xml->get_node_value($element, 'name'), $matches);
                        if (isset($matches[1])) {
                            $field_id = $matches[1];
                        }
                    } else {
                        $field_id = $xml->get_node_value($element, 'name');
                    }

                    // Modify Star fields
                    if ($xml->get_node_value($element, 'data-type') == 'star') {
                        $element = $xml->set_node_value($element, 'style', '');
                    }

                    // Modify Range fields
                    if ($xml->get_node_value($element, 'type') == 'range') {

                        $element = $xml->set_node_value($element, 'style', '', true);
                        $element = $xml->set_node_value($element, 'class', 'col-xs-12', true);

                        $next_element = $xpath->query(".//following-sibling::div[1]", $element->parentNode)->item(0);
                        if ($next_element) {
                            $next_element->parentNode->removeChild($next_element);
                        }
                    }

                    if ($field_id) {
                        if (!empty($form['is_connected_form'])) {
                            foreach ($forms as $sub_form) {
                                $field = Caldera_Forms_Field_Util::get_field($field_id, $sub_form);
                                if ($field && isset($field['slug'])) {
                                    $element = $xml->set_node_value($element, 'name', '%' . $field['slug'] . '%');
                                    break;
                                }
                            }
                        } else {
                            $field = Caldera_Forms_Field_Util::get_field($field_id, $form);
                            if ($field && isset($field['slug'])) {
                                $element = $xml->set_node_value($element, 'name', '%' . $field['slug'] . '%');
                            }
                        }
                    }
                }

                // Convert Calculation Fields
                $calculation_fields = $xpath->query("//input[@data-type='calculation']");
                foreach ($calculation_fields as $element) {

                    $element = $xml->set_node_value($element, 'type', 'text');
                    $element = $xml->set_node_value($element, 'class', $xml->get_node_value($element, 'class') . ' form-control');

                    $calculation_text = $xpath->query(".//h3", $element->parentNode)->item(0);
                    if ($calculation_text) {
                        $element->parentNode->removeChild($calculation_text);
                    }
                }

                // Modify File Upload
                $file_uploads = $xpath->query("//input[@type='file']");
                foreach ($file_uploads as $element) {
                    $element = $xml->set_node_value($element, 'class', $xml->get_node_value($element, 'type') . ' form-control');
                }

                // Modify Toggle Fields
                $toggle_gorups = $xpath->query("//*[contains(@class, 'cf-toggle-switch')]");
                foreach ($toggle_gorups as $element) {
                    $toggle_elements = $xpath->query(".//*[contains(@class, 'cf-toggle-group-buttons')]//a", $element);
                    foreach ($toggle_elements as $sub_element) {
                        if ($sub_element->attributes->getNamedItem("id")) {
                            $toggle_id = $xml->get_node_value($sub_element, "id");
                            $radio = $xpath->query(".//input[@id='{$toggle_id}']", $element)->item(0);
                            if ($radio) {
                                $radio_cloned = $radio->cloneNode(true);
                                $radio_cloned = $xml->set_node_value($radio_cloned, 'style', 'position: absolute;width: 100%;top: 0;left: 0;height: 100%; opacity: 0;');
                                $sub_element->appendChild($radio_cloned);
                            }
                        }
                    }
                }

                // Modify Advanced File Upload
                $advanced_uploaders = $xpath->query("//*[contains(@class, 'cf-uploader-trigger')]");
                foreach ($advanced_uploaders as $element) {
                    $input = $xpath->query(".//input[@type='hidden']", $element->parentNode)->item(0);

                    $attr = $dom->createAttribute('style');
                    $attr->value = 'position: relative;';
                    $element->appendChild($attr);

                    if ($input) {
                        $input_cloned = $input->cloneNode(true);
                        $input_cloned = $xml->set_node_value($input_cloned, 'style', 'position: absolute;width: 100%;top: 0;left: 0;height: 100%; opacity: 0;');
                        $input_cloned = $xml->set_node_value($input_cloned, 'type', 'text');
                        $element->appendChild($input_cloned);
                    }
                }

                // Modify Text Area same size
                $textareas = $xpath->query("//textarea");
                foreach ($textareas as $element) {
                    $element = $xml->set_node_value($element, 'rows', '4');
                }

                // Remove unecessary elements
                $submit_buttons = $xpath->query("//input[@type='submit']");
                foreach ($submit_buttons as $element) {
                    $element->parentNode->removeChild($element);
                }
                return $dom->saveHTML();
            }
        }
        return false;
    }

    /**
     * Auto Generate of Template for this extension
     * 
     * @return array - List of elements
     */
    public function auto() {

        $response = array();
        $elements = array();
        $fields = array();
        $forms = array();

        $item = $this->get('item');

        if (class_exists("Caldera_Forms_Forms")) {

            $form = Caldera_Forms_Forms::get_form($item);
            if (!empty($form['is_connected_form']) && isset($form['node']) && !empty($form['node'])) {
                foreach ($form['node'] as $node) {
                    if (isset($node['form']) && $node['form']) {
                        $sub_form = Caldera_Forms_Forms::get_form($node['form']);
                        $forms[] = $sub_form;
                        if ($sub_form && isset($sub_form['fields']) && is_array($sub_form['fields'])) {
                            $fields = array_merge($fields, $sub_form['fields']);
                        }
                    }
                }
            } else {
                $forms[] = $form;
                $fields = $form['fields'];
            }

            foreach ($forms as $sub_form) {
                if (isset($sub_form['layout_grid']['structure']) && $sub_form['layout_grid']['structure']) {
                    $sub_form['layout_grid']['structure'] = str_replace('#', '|', $sub_form['layout_grid']['structure']);
                }

                if ($sub_form && isset($sub_form['layout_grid']['structure']) && isset($sub_form['layout_grid']['fields'])) {
                    $struct = explode('|', $sub_form['layout_grid']['structure']);
                    foreach ($struct as $row_num => $row) {

                        $float = false;
                        $columns = explode(':', $row);
                        foreach ($columns as $column_num => $column) {
                            $column_fields = array_keys($sub_form['layout_grid']['fields'], ( $row_num + 1) . ':' . ( $column_num + 1));

                            $width = 100 / (12 / $column) . "%";

                            foreach ($column_fields as $column_field_key) {
                                if (isset($fields[$column_field_key])) {
                                    $field = $fields[$column_field_key];

                                    switch ($field['type']) {
                                        case 'text':
                                        case 'email':
                                        case 'phone_better':
                                        case 'phone_better':
                                        case 'number':
                                        case 'phone':
                                        case 'url':
                                        case 'date_picker':
                                        case 'color_picker':
                                        case 'credit_card_number':
                                        case 'credit_card_exp':
                                        case 'credit_card_cvc':
                                        case 'calculation':
                                        case 'range_slider':
                                        case 'star_rating':
                                        case 'utm':
                                        case 'file':
                                        case 'advanced_file':
                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-html',
                                                'block' => true,
                                                'float' => $float,
                                                'properties' => array(
                                                    'top' => '20',
                                                    'left' => '20',
                                                    'right' => '20',
                                                    'width' => $width,
                                                    'height' => 'auto',
                                                    'value' => $field['label'],
                                                )
                                            ));

                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-input',
                                                'properties' => array(
                                                    'top' => '5',
                                                    'width' => '100%',
                                                    'height' => 'auto',
                                                    'value' => "%{$field['slug']}%",
                                                )
                                            ));
                                            break;
                                        case 'paragraph':
                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-html',
                                                'block' => true,
                                                'float' => $float,
                                                'properties' => array(
                                                    'top' => '20',
                                                    'left' => '20',
                                                    'right' => '20',
                                                    'width' => $width,
                                                    'height' => 'auto',
                                                    'value' => $field['label'],
                                                )
                                            ));

                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-textarea',
                                                'properties' => array(
                                                    'top' => '5',
                                                    'width' => '100%',
                                                    'height' => '150',
                                                    'value' => "%{$field['slug']}%",
                                                )
                                            ));
                                            break;
                                        case 'wysiwyg':
                                        case 'html':
                                        case 'summary':
                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-html',
                                                'block' => true,
                                                'float' => $float,
                                                'properties' => array(
                                                    'top' => '20',
                                                    'left' => '20',
                                                    'right' => '20',
                                                    'width' => $width,
                                                    'height' => 'auto',
                                                    'value' => $field['label'],
                                                )
                                            ));

                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-html',
                                                'properties' => array(
                                                    'top' => '5',
                                                    'width' => '100%',
                                                    'height' => '150',
                                                    'value' => "%{$field['slug']}%",
                                                )
                                            ));
                                            break;
                                        case 'dropdown':
                                        case 'filtered_select2':
                                        case 'states':
                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-html',
                                                'block' => true,
                                                'float' => $float,
                                                'properties' => array(
                                                    'top' => '20',
                                                    'left' => '20',
                                                    'right' => '20',
                                                    'width' => $width,
                                                    'height' => 'auto',
                                                    'value' => $field['label'],
                                                )
                                            ));

                                            $options_tmp = array();

                                            if (isset($field['config']['option']) && is_array($field['config']['option'])) {
                                                foreach ($field['config']['option'] as $opt_key => $option) {
                                                    if (is_array($option)) {
                                                        $options_tmp[] = $option['value'];
                                                    }
                                                }
                                            }

                                            if ($field['type'] == 'states' && defined("CFCORE_PATH")) {
                                                if (file_exists(CFCORE_PATH . "fields/states/field.php")) {
                                                    $contents = file_get_contents(CFCORE_PATH . "fields/states/field.php");
                                                    preg_match_all('/<option value="(.*?)">(.*?)<\/option>/', $contents, $matches);
                                                    if (isset($matches[1])) {
                                                        foreach ($matches[1] as $state) {
                                                            $options_tmp[] = $state;
                                                        }
                                                    }
                                                }
                                            }

                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-select',
                                                'properties' => array(
                                                    'top' => '5',
                                                    'width' => '100%',
                                                    'height' => 'auto',
                                                    'options' => implode("\n", $options_tmp),
                                                    'value' => "%{$field['slug']}%",
                                                )
                                            ));
                                            break;
                                        case 'checkbox':
                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-html',
                                                'block' => true,
                                                'float' => $float,
                                                'properties' => array(
                                                    'top' => '20',
                                                    'left' => '20',
                                                    'right' => '20',
                                                    'width' => $width,
                                                    'height' => 'auto',
                                                    'value' => $field['label'],
                                                )
                                            ));

                                            if (isset($field['config']['option']) && is_array($field['config']['option'])) {
                                                foreach ($field['config']['option'] as $opt_key => $option) {
                                                    if (is_array($option)) {
                                                        $elements[] = $this->auto_field($field, array(
                                                            'type' => 'e2pdf-checkbox',
                                                            'properties' => array(
                                                                'top' => '5',
                                                                'width' => 'auto',
                                                                'height' => 'auto',
                                                                'value' => "%{$field['slug']}%",
                                                                'option' => $option['value']
                                                            )
                                                        ));
                                                        $elements[] = $this->auto_field($field, array(
                                                            'type' => 'e2pdf-html',
                                                            'float' => true,
                                                            'properties' => array(
                                                                'left' => '5',
                                                                'width' => '100%',
                                                                'height' => 'auto',
                                                                'value' => $option['label']
                                                            )
                                                        ));
                                                    }
                                                }
                                            }
                                            break;
                                        case 'radio':
                                        case 'toggle_switch':
                                            $elements[] = $this->auto_field($field, array(
                                                'type' => 'e2pdf-html',
                                                'block' => true,
                                                'float' => $float,
                                                'properties' => array(
                                                    'top' => '20',
                                                    'left' => '20',
                                                    'right' => '20',
                                                    'width' => $width,
                                                    'height' => 'auto',
                                                    'value' => $field['label'],
                                                )
                                            ));

                                            if (isset($field['config']['option']) && is_array($field['config']['option'])) {
                                                foreach ($field['config']['option'] as $opt_key => $option) {
                                                    if (is_array($option)) {
                                                        $elements[] = $this->auto_field($field, array(
                                                            'type' => 'e2pdf-radio',
                                                            'properties' => array(
                                                                'top' => '5',
                                                                'width' => 'auto',
                                                                'height' => 'auto',
                                                                'value' => "%{$field['slug']}%",
                                                                'option' => $option['value'],
                                                                'group' => "%{$field['slug']}%",
                                                            )
                                                        ));
                                                        $elements[] = $this->auto_field($field, array(
                                                            'type' => 'e2pdf-html',
                                                            'float' => true,
                                                            'properties' => array(
                                                                'left' => '5',
                                                                'width' => '100%',
                                                                'height' => 'auto',
                                                                'value' => $option['label']
                                                            )
                                                        ));
                                                    }
                                                }
                                            }
                                            break;
                                        default:
                                            /*
                                             *  Unsupported fields: section_break, live_gravatar, button 
                                             */
                                            break;
                                    }
                                }
                            }

                            $float = true;
                        }
                    }
                }
            }
        }

        $response['page'] = array(
            'bottom' => '20',
            'top' => '20',
        );

        $response['elements'] = $elements;
        return $response;
    }

    /**
     * Element generation for Auto PDF
     * 
     * @param array $field - Field options
     * @param array $element - Element options
     * 
     * @return array - Element with modified options
     */
    public function auto_field($field = false, $element = array()) {

        if (!$field) {
            return false;
        }

        if (!isset($element['block'])) {
            $element['block'] = false;
        }

        if (!isset($element['float'])) {
            $element['float'] = false;
        }

        return $element;
    }

    /**
     * Load actions for this extension
     */
    public function load_actions() {
        add_action('caldera_forms_mailer_complete', array($this, 'action_caldera_forms_mailer_complete'), 99, 0);
        add_action('caldera_forms_mailer_failed', array($this, 'action_caldera_forms_mailer_complete'), 99, 0);
        add_action('caldera_forms_submit_complete', array($this, 'action_caldera_forms_mailer_complete'), 99, 0);
    }

    /**
     * Delete attachments that were sent by email
     */
    public function action_caldera_forms_mailer_complete() {

        $files = $this->helper->get('caldera_attachments');
        if (is_array($files) && !empty($files)) {
            foreach ($files as $key => $file) {
                $this->helper->delete_dir(dirname($file) . '/');
            }
            $this->helper->deset('caldera_attachments');
        }
    }

    /**
     * Remove Page Breaks
     * 
     * @param array $form - Form
     * 
     * @return array - Form
     */
    public function filter_caldera_forms_render_get_form($form) {
        if (isset($form['layout_grid']['structure']) && $form['layout_grid']['structure']) {
            $form['layout_grid']['structure'] = str_replace('#', '|', $form['layout_grid']['structure']);
        }
        return $form;
    }

    /**
     * Incorrectly rendered Checkbox Value
     * 
     * @param array $entry - Field
     * @param array $field_id - Field ID
     * @param array $form - Current Form
     * @param array $entry_id - Entry ID  
     * 
     * @return mixed - Fixed entry
     */
    public function filter_caldera_forms_get_field_entry($entry, $field_id, $form, $entry_id) {
        if (class_exists('Caldera_Forms_Field_Util') && $field_id) {
            if (Caldera_Forms_Field_Util::get_type($field_id, $form) == 'checkbox' && $entry && is_array($entry)) {
                $last = array_pop($entry);
                if ($last && is_array($last) && isset($last['value'])) {
                    $is_json = @json_decode($last['value'], ARRAY_A);
                    if (!empty($is_json)) {
                        $entry = array(
                            '0' => array(
                                'value' => $is_json
                            )
                        );
                    } else {
                        if (is_serialized($last['value'])) {
                            $serialized = @unserialize($last['value']);
                            if (!empty($serialized)) {
                                $entry = array(
                                    '0' => array(
                                        'value' => $serialized
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        return $entry;
    }

    /**
     * Disable conditions to render inside PDF
     * 
     * @return boolean
     */
    public function filter_caldera_forms_pre_check_condition($result, $conditions, $form, $entry_id) {
        return true;
    }

    /**
     * Convert file and advanced_file field type to not rendered
     * 
     * @param array $field - Field
     * @param array $form - Current form 
     * 
     * @return array - Updated field
     */
    public function filter_caldera_forms_render_get_field($field, $form) {
        if (isset($field['type']) && ($field['type'] == 'file' || $field['type'] == 'advanced_file')) {
            $field['config']['media_lib'] = false;
        }
        return $field;
    }

    /**
     * Fix for {entry_id}
     * 
     * @param array $form - Current form
     * @param int $entry_id - Current entry_id
     * 
     * @return array - Updated form
     */
    public function filter_caldera_forms_magic_form($form, $entry_id) {
        if (class_exists("Caldera_Forms") && $entry_id) {
            Caldera_Forms::set_field_data('_entry_id', $entry_id, $form);
        }
        return $form;
    }

    /**
     * Render file and advanced_file field types to url instead image and link
     * 
     * @param string $_value - NULL value to overide
     * @param string $value - Value to replace shortcodes
     * @param array $matches - List of matches of magic tags
     * @param int $entry_id - Current entry
     * @param array $form - Current form  
     * 
     * @return string - Updated value
     */
    public function filter_caldera_forms_pre_do_field_magic($_value, $value, $matches, $entry_id, $form) {

        if (class_exists('Caldera_Forms') && $value) {
            if (!empty($matches[1])) {
                foreach ($matches[1] as $key => $tag) {
                    // check for parts
                    $part_tags = explode(':', $tag);
                    if (!empty($part_tags[1])) {
                        $tag = $part_tags[0];
                    }
                    $entry = Caldera_Forms::get_slug_data($tag, $form, $entry_id);

                    $field = false;
                    if ($entry !== null) {
                        $field = Caldera_Forms_Field_Util::get_field_by_slug($tag, $form);
                    }

                    if ('html' === $this->get_field_type_by_slug($tag, $form)) {
                        $field = Caldera_Forms_Field_Util::get_field_by_slug($tag, $form);
                        $entry = Caldera_Forms::do_magic_tags(Caldera_Forms_Field_Util::get_default($field, $form));
                    }

                    if (Caldera_Forms_Field_Util::is_file_field($field, $form)) {
                        if (is_string($entry) && Caldera_Forms_Field_Util::is_file_field($field, $form)) {
                            if (!filter_var($entry, FILTER_VALIDATE_URL)) {
                                $entry = '';
                            }
                        }
                    }

                    if (is_string($entry)) {
                        if (!empty($field) && !empty($part_tags[1]) && $part_tags[1] == 'label') {
                            $_entry = @json_decode($entry);
                            if (is_object($_entry)) {
                                $entry = $_entry;
                            }
                        } else {
                            $entry = $this->maybe_implode_opts($entry);
                        }
                    }

                    if (!empty($field) && !empty($part_tags[1]) && $part_tags[1] == 'label') {
                        if (!is_array($entry)) {
                            $entry = (array) $entry;
                        }

                        foreach ((array) $entry as $entry_key => $entry_line) {
                            if (!empty($field['config']['option'])) {
                                foreach ($field['config']['option'] as $option) {
                                    if ($option['value'] == $entry_line) {
                                        $entry[$entry_key] = $option['label'];
                                    }
                                }
                            }
                        }
                    }

                    if (is_array($entry)) {
                        if (count($entry) === 1) {
                            $entry = array_shift($entry);
                        } elseif (count($entry) === 2) {
                            $entry = implode(', ', $entry);
                        } elseif (count($entry) > 2) {
                            $last = array_pop($entry);
                            $entry = implode(', ', $entry) . ', ' . $last;
                        } else {
                            $entry = null;
                        }
                    }

                    $value = str_replace($matches[0][$key], $entry, $value);
                }
                return apply_filters('caldera_forms_do_field_magic_value', $value, $matches, $entry_id, $form);
            }
        }

        return $_value;
    }

    /**
     * Implode "opts" -- IE checkboxes stored as "opts" as needed
     *
     * @param string $value Value to check and possibly convert
     *
     * @return string
     */
    public function maybe_implode_opts($value) {
        if (is_string($value) && '{"opt' == substr($value, 0, 5)) {
            $_value = @json_decode($value);
            if (is_object($_value)) {
                $value = implode(', ', (array) $_value);
            }
        } else if (is_array($value)) {
            $value = implode(', ', $value);
        }
        return $value;
    }

    /**
     * Get field type by slug for HTML field render
     *
     * @param string $slug Slug to check
     * @param array $form Form to check
     * @return boolean|mixed
     */
    public function get_field_type_by_slug($slug, $form) {
        if (is_array($form) && isset($form['fields'])) {
            foreach ($form['fields'] as $field_id => $field) {
                if ($field['slug'] == $slug) {
                    if (isset($field['type'])) {
                        return $field['type'];
                    } else {
                        return false;
                    }
                }
            }
        }
        return false;
    }

    /**
     * [e2pdf-view] shortcode fix
     * 
     * @param string $out - HTML Output
     * @param array $form - Current form 
     * 
     * @return string - Updated HTML Output
     */
    public function filter_caldera_forms_render_form($out, $form) {

        if (false === strpos($out, '[')) {
            return $out;
        }

        $shortcode_tags = array(
            'e2pdf-view'
        );

        preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $out, $matches);

        $tagnames = array_intersect($shortcode_tags, $matches[1]);

        if (!empty($tagnames)) {

            $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

            preg_match_all("/$pattern/", $out, $shortcodes);
            foreach ($shortcodes[0] as $key => $shortcode_value) {

                $shortcode = array();
                $shortcode[1] = $shortcodes[1][$key];
                $shortcode[2] = $shortcodes[2][$key];
                $shortcode[3] = $shortcodes[3][$key];
                $shortcode[4] = $shortcodes[4][$key];
                $shortcode[5] = $shortcodes[5][$key];
                $shortcode[6] = $shortcodes[6][$key];

                $out = str_replace($shortcode_value, do_shortcode_tag($shortcode), $out);
            }
        }

        return $out;
    }

    /**
     * [e2pdf-view] shortcode fix
     * 
     * @param array $out - Array with response
     * @param array $form - Current form 
     * 
     * @return array - Updated response
     */
    public function filter_caldera_forms_ajax_return($out, $form) {

        if (isset($out['html'])) {

            if (false === strpos($out['html'], '[')) {
                return $out;
            }

            $shortcode_tags = array(
                'e2pdf-view'
            );

            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $out['html'], $matches);

            $tagnames = array_intersect($shortcode_tags, $matches[1]);

            if (!empty($tagnames)) {

                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                preg_match_all("/$pattern/", $out['html'], $shortcodes);
                foreach ($shortcodes[0] as $key => $shortcode_value) {

                    $shortcode = array();
                    $shortcode[1] = $shortcodes[1][$key];
                    $shortcode[2] = $shortcodes[2][$key];
                    $shortcode[3] = $shortcodes[3][$key];
                    $shortcode[4] = $shortcodes[4][$key];
                    $shortcode[5] = $shortcodes[5][$key];
                    $shortcode[6] = $shortcodes[6][$key];

                    $out['html'] = str_replace($shortcode_value, do_shortcode_tag($shortcode), $out['html']);
                }
            }
        }

        return $out;
    }

    public function filter_success_message($message = '', $form = array(), $dataset_id = false) {

        if (false !== strpos($message, '[')) {

            $shortcode_tags = array(
                'e2pdf-download',
                'e2pdf-save',
                'e2pdf-view',
                'e2pdf-adobesign',
                'e2pdf-zapier'
            );

            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $message, $matches);

            $tagnames = array_intersect($shortcode_tags, $matches[1]);

            if (!empty($tagnames)) {

                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                preg_match_all("/$pattern/", $message, $shortcodes);
                foreach ($shortcodes[0] as $key => $shortcode_value) {

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
                        if (!isset($atts['dataset']) && isset($atts['id'])) {
                            $template = new Model_E2pdf_Template();
                            $template->load($atts['id']);
                            if ($template->get('extension') === 'caldera') {
                                if ($dataset_id) {
                                    $atts['dataset'] = $dataset_id;
                                    $shortcode[3] .= ' dataset="' . $dataset_id . '"';
                                }
                            }
                        }

                        if (!isset($atts['apply'])) {
                            $shortcode[3] .= ' apply="true"';
                        }

                        if ($shortcode[2] === 'e2pdf-view') {
                            $new_shortcode = $shortcode[2] . $shortcode[3];
                            $message = str_replace($shortcode_value, "[{$new_shortcode}]", $message);
                        } else {
                            $message = str_replace($shortcode_value, do_shortcode_tag($shortcode), $message);
                        }
                    }
                }
            }
        }

        return $message;
    }

    /**
     * Load filters for this extension
     */
    public function load_filters() {
        /*
         * 1.15.06 SendGripd Priority Fix (30 to 25)
         */
        add_filter('caldera_forms_mailer', array($this, 'filter_caldera_forms_mailer'), 25, 4);
        add_filter('caldera_forms_autoresponse_mail', array($this, 'filter_caldera_forms_mailer'), 30, 4);
        add_filter('caldera_forms_render_notices', array($this, 'filter_caldera_forms_render_notices'), 99, 2);
        add_filter('caldera_forms_ajax_return', array($this, 'filter_caldera_forms_ajax_return'), 30, 2);
        add_filter('caldera_forms_render_form', array($this, 'filter_caldera_forms_render_form'), 30, 2);
    }

    public function filter_caldera_forms_render_notices($notices, $form) {

        if (class_exists('Caldera_Forms')) {
            if (!empty($notices['success']['note'])) {
                $dataset_id = false;
                if (!empty($form['form_ajax'])) {
                    if (!empty($form['stage_form'])) {
                        if (function_exists('cf_form_connector_get_current_position')) {
                            $process_record = cf_form_connector_get_current_position();
                            if (!empty($process_record[$form['stage_form']]['entry_id'])) {
                                $dataset_id = $process_record[$form['stage_form']]['entry_id'];
                            }
                        }
                    } else {
                        $dataset_id = (int) Caldera_Forms::do_magic_tags('{entry_id}');
                    }
                } else {
                    if (isset($_GET['cf_id']) && isset($_GET['cf_su'])) {
                        $dataset_id = absint($_GET['cf_id']);
                    }
                }

                $notices['success']['note'] = $this->filter_success_message($notices['success']['note'], $form, $dataset_id);
            }
        }

        return $notices;
    }

    /**
     * Filter to replace mail message E2Pdf shortcodes
     */
    public function filter_caldera_forms_mailer($mail, $data, $form, $entryid) {

        if ($mail && isset($mail['message'])) {
            if (false !== strpos($mail['message'], '[')) {
                $shortcode_tags = array(
                    'e2pdf-download',
                    'e2pdf-save',
                    'e2pdf-attachment',
                    'e2pdf-adobesign',
                    'e2pdf-zapier'
                );

                preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $mail['message'], $matches);
                $tagnames = array_intersect($shortcode_tags, $matches[1]);

                if (!empty($tagnames)) {

                    $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                    preg_match_all("/$pattern/", $mail['message'], $shortcodes);

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

                        if (!isset($atts['dataset']) && isset($atts['id'])) {
                            $template = new Model_E2pdf_Template();
                            $template->load($atts['id']);
                            if ($template->get('extension') === 'caldera') {
                                $dataset_id = $entryid;
                                if (!$dataset_id) {
                                    $dataset_id = isset($data['_entry_id']) ? $data['_entry_id'] : false;
                                }
                                if ($dataset_id) {
                                    $atts['dataset'] = $dataset_id;
                                    $shortcode[3] .= ' dataset="' . $dataset_id . '"';
                                }
                            }
                        }

                        if (!isset($atts['apply'])) {
                            $shortcode[3] .= ' apply="true"';
                        }

                        if (!isset($atts['filter'])) {
                            $shortcode[3] .= ' filter="true"';
                        }

                        if (($shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $shortcode[2] === 'e2pdf-attachment') {
                            $file = do_shortcode_tag($shortcode);
                            if ($file) {
                                if ($shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                                    $this->helper->add('caldera_attachments', $file);
                                }
                                $mail['attachments'][] = $file;
                            }
                            $mail['message'] = str_replace($shortcode_value, '', $mail['message']);
                        } else {
                            $mail['message'] = str_replace($shortcode_value, do_shortcode_tag($shortcode), $mail['message']);
                        }
                    }
                }
            }
        }

        return $mail;
    }

    /**
     * Get styles for generating Map Field function
     * 
     * @return array - List of css files to load
     */
    public function styles($item = false) {

        $styles = array();
        if (class_exists('Caldera_Forms_Render_Assets')) {
            $url = Caldera_Forms_Render_Assets::make_url('caldera-forms-front', false);
            $styles[] = $url;
        }

        return $styles;
    }

}
