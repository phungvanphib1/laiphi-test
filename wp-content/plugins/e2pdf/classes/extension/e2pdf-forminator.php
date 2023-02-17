<?php

/**
 * E2pdf Forminator Extension
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      01.01.01
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Extension_E2pdf_Forminator extends Model_E2pdf_Model {

    private $options;
    private $info = array(
        'key' => 'forminator',
        'title' => 'Forminator'
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

    public function active() {

        if (!function_exists('is_plugin_active')) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        if (is_plugin_active('forminator/forminator.php') || is_plugin_active('forminator-pro/forminator.php') || defined('E2PDF_FORMINATOR_EXTENSION')) {
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
        if (class_exists("Forminator_API")) {
            $forms = Forminator_API::get_forms(null, 1, 99999);
            foreach ($forms as $key => $value) {
                $content[] = $this->item($value->id);
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

        if (class_exists("Forminator_API") && $item) {
            $entries = Forminator_API::get_entries($item);
            foreach ($entries as $key => $dataset) {
                $this->set('item', $item);
                $this->set('dataset', $dataset->entry_id);

                $dataset_title = $this->render($name);
                if (!$dataset_title) {
                    $dataset_title = $dataset->entry_id;
                }
                $datasets[] = array(
                    'key' => $dataset->entry_id,
                    'value' => $dataset_title
                );
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

        $forminator_form = false;
        if (class_exists("Forminator_API")) {
            $forminator_form = Forminator_API::get_form($item);
        }

        if (!is_wp_error($forminator_form)) {
            $form->id = (string) $item;
            $form->url = $this->helper->get_url(array('page' => 'forminator-cform-wizard', 'id' => $forminator_form->id));
            $form->name = function_exists('forminator_get_form_name') ? forminator_get_form_name($form->id, 'custom_form') : $forminator_form->name;
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
     * @param string $field - Field
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
     * @param string $type - Type of rendering value
     * @param string $value - Content
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

        if ($this->verify() && function_exists("forminator_replace_form_data") && class_exists("Forminator_API")) {

            $args = apply_filters('e2pdf_extension_render_shortcodes_args', $args, $element_id, $template_id, $item, $dataset, false, false);

            $form = Forminator_API::get_form($item);

            $metas = array();
            $data = array();

            if ($form->is_prevent_store()) {
                if (class_exists('Forminator_Form_Entry_Model')) {
                    $entry = new Forminator_Form_Entry_Model();
                    if ($this->get('field_data_array') && is_array($this->get('field_data_array'))) {
                        $entry->set_fields($this->get('field_data_array'));
                        $metas = $entry->meta_data;
                    }
                } else {
                    $entry = null;
                }
            } else {
                $entry = Forminator_API::get_entry($item, $dataset);
                $metas = $entry->meta_data;
            }

            foreach ($metas as $key => $meta) {
                if (is_array($meta['value'])) {
                    if (array_unique(array_map("is_int", array_keys($meta['value']))) === array(true)) {
                        $data[$key] = $meta['value'];
                    } else {
                        if (isset($meta['value']['file']['file_url'])) {
                            $data[$key] = $meta['value']['file']['file_url'];
                        } else {
                            foreach ($meta['value'] as $sub_key => $sub_meta) {
                                $data[$key . "-" . $sub_key] = $sub_meta;
                            }
                        }
                    }
                } else {
                    $data[$key] = $meta['value'];
                }
            }

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
                                    'e2pdf_extension_forminator_render_shortcodes_site_url'
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

            if (defined('FORMINATOR_VERSION') && version_compare(FORMINATOR_VERSION, '1.16.0', '>=')) {
                $prepared_data = Forminator_CForm_Front_Action::$prepared_data;
                Forminator_CForm_Front_Action::$prepared_data = $data;
                $value = $this->forminator_replace_form_data($value, $form, $entry);
                $value = forminator_replace_form_data($value, $form, $entry);
                if (version_compare(FORMINATOR_VERSION, '1.17.2', '>=')) {
                    $value = forminator_replace_variables($value, $item, $entry);
                } else {
                    $value = forminator_replace_variables($value, $item);
                }
                if (function_exists('forminator_replace_custom_form_data')) {
                    $value = forminator_replace_custom_form_data($value, $form, $entry);
                }
                Forminator_CForm_Front_Action::$prepared_data = $prepared_data;
            } else {
                $value = forminator_replace_form_data($value, $data, $form, $entry);
                $value = forminator_replace_variables($value, $item);
                if (function_exists('forminator_replace_custom_form_data')) {
                    $value = forminator_replace_custom_form_data($value, $form, $data, $entry, array());
                }
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
        }

        $value = apply_filters('e2pdf_extension_render_shortcodes_value', $value, $element_id, $template_id, $item, $dataset, false, false);

        return $value;
    }

    /**
     * Compatibility fix with Forminator 1.16
     * 
     * @param type $content
     * @param Forminator_Form_Model $custom_form
     * @param type $data
     * @return type
     */
    public function forminator_replace_form_data($content, $custom_form = null, $entry = null) {
        $matches = array();
        $data = Forminator_CForm_Front_Action::$prepared_data;
        $field_types = Forminator_Core::get_field_types();

        $suffix_time = array('hours', 'minutes', 'ampm');
        $suffix_address = array('street_address', 'address_line', 'city', 'state', 'zip', 'country');
        $suffix_name = array('prefix', 'first-name', 'middle-name', 'last-name');

        $pattern = '/\{foreach:(group-\d+)\}(.*?)\{\/foreach:(group-\d+)\}/s';
        if (preg_match_all($pattern, $content, $matches)) {
            if (isset($matches[0]) && is_array($matches[0])) {
                foreach ($matches[0] as $key => $match) {

                    $group_id = isset($matches[1][$key]) ? $matches[1][$key] : '';
                    $inner = isset($matches[2][$key]) ? $matches[2][$key] : '';
                    $outer = '';

                    if ($group_id) {

                        $group_fields = $custom_form->get_grouped_fields($group_id);
                        $original_keys = wp_list_pluck($group_fields, 'slug');
                        $repeater_keys = forminator_get_cloned_field_keys($entry, $original_keys);

                        $num_entries = 0;
                        if (count($repeater_keys) > 0) {
                            $num_entries = count($repeater_keys) + 1;
                        } else {
                            foreach ($original_keys as $original_key) {
                                if (stripos($original_key, 'address') !== false) {
                                    foreach ($suffix_address as $sub_suffix) {
                                        if (isset($data[$original_key . '-' . $sub_suffix]) && $data[$original_key . '-' . $sub_suffix]) {
                                            $num_entries = 1;
                                            break;
                                        }
                                    }
                                } elseif (stripos($original_key, 'time') !== false) {
                                    foreach ($suffix_time as $sub_suffix) {
                                        if (isset($data[$original_key . '-' . $sub_suffix]) && $data[$original_key . '-' . $sub_suffix]) {
                                            $num_entries = 1;
                                            break;
                                        }
                                    }
                                } elseif (stripos($original_key, 'name') !== false) {
                                    foreach ($suffix_name as $sub_suffix) {
                                        if (isset($data[$original_key . '-' . $sub_suffix]) && $data[$original_key . '-' . $sub_suffix]) {
                                            $num_entries = 1;
                                            break;
                                        }
                                    }
                                }

                                if (isset($data[$original_key])) {
                                    $num_entries = 1;
                                }
                                if ($num_entries > 0) {
                                    break;
                                }
                            }
                        }

                        $i = 1;
                        while ($i <= $num_entries) {
                            $replace = array();
                            foreach ($original_keys as $original_key) {
                                if (stripos($original_key, 'address') !== false) {
                                    foreach ($suffix_address as $sub_suffix) {
                                        $replace['{' . $original_key . '-' . $sub_suffix . '}'] = '{' . $original_key . '-' . $sub_suffix . ':' . $i . '}';
                                    }
                                } elseif (stripos($original_key, 'time') !== false) {
                                    foreach ($suffix_time as $sub_suffix) {
                                        $replace['{' . $original_key . '-' . $sub_suffix . '}'] = '{' . $original_key . '-' . $sub_suffix . ':' . $i . '}';
                                    }
                                } elseif (stripos($original_key, 'name') !== false) {
                                    foreach ($suffix_name as $sub_suffix) {
                                        $replace['{' . $original_key . '-' . $sub_suffix . '}'] = '{' . $original_key . '-' . $sub_suffix . ':' . $i . '}';
                                    }
                                }
                                $replace['{' . $original_key . '}'] = '{' . $original_key . ':' . $i . '}';
                            }

                            $outer .= str_replace(array_keys($replace), $replace, $inner);
                            $i++;
                        }
                        $content = str_replace($match, $outer, $content);
                    }
                }
            }
        }

        $randomed_field_pattern = 'field-\d+-\d+';
        $increment_field_pattern = sprintf('(%s)-\d+', implode('|', $field_types));
        $pattern = '/\{((' . $randomed_field_pattern . ')|(' . $increment_field_pattern . '))(\-[A-Za-z-_]+)?(:\d+)?(:html)?\}/';

        if (preg_match_all($pattern, $content, $matches)) {
            if (!isset($matches[0]) || !is_array($matches[0])) {
                return $content;
            }
            foreach ($matches[0] as $key => $match) {
                $element_id = forminator_clear_field_id($match);

                $suffix = isset($matches[5][$key]) ? $matches[5][$key] : null;
                $index = isset($matches[6][$key]) ? str_replace(':', '', $matches[6][$key]) : null;
                $filter = isset($matches[7][$key]) ? str_replace(':', '', $matches[7][$key]) : null;

                if ($index) {
                    $field_id = isset($matches[1][$key]) ? $matches[1][$key] : null;
                    if (stripos($field_id, 'html') !== false) {
                        $content = str_replace($match, '{' . $field_id . '}', $content);
                        continue;
                    } else {
                        $field = $custom_form->get_field($field_id);
                        if ($field && isset($field['parent_group'])) {
                            $group_fields = $custom_form->get_grouped_fields($field['parent_group']);
                            $original_keys = wp_list_pluck($group_fields, 'slug');
                            $repeater_keys = array_values(forminator_get_cloned_field_keys($entry, $original_keys));

                            if ($index && $index >= '2') {
                                if (isset($repeater_keys[$index - 2])) {
                                    $element_id = $field_id . $repeater_keys[$index - 2] . $suffix;
                                } else {
                                    continue;
                                }
                            } else {
                                $element_id = $field_id . $suffix;
                            }
                        }
                    }
                } elseif ($filter && $filter == 'html') {
                    $field_id = isset($matches[1][$key]) ? $matches[1][$key] : null;
                    $content = str_replace($match, forminator_replace_form_data('{' . $field_id . '}', $custom_form, $entry), $content);
                    continue;
                }

                if (isset($data[$element_id])) {
                    if (stripos($element_id, 'currency') !== false || stripos($element_id, 'number') !== false) {
                        $field = $custom_form->get_field($element_id, true);
                        $value = Forminator_Field::forminator_number_formatting($field, $data[$element_id]);
                    } else {
                        $value = $data[$element_id];
                    }
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $content = str_replace($match, $value, $content);
                } elseif (stripos($element_id, 'calculation') !== false && $custom_form && $entry) {
                    if ($index) {
                        /*
                         * Incorrect Value for Repeater with default function
                         */
                        $value = render_entry($entry, $element_id);
                    } else {
                        $value = forminator_get_field_from_form_entry($element_id, $custom_form, $entry);
                    }

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $content = str_replace($match, $value, $content);
                } elseif (stripos($element_id, 'address') !== false && !$suffix) {

                    $address = array();
                    foreach ($suffix_address as $sub_suffix) {
                        if (isset($data[$element_id . '-' . $sub_suffix]) && $data[$element_id . '-' . $sub_suffix]) {
                            $address[$sub_suffix] = $data[$element_id . '-' . $sub_suffix];
                        }
                    }

                    $value = apply_filters('e2pdf_extension_forminator_replace_form_data_address', implode(', ', $address), $element_id, $address, $custom_form, $entry);
                    $content = str_replace($match, $value, $content);
                } elseif (stripos($element_id, 'time') !== false && !$suffix) {

                    $time = array();
                    foreach ($suffix_time as $sub_suffix) {
                        if (isset($data[$element_id . '-' . $sub_suffix]) && $data[$element_id . '-' . $sub_suffix]) {
                            $time[$sub_suffix] = $data[$element_id . '-' . $sub_suffix];
                        }
                    }

                    $value = apply_filters('e2pdf_extension_forminator_replace_form_data_time', implode(':', $time), $element_id, $time, $custom_form, $entry);
                    $content = str_replace($match, $value, $content);
                }
            }
        }

        return $content;
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
        $value = preg_replace('~{[^}]*}~', "", $value);
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

    /**
     * Verify if item and dataset exists
     * 
     * @return bool - item and dataset exists
     */
    public function verify() {
        $item = $this->get('item');
        $dataset = $this->get('dataset');

        if ($item && $dataset && class_exists('Forminator_API')) {
            $form = Forminator_API::get_form($item);
            if (!is_wp_error($form)) {
                if ($form->is_prevent_store()) {
                    if ($dataset == 'is_prevent_store') {
                        return true;
                    }
                } else {
                    $entry = Forminator_API::get_entry($item, $dataset);
                    if (!is_wp_error($entry)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function auto_form($template, $data = array()) {

        if ($template->get('ID')) {

            $auto_form_label = isset($data['auto_form_label']) && $data['auto_form_label'] ? $data['auto_form_label'] : false;
            $auto_form_shortcode = isset($data['auto_form_shortcode']) ? true : false;

            $wrappers = array();

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
                            $type = 'textarea';
                            $label = __('Textarea', 'e2pdf');
                        } elseif ($element['type'] == 'e2pdf-select') {

                            $type = 'select';
                            $label = __('Select', 'e2pdf');

                            $options = array();
                            $field_options = array();

                            if (isset($element['properties']['options'])) {
                                $field_options = explode("\n", $element['properties']['options']);
                                foreach ($field_options as $option) {
                                    $options[] = array(
                                        'label' => $option,
                                        'value' => ''
                                    );
                                }
                            }
                        } elseif ($element['type'] == 'e2pdf-checkbox') {
                            $field_key = array_search($element['name'], array_column($checkboxes, 'name'));
                            if ($field_key !== false) {
                                $checkboxes[$field_key]['options'][] = array(
                                    'label' => $element['properties']['option'],
                                    'value' => $element['properties']['option'],
                                );
                                $pages[$page_key]['elements'][$element_key]['value'] = '{checkbox-' . $checkboxes[$field_key]['element_id'] . '}';
                            } else {
                                $type = 'checkbox';
                                $label = __('Checkbox', 'e2pdf');
                                $options = array(
                                    'label' => $element['properties']['option'],
                                    'value' => $element['properties']['option'],
                                );
                            }
                        } elseif ($element['type'] == 'e2pdf-radio') {
                            if (isset($element['properties']['group']) && $element['properties']['group']) {
                                $element['name'] = $element['properties']['group'];
                            } else {
                                $element['name'] = $element['element_id'];
                            }

                            $field_key = array_search($element['name'], array_column($radios, 'name'));
                            if ($field_key !== false) {
                                $radios[$field_key]['options'][] = array(
                                    'label' => $element['properties']['option'],
                                    'value' => '',
                                );
                                $pages[$page_key]['elements'][$element_key]['value'] = '{radio-' . $radios[$field_key]['element_id'] . '}';
                            } else {
                                $type = 'radio';
                                $label = __('Radio', 'e2pdf');
                                $options = array(
                                    'label' => $element['properties']['option'],
                                    'value' => '',
                                );
                            }
                        }

                        if ($type) {
                            $labels = array();
                            if ($auto_form_shortcode) {
                                $labels[] = '{' . $type . '-' . $element['element_id'] . '}';
                            }

                            if ($auto_form_label && $auto_form_label == 'value' && isset($element['value']) && $element['value']) {
                                $labels[] = $element['value'];
                            } elseif ($auto_form_label && $auto_form_label == 'name' && isset($element['name']) && $element['name']) {
                                $labels[] = $element['name'];
                            }

                            if ($type == 'checkbox' || $type == 'radio') {

                                $field_data = array(
                                    'name' => $element['name'],
                                    'element_id' => $element['element_id'],
                                    'field_label' => !empty($labels) ? implode(' ', $labels) : $label,
                                    'options' => array(
                                        $options
                                    )
                                );

                                if ($type == 'checkbox') {
                                    $checkboxes[] = $field_data;
                                } else {
                                    $radios[] = $field_data;
                                }
                            } else {
                                $field_data = array(
                                    'element_id' => $type . '-' . $element['element_id'],
                                    'type' => $type,
                                    'cols' => '12',
                                    'required' => false,
                                    'field_label' => !empty($labels) ? implode(' ', $labels) : $label,
                                    'placeholder' => '',
                                    'validation' => false,
                                );

                                if ($type == 'select') {
                                    $field_data['options'] = $options;
                                }

                                $wrappers[] = array(
                                    'wrapper_id' => 'wrapper-' . mt_rand(1000000000000, 9999999999999) . '-' . mt_rand(1000, 9999),
                                    'fields' => array(
                                        $field_data
                                    ),
                                );
                            }

                            $pages[$page_key]['elements'][$element_key]['value'] = '{' . $type . '-' . $element['element_id'] . '}';
                            if (isset($element['properties']['esig'])) {
                                unset($pages[$page_key]['elements'][$element_key]['properties']['esig']);
                            }
                        }
                    }
                }
            }

            foreach ($checkboxes as $element) {
                $wrappers[] = array(
                    'wrapper_id' => 'wrapper-' . mt_rand(1000000000000, 9999999999999) . '-' . mt_rand(1000, 9999),
                    'fields' => array(
                        array(
                            'element_id' => 'checkbox-' . $element['element_id'],
                            'type' => 'checkbox',
                            'cols' => '12',
                            'required' => false,
                            'options' => $element['options'],
                            'field_label' => $element['field_label'],
                            'placeholder' => '',
                            'validation' => false,
                        ),
                    ),
                );
            }

            foreach ($radios as $element) {
                $wrappers[] = array(
                    'wrapper_id' => 'wrapper-' . mt_rand(1000000000000, 9999999999999) . '-' . mt_rand(1000, 9999),
                    'fields' => array(
                        array(
                            'element_id' => 'radio-' . $element['element_id'],
                            'type' => 'radio',
                            'cols' => '12',
                            'required' => false,
                            'options' => $element['options'],
                            'field_label' => $element['field_label'],
                            'placeholder' => '',
                            'validation' => false,
                        ),
                    ),
                );
            }

            $template->set('pages', $pages);
            $settings = array(
                'formName' => $template->get('title'),
                'thankyou' => "true",
                'thankyou-message' => sprintf(__("Success. [e2pdf-download id=\"%s\"]", 'e2pdf'), $template->get('ID')),
                'use-custom-submit' => "true",
                'custom-submit-text' => __("Send Message", 'forminator'),
                'use-custom-invalid-form' => "true",
                'custom-invalid-form-message' => __("Error: Your form is not valid, please fix the errors!", 'forminator'),
                'enable-ajax' => "true",
                'validation' => "on_submit"
            );

            if (class_exists('Forminator_API')) {
                if ($item = Forminator_API::add_form($template->get('title'), $wrappers, $settings)) {
                    $template->set('item', $item);
                }
            }
        }

        return $template;
    }

    /**
     * Init Visual Mapper data
     * 
     * @return bool|string - HTML data source for Visual Mapper
     */
    public function visual_mapper() {

        $item = $this->get('item');
        $html = '';
        $source = '';

        if ($item && class_exists("Forminator_API") && class_exists('Forminator_CForm_Front')) {

            $custom_form = Forminator_API::get_form($item);
            if (is_wp_error($custom_form)) {
                return __('Form could not be found', 'e2pdf');
            }

            $view = Forminator_CForm_Front::get_instance();

            if (class_exists('Forminator_Custom_Form_Model')) {
                $view->model = Forminator_Custom_Form_Model::model()->load($item);
            } elseif (class_exists('Forminator_Form_Model')) {
                $view->model = Forminator_Form_Model::model()->load($item);
            } else {
                return __('Something went wrong', 'e2pdf');
            }

            $fields = $view->get_fields();
            if (!empty($fields)) {
                foreach ($fields as $field_key => $field) {
                    if ("page-break" === $field["type"] && isset($field['element_id'])) {
                        $view->model->remove_field($field_key);
                    }
                }
            }

            $form = $view->get_html(true, true);
            ob_start();
            $view->print_styles();
            $styles = ob_get_clean();

            if ($form) {
                $source = $styles . $form;
            }

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

                $use_labels = true;
                if ((defined('FORMINATOR_VERSION') && version_compare(FORMINATOR_VERSION, '1.14.10', '<')) || (!empty($custom_form->settings['print_value']) && filter_var($custom_form->settings['print_value'], FILTER_VALIDATE_BOOLEAN))) {
                    $use_labels = false;
                }

                $xml = $this->helper->load('xml');
                $xml->set('dom', $dom);
                $xpath = new DomXPath($dom);

                $remove_by_class = array(
                    'forminator-pagination-submit',
                    'forminator-response-message'
                );
                foreach ($remove_by_class as $key => $class) {
                    $elements = $xpath->query("//*[contains(@class, '{$class}')]");
                    foreach ($elements as $element) {
                        $element->parentNode->removeChild($element);
                    }
                }

                $remove_by_tag = array(
                    'script'
                );
                foreach ($remove_by_tag as $key => $tag) {
                    $elements = $xpath->query("//{$tag}");
                    foreach ($elements as $element) {
                        $element->parentNode->removeChild($element);
                    }
                }

                $inputs = $xpath->query("//*[contains(@class, 'forminator-input') or contains(@class, 'forminator-calculation')]");
                foreach ($inputs as $element) {
                    $element = $xml->set_node_value($element, 'type', 'text');
                }

                //remove pagination
                $pagination = $xpath->query("//*[contains(@class, 'forminator-pagination')]");
                foreach ($pagination as $element) {
                    $element = $xml->set_node_value($element, 'style', '');
                }

                //remove buttons
                $remove_rows = $xpath->query("//*[contains(@class, 'forminator-row')][.//button[contains(concat(' ',@class,' '), ' forminator-button ') and not(contains(@class, 'forminator-upload-button')) and not(contains(@class, 'forminator-button-upload'))]]");
                foreach ($remove_rows as $element) {
                    $element->parentNode->removeChild($element);
                }

                //replace name on fileuploads
                $fileuploads = $xpath->query("//*[contains(@class, 'forminator-upload')]");
                foreach ($fileuploads as $element) {
                    $file = $xpath->query(".//input[contains(@class, 'forminator-input-file')]", $element)->item(0);
                    $button = $xpath->query(".//button[contains(@class, 'forminator-upload-button') or contains(@class, 'forminator-button-upload')]", $element)->item(0);
                    if ($file && $button) {
                        $button = $xml->set_node_value($button, 'type', 'upload');
                        $button = $xml->set_node_value($button, 'name', $xml->get_node_value($file, 'name'));
                    }
                }

                //replace name on multi-fileuploads
                $fileuploads_multi = $xpath->query("//*[contains(@class, 'forminator-multi-upload')]");
                foreach ($fileuploads_multi as $element) {
                    $file = $xpath->query(".//input[contains(@class, 'forminator-input-file')]", $element)->item(0);
                    if ($file) {
                        $file = $xml->set_node_value($file, 'name', str_replace('[]', '', $xml->get_node_value($file, 'name')));
                    }
                }

                //replace name on signatures
                $signatures = $xpath->query("//*[contains(@class, 'forminator-field-signature')]");
                foreach ($signatures as $element) {
                    $button = $xpath->query(".//input[contains(@type, 'hidden')]", $element)->item(0);
                    if ($button) {
                        $button = $xml->set_node_value($button, 'type', 'text');
                        $button = $xml->set_node_value($button, 'value', '');
                        $button = $xml->set_node_value($button, 'name', str_replace('field-', '', $xml->get_node_value($button, 'name')));
                    }
                }

                //replace names on inputs
                $inputs = $xpath->query("//input|//textarea|//select");
                foreach ($inputs as $element) {
                    if ($xml->get_node_value($element, 'type') == 'checkbox') {
                        $element = $xml->set_node_value($element, 'name', str_replace("[]", "", $xml->get_node_value($element, 'name')));

                        //Forminator 1.14.10 Checkbox "Option" Fix
                        if ($use_labels) {
                            $parent = $xpath->query(".//parent::*", $element);
                            if ($parent && $parent->item(0)) {
                                if (strpos($xml->get_node_value($parent->item(0), 'class'), 'forminator-option') !== false) {
                                    $element = $xml->set_node_value($element, 'value', $parent->item(0)->nodeValue);
                                } else {
                                    $label = $xpath->query(".//parent::*/span[not(@aria-hidden)]", $element);
                                    if ($label && $label->item(0)) {
                                        $element = $xml->set_node_value($element, 'value', $label->item(0)->nodeValue);
                                    }
                                }
                            }
                        }
                    }

                    if ($use_labels) {
                        //Forminator 1.14.10 Radio "Option" Fix
                        if ($xml->get_node_value($element, 'type') == 'radio') {
                            $label = $xpath->query(".//parent::*/span[not(@aria-hidden)]", $element);
                            if ($label && $label->item(0)) {
                                $element = $xml->set_node_value($element, 'value', $label->item(0)->nodeValue);
                            }
                        }

                        //Forminator 1.14.10 Select "Option" Fix
                        if ($element->tagName == 'select') {
                            $options = $xpath->query(".//option", $element);
                            if ($options) {
                                foreach ($options as $option) {
                                    $option = $xml->set_node_value($option, 'value', $option->nodeValue);
                                }
                            }
                        }
                    }

                    $parent_repeater = $xpath->query("./ancestor::div[contains(@class, 'forminator-repeater-field')]", $element);
                    if ($parent_repeater && $parent_repeater->item(0)) {
                        $element = $xml->set_node_value($element, 'name', $xml->get_node_value($element, 'name') . ':1');
                    }

                    $element = $xml->set_node_value($element, 'name', '{' . $xml->get_node_value($element, 'name') . '}');

                    if (strpos($xml->get_node_value($element, 'class'), 'forminator-checkbox--input') !== false) {
                        $element = $xml->set_node_value($element, 'class', $xml->get_node_value($element, 'class') . ' forminator-checkbox--design');
                    }
                    if (strpos($xml->get_node_value($element, 'class'), 'forminator-radio--input') !== false) {
                        $element = $xml->set_node_value($element, 'class', $xml->get_node_value($element, 'class') . ' forminator-radio--design');
                    }
                }

                //multiselects
                $multiselect = $xpath->query("//ul[contains(@class, 'forminator-multiselect')]");
                foreach ($multiselect as $element) {
                    $name = '';
                    $options = array();
                    $inputs = $xpath->query(".//*[contains(@class, 'forminator-multiselect--item')]", $element);

                    foreach ($inputs as $sub_element) {
                        $input = $xpath->query(".//input", $sub_element)->item(0);
                        $label = $xpath->query(".//label", $sub_element)->item(0);

                        if ($input && $label) {
                            if ($label->childNodes->item(0)) {
                                $options[] = array(
                                    'value' => $xml->get_node_value($input, 'value'),
                                    'label' => $label->childNodes->item(0)->nodeValue
                                );
                            }
                            $name = $xml->get_node_value($input, 'name');
                        }
                        $sub_element->parentNode->removeChild($sub_element);
                    }

                    $li = $dom->createElement('li');
                    $field_atts = array(
                        'class' => 'forminator-multiselect--item',
                    );
                    foreach ($field_atts as $key => $value) {
                        $attr = $dom->createAttribute($key);
                        $attr->value = $value;
                        $li->appendChild($attr);
                    }
                    $element->appendChild($li);

                    $field = $dom->createElement('select');
                    $field_atts = array(
                        'multiple' => 'multiple',
                        'name' => $name,
                    );
                    foreach ($field_atts as $key => $value) {
                        $attr = $dom->createAttribute($key);
                        $attr->value = $value;
                        $field->appendChild($attr);
                    }

                    $li->appendChild($field);

                    foreach ($options as $option) {
                        $option_field = $dom->createElement('option');
                        $field_atts = array(
                            'value' => $option['value'],
                        );
                        foreach ($field_atts as $key => $value) {
                            $attr = $dom->createAttribute($key);
                            $attr->value = $value;
                            $option_field->appendChild($attr);
                        }

                        $label = $dom->createTextNode($option['label']);
                        $option_field->appendChild($label);
                        $field->appendChild($option_field);
                    }
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

        $item = $this->get('item');

        $response = array();
        $elements = array();
        $fields = array();
        $custom_form = false;

        if (class_exists("Forminator_API")) {
            $custom_form = Forminator_API::get_form($item);
            if (!is_wp_error($custom_form)) {
                $fields = $custom_form->fields;
            }
        }

        if ($custom_form && $fields) {

            $use_labels = true;
            if ((defined('FORMINATOR_VERSION') && version_compare(FORMINATOR_VERSION, '1.14.10', '<')) || (!empty($custom_form->settings['print_value']) && filter_var($custom_form->settings['print_value'], FILTER_VALIDATE_BOOLEAN))) {
                $use_labels = false;
            }

            foreach ($fields as $field_obj) {

                $field = $field_obj->raw;
                $float = true;
                $width = 100 / (12 / $field['cols']);

                $repeater = '';
                if (isset($field_obj->parent_group) && $field_obj->parent_group) {
                    $parent_field = $custom_form->get_field($field_obj->parent_group);
                    if ($parent_field) {
                        if (isset($parent_field['is_repeater']) && $parent_field['is_repeater']) {
                            $repeater = ':1';
                        }
                    }
                }

                switch ($field['type']) {
                    case 'group':
                        if ($field['field_label']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . '%',
                                    'height' => 'auto',
                                    'value' => "<h2>" . $field['field_label'] . "</h2>",
                                )
                            ));
                        }
                        break;

                    case 'name':
                    case 'email':
                    case 'phone':
                    case 'url':
                    case 'upload':
                    case 'number':
                    case 'calculation':
                    case 'date':
                        //multiple name field
                        if ($field['type'] == 'name' && isset($field['multiple_name']) && $field['multiple_name']) {

                            if (!$field['prefix'] && !$field['fname']) {
                                if ($field['mname']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['lname'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['mname_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-middle-name" . $repeater . "}",
                                        )
                                    ));
                                }

                                if ($field['lname']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['mname'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['lname_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-last-name" . $repeater . "}",
                                        )
                                    ));
                                }
                            } else {

                                if ($field['prefix']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['fname'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['prefix_label'],
                                        )
                                    ));

                                    $options_tmp = array();
                                    if (function_exists('forminator_get_name_prefixes')) {
                                        $options = forminator_get_name_prefixes();
                                        foreach ($options as $key => $option) {
                                            $options_tmp[] = $key;
                                        }
                                    }

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-select',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'options' => implode("\n", $options_tmp),
                                            'value' => "{" . $field['element_id'] . "-prefix" . $repeater . "}",
                                        )
                                    ));

                                    if ($field['mname'] && $field['fname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'width' => $field['lname'] ? '100%' : '200%',
                                                'right' => $field['lname'] ? '0' : '-40',
                                                'height' => 'auto',
                                                'value' => $field['mname_label'],
                                            )
                                        ));

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => $field['lname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['lname'] ? '0' : '-40',
                                                'value' => "{" . $field['element_id'] . "-middle-name" . $repeater . "}",
                                            )
                                        ));
                                    } elseif ($field['lname'] && !$field['mname'] && $field['fname']) {

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'width' => $field['mname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['mname'] ? '0' : '-40',
                                                'value' => $field['lname_label'],
                                            )
                                        ));

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => $field['mname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['mname'] ? '0' : '-40',
                                                'value' => "{" . $field['element_id'] . "-last-name" . $repeater . "}",
                                            )
                                        ));
                                    }
                                }

                                if ($field['fname']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['prefix'] ? ($width / 2) . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['fname_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-first-name" . $repeater . "}",
                                        )
                                    ));

                                    if ($field['lname'] && $field['mname'] && $field['prefix']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'width' => $field['mname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['mname'] ? '0' : '-40',
                                                'value' => $field['lname_label'],
                                            )
                                        ));

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => $field['mname'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'right' => $field['mname'] ? '0' : '-40',
                                                'value' => "{" . $field['element_id'] . "-last-name" . $repeater . "}",
                                            )
                                        ));
                                    }
                                }

                                if (!$field['prefix'] || !$field['fname']) {
                                    if ($field['mname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['lname'] ? '20' : '0',
                                                'width' => $field['lname'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => $field['mname_label'],
                                            )
                                        ));
                                    }

                                    if ($field['lname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'float' => $field['mname'] ? true : false,
                                            'properties' => array(
                                                'top' => '20',
                                                'left' => $field['mname'] ? '20' : '0',
                                                'width' => $field['mname'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => $field['lname_label'],
                                            )
                                        ));
                                    }

                                    if ($field['mname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['lname'] ? '20' : '0',
                                                'width' => $field['lname'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => "{" . $field['element_id'] . "-middle-name" . $repeater . "}",
                                            )
                                        ));
                                    }

                                    if ($field['lname']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'float' => $field['mname'] ? true : false,
                                            'properties' => array(
                                                'top' => '5',
                                                'left' => $field['mname'] ? '20' : '0',
                                                'width' => $field['mname'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => "{" . $field['element_id'] . "-last-name" . $repeater . "}",
                                            )
                                        ));
                                    }
                                }
                            }
                        } elseif ($field['type'] == 'date' && $field['field_type'] == 'select') {

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $float,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . "%",
                                    'height' => 'auto',
                                    'value' => $field['field_label'],
                                )
                            ));

                            $days = array();
                            $months = array();
                            $years = array();
                            if (class_exists('Forminator_Date')) {
                                $forminator_date = new Forminator_Date();
                                $options = $forminator_date->get_day();
                                foreach ($options as $option) {
                                    $days[] = $option['value'];
                                }

                                $options = $forminator_date->get_months();
                                foreach ($options as $option) {
                                    $months[] = $option['value'];
                                }

                                $options = $forminator_date->get_years(isset($field['min_year']) ? $field['min_year'] : '', isset($field['max_year']) ? $field['max_year'] : '');
                                foreach ($options as $option) {
                                    $years[] = $option['value'];
                                }
                            }

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '30%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $days),
                                    'value' => "{" . $field['element_id'] . "-month" . $repeater . "}",
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'float' => true,
                                'properties' => array(
                                    'top' => '5',
                                    'left' => '5%',
                                    'right' => '5%',
                                    'width' => '40%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $months),
                                    'value' => "{" . $field['element_id'] . "-day" . $repeater . "}",
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'float' => true,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '30%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $years),
                                    'value' => "{" . $field['element_id'] . "-year" . $repeater . "}",
                                )
                            ));
                        } elseif ($field['type'] == 'date' && $field['field_type'] == 'input') {

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $float,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . "%",
                                    'height' => 'auto',
                                    'value' => $field['field_label'],
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '30%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-month" . $repeater . "}",
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'float' => true,
                                'properties' => array(
                                    'top' => '5',
                                    'left' => '5%',
                                    'right' => '5%',
                                    'width' => '40%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-day" . $repeater . "}",
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'float' => true,
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '30%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-year" . $repeater . "}",
                                )
                            ));
                        } else {

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $float,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . "%",
                                    'height' => 'auto',
                                    'value' => $field['field_label'],
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . $repeater . "}",
                                )
                            ));

                            if (isset($field['description']) && $field['description']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => false,
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => $field['description']
                                    )
                                ));
                            }
                        }
                        break;
                    case 'address':
                        if (!$field['street_address'] && !$field['address_line']) {

                            if (!$field['address_city'] && !$field['address_state']) {
                                if ($field['address_zip']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['address_country'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_zip_label']
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-zip" . $repeater . "}",
                                        )
                                    ));
                                }

                                if ($field['address_country']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['address_zip'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_country_label'],
                                        )
                                    ));

                                    $options_tmp = array();
                                    if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                        $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                        foreach ($options as $option) {
                                            $options_tmp[] = $option['value'];
                                        }
                                        //can be value or label
                                    }

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-select',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'options' => implode("\n", $options_tmp),
                                            'value' => "{" . $field['element_id'] . "-country" . $repeater . "}",
                                        )
                                    ));
                                }
                            } else {

                                if ($field['address_city']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['address_state'] ? $width / 2 . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_city_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-city" . $repeater . "}",
                                        )
                                    ));

                                    if ($field['address_zip'] && $field['address_state']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['address_country'] ? '0' : '-40',
                                                'width' => $field['address_country'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'value' => $field['address_zip_label'],
                                            )
                                        ));

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['address_country'] ? '0' : '-40',
                                                'width' => $field['address_country'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'value' => "{" . $field['element_id'] . "-zip" . $repeater . "}",
                                            )
                                        ));
                                    } elseif ($field['address_country'] && !$field['address_zip'] && $field['address_state']) {

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['address_zip'] ? '0' : '-40',
                                                'width' => $field['address_zip'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'value' => $field['address_country_label'],
                                            )
                                        ));

                                        $options_tmp = array();
                                        if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                            $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                            foreach ($options as $option) {
                                                $options_tmp[] = $option['value'];
                                            }
                                            //can be value or label
                                        }

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-select',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['address_zip'] ? '0' : '-40',
                                                'width' => $field['address_zip'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'options' => implode("\n", $options_tmp),
                                                'value' => "{" . $field['element_id'] . "-country" . $repeater . "}",
                                            )
                                        ));
                                    }
                                }

                                if ($field['address_state']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => true,
                                        'float' => true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => '20',
                                            'right' => '20',
                                            'width' => $field['address_city'] ? ($width / 2) . '%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_state_label'],
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-input',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . "-state" . $repeater . "}",
                                        )
                                    ));

                                    if ($field['address_country'] && $field['address_zip'] && $field['address_city']) {

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['address_zip'] ? '0' : '-40',
                                                'width' => $field['address_zip'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'value' => $field['address_country_label'],
                                            )
                                        ));

                                        $options_tmp = array();
                                        if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                            $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                            foreach ($options as $option) {
                                                $options_tmp[] = $option['value'];
                                            }
                                            //can be value or label
                                        }

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-select',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['address_zip'] ? '0' : '-40',
                                                'width' => $field['address_zip'] ? '100%' : '200%',
                                                'height' => 'auto',
                                                'options' => implode("\n", $options_tmp),
                                                'value' => "{" . $field['element_id'] . "-country" . $repeater . "}",
                                            )
                                        ));
                                    }
                                }

                                if (!$field['address_city'] || !$field['address_state']) {
                                    if ($field['address_zip']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'properties' => array(
                                                'top' => '20',
                                                'right' => $field['address_country'] ? '20' : '0',
                                                'width' => $field['address_country'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => $field['address_zip_label'],
                                            )
                                        ));
                                    }

                                    if ($field['address_country']) {

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-html',
                                            'float' => $field['address_zip'] ? true : false,
                                            'properties' => array(
                                                'top' => '20',
                                                'left' => $field['address_zip'] ? '20' : '0',
                                                'width' => $field['address_zip'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => $field['address_country_label'],
                                            )
                                        ));
                                    }

                                    if ($field['address_zip']) {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'right' => $field['address_country'] ? '20' : '0',
                                                'width' => $field['address_country'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'value' => "{" . $field['element_id'] . "-zip" . $repeater . "}",
                                            )
                                        ));
                                    }

                                    if ($field['address_country']) {
                                        $options_tmp = array();
                                        if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                            $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                            foreach ($options as $option) {
                                                $options_tmp[] = $option['value'];
                                            }
                                            //can be value or label
                                        }

                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-select',
                                            'float' => $field['address_zip'] ? true : false,
                                            'properties' => array(
                                                'top' => '5',
                                                'left' => $field['address_zip'] ? '20' : '0',
                                                'width' => $field['address_zip'] ? '50%' : '100%',
                                                'height' => 'auto',
                                                'options' => implode("\n", $options_tmp),
                                                'value' => "{" . $field['element_id'] . "-country" . $repeater . "}",
                                            )
                                        ));
                                    }
                                }
                            }
                        } else {

                            if ($field['street_address']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'block' => true,
                                    'float' => true,
                                    'properties' => array(
                                        'top' => '20',
                                        'left' => '20',
                                        'right' => '20',
                                        'width' => $width . '%',
                                        'height' => 'auto',
                                        'value' => $field['street_address_label'],
                                    )
                                ));

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-street_address" . $repeater . "}",
                                    )
                                ));
                            }

                            if ($field['address_line']) {

                                if ($field['address_line_label']) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'block' => $field['street_address'] ? false : true,
                                        'float' => $field['street_address'] ? false : true,
                                        'properties' => array(
                                            'top' => '20',
                                            'left' => $field['street_address'] ? '0' : '20',
                                            'right' => $field['street_address'] ? '0' : '20',
                                            'width' => $field['street_address'] ? '100%' : $width . '%',
                                            'height' => 'auto',
                                            'value' => $field['address_line_label'],
                                        )
                                    ));
                                }

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => $field['address_line_label'] ? '0' : '20',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-address_line" . $repeater . "}",
                                    )
                                ));
                            }

                            if ($field['address_city']) {

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'properties' => array(
                                        'top' => '20',
                                        'right' => $field['address_state'] ? '20' : '0',
                                        'width' => $field['address_state'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => $field['address_city_label'],
                                    )
                                ));
                            }

                            if ($field['address_state']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => $field['address_city'] ? true : false,
                                    'properties' => array(
                                        'top' => $field['address_city'] ? '0' : '20',
                                        'left' => $field['address_city'] ? '20' : '0',
                                        'width' => $field['address_city'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => $field['address_state_label'],
                                    )
                                ));
                            }

                            if ($field['address_city']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'right' => $field['address_state'] ? '20' : '0',
                                        'width' => $field['address_state'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-city" . $repeater . "}",
                                    )
                                ));
                            }

                            if ($field['address_state']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'float' => $field['address_city'] ? true : false,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $field['address_city'] ? '20' : '0',
                                        'width' => $field['address_city'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-state" . $repeater . "}",
                                    )
                                ));
                            }

                            if ($field['address_zip']) {

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'properties' => array(
                                        'top' => '20',
                                        'right' => $field['address_country'] ? '20' : '0',
                                        'width' => $field['address_country'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => $field['address_zip_label'],
                                    )
                                ));
                            }

                            if ($field['address_country']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => $field['address_zip'] ? true : false,
                                    'properties' => array(
                                        'top' => '20',
                                        'left' => $field['address_zip'] ? '20' : '0',
                                        'width' => $field['address_zip'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => $field['address_country_label'],
                                    )
                                ));
                            }

                            if ($field['address_zip']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'properties' => array(
                                        'top' => '5',
                                        'right' => $field['address_country'] ? '20' : '0',
                                        'width' => $field['address_country'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . "-zip" . $repeater . "}",
                                    )
                                ));
                            }

                            if ($field['address_country']) {
                                $options_tmp = array();
                                if (function_exists('forminator_to_field_array') && function_exists('forminator_get_countries_list')) {
                                    $options = forminator_to_field_array(forminator_get_countries_list(), true);
                                    foreach ($options as $option) {
                                        $options_tmp[] = $option['value'];
                                    }
                                    //can be value or label
                                }

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-select',
                                    'float' => $field['address_zip'] ? true : false,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $field['address_zip'] ? '20' : '0',
                                        'width' => $field['address_zip'] ? '50%' : '100%',
                                        'height' => 'auto',
                                        'options' => implode("\n", $options_tmp),
                                        'value' => "{" . $field['element_id'] . "-country" . $repeater . "}",
                                    )
                                ));
                            }
                        }
                        break;
                    case 'time':
                        $hours = array();
                        $minutes = array();

                        if (class_exists('Forminator_Time')) {
                            $forminator_time = new Forminator_Time();
                            $options = $forminator_time->get_hours($field['time_type'], '', '', false);
                            foreach ($options as $option) {
                                $hours[] = $option['value'];
                            }

                            $options = $forminator_time->get_minutes($field['time_type'], '', '', false);
                            foreach ($options as $option) {
                                $minutes[] = $option['value'];
                            }
                        }

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => ($field['time_type'] == 'twelve' ? $width / 3 : $width / 2) . '%',
                                'height' => 'auto',
                                'value' => $field['hh_label'],
                            )
                        ));

                        if ($field['field_type'] == 'select') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $hours),
                                    'value' => "{" . $field['element_id'] . "-hours" . $repeater . "}",
                                )
                            ));
                        } else {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-hours" . $repeater . "}",
                                )
                            ));
                        }

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => ($field['time_type'] == 'twelve' ? $width / 3 : $width / 2) . '%',
                                'height' => 'auto',
                                'value' => $field['mm_label'],
                            )
                        ));

                        if ($field['field_type'] == 'select') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $minutes),
                                    'value' => "{" . $field['element_id'] . "-minutes" . $repeater . "}",
                                )
                            ));
                        } else {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . "-minutes" . $repeater . "}",
                                )
                            ));
                        }

                        if ($field['time_type'] == 'twelve') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'float' => $float,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => ($width / 3) . '%',
                                    'height' => 'auto',
                                    'value' => '',
                                )
                            ));

                            $ampm = array(
                                'am', 'pm'
                            );

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $ampm),
                                    'value' => "{" . $field['element_id'] . "-ampm" . $repeater . "}",
                                )
                            ));
                        }
                        break;
                    case 'html':
                        if ($field['field_label']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . '%',
                                    'height' => 'auto',
                                    'value' => $field['field_label'],
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field['variations'],
                                )
                            ));
                        } else {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . '%',
                                    'height' => 'auto',
                                    'value' => $field['variations'],
                                )
                            ));
                        }
                        break;
                    case 'section':
                        $section = '';
                        if ($field['section_title']) {
                            $section .= "<h2>" . $field['section_title'] . "</h2>";
                        }
                        if (isset($field['section_subtitle']) && $field['section_subtitle']) {
                            $section .= $field['section_subtitle'];
                        }
                        if ($section) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => $width . '%',
                                    'height' => 'auto',
                                    'value' => $section,
                                )
                            ));
                        }
                        break;
                    case 'text':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));

                        if (isset($field['input_type']) && $field['input_type'] == 'line') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . $repeater . "}",
                                )
                            ));
                        } else {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-textarea',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => "{" . $field['element_id'] . $repeater . "}",
                                )
                            ));
                        }

                        if (isset($field['description']) && $field['description']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'float' => false,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field['description']
                                )
                            ));
                        }
                        break;
                    case 'textarea':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-textarea',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => "{" . $field['element_id'] . $repeater . "}",
                            )
                        ));

                        if (isset($field['description']) && $field['description']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'float' => false,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field['description']
                                )
                            ));
                        }
                        break;
                    case 'signature':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-signature',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => '150',
                                'scale' => '1',
                                'dimension' => '1',
                                'value' => "{" . $field['element_id'] . $repeater . "}",
                            )
                        ));

                        if (isset($field['description']) && $field['description']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'float' => false,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field['description']
                                )
                            ));
                        }
                        break;
                    case 'select':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));

                        if ($field['value_type'] == 'radio') {
                            foreach ($field['options'] as $opt_key => $option) {
                                if (is_array($option)) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-radio',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . $repeater . "}",
                                            'option' => $option['value'] && !$use_labels ? $option['value'] : $option['label'],
                                            'group' => "{" . $field['element_id'] . $repeater . "}",
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
                        } else {
                            $options_tmp = array();
                            foreach ($field['options'] as $option) {
                                $options_tmp[] = $option['value'] && !$use_labels ? $option['value'] : $option['label'];
                            }
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $options_tmp),
                                    'value' => "{" . $field['element_id'] . $repeater . "}",
                                )
                            ));
                        }

                        if (isset($field['description']) && $field['description']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'float' => false,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field['description']
                                )
                            ));
                        }
                        break;
                    case 'consent':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-checkbox',
                            'properties' => array(
                                'top' => '5',
                                'width' => 'auto',
                                'height' => 'auto',
                                'value' => "{" . $field['element_id'] . $repeater . "}",
                                'option' => 'checked'
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'float' => true,
                            'properties' => array(
                                'left' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field['consent_description']
                            )
                        ));
                        break;
                    case 'checkbox':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));

                        if ($field['value_type'] == 'multiselect') {
                            $options_tmp = array();
                            foreach ($field['options'] as $opt_key => $option) {
                                $options_tmp[] = $option['value'] && !$use_labels ? $option['value'] : $option['label'];
                            }
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => '44',
                                    'multiline' => '1',
                                    'options' => implode("\n", $options_tmp),
                                    'value' => "{" . $field['element_id'] . $repeater . "}",
                                )
                            ));
                        } else {
                            foreach ($field['options'] as $opt_key => $option) {
                                if (is_array($option)) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-checkbox',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => "{" . $field['element_id'] . $repeater . "}",
                                            'option' => $option['value'] && !$use_labels ? $option['value'] : $option['label']
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

                        if (isset($field['description']) && $field['description']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'float' => false,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field['description']
                                )
                            ));
                        }
                        break;

                    case 'radio':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => $field['field_label'],
                            )
                        ));

                        foreach ($field['options'] as $opt_key => $option) {
                            if (is_array($option)) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-radio',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => 'auto',
                                        'height' => 'auto',
                                        'value' => "{" . $field['element_id'] . $repeater . "}",
                                        'option' => $option['value'] && !$use_labels ? $option['value'] : $option['label'],
                                        'group' => "{" . $field['element_id'] . $repeater . "}",
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

                        if (isset($field['description']) && $field['description']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'float' => false,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field['description']
                                )
                            ));
                        }
                        break;

                    case 'gdprcheckbox':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'float' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => $width . '%',
                                'height' => 'auto',
                                'value' => '',
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-checkbox',
                            'properties' => array(
                                'top' => '5',
                                'width' => 'auto',
                                'height' => 'auto',
                                'value' => "{" . $field['element_id'] . $repeater . "}",
                                'option' => 'true'
                            )
                        ));
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'float' => true,
                            'properties' => array(
                                'left' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field['gdpr_description']
                            )
                        ));
                        break;
                    default:
                        break;
                }
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
        add_action('forminator_custom_form_submit_before_set_fields', array($this, 'action_forminator_custom_form_submit_before_set_fields'), 30, 3);
        add_action('forminator_custom_form_mail_admin_sent', array($this, 'action_forminator_custom_form_mail_admin_sent'), 30, 5);
    }

    /**
     * Load filters for this extension
     */
    public function load_filters() {
        add_filter('forminator_custom_form_mail_admin_message', array($this, 'filter_forminator_mail_message'), 30, 5);
        add_filter('forminator_custom_form_submit_response', array($this, 'filter_forminator_custom_form_submit_response'), 30, 2);
        add_filter('forminator_custom_form_ajax_submit_response', array($this, 'filter_forminator_custom_form_submit_response'), 30, 2);

        // Forminator 1.14.11 compatibility fix
        add_filter('forminator_form_submit_response', array($this, 'filter_forminator_custom_form_submit_response'), 30, 2);
        add_filter('forminator_form_ajax_submit_response', array($this, 'filter_forminator_custom_form_submit_response'), 30, 2);
    }

    public function action_forminator_custom_form_submit_before_set_fields($entry, $form_id, $field_data_array) {
        if (class_exists('Forminator_API')) {
            $form = Forminator_API::get_form($form_id);
            if (!is_wp_error($form)) {
                if ($form->is_prevent_store()) {
                    $this->set('field_data_array', $field_data_array);
                    $this->set('dataset', 'is_prevent_store');
                } elseif ($entry && isset($entry->entry_id)) {
                    $this->set('dataset', $entry->entry_id);
                }
            }
        }
    }

    public function action_forminator_custom_form_mail_admin_sent($mail, $custom_form, $data, $entry, $recipients) {
        remove_filter('wp_mail', array($this, 'filter_wp_mail'), 30);
        $files = $this->helper->get('forminator_attachments');
        if (is_array($files) && !empty($files)) {

            $saved = $this->helper->get('forminator_saved_attachments');
            if (!is_array($saved)) {
                $saved = array();
            }
            foreach ($files as $key => $file) {
                if (!in_array($file, $saved)) {
                    $this->helper->delete_dir(dirname($file) . '/');
                }
            }
            $this->helper->deset('forminator_attachments');
            $this->helper->deset('forminator_saved_attachments');
        }
    }

    public function filter_forminator_mail_message($message, $custom_form, $data, $entry, $mail) {
        if (isset($message) && false !== strpos($message, '[')) {
            $shortcode_tags = array(
                'e2pdf-download',
                'e2pdf-save',
                'e2pdf-attachment',
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
                    $file = false;
                    if (!isset($atts['dataset']) && isset($atts['id'])) {
                        $template = new Model_E2pdf_Template();
                        $template->load($atts['id']);
                        if ($template->get('extension') === 'forminator') {

                            if ($this->get('dataset')) {
                                $entry_id = $this->get('dataset');
                            } else {
                                $entry_id = isset($entry->entry_id) ? $entry->entry_id : false;
                            }
                            if ($entry_id) {
                                if (($shortcode[2] === 'e2pdf-download' || $shortcode['2'] === 'e2pdf-view' || $shortcode['2'] === 'e2pdf-zapier') && $entry_id == 'is_prevent_store') {
                                    //data is empty to fill pdf
                                } else {
                                    if ($entry_id == 'is_prevent_store') {
                                        add_filter('e2pdf_model_shortcode_extension_options', array($this, 'filter_e2pdf_model_shortcode_extension_options'), 30, 2);
                                    }
                                    $atts['dataset'] = $entry_id;
                                    $shortcode[3] .= ' dataset="' . $entry_id . '"';
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

                    if (($shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $shortcode[2] === 'e2pdf-attachment') {
                        $file = do_shortcode_tag($shortcode);
                        if ($file) {
                            if ($shortcode[2] === 'e2pdf-save' || isset($atts['pdf'])) {
                                $this->helper->add('forminator_saved_attachments', $file);
                            }
                            $this->helper->add('forminator_attachments', $file);
                        }
                        $message = str_replace($shortcode_value, '', $message);
                    } else {
                        $message = str_replace($shortcode_value, do_shortcode_tag($shortcode), $message);
                    }
                    remove_filter('e2pdf_model_shortcode_extension_options', array($this, 'filter_e2pdf_model_shortcode_extension_options'), 30);
                }

                add_filter('wp_mail', array($this, 'filter_wp_mail'), 30, 1);
            }
        }
        return $message;
    }

    public function filter_e2pdf_model_shortcode_extension_options($options, $template) {
        if ($this->get('dataset') && $this->get('dataset') == 'is_prevent_store') {
            $options['field_data_array'] = $this->get('field_data_array');
        }
        return $options;
    }

    public function filter_pre_filter($response, $form_id) {
        if (isset($response['message']) && false !== strpos($response['message'], '[') && isset($response['success']) && $response['success']) {
            $model_e2pdf_filter = new Model_E2pdf_Filter();
            $response['message'] = $model_e2pdf_filter->pre_filter($response['message']);
        }
        return $response;
    }

    public function filter_filter($response, $form_id) {
        if (isset($response['message']) && false !== strpos($response['message'], '[') && isset($response['success']) && $response['success']) {
            $model_e2pdf_filter = new Model_E2pdf_Filter();
            $response['message'] = $model_e2pdf_filter->filter($response['message']);
        }
        return $response;
    }

    public function filter_forminator_custom_form_submit_response($response, $form_id) {

        if (isset($response['message']) && false !== strpos($response['message'], '[') && isset($response['success']) && $response['success']) {
            $shortcode_tags = array(
                'e2pdf-download',
                'e2pdf-save',
                'e2pdf-view',
                'e2pdf-adobesign',
                'e2pdf-zapier'
            );

            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $response['message'], $matches);

            $tagnames = array_intersect($shortcode_tags, $matches[1]);

            if (!empty($tagnames)) {

                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                preg_match_all("/$pattern/", $response['message'], $shortcodes);
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
                            if ($template->get('extension') === 'forminator') {
                                $entry_id = $this->get('dataset');
                                if ($entry_id) {
                                    if (($shortcode[2] === 'e2pdf-download' || $shortcode['2'] === 'e2pdf-view' || $shortcode['2'] === 'e2pdf-zapier') && $entry_id == 'is_prevent_store') {
                                        //data is empty to fill pdf
                                    } else {
                                        if ($entry_id == 'is_prevent_store') {
                                            add_filter('e2pdf_model_shortcode_extension_options', array($this, 'filter_e2pdf_model_shortcode_extension_options'), 30, 2);
                                        }
                                        $atts['dataset'] = $entry_id;
                                        $shortcode[3] .= ' dataset="' . $entry_id . '"';
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

                        $response['message'] = str_replace($shortcode_value, do_shortcode_tag($shortcode), $response['message']);
                        remove_filter('e2pdf_model_shortcode_extension_options', array($this, 'filter_e2pdf_model_shortcode_extension_options'), 30);
                    }
                }
            }
        }
        return $response;
    }

    public function filter_wp_mail($args = array()) {
        $files = $this->helper->get('forminator_attachments');
        if (is_array($files) && !empty($files)) {
            foreach ($files as $file) {
                $args['attachments'][] = $file;
            }
        }
        $wp_mail = array(
            'to' => $args['to'],
            'subject' => $args['subject'],
            'message' => $args['message'],
            'headers' => $args['headers'],
            'attachments' => $args['attachments'],
        );

        return $wp_mail;
    }

    /**
     * Get styles for generating Map Field function
     * 
     * @return array - List of css files to load
     */
    public function styles($item = false) {
        $styles = array();
        if (function_exists('forminator_plugin_url') && class_exists("Forminator_API")) {
            if (defined('FORMINATOR_VERSION')) {
                $version = FORMINATOR_VERSION;
            } else {
                $version = '0';
            }


            $styles = array();
            if ($item) {
                $forminator_form = Forminator_API::get_form($item);
                if (!is_wp_error($forminator_form)) {
                    $form_settings = isset($forminator_form->settings) ? $forminator_form->settings : array();
                    if (isset($form_settings['form-style']) && $form_settings['form-style']) {
                        $form_design = $form_settings['form-style'];
                    } else {
                        $form_design = 'default';
                    }

                    $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/forminator-icons.min.css?v=' . $version;
                    $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/src/forminator-utilities.min.css?v=' . $version;

                    if (isset($form_settings['fields-style']) && 'open' === $form_settings['fields-style']) {
                        $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/src/grid/forminator-grid.open.min.css?v=' . $version;
                    } else if (isset($form_settings['fields-style']) && 'enclosed' === $form_settings['fields-style']) {
                        $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/src/grid/forminator-grid.enclosed.min.css?v=' . $version;
                    }
                    if ('none' !== $form_design) {
                        $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/src/form/forminator-form-' . $form_design . '.base.min.css?v=' . $version;
                        $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/src/form/forminator-form-' . $form_design . '.full.min.css?v=' . $version;
                        $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/src/form/forminator-form-' . $form_design . '.pagination.min.css?v=' . $version;
                        $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/src/form/select2.min.css?v=' . $version;
                        $styles[] = forminator_plugin_url() . 'assets/forminator-ui/css/src/form/forminator-authentication.min.css?v=' . $version;
                    }
                    $styles[] = plugins_url('css/extension/forminator.css?v=' . time(), $this->helper->get('plugin_file_path'));
                }
            }
        }
        return $styles;
    }

}
