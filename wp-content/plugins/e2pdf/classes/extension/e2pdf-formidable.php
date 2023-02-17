<?php

/**
 * E2pdf Formidable Extension
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

class Extension_E2pdf_Formidable extends Model_E2pdf_Model {

    private $options;
    private $info = array(
        'key' => 'formidable',
        'title' => 'Formidable Forms'
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

        if (is_plugin_active('formidable/formidable.php') || defined('E2PDF_FORMIDABLE_EXTENSION')) {
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

        if (class_exists('FrmForm')) {
            $where = array(
                'is_template' => 0,
                'status' => 'published',
                array(
                    'or' => 1,
                    'parent_form_id' => null,
                    'parent_form_id <' => 1,
                ),
            );

            $forms = FrmForm::getAll($where, 'name');
            if ($forms) {
                foreach ($forms as $key => $value) {
                    $content[] = $this->item($value->id);
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

        $item = (int) $item;
        $datasets = array();

        if (class_exists('FrmEntry') && $item) {
            $where = array(
                'it.form_id' => $item
            );

            $datasets_tmp = FrmEntry::getAll($where, ' ORDER BY id DESC');

            if ($datasets_tmp) {
                foreach ($datasets_tmp as $key => $dataset) {
                    $this->set('item', $item);
                    $this->set('dataset', $dataset->id);

                    $dataset_title = $this->render($name);
                    if (!$dataset_title) {
                        $dataset_title = $dataset->item_key;
                    }
                    $datasets[] = array(
                        'key' => $dataset->id,
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
        $data->url = $this->helper->get_url(array('page' => 'formidable-entries', 'frm_action' => 'show', 'id' => $dataset));

        return $data;
    }

    /**
     * Get item
     * 
     * @param int $item - Item ID
     * 
     * @return object - Item
     */
    public function item($item = false) {

        $item = (int) $item;
        if (!$item && $this->get('item')) {
            $item = $this->get('item');
        }

        $form = new stdClass();

        $formidable_form = false;
        if (class_exists('FrmForm')) {
            $formidable_form = FrmForm::getOne($item);
        }

        if ($formidable_form) {
            $form->id = (string) $item;
            $form->url = $this->helper->get_url(array('page' => 'formidable', 'frm_action' => 'edit', 'id' => $item));
            $form->name = $formidable_form->name;
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
     * Load actions for this extension
     */
    public function load_actions() {
        add_action('frm_notification', array($this, 'action_frm_notification'), 30, 3);
    }

    /**
     * Load filters for this extension
     */
    public function load_filters() {

        if (!get_option('e2pdf_formidable_disable_filter')) {
            add_filter('frm_display_entry_content', array(new Model_E2pdf_Filter(), 'pre_filter'), 9, 1);
            add_filter('frm_display_entry_content', array(new Model_E2pdf_Filter(), 'filter'), 25, 1);
            add_filter('frm_display_entry_content', array($this, 'filter_frm_display_entry_content'), 30, 1);
            add_filter('frm_content', array(new Model_E2pdf_Filter(), 'pre_filter'), 10, 1);
            add_filter('frm_content', array(new Model_E2pdf_Filter(), 'filter'), 25, 1);
        }

        add_filter('frm_content', array($this, 'filter_frm_content'), 30, 3);
        add_filter('frm_image_html_array', array($this, 'filter_frm_image_html_array'), 10, 2);
        add_filter('frm_notification_attachment', array($this, 'filter_frm_notification_attachment'), 30, 3);
        add_filter('e2pdf_model_options_get_options_options', array($this, 'filter_e2pdf_model_options_get_options_options'), 10, 1);
        add_filter('e2pdf_controller_templates_backup_options', array($this, 'filter_e2pdf_controller_templates_backup_options'), 10, 3);
        add_filter('e2pdf_controller_templates_import_options', array($this, 'filter_e2pdf_controller_templates_import_options'), 10, 3);

        // Backup Pages and Actions
        add_filter('e2pdf_controller_templates_backup_pages', array($this, 'filter_e2pdf_controller_templates_backup_pages'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_actions', array($this, 'filter_e2pdf_controller_templates_backup_actions'), 10, 4);

        // Backup Shortcodes
        add_filter('e2pdf_controller_templates_backup_dataset_title', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_dataset_title1', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_dataset_title2', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_button_title', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_name', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_password', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_meta_title', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_meta_subject', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_meta_author', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);
        add_filter('e2pdf_controller_templates_backup_meta_keywords', array($this, 'filter_e2pdf_controller_templates_backup_replace_shortcodes'), 10, 4);

        // Import Pages and Actions
        add_filter('e2pdf_controller_templates_import_pages', array($this, 'filter_e2pdf_controller_templates_import_pages'), 10, 5);
        add_filter('e2pdf_controller_templates_import_actions', array($this, 'filter_e2pdf_controller_templates_import_actions'), 10, 5);

        // Import Shortcodes
        add_filter('e2pdf_controller_templates_import_dataset_title', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_dataset_title1', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_dataset_title2', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_button_title', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_name', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_password', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_meta_title', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_meta_subject', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_meta_author', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
        add_filter('e2pdf_controller_templates_import_meta_keywords', array($this, 'filter_e2pdf_controller_templates_import_replace_shortcodes'), 10, 5);
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

        $args = $this->get('args');
        $user_id = $this->get('user_id');

        $template_id = $this->get('template_id') ? $this->get('template_id') : '0';
        $element_id = isset($field['element_id']) ? $field['element_id'] : '0';

        $form = false;
        $entry = false;
        $item = false;
        $dataset = false;
        $entry2 = false;
        $item2 = false;
        $dataset2 = false;
        $maybe_checkbox_separated = false;

        if ($this->verify()) {

            if ($this->get('item') == '-2') {
                if ($this->get('dataset') && $this->get('dataset2')) {
                    $item = $this->get('item1');
                    $dataset = $this->get('dataset');
                    $form = FrmForm::getOne($this->get('item1'));
                    $entry = FrmEntry::getOne($this->get('dataset'));

                    $item2 = $this->get('item2');
                    $dataset2 = $this->get('dataset2');
                    $form2 = FrmForm::getOne($this->get('item2'));
                    $entry2 = FrmEntry::getOne($this->get('dataset2'));
                } elseif ($this->get('dataset')) {
                    $item = $this->get('item1');
                    $dataset = $this->get('dataset');
                    $form = FrmForm::getOne($this->get('item1'));
                    $entry = FrmEntry::getOne($this->get('dataset'));
                } elseif ($this->get('dataset2')) {
                    $item2 = $this->get('item2');
                    $dataset2 = $this->get('dataset2');
                    $form2 = FrmForm::getOne($this->get('item2'));
                    $entry2 = FrmEntry::getOne($this->get('dataset2'));
                }
            } else {
                $item = $this->get('item');
                $dataset = $this->get('dataset');
                $form = FrmForm::getOne($this->get('item'));
                if ($this->get('ff_transient_entry')) {
                    $entry = get_transient($this->get('ff_transient_entry'));
                } else {
                    $entry = FrmEntry::getOne($dataset);
                }
            }

            $args = apply_filters('e2pdf_extension_render_shortcodes_args', $args, $element_id, $template_id, $item, $dataset, $item2, $dataset2);

            if (false !== strpos($value, '[')) {

                $replace = array(
                    '[e2pdf-dataset]' => $dataset ? $dataset : '',
                    '[e2pdf-dataset2]' => $dataset2 ? $dataset2 : '',
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
                                    'e2pdf_extension_formidable_render_shortcodes_site_url'
                                ))
                        );
                    }

                    $replace = array(
                        '[e2pdf-url]' => $pdf_url
                    );
                    $value = str_replace(array_keys($replace), $replace, $value);
                }

                // Start render Separatable fields shortcode
                if (class_exists('FrmProEntry')) {
                    $shortcode_tags = array();
                    preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $value, $matches);
                    $tagnames = array_intersect($shortcode_tags, $matches[1]);

                    foreach ($matches[1] as $key => $shortcode) {
                        if (strpos($shortcode, ':') !== false) {
                            $shortcode_tags[] = $shortcode;
                        }
                    }

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

                            $shortcode_details = explode(":", $shortcode[2]);

                            $field_id = $shortcode_details['0'];
                            $child_id = $shortcode_details['1'];
                            $child_entry = 0;

                            $child_field = false;
                            $child_form = false;

                            if (class_exists("FrmField")) {
                                $child_field = FrmField::getOne($field_id);
                                if ($child_field) {
                                    $child_form = $child_field->form_id;
                                }
                            }

                            $child_entries = array();
                            if ($this->get('dataset')) {
                                $childs = FrmEntry::getAll(array('parent_item_id' => $dataset), ' ORDER BY it.id ASC', '', true, false);
                                if ($childs && $child_form) {
                                    foreach ($childs as $child) {
                                        if ($child->form_id == $child_form) {
                                            $child_entries[] = $child->id;
                                        }
                                    }
                                }
                            }

                            if ($this->get('item') == '-2') {
                                if ($this->get('dataset2')) {
                                    $childs = FrmEntry::getAll(array('parent_item_id' => $dataset2), ' ORDER BY it.id ASC', '', true, false);
                                    if ($childs && $child_form) {
                                        foreach ($childs as $child) {
                                            if ($child->form_id == $child_form) {
                                                $child_entries[] = $child->id;
                                            }
                                        }
                                    }
                                }
                            }

                            $new_shortcode = "";
                            if (!empty($child_entries) && count($child_entries) >= $child_id) {
                                $start = 1;
                                foreach ($child_entries as $key => $sub_entry) {
                                    if ($start == $child_id) {
                                        $child_entry = $sub_entry;
                                        break;
                                    }
                                    $start++;
                                }

                                $shortcode[2] = "frm-field-value field_id={$field_id} entry={$child_entry}";
                                $new_shortcode = "[" . $shortcode[2] . $shortcode[3] . "]";
                            }

                            $maybe_checkbox_separated = true;
                            $value = str_replace($shortcode_value, $new_shortcode, $value);
                        }
                    }
                }
                // End render Separatable fields shortcode

                $shortcode_tags = array(
                    'e2pdf-user',
                    'e2pdf-arg',
                    'e2pdf-frm-lookup-values',
                    'e2pdf-frm-data-values',
                    'default-message',
                    'default_message',
                    'default-message2',
                    'default_message2'
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
                        } elseif ($shortcode['2'] === 'e2pdf-frm-lookup-values' || $shortcode['2'] === 'e2pdf-frm-data-values') {
                            if (!isset($atts['user_id']) && $user_id) {
                                $shortcode[3] .= ' user_id="' . $user_id . '"';
                                $value = str_replace($shortcode_value, "[" . $shortcode['2'] . $shortcode['3'] . "]", $value);
                            }
                        } elseif (($shortcode['2'] === 'default-message' || $shortcode['2'] === 'default_message') && $dataset && class_exists('FrmEntriesHelper')) {
                            $default_message = FrmEntriesHelper::replace_default_message(
                                            $shortcode_value,
                                            array(
                                                'id' => $entry->id,
                                                'entry' => clone $entry,
                                                'plain_text' => isset($atts['is_plain_text']) && $atts['is_plain_text'] == 'true' ? true : false,
                                                'user_info' => isset($atts['user_info']) && $atts['user_info'] == 'true' ? true : false
                                            )
                            );
                            $value = str_replace($shortcode_value, $default_message, $value);
                        } elseif (($shortcode['2'] === 'default-message2' || $shortcode['2'] == 'default_message2') && $dataset2 && class_exists('FrmEntriesHelper')) {
                            $default_message = FrmEntriesHelper::replace_default_message(
                                            str_replace(array('default-message2', 'default_message2'), array('default-message', 'default_message'), $shortcode_value),
                                            array(
                                                'id' => $entry2->id,
                                                'entry' => clone $entry2,
                                                'plain_text' => isset($atts['is_plain_text']) && $atts['is_plain_text'] == 'true' ? true : false,
                                                'user_info' => isset($atts['user_info']) && $atts['user_info'] == 'true' ? true : false
                                            )
                            );
                            $value = str_replace($shortcode_value, $default_message, $value);
                        }
                    }
                }

                $shortcode_tags = array(
                    'e2pdf-format-number',
                    'e2pdf-format-date',
                    'e2pdf-format-output',
                    'frm-math'
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


                /*
                 * Do not replace inside [foreach]
                 * $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach\](*SKIP)(*FAIL)|\[id\]/is', $entry->id, $value);
                 */

                if (strpos($value, '[foreach') !== false) {
                    if ($dataset) {
                        $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach(*SKIP)(*FAIL)|\[id\]/is', $entry->id, $value);
                        $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach(*SKIP)(*FAIL)|\[key\]/is', $entry->item_key, $value);
                    } else {
                        $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach(*SKIP)(*FAIL)|\[id\]/is', '', $value);
                        $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach(*SKIP)(*FAIL)|\[key\]/is', '', $value);
                    }

                    if ($dataset2) {
                        $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach(*SKIP)(*FAIL)|\[id2\]/is', $entry2->id, $value);
                        $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach(*SKIP)(*FAIL)|\[key2\]/is', $entry2->item_key, $value);
                    } else {
                        $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach(*SKIP)(*FAIL)|\[id2\]/is', '', $value);
                        $value = preg_replace('/\[foreach[^\]]*?\](.*?)\[\/foreach(*SKIP)(*FAIL)|\[key2\]/is', '', $value);
                    }
                } else {
                    $replace = array(
                        '[id]' => $dataset ? $entry->id : '',
                        '[key]' => $dataset ? $entry->item_key : '',
                        '[id2]' => $dataset2 ? $entry2->id : '',
                        '[key2]' => $dataset2 ? $entry2->item_key : '',
                    );

                    $value = str_replace(array_keys($replace), $replace, $value);
                }
            }

            /*
             * Checkboxes separatable fix filter add
             * @since 0.01.43
             */
            if ($maybe_checkbox_separated) {
                add_filter('frm_display_value_custom', array($this, 'filter_frm_display_value_custom'), 0, 3);
            }

            $value = apply_filters('e2pdf_extension_render_shortcodes_pre_do_shortcode', $value, $element_id, $template_id, $item, $dataset, $item2, $dataset2);
            $value = do_shortcode($value);
            $value = apply_filters('e2pdf_extension_render_shortcodes_after_do_shortcode', $value, $element_id, $template_id, $item, $dataset, $item2, $dataset2);

            /*
             * Checkboxes separatable fix filter remove
             * @since 0.01.43
             */
            if ($maybe_checkbox_separated) {
                remove_filter('frm_display_value_custom', array($this, 'filter_frm_display_value_custom'), 0);
            }

            //Bug fix for Formidable Forms Signature (2.0.1)
            $upd_signature = false;
            if (class_exists('FrmFieldFactory')) {
                $sig_obj = FrmFieldFactory::get_field_type('signature');
                if ($sig_obj->get_display_value('signature') == '') {
                    $upd_signature = true;
                }
            }

            if ($upd_signature) {
                remove_filter('frm_get_signature_display_value', 'FrmSigAppController::display_signature', 10);
                remove_filter('frmpro_fields_replace_shortcodes', 'FrmSigAppController::custom_display_signature', 10);
                add_filter('frm_keep_signature_value_array', array($this, 'filter_frm_keep_signature_value_array'), 10, 2);
            }

            add_filter('frmpro_fields_replace_shortcodes', array($this, 'filter_frmpro_fields_replace_shortcodes'), 20, 4);

            if ($this->get('item') == '-2') {
                if ($this->get('dataset') && $this->get('dataset2')) {
                    $value = apply_filters('frm_content', $value, $form, $entry);
                    if (!class_exists('FrmProContent')) {
                        $value = apply_filters('frm_content', $value, $form2, $entry2);
                    }
                } elseif ($this->get('dataset')) {
                    $value = apply_filters('frm_content', $value, $form, $entry);
                } elseif ($this->get('dataset2')) {
                    $value = apply_filters('frm_content', $value, $form2, $entry2);
                }
            } else {
                $value = apply_filters('frm_content', $value, $form, $entry);
            }

            remove_filter('frmpro_fields_replace_shortcodes', array($this, 'filter_frmpro_fields_replace_shortcodes'), 20, 4);

            if ($upd_signature) {
                remove_filter('frm_keep_signature_value_array', array($this, 'filter_frm_keep_signature_value_array'), 10);
                add_filter('frm_get_signature_display_value', 'FrmSigAppController::display_signature', 10, 3);
                add_filter('frmpro_fields_replace_shortcodes', 'FrmSigAppController::custom_display_signature', 10, 4);
            }

            $value = apply_filters('e2pdf_extension_render_shortcodes_pre_value', $value, $element_id, $template_id, $item, $dataset, $item2, $dataset2);

            if (isset($field['type']) && ($field['type'] === 'e2pdf-image' || $field['type'] === 'e2pdf-signature')) {
                $esig = isset($field['properties']['esig']) && $field['properties']['esig'] ? true : false;
                if ($esig) {
                    //process e-signature
                    $value = "";
                } else {
                    $value = $this->helper->load('properties')->apply($field, $value);
                    if (!$this->helper->load('image')->get_image($value, 'formidable')) {
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
                            $options = apply_filters('e2pdf_frm_sig_output_options', $options, $element_id);
                            $options = apply_filters('e2pdf_image_sig_output_options', $options, $element_id, $template_id);
                            $options = array_merge($default_options, $options);

                            $model_e2pdf_font = new Model_E2pdf_Font();

                            $font = false;
                            if (isset($field['properties']['text_font']) && $field['properties']['text_font']) {
                                $font = $model_e2pdf_font->get_font_path($field['properties']['text_font']);
                            } elseif (class_exists('FrmSigAppHelper')) {
                                if (file_exists(FrmSigAppHelper::plugin_path() . '/assets/journal.ttf')) {
                                    $font = FrmSigAppHelper::plugin_path() . '/assets/journal.ttf';
                                }
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
                if ((false !== strpos($value, '[referer]') || false !== strpos($value, '[browser]')) || (false !== strpos($value, '[referer2]') || false !== strpos($value, '[browser2]'))) {

                    $browser = '';
                    $referer = '';
                    $browser2 = '';
                    $referer2 = '';

                    if ($entry && isset($entry->description)) {
                        $description = maybe_unserialize($entry->description);
                        if (isset($description['referrer'])) {
                            if (@preg_match('/Referer +\d+\:[ \t]+([^\n\t]+)/', $description['referrer'], $m)) {
                                $referer = $m[1];
                            } else {
                                $referer = $description['referrer'];
                            }
                        }

                        if (isset($description['browser'])) {
                            $browser = $description['browser'];
                        }
                    }

                    if ($entry2 && isset($entry2->description)) {
                        $description = maybe_unserialize($entry2->description);
                        if (isset($description['referrer'])) {
                            if (@preg_match('/Referer +\d+\:[ \t]+([^\n\t]+)/', $description['referrer'], $m)) {
                                $referer2 = $m[1];
                            } else {
                                $referer2 = $description['referrer'];
                            }
                        }
                        if (isset($description['browser'])) {
                            $browser = $description['browser'];
                        }
                    }

                    $replace = array(
                        '[referer]' => $referer,
                        '[browser]' => $browser,
                        '[referer2]' => $referer2,
                        '[browser2]' => $browser2
                    );
                    $value = str_replace(array_keys($replace), $replace, $value);
                }

                if (false !== strpos($value, '[entry_num]') || false !== strpos($value, '[entry_num2]')) {
                    $entry_num = '';
                    $entry_num2 = '';

                    if (class_exists("FrmDb")) {
                        if ($entry) {
                            $entry_num = FrmDb::get_count('frm_items', array("form_id = '{$form->id}' AND id <= '{$entry->id}' AND 1" => "1"));
                        }
                        if ($entry2) {
                            $entry_num2 = FrmDb::get_count('frm_items', array("form_id = '{$form2->id}' AND id <= '{$entry2->id}' AND 1" => "1"));
                        }
                    }

                    $replace = array(
                        '[entry_num]' => $entry_num,
                        '[entry_num2]' => $entry_num2
                    );
                    $value = str_replace(array_keys($replace), $replace, $value);
                }

                if (false !== strpos($value, '[pdf_num]')) {
                    $pdf_num = '0';
                    $uid = $this->get('uid');
                    if ($uid) {
                        $uid_entry = new Model_E2pdf_Entry();
                        if ($uid_entry->load_by_uid($uid)) {
                            $pdf_num = $uid_entry->get('pdf_num') + 1;
                        }
                    }

                    $replace = array(
                        '[pdf_num]' => $pdf_num
                    );
                    $value = str_replace(array_keys($replace), $replace, $value);
                }
                $value = $this->helper->load('properties')->apply($field, $value);
            }
        }

        $value = apply_filters('e2pdf_extension_render_shortcodes_value', $value, $element_id, $template_id, $item, $dataset, $item2, $dataset2);

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
                $search = array('&#91;', '&#93;', '&#091;', '&#093;');
                $replace = array('[', ']', '[', ']');
                $value = str_replace($search, $replace, $value);
                if (!$html) {
                    $value = wp_specialchars_decode($value, ENT_QUOTES);
                }
            } else {
                $search = array('[', ']', '&#091;', '&#093;');
                $replace = array('&#91;', '&#93;', '&#91;', '&#93;');
                $value = str_replace($search, $replace, $value);
            }
        }
        return $value;
    }

    public function filter_frm_lookup_is_current_user_filter_needed($is_filter_needed, $field_id, $field_options) {
        if (FrmField::is_option_true_in_array($field_options, 'lookup_filter_current_user')) {
            return true;
        }
        return $is_filter_needed;
    }

    public function filter_frm_keep_signature_value_array($keep_array, $atts) {
        return true;
    }

    /**
     * Search and update shortcodes for this extension inside content
     * Auto set of dataset id
     * 
     * @param string $content - Content
     * @param int $form - ID of form
     * @param int $dataset - ID of dataset
     * 
     * @return string - Content with updates shortcodes
     */
    public function filter_frm_content($content, $form, $dataset) {

        if (false === strpos($content, '[')) {
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
                        if ($template->get('extension') === 'formidable') {
                            $entry_id = is_object($dataset) ? $dataset->id : $dataset;
                            if ($entry_id) {
                                $atts['dataset'] = $entry_id;
                                $shortcode[3] .= ' dataset="' . $entry_id . '"';
                            }
                        }
                    }

                    if (!isset($atts['apply'])) {
                        $shortcode[3] .= ' apply="true"';
                    }

                    if (!isset($atts['filter'])) {
                        $shortcode[3] .= ' filter="true"';
                    }

                    $content = str_replace($shortcode_value, do_shortcode_tag($shortcode), $content);
                }
            }
        }

        return $content;
    }

    public function filter_frm_display_entry_content($content) {

        if (false === strpos($content, '[')) {
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

                    if (!isset($atts['apply'])) {
                        $shortcode[3] .= ' apply="true"';
                    }

                    if (!isset($atts['filter'])) {
                        $shortcode[3] .= ' filter="true"';
                    }

                    $content = str_replace($shortcode_value, do_shortcode_tag($shortcode), $content);
                }
            }
        }

        return $content;
    }

    /**
     * Filter for checkbox repeatable Label/Value fix
     * 
     * @param array $value - Value
     * @param obj $field - Field
     * @param array $atts - Shortcode Attributes
     * 
     * @return mixed - Updated value
     */
    public function filter_frm_display_value_custom($value, $field, $atts = array()) {
        if (class_exists("FrmProEntriesController")) {
            $defaults = array('html' => 0, 'type' => $field->type, 'keepjs' => 0);
            $atts = array_merge($defaults, $atts);

            if ($atts['type'] === 'checkbox') {
                $value = FrmProEntriesController::get_option_label_for_saved_value($value, $field, $atts);
            }
        }
        return $value;
    }

    public function filter_frmpro_fields_replace_shortcodes($replace_with, $tag, $atts, $field) {

        if ($this->get('item') == '-2' && $this->get('item2') && $field->form_id == $this->get('item2')) {
            $atts['entry_id'] = $this->get('dataset2');
            $entry = FrmEntry::getOne($this->get('dataset2'));
            $replace_with = FrmProEntryMetaHelper::get_post_or_meta_value($entry, $field, $atts);
        }

        if ($field->type === 'checkbox' && is_array($replace_with)) {
            if (isset($atts['key']) && $atts['key']) {
                if (isset($replace_with[$atts['key']])) {
                    $replace_with = $replace_with[$atts['key']];
                } else {
                    $replace_with = '';
                }
            }
        }

        if ($field->type === 'radio') {
            if (isset($atts['key']) && $atts['key'] === 'other') {
                if (isset($field->options) && is_array($field->options)) {
                    $field_key = array_search($replace_with, array_column($field->options, 'value'));
                    if ($field_key !== false) {
                        $replace_with = '';
                    }
                }
            }
        }

        return $replace_with;
    }

    /**
     * Generate attachments according shortcodes in Email template
     * 
     * @param array $attachments - List of attachments
     * @param int $form - ID of form
     * @param array $args - Arguments
     * 
     * @return array - Updated list of attachments
     */
    public function filter_frm_notification_attachment($attachments, $form, $args) {

        if (!isset($args['email_key'])) {
            return $attachments;
        }

        $form_actions = FrmFormAction::get_action_for_form($form->id);

        $dataset = $args['entry']->id;
        $email_key = $args['email_key'];

        $shortcode_tags = array(
            'e2pdf-attachment',
            'e2pdf-save'
        );

        foreach ($form_actions as $key => $action) {
            if ($action->ID === $email_key) {

                $content = $action->post_content['email_message'];

                if (false === strpos($content, '[')) {
                    return $attachments;
                }

                remove_filter('frm_content', array($this, 'filter_frm_content'), 30);
                $content = apply_filters('frm_content', $content, $form, $args['entry']);
                add_filter('frm_content', array($this, 'filter_frm_content'), 30, 3);

                preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
                $tagnames = array_intersect($shortcode_tags, $matches[1]);

                if (!empty($tagnames)) {

                    $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                    preg_match_all("/$pattern/", $content, $shortcodes);

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

                        if (($shortcode[2] === 'e2pdf-save' && isset($atts['attachment']) && $atts['attachment'] == 'true') || $shortcode[2] === 'e2pdf-attachment') {

                            if (!isset($atts['dataset']) && isset($atts['id'])) {
                                $template = new Model_E2pdf_Template();
                                $template->load($atts['id']);
                                if ($template->get('extension') === 'formidable') {
                                    $entry_id = is_object($dataset) ? $dataset->id : $dataset;
                                    if ($entry_id) {
                                        $atts['dataset'] = $entry_id;
                                        $shortcode[3] .= ' dataset="' . $entry_id . '"';
                                    }
                                }
                            }

                            if (!isset($atts['apply'])) {
                                $shortcode[3] .= ' apply="true"';
                            }

                            if (!isset($atts['filter'])) {
                                $shortcode[3] .= ' filter="true"';
                            }

                            $ff_transient_entry = false;
                            if (isset($atts['use_args_entry']) && $atts['use_args_entry'] == 'true' && $atts['dataset'] == $dataset) {
                                $ff_transient_entry = 'e2pdf_frm_notification_attachment_entry_' . md5(microtime() . '_' . wp_rand());
                                set_transient($ff_transient_entry, $args['entry'], 30 * MINUTE_IN_SECONDS);
                                $shortcode[3] .= ' ff_transient_entry="' . $ff_transient_entry . '"';
                            }

                            $file = do_shortcode_tag($shortcode);

                            if ($file) {
                                if ($shortcode[2] != 'e2pdf-save' && !isset($atts['pdf'])) {
                                    $this->helper->add('formidable_attachments', $file);
                                }
                                $attachments[] = $file;
                            }

                            if ($ff_transient_entry) {
                                delete_transient($ff_transient_entry);
                            }
                        }
                    }
                }
            }
        }
        return $attachments;
    }

    /**
     * Decrypt protected uploaded file in a File Upload field
     *
     * @param string $html
     * @param array $atts
     * 
     * @return string $html
     */
    public function filter_frm_image_html_array($html, $atts) {

        if (isset($atts['decrypt']) &&
                $atts['decrypt'] &&
                class_exists('FrmProFileField') &&
                class_exists('FrmProFilePayloadBuilder') &&
                class_exists('FrmAppHelper')
        ) {

            $id = $atts['media_id'];

            $permission = isset($atts['permission']) && $atts['permission'] ? true : false;

            remove_filter('wp_get_attachment_url', 'FrmProFileField::filter_attachment_url');
            remove_filter('wp_get_attachment_image_src', 'FrmProFileField::filter_attachment_image_src');

            $is_image = wp_attachment_is_image($id);
            $url = $this->get_file_url($id, $is_image ? $atts['size'] : false);

            if (!FrmProFileField::user_has_permission($id) && !$permission) {
                $frm_settings = FrmAppHelper::get_settings();
                $html = $frm_settings->admin_permission;
            } else {
                $html = $atts['show_image'] ? wp_get_attachment_image($id, $atts['size'], !$is_image) : '';

                // If show_filename=1 is included
                if ($atts['show_filename']) {
                    $label = $this->get_single_file_name($id);
                    if ($atts['show_image']) {
                        $html .= ' <span id="frm_media_' . absint($id) . '" class="frm_upload_label">' . $label . '</span>';
                    } else {
                        $html .= $label;
                    }
                }

                // If neither show_image or show_filename are included, get file URL
                if (!$html) {
                    $html = $url;
                }

                // If add_link=1 is included
                if ($atts['add_link'] || (!$is_image && $atts['add_link_for_non_image'] )) {
                    $href = $is_image ? $this->get_file_url($id) : $url;
                    $target = !empty($atts['new_tab']) ? ' target="_blank"' : '';
                    $html = '<a href="' . esc_url($href) . '" class="frm_file_link"' . $target . '>' . $html . '</a>';
                }

                if (!empty($atts['class'])) {
                    $html = str_replace(' class="', ' class="' . esc_attr($atts['class'] . ' '), $html);
                }

                add_filter('wp_get_attachment_url', 'FrmProFileField::filter_attachment_url', 10, 2);
                add_filter('wp_get_attachment_image_src', 'FrmProFileField::filter_attachment_image_src', 10, 4);
            }
        }

        return $html;
    }

    /**
     * @param int $id
     * @param string|int[]|bool $size
     * @param array $args supported keys include "url" and "leave_size_out_of_payload"
     * 
     * @return string unprotected url to use for our specified file id and size.
     */
    public function get_file_url($id, $size = false, $args = array()) {
        $url = isset($args['url']) ? $args['url'] : false;
        $builder = new FrmProFilePayloadBuilder($id, $size, $url);
        return $builder->get_url();
    }

    /**
     * Remove Page Breaks for Visual Mapper
     * 
     * @param array $fields - List of fields 
     * 
     * @return array - Updated fields
     */
    function filter_remove_pagebreaks($fields) {
        foreach ((array) $fields as $field_key => $field) {
            if ($field->type == 'break') {
                unset($fields[$field_key]);
            }
        }
        return $fields;
    }

    function filter_frm_match_xml_form($edit_query, $form) {
        if (isset($edit_query['created_at'])) {
            $edit_query['created_at'] = date('Y-m-d H:i:s', strtotime("now"));
        }
        return $edit_query;
    }

    function filter_frm_show_new_entry_page() {
        return 'new';
    }

    /**
     * Add options for Formidable extension
     * 
     * @param array $options - List of options 
     * 
     * @return array - Updated options list
     */
    public function filter_e2pdf_model_options_get_options_options($options = array()) {
        $options['formidable_group'] = array(
            'name' => __('Formidable Forms', 'e2pdf'),
            'action' => 'extension',
            'group' => 'formidable_group',
            'options' => array(
                array(
                    'name' => __('Auto PDF and Visual Mapper', 'e2pdf'),
                    'key' => 'e2pdf_formidable_use_keys',
                    'value' => get_option('e2pdf_formidable_use_keys') === false ? '0' : get_option('e2pdf_formidable_use_keys'),
                    'default_value' => '0',
                    'type' => 'checkbox',
                    'checkbox_value' => '1',
                    'placeholder' => __('Use Field Keys instead Field IDs', 'e2pdf'),
                ),
                array(
                    'name' => __('Filter', 'e2pdf'),
                    'key' => 'e2pdf_formidable_disable_filter',
                    'value' => get_option('e2pdf_formidable_disable_filter') === false ? '0' : get_option('e2pdf_formidable_disable_filter'),
                    'default_value' => '0',
                    'type' => 'checkbox',
                    'checkbox_value' => '1',
                    'placeholder' => __('Disable Filter', 'e2pdf'),
                ),
            )
        );
        return $options;
    }

    public function filter_e2pdf_controller_templates_import_options($options) {

        if (isset($options['item'])) {
            $options['item']['options'][] = array(
                'name' => __('Formidable Forms', 'e2pdf'),
                'key' => 'options[formidable_item_new_form]',
                'value' => 1,
                'default_value' => 0,
                'type' => 'radio',
                'li' => array(
                    'class' => 'e2pdf-import-extension-option e2pdf-hide',
                ),
                'options' => array(
                    '1' => 'Recreate Web Form', '0' => 'Overwrite Web Form'
                )
            );
        }

        return $options;
    }

    public function filter_e2pdf_controller_templates_backup_options($options, $template, $extension) {

        if ($extension->loaded('formidable')) {
            $options['formidable'] = array(
                'name' => __('Formidable', 'e2pdf'),
                'options' => array(
                    array(
                        'name' => __('Force shortcodes to use', 'e2pdf'),
                        'key' => 'options[formidable_force_shortcodes]',
                        'value' => 0,
                        'default_value' => 0,
                        'type' => 'select',
                        'options' => array(
                            '0' => __('None', 'e2pdf'),
                            '1' => __('Fields IDs', 'e2pdf'),
                            '2' => __('Field Keys', 'e2pdf'),
                        )
                    ),
                )
            );
        }

        return $options;
    }

    public function filter_e2pdf_controller_templates_backup_pages($pages, $options, $template, $extension) {

        if ($extension->loaded('formidable') && (isset($options['formidable_force_shortcodes']) && $options['formidable_force_shortcodes'])) {

            $fields = array();
            if ($template->get('item') == '-2') {
                if ($template->get('item1')) {
                    $where = array('fi.form_id' => (int) $template->get('item1'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
                if ($template->get('item2')) {
                    $where = array('fi.form_id' => (int) $template->get('item2'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
            } else {
                $where = array('fi.form_id' => (int) $template->get('item'));
                $fields = FrmField::getAll($where, 'id ASC');
            }

            $search = array();
            $replace = array();

            if ($options['formidable_force_shortcodes'] == '1') {
                foreach ($fields as $field_key => $field) {
                    if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                        $sub_where = array('fi.form_id' => $field->field_options['form_select']);
                        $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                        foreach ($sub_fields as $sub_field_key => $sub_field) {
                            $search[] = $sub_field->field_key;
                            $replace[] = $sub_field->id;
                        }
                        $search[] = $field->field_key;
                        $replace[] = $field->id;
                    } else {
                        $search[] = $field->field_key;
                        $replace[] = $field->id;
                    }
                }
            } elseif ($options['formidable_force_shortcodes'] == '2') {
                foreach ($fields as $field_key => $field) {

                    if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                        $sub_where = array('fi.form_id' => $field->field_options['form_select']);
                        $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                        foreach ($sub_fields as $sub_field_key => $sub_field) {
                            $search[] = $sub_field->id;
                            $replace[] = $sub_field->field_key;
                        }
                        $search[] = $field->id;
                        $replace[] = $field->field_key;
                    } else {
                        $search[] = $field->id;
                        $replace[] = $field->field_key;
                    }
                }
            }

            $search = array_reverse($search);
            $replace = array_reverse($replace);

            $list = array_combine($search, $replace);

            $pages = $this->pages_replace_shortcodes($pages, $list);
        }

        return $pages;
    }

    public function filter_e2pdf_controller_templates_backup_actions($actions, $options, $template, $extension) {

        if ($extension->loaded('formidable') && (isset($options['formidable_force_shortcodes']) && $options['formidable_force_shortcodes'])) {

            $fields = array();
            if ($template->get('item') == '-2') {
                if ($template->get('item1')) {
                    $where = array('fi.form_id' => (int) $template->get('item1'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
                if ($template->get('item2')) {
                    $where = array('fi.form_id' => (int) $template->get('item2'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
            } else {
                $where = array('fi.form_id' => (int) $template->get('item'));
                $fields = FrmField::getAll($where, 'id ASC');
            }

            $search = array();
            $replace = array();

            if ($options['formidable_force_shortcodes'] == '1') {
                foreach ($fields as $field_key => $field) {
                    if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                        $sub_where = array('fi.form_id' => $field->field_options['form_select']);
                        $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                        foreach ($sub_fields as $sub_field_key => $sub_field) {
                            $search[] = $sub_field->field_key;
                            $replace[] = $sub_field->id;
                        }
                        $search[] = $field->field_key;
                        $replace[] = $field->id;
                    } else {
                        $search[] = $field->field_key;
                        $replace[] = $field->id;
                    }
                }
            } elseif ($options['formidable_force_shortcodes'] == '2') {
                foreach ($fields as $field_key => $field) {

                    if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                        $sub_where = array('fi.form_id' => $field->field_options['form_select']);
                        $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                        foreach ($sub_fields as $sub_field_key => $sub_field) {
                            $search[] = $sub_field->id;
                            $replace[] = $sub_field->field_key;
                        }
                        $search[] = $field->id;
                        $replace[] = $field->field_key;
                    } else {
                        $search[] = $field->id;
                        $replace[] = $field->field_key;
                    }
                }
            }

            $search = array_reverse($search);
            $replace = array_reverse($replace);
            $list = array_combine($search, $replace);

            $actions = $this->actions_replace_shortcodes($actions, $list);
        }

        return $actions;
    }

    public function filter_e2pdf_controller_templates_backup_replace_shortcodes($value, $options, $template, $extension) {

        if ($extension->loaded('formidable') && (isset($options['formidable_force_shortcodes']) && $options['formidable_force_shortcodes'])) {

            $fields = array();
            if ($template->get('item') == '-2') {
                if ($template->get('item1')) {
                    $where = array('fi.form_id' => (int) $template->get('item1'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
                if ($template->get('item2')) {
                    $where = array('fi.form_id' => (int) $template->get('item2'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
            } else {
                $where = array('fi.form_id' => (int) $template->get('item'));
                $fields = FrmField::getAll($where, 'id ASC');
            }

            $search = array();
            $replace = array();

            if ($options['formidable_force_shortcodes'] == '1') {
                foreach ($fields as $field_key => $field) {
                    if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                        $sub_where = array('fi.form_id' => $field->field_options['form_select']);
                        $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                        foreach ($sub_fields as $sub_field_key => $sub_field) {
                            $search[] = $sub_field->field_key;
                            $replace[] = $sub_field->id;
                        }
                        $search[] = $field->field_key;
                        $replace[] = $field->id;
                    } else {
                        $search[] = $field->field_key;
                        $replace[] = $field->id;
                    }
                }
            } elseif ($options['formidable_force_shortcodes'] == '2') {
                foreach ($fields as $field_key => $field) {

                    if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                        $sub_where = array('fi.form_id' => $field->field_options['form_select']);
                        $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                        foreach ($sub_fields as $sub_field_key => $sub_field) {
                            $search[] = $sub_field->id;
                            $replace[] = $sub_field->field_key;
                        }
                        $search[] = $field->id;
                        $replace[] = $field->field_key;
                    } else {
                        $search[] = $field->id;
                        $replace[] = $field->field_key;
                    }
                }
            }

            $search = array_reverse($search);
            $replace = array_reverse($replace);

            $list = array_combine($search, $replace);
            $value = $this->replace_shortcodes($value, $list);
        }

        return $value;
    }

    public function filter_e2pdf_controller_templates_import_pages($pages, $options, $xml, $template, $extension) {

        if ($extension->loaded('formidable') &&
                $template->get('item') &&
                (($template->get('item') != '-2' && $template->get('item') != (String) $xml->template->item) || ($template->get('item') == '-2' && ($template->get('item1') != (String) $xml->template->item1 || $template->get('item2') != (String) $xml->template->item2))) &&
                $xml->item->formidable &&
                $options['item'] &&
                class_exists('FrmXMLHelper') &&
                class_exists('FrmField')
        ) {

            $tmp = tempnam($this->helper->get('tmp_dir'), 'e2pdf');
            file_put_contents($tmp, base64_decode((String) $xml->item->formidable));

            $dom = new DOMDocument;
            $success = $dom->loadXML(file_get_contents($tmp));

            $old_ids = array();
            $old_keys = array();

            if ($success && function_exists('simplexml_import_dom')) {
                $item_xml = simplexml_import_dom($dom);

                foreach ($item_xml->form as $form_key => $form) {
                    if (($template->get('item') != '-2' && (String) $form->id == (String) $xml->template->item) || ($template->get('item') == '-2' && ((String) $form->id == (String) $xml->template->item1 || (String) $form->id == (String) $xml->template->item2))) {
                        foreach ($form->field as $field_key => $field) {
                            $field_options = @json_decode((String) $field->field_options, true);
                            if (isset($field_options['form_select']) && $field_options['form_select']) {
                                foreach ($item_xml->form as $sub_form_key => $sub_form) {
                                    if ((String) $sub_form->id == $field_options['form_select']) {
                                        foreach ($sub_form->field as $sub_field_key => $sub_field) {
                                            $old_ids[] = (String) $sub_field->id;
                                            $old_keys[] = (String) $sub_field->field_key;
                                        }
                                    }
                                }
                                $old_ids[] = (String) $field->id;
                                $old_keys[] = (String) $field->field_key;
                            } else {
                                $old_ids[] = (String) $field->id;
                                $old_keys[] = (String) $field->field_key;
                            }
                        }
                    }
                }
            }

            $fields = array();
            if ($template->get('item') == '-2') {
                if ($template->get('item1')) {
                    $where = array('fi.form_id' => (int) $template->get('item1'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }

                if ($template->get('item2')) {
                    $where = array('fi.form_id' => (int) $template->get('item2'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
            } else {
                $where = array('fi.form_id' => (int) $template->get('item'));
                $fields = FrmField::getAll($where, 'id ASC');
            }

            $new_ids = array();
            $new_keys = array();

            foreach ($fields as $field_key => $field) {

                if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                    $sub_where = array('fi.form_id' => (int) $field->field_options['form_select']);
                    $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                    foreach ($sub_fields as $sub_field_key => $sub_field) {
                        $new_ids[] = $sub_field->id;
                        $new_keys[] = $sub_field->field_key;
                    }
                    $new_ids[] = $field->id;
                    $new_keys[] = $field->field_key;
                } else {
                    $new_ids[] = $field->id;
                    $new_keys[] = $field->field_key;
                }
            }

            if (count($old_ids) === count($new_ids)) {
                $old_ids = array_reverse($old_ids);
                $new_ids = array_reverse($new_ids);

                $old_keys = array_reverse($old_keys);
                $new_keys = array_reverse($new_keys);

                $list_ids = array_combine($old_ids, $new_ids);
                $pages = $this->pages_replace_shortcodes($pages, $list_ids);

                $list_keys = array_combine($old_keys, $new_keys);
                $pages = $this->pages_replace_shortcodes($pages, $list_keys);
            }

            unset($dom);
            unlink($tmp);
        }

        return $pages;
    }

    public function filter_e2pdf_controller_templates_import_actions($actions, $options, $xml, $template, $extension) {

        if ($extension->loaded('formidable') &&
                $template->get('item') &&
                (($template->get('item') != '-2' && $template->get('item') != (String) $xml->template->item) || ($template->get('item') == '-2' && ($template->get('item1') != (String) $xml->template->item1 || $template->get('item2') != (String) $xml->template->item2))) &&
                $xml->item->formidable &&
                $options['item'] &&
                class_exists('FrmXMLHelper') &&
                class_exists('FrmField')
        ) {

            $tmp = tempnam($this->helper->get('tmp_dir'), 'e2pdf');
            file_put_contents($tmp, base64_decode((String) $xml->item->formidable));

            $dom = new DOMDocument;
            $success = $dom->loadXML(file_get_contents($tmp));

            $old_ids = array();
            $old_keys = array();

            if ($success && function_exists('simplexml_import_dom')) {
                $item_xml = simplexml_import_dom($dom);

                foreach ($item_xml->form as $form_key => $form) {
                    if (($template->get('item') != '-2' && (String) $form->id == (String) $xml->template->item) || ($template->get('item') == '-2' && ((String) $form->id == (String) $xml->template->item1 || (String) $form->id == (String) $xml->template->item2))) {
                        foreach ($form->field as $field_key => $field) {
                            $field_options = @json_decode((String) $field->field_options, true);
                            if (isset($field_options['form_select']) && $field_options['form_select']) {
                                foreach ($item_xml->form as $sub_form_key => $sub_form) {
                                    if ((String) $sub_form->id == $field_options['form_select']) {
                                        foreach ($sub_form->field as $sub_field_key => $sub_field) {
                                            $old_ids[] = (String) $sub_field->id;
                                            $old_keys[] = (String) $sub_field->field_key;
                                        }
                                    }
                                }
                                $old_ids[] = (String) $field->id;
                                $old_keys[] = (String) $field->field_key;
                            } else {
                                $old_ids[] = (String) $field->id;
                                $old_keys[] = (String) $field->field_key;
                            }
                        }
                    }
                }
            }

            $fields = array();
            if ($template->get('item') == '-2') {
                if ($template->get('item1')) {
                    $where = array('fi.form_id' => (int) $template->get('item1'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }

                if ($template->get('item2')) {
                    $where = array('fi.form_id' => (int) $template->get('item2'));
                    $item_fields = FrmField::getAll($where, 'id ASC');

                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
            } else {
                $where = array('fi.form_id' => (int) $template->get('item'));
                $fields = FrmField::getAll($where, 'id ASC');
            }

            $new_ids = array();
            $new_keys = array();

            foreach ($fields as $field_key => $field) {

                if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                    $sub_where = array('fi.form_id' => (int) $field->field_options['form_select']);
                    $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                    foreach ($sub_fields as $sub_field_key => $sub_field) {
                        $new_ids[] = $sub_field->id;
                        $new_keys[] = $sub_field->field_key;
                    }
                    $new_ids[] = $field->id;
                    $new_keys[] = $field->field_key;
                } else {
                    $new_ids[] = $field->id;
                    $new_keys[] = $field->field_key;
                }
            }

            if (count($old_ids) === count($new_ids)) {
                $old_ids = array_reverse($old_ids);
                $new_ids = array_reverse($new_ids);

                $old_keys = array_reverse($old_keys);
                $new_keys = array_reverse($new_keys);

                $list_ids = array_combine($old_ids, $new_ids);
                $actions = $this->actions_replace_shortcodes($actions, $list_ids);

                $list_keys = array_combine($old_keys, $new_keys);
                $actions = $this->pages_replace_shortcodes($actions, $list_keys);
            }

            unset($dom);
            unlink($tmp);
        }

        return $actions;
    }

    public function filter_e2pdf_controller_templates_import_replace_shortcodes($value, $options, $xml, $template, $extension) {

        if ($extension->loaded('formidable') &&
                $template->get('item') &&
                (($template->get('item') != '-2' && $template->get('item') != (String) $xml->template->item) || ($template->get('item') == '-2' && ($template->get('item1') != (String) $xml->template->item1 || $template->get('item2') != (String) $xml->template->item2))) &&
                $xml->item->formidable &&
                $options['item'] &&
                class_exists('FrmXMLHelper') &&
                class_exists('FrmField')
        ) {

            $tmp = tempnam($this->helper->get('tmp_dir'), 'e2pdf');
            file_put_contents($tmp, base64_decode((String) $xml->item->formidable));

            $dom = new DOMDocument;
            $success = $dom->loadXML(file_get_contents($tmp));

            $old_ids = array();
            $old_keys = array();

            if ($success && function_exists('simplexml_import_dom')) {
                $item_xml = simplexml_import_dom($dom);

                foreach ($item_xml->form as $form_key => $form) {
                    if (($template->get('item') != '-2' && (String) $form->id == (String) $xml->template->item) || ($template->get('item') == '-2' && ((String) $form->id == (String) $xml->template->item1 || (String) $form->id == (String) $xml->template->item2))) {
                        foreach ($form->field as $field_key => $field) {
                            $field_options = @json_decode((String) $field->field_options, true);
                            if (isset($field_options['form_select']) && $field_options['form_select']) {
                                foreach ($item_xml->form as $sub_form_key => $sub_form) {
                                    if ((String) $sub_form->id == $field_options['form_select']) {
                                        foreach ($sub_form->field as $sub_field_key => $sub_field) {
                                            $old_ids[] = (String) $sub_field->id;
                                            $old_keys[] = (String) $sub_field->field_key;
                                        }
                                    }
                                }
                                $old_ids[] = (String) $field->id;
                                $old_keys[] = (String) $field->field_key;
                            } else {
                                $old_ids[] = (String) $field->id;
                                $old_keys[] = (String) $field->field_key;
                            }
                        }
                    }
                }
            }

            $fields = array();
            if ($template->get('item') == '-2') {
                if ($template->get('item1')) {
                    $where = array('fi.form_id' => (int) $template->get('item1'));
                    $item_fields = FrmField::getAll($where, 'id ASC');
                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }

                if ($template->get('item2')) {
                    $where = array('fi.form_id' => (int) $template->get('item2'));
                    $item_fields = FrmField::getAll($where, 'id ASC');

                    if ($item_fields) {
                        $fields = array_merge($fields, $item_fields);
                    }
                }
            } else {
                $where = array('fi.form_id' => (int) $template->get('item'));
                $fields = FrmField::getAll($where, 'id ASC');
            }

            $new_ids = array();
            $new_keys = array();

            foreach ($fields as $field_key => $field) {

                if (isset($field->field_options['form_select']) && $field->field_options['form_select']) {
                    $sub_where = array('fi.form_id' => (int) $field->field_options['form_select']);
                    $sub_fields = FrmField::getAll($sub_where, 'id ASC');
                    foreach ($sub_fields as $sub_field_key => $sub_field) {
                        $new_ids[] = $sub_field->id;
                        $new_keys[] = $sub_field->field_key;
                    }
                    $new_ids[] = $field->id;
                    $new_keys[] = $field->field_key;
                } else {
                    $new_ids[] = $field->id;
                    $new_keys[] = $field->field_key;
                }
            }

            if (count($old_ids) === count($new_ids)) {

                $old_ids = array_reverse($old_ids);
                $new_ids = array_reverse($new_ids);

                $old_keys = array_reverse($old_keys);
                $new_keys = array_reverse($new_keys);

                $list_ids = array_combine($old_ids, $new_ids);
                $value = $this->replace_shortcodes($value, $list_ids);

                $list_keys = array_combine($old_keys, $new_keys);
                $value = $this->replace_shortcodes($value, $list_keys);
            }

            unset($dom);
            unlink($tmp);
        }

        return $value;
    }

    /**
     * Delete attachments that were sent by email
     */
    public function action_frm_notification() {

        $files = $this->helper->get('formidable_attachments');
        if (is_array($files) && !empty($files)) {
            foreach ($files as $key => $file) {
                $this->helper->delete_dir(dirname($file) . '/');
            }
            $this->helper->deset('formidable_attachments');
        }
    }

    /**
     * Auto Generate of Template for this extension
     * 
     * @return array - List of elements
     */
    public function auto() {

        $response = array();
        $elements = array();

        $form_id = $this->get('item');

        $fields = array();
        if (class_exists('FrmField')) {
            $fields = FrmField::get_all_for_form($form_id, '', 'include', 'include');
        }

        if ($fields) {
            foreach ($fields as $key => $field) {

                $field_id = get_option('e2pdf_formidable_use_keys') ? $field->field_key : $field->id;

                if ($field->type === 'lookup' && isset($field->field_options['data_type'])) {
                    if ($field->field_options['data_type'] == 'select') {
                        $field->options = array(
                            '[e2pdf-frm-lookup-values id="' . $field_id . '"]'
                        );
                    } elseif ($field->field_options['data_type'] == 'radio' || $field->field_options['data_type'] == 'checkbox') {
                        $options = array();
                        $frm_filter = false;
                        if (class_exists('FrmProFieldsHelper') && method_exists('FrmProFieldsHelper', 'add_default_field_settings')) {
                            if (!has_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings')) {
                                add_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings', 10, 2);
                                $frm_filter = true;
                            }
                        }

                        $args = array();
                        $field_array = FrmAppHelper::start_field_array($field);
                        FrmFieldsHelper::prepare_new_front_field($field_array, $field, $args);
                        $field_array = array_merge($field->field_options, $field_array);
                        if (isset($field_array['options']) && is_array($field_array['options'])) {
                            $options = $field_array['options'];
                        }
                        if ($frm_filter) {
                            remove_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings', 10);
                        }
                        $field->options = $options;
                    }
                    $field->type = $field->field_options['data_type'];
                } elseif ($field->type === 'data' && isset($field->field_options['data_type'])) {
                    if ($field->field_options['data_type'] == 'select') {
                        $field->options = array(
                            '[e2pdf-frm-data-values id="' . $field_id . '"]'
                        );
                    } elseif ($field->field_options['data_type'] == 'radio' || $field->field_options['data_type'] == 'checkbox') {
                        $options = array();
                        $frm_filter = false;
                        if (class_exists('FrmProFieldsHelper') && method_exists('FrmProFieldsHelper', 'add_default_field_settings')) {
                            if (!has_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings')) {
                                add_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings', 10, 2);
                                $frm_filter = true;
                            }
                        }

                        $args = array();
                        $field->field_options['restrict'] = '0';
                        $field_array = FrmAppHelper::start_field_array($field);
                        FrmFieldsHelper::prepare_new_front_field($field_array, $field, $args);

                        $field_array = array_merge($field->field_options, $field_array);
                        if (isset($field_array['options']) && is_array($field_array['options'])) {
                            $options = $field_array['options'];
                        }

                        if ($frm_filter) {
                            remove_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings', 10);
                        }
                        $field->options = $options;
                    }

                    $field->type = $field->field_options['data_type'];
                }

                /*
                 * Repeatable fields Field ID modification
                 * @since 0.01.42
                 */
                if ($field->type !== 'lookup' && $field->type !== 'data' && isset($field->form_id) && $field->form_id != $form_id) {
                    $field_id = $field_id . ":1";
                }

                switch ($field->type) {
                    case 'divider':
                        if ($field->name) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => '<h2>' . $field->name . '</h2>',
                                )
                            ));
                        }
                        break;
                    case 'html':
                        if ($field->description) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'block' => true,
                                'properties' => array(
                                    'top' => '20',
                                    'left' => '20',
                                    'right' => '20',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field->description,
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
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
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
                                'value' => "[$field_id]"
                            )
                        ));
                        break;
                    case 'scale':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        if (isset($field->options) && is_array($field->options)) {
                            $start = true;
                            foreach ($field->options as $opt_key => $option) {
                                if (is_array($option)) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-radio',
                                        'float' => $start ? false : true,
                                        'properties' => array(
                                            'top' => $start ? '5' : '0',
                                            'left' => $start ? '0' : '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => "[$field_id]",
                                            'option' => $option['value'],
                                            'group' => "group_" . $field_id
                                        )
                                    ));
                                } else {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-radio',
                                        'float' => $start ? false : true,
                                        'properties' => array(
                                            'top' => $start ? '5' : '0',
                                            'left' => $start ? '0' : '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => "[$field_id]",
                                            'option' => $option,
                                            'group' => "group_" . $field_id
                                        )
                                    ));
                                }

                                $start = false;
                            }

                            $start = true;
                            foreach ($field->options as $opt_key => $option) {
                                if (is_array($option)) {

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'float' => $start ? false : true,
                                        'properties' => array(
                                            'top' => $start ? '5' : '0',
                                            'left' => $start ? '0' : '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => $option['label'],
                                            'text_align' => 'center',
                                        )
                                    ));
                                } else {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'float' => $start ? false : true,
                                        'properties' => array(
                                            'top' => $start ? '5' : '0',
                                            'left' => $start ? '0' : '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => $option,
                                            'text_align' => 'center',
                                        )
                                    ));
                                }
                                $start = false;
                            }
                        }
                        break;
                    case 'rte':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => '150',
                                'value' => "[$field_id wpautop=0]"
                            )
                        ));
                        break;
                    case 'file':
                    case 'text':
                    case 'email':
                    case 'url':
                    case 'number':
                    case 'phone':
                    case 'date':
                    case 'image':
                    case 'tag':
                    case 'password':
                    case 'quiz_score':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        if ($field->type == 'file') {
                            if (strpos($field_id, ':') !== false) {
                                $field_id .= ' size="full" show_image="0" add_link="0"';
                            } else {
                                $field_id .= ' size="full"';
                            }
                        }

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-input',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => "[$field_id]",
                                'pass' => $field->type === 'password' ? '1' : '0',
                            )
                        ));
                        break;

                    case 'time':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        $options_tmp = array();
                        if (class_exists("FrmProFieldTime")) {
                            $frm_pro_field_time = new FrmProFieldTime($field);
                            $options_tmp = $frm_pro_field_time->get_options($field->field_options);
                        }

                        if (isset($field->field_options['single_time']) && $field->field_options['single_time']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $options_tmp),
                                    'value' => "[$field_id]",
                                )
                            ));
                        } else {

                            $options_h = isset($options_tmp['H']) && is_array($options_tmp['H']) ? $options_tmp['H'] : array();
                            $options_m = isset($options_tmp['m']) && is_array($options_tmp['m']) ? $options_tmp['m'] : array();
                            $options_a = isset($options_tmp['A']) && is_array($options_tmp['A']) ? $options_tmp['A'] : array();

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'float' => true,
                                'properties' => array(
                                    'top' => '5',
                                    'width' => isset($field->field_options['clock']) && $field->field_options['clock'] == '12' ? '33.3%' : '50%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $options_h),
                                    'value' => isset($field->field_options['clock']) && $field->field_options['clock'] == '12' ? '[$field_id format="g"]' : '[' . $field_id . ' format="H"]',
                                )
                            ));

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'float' => true,
                                'properties' => array(
                                    'top' => '5',
                                    'left' => '20',
                                    'width' => isset($field->field_options['clock']) && $field->field_options['clock'] == '12' ? '33.3%' : '50%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $options_m),
                                    'value' => '[' . $field_id . ' format="i"]',
                                )
                            ));

                            if (isset($field->field_options['clock']) && $field->field_options['clock'] == '12') {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-select',
                                    'float' => true,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => '20',
                                        'width' => '33.3%',
                                        'height' => 'auto',
                                        'options' => implode("\n", $options_a),
                                        'value' => '[' . $field_id . ' format="A"]',
                                    )
                                ));
                            }
                        }
                        break;
                    case 'select':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        $options_tmp = array();
                        if (isset($field->options) && is_array($field->options)) {
                            foreach ($field->options as $opt_key => $option) {
                                if (is_array($option)) {
                                    $options_tmp[] = isset($option['label']) ? $option['label'] : $option['value'];
                                } else {
                                    $options_tmp[] = $option;
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
                                'value' => "[$field_id]",
                                'height' => isset($field->field_options['multiple']) && $field->field_options['multiple'] ? '80' : 'auto',
                                'multiline' => isset($field->field_options['multiple']) && $field->field_options['multiple'] ? '1' : '0',
                            )
                        ));
                        break;
                    case 'credit_card':

                        //parent block
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-input',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => '[' . $field_id . ' show="cc"]',
                            )
                        ));

                        //month
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-select',
                            'properties' => array(
                                'top' => '5',
                                'width' => '33.3%',
                                'left' => '0',
                                'height' => 'auto',
                                'options' => implode("\n", range(1, 12)),
                                'value' => '[' . $field_id . ' show="month"]',
                            )
                        ));

                        //year
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-select',
                            'float' => true,
                            'properties' => array(
                                'top' => '5',
                                'left' => '20',
                                'width' => '33.3%',
                                'height' => 'auto',
                                'options' => implode("\n", range(date('Y'), date('Y') + 10)),
                                'value' => '[' . $field_id . ' show="year"]',
                            )
                        ));

                        //cvc
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-input',
                            'float' => true,
                            'properties' => array(
                                'top' => '5',
                                'left' => '20',
                                'width' => '33.3%',
                                'height' => 'auto',
                                'value' => '[' . $field_id . ' show="cvc"]',
                            )
                        ));
                        break;
                    case 'address':
                        //parent block
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        //line1
                        if (isset($field->field_options['line1_desc']) && $field->field_options['line1_desc']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field->field_options['line1_desc'],
                                )
                            ));
                        }

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-input',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => '[' . $field_id . ' show="line1"]',
                            )
                        ));

                        //line 2
                        if (isset($field->field_options['line2_desc']) && $field->field_options['line2_desc']) {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'value' => $field->field_options['line2_desc'],
                                )
                            ));
                        }

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-input',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => '[' . $field_id . ' show="line2"]',
                            )
                        ));

                        //labels
                        if (
                                (isset($field->field_options['city_desc']) && $field->field_options['city_desc']) ||
                                (isset($field->field_options['state_desc']) && $field->field_options['state_desc'] && isset($field->field_options['address_type']) && $field->field_options['address_type'] != 'europe') ||
                                (isset($field->field_options['zip_desc']) && $field->field_options['zip_desc'])
                        ) {

                            $float = false;
                            //city label
                            if (isset($field->field_options['address_type']) && $field->field_options['address_type'] != 'europe') {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => $float,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $float ? '20' : '0',
                                        'width' => '33.3%',
                                        'height' => 'auto',
                                        'value' => isset($field->field_options['city_desc']) && $field->field_options['city_desc'] ? $field->field_options['city_desc'] : '',
                                    )
                                ));
                                $float = true;
                            }

                            //state label
                            if (isset($field->field_options['address_type']) && (
                                    $field->field_options['address_type'] == 'international' ||
                                    $field->field_options['address_type'] == 'us' ||
                                    $field->field_options['address_type'] == 'generic'
                                    )
                            ) {

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => $float,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $float ? '20' : '0',
                                        'width' => '33.3%',
                                        'height' => 'auto',
                                        'value' => isset($field->field_options['state_desc']) && $field->field_options['state_desc'] ? $field->field_options['state_desc'] : '',
                                    )
                                ));
                                $float = true;
                            }

                            //zip label
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-html',
                                'float' => $float,
                                'properties' => array(
                                    'top' => '5',
                                    'left' => $float ? '20' : '0',
                                    'width' => '33.3%',
                                    'height' => 'auto',
                                    'value' => isset($field->field_options['zip_desc']) && $field->field_options['zip_desc'] ? $field->field_options['zip_desc'] : '',
                                )
                            ));
                            $float = true;

                            //city label for Europe
                            if (isset($field->field_options['address_type']) && $field->field_options['address_type'] == 'europe') {

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'float' => $float,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $float ? '20' : '0',
                                        'width' => '33.3%',
                                        'height' => 'auto',
                                        'value' => isset($field->field_options['city_desc']) && $field->field_options['city_desc'] ? $field->field_options['city_desc'] : '',
                                    )
                                ));
                            }
                        }


                        //fields
                        $float = false;
                        //city field
                        if (isset($field->field_options['address_type']) && $field->field_options['address_type'] != 'europe') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'float' => $float,
                                'properties' => array(
                                    'top' => '5',
                                    'left' => $float ? '20' : '0',
                                    'width' => '33.3%',
                                    'height' => 'auto',
                                    'value' => '[' . $field_id . ' show="city"]',
                                )
                            ));
                            $float = true;
                        }

                        //state field
                        if (isset($field->field_options['address_type']) && (
                                $field->field_options['address_type'] == 'international' ||
                                $field->field_options['address_type'] == 'us' ||
                                $field->field_options['address_type'] == 'generic'
                                )
                        ) {
                            if ($field->field_options['address_type'] == 'us') {
                                $options_tmp = array();
                                if (class_exists('FrmFieldsHelper')) {
                                    $options_tmp = array_values(FrmFieldsHelper::get_us_states());
                                }

                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-select',
                                    'float' => $float,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $float ? '20' : '0',
                                        'width' => '33.3%',
                                        'height' => 'auto',
                                        'options' => implode("\n", $options_tmp),
                                        'value' => '[' . $field_id . ' show="state"]',
                                    )
                                ));
                            } else {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-input',
                                    'float' => $float,
                                    'properties' => array(
                                        'top' => '5',
                                        'left' => $float ? '20' : '0',
                                        'width' => '33.3%',
                                        'height' => 'auto',
                                        'value' => '[' . $field_id . ' show="state"]',
                                    )
                                ));
                            }
                            $float = true;
                        }

                        //zip field
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-input',
                            'float' => $float,
                            'properties' => array(
                                'top' => '5',
                                'left' => $float ? '20' : '0',
                                'width' => '33.3%',
                                'height' => 'auto',
                                'value' => '[' . $field_id . ' show="zip"]',
                            )
                        ));
                        $float = true;

                        //city field for Europe
                        if (isset($field->field_options['address_type']) && $field->field_options['address_type'] == 'europe') {
                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-input',
                                'float' => $float,
                                'properties' => array(
                                    'top' => '5',
                                    'left' => $float ? '20' : '0',
                                    'width' => '33.3%',
                                    'height' => 'auto',
                                    'value' => '[' . $field_id . ' show="city"]',
                                )
                            ));
                        }

                        //country
                        if (isset($field->field_options['address_type']) && ($field->field_options['address_type'] == 'international' || $field->field_options['address_type'] == 'europe')) {
                            if (isset($field->field_options['country_desc']) && $field->field_options['country_desc']) {
                                $elements[] = $this->auto_field($field, array(
                                    'type' => 'e2pdf-html',
                                    'properties' => array(
                                        'top' => '5',
                                        'width' => '100%',
                                        'height' => 'auto',
                                        'value' => $field->field_options['country_desc'],
                                    )
                                ));
                            }

                            $options_tmp = array();
                            if (class_exists('FrmFieldsHelper')) {
                                $options_tmp = array_values(FrmFieldsHelper::get_countries());
                            }

                            $elements[] = $this->auto_field($field, array(
                                'type' => 'e2pdf-select',
                                'properties' => array(
                                    'top' => '5',
                                    'width' => '100%',
                                    'height' => 'auto',
                                    'options' => implode("\n", $options_tmp),
                                    'value' => '[' . $field_id . ' show="country"]',
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
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-textarea',
                            'properties' => array(
                                'top' => '5',
                                'width' => '100%',
                                'height' => '150',
                                'value' => "[$field_id wpautop=0]",
                            )
                        ));
                        break;
                    case 'radio':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        $image_options = false;
                        if (isset($field->field_options['image_options']) && $field->field_options['image_options']) {
                            $image_options = true;
                        }

                        $separate_value = false;
                        if (isset($field->field_options['separate_value']) && $field->field_options['separate_value']) {
                            $separate_value = true;
                        }

                        if (isset($field->options) && is_array($field->options)) {

                            foreach ($field->options as $opt_key => $option) {
                                if (is_array($option)) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-radio',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => $image_options ? '[' . $field_id . ' show="value"]' : '[' . $field_id . ']',
                                            'option' => $image_options && $separate_value && isset($option['value']) ? $option['value'] : $option['label'],
                                            'group' => "[$field_id]",
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
                                } else {

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-radio',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => "[$field_id]",
                                            'option' => substr($opt_key, 0, 6) === "other_" ? '[' . $field_id . ' show="value" key="other"]' : $option,
                                            'group' => "[$field_id]",
                                        )
                                    ));
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'float' => true,
                                        'properties' => array(
                                            'left' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => $option
                                        )
                                    ));

                                    if (substr($opt_key, 0, 6) === "other_") {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => '100%',
                                                'height' => 'auto',
                                                'value' => '[' . $field_id . ' show="value" key="other"]',
                                            )
                                        ));
                                    }
                                }
                            }
                        }
                        break;
                    case 'checkbox':
                        $elements[] = $this->auto_field($field, array(
                            'type' => 'e2pdf-html',
                            'block' => true,
                            'properties' => array(
                                'top' => '20',
                                'left' => '20',
                                'right' => '20',
                                'width' => '100%',
                                'height' => 'auto',
                                'value' => $field->name,
                            )
                        ));

                        $image_options = false;
                        if (isset($field->field_options['image_options']) && $field->field_options['image_options']) {
                            $image_options = true;
                        }

                        $separate_value = false;
                        if (isset($field->field_options['separate_value']) && $field->field_options['separate_value']) {
                            $separate_value = true;
                        }

                        if (isset($field->options) && is_array($field->options)) {
                            foreach ($field->options as $opt_key => $option) {
                                if (is_array($option)) {
                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-checkbox',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => $image_options ? '[' . $field_id . ' show="value"]' : '[' . $field_id . ']',
                                            'option' => $image_options && $separate_value && isset($option['value']) ? $option['value'] : $option['label'],
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
                                } else {

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-checkbox',
                                        'properties' => array(
                                            'top' => '5',
                                            'width' => 'auto',
                                            'height' => 'auto',
                                            'value' => "[$field_id]",
                                            'option' => substr($opt_key, 0, 6) === "other_" ? '[' . $field_id . ' show="value" key="' . $opt_key . '"]' : $option
                                        )
                                    ));

                                    $elements[] = $this->auto_field($field, array(
                                        'type' => 'e2pdf-html',
                                        'float' => true,
                                        'properties' => array(
                                            'left' => '5',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'value' => $option
                                        )
                                    ));

                                    if (substr($opt_key, 0, 6) === "other_") {
                                        $elements[] = $this->auto_field($field, array(
                                            'type' => 'e2pdf-input',
                                            'properties' => array(
                                                'top' => '5',
                                                'width' => '100%',
                                                'height' => 'auto',
                                                'value' => '[' . $field_id . ' show="value" key="' . $opt_key . '"]',
                                            )
                                        ));
                                    }
                                }
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        $response['page'] = array(
            'bottom' => '20',
            'top' => '20',
            'right' => '20',
            'left' => '20'
        );

        $response['elements'] = $elements;
        return $response;
    }

    /**
     * Generate field for Auto PDF
     * 
     * @param object $field - Formidable field object
     * @param string $type - Field type
     * @param array $options - Field additional options
     * 
     * @return array - Prepared auto field
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

        $classes = array();
        if (isset($field->field_options['classes'])) {
            $classes = explode(" ", $field->field_options['classes']);
        }

        $float_classes = array(
            'frm_half',
            'frm_third',
            'frm_two_thirds',
            'frm_fourth',
            'frm_three_fourths',
            'frm_fifth',
            'frm_two_fifths',
            'frm_sixth',
            'frm_seventh',
            'frm_eighth'
        );

        $array_intersect = array_intersect($classes, $float_classes);

        if (!empty($array_intersect) && !in_array('frm_first', $classes) && isset($element['block']) && $element['block']) {
            $element['float'] = true;
        };

        $primary_class = false;
        if (!empty($array_intersect)) {
            $primary_class = end($array_intersect);
        }

        if (isset($element['block']) && $element['block']) {
            switch ($primary_class) {
                case 'frm_half':
                    $element['properties']['width'] = '50%';
                    break;
                case 'frm_third':
                    $element['properties']['width'] = '33.3%';
                    break;
                case 'frm_two_thirds':
                    $element['properties']['width'] = '66.67%';
                    break;
                case 'frm_fourth':
                    $element['properties']['width'] = '25%';
                    break;
                case 'frm_three_fourths':
                    $element['properties']['width'] = '75%';
                    break;
                case 'frm_fifth':
                    $element['properties']['width'] = '20%';
                    break;
                case 'frm_two_fifths':
                    $element['properties']['width'] = '80%';
                    break;
                case 'frm_sixth':
                    $element['properties']['width'] = '16.67%';
                    break;
                case 'frm_seventh':
                    $element['properties']['width'] = '14.29%';
                    break;
                case 'frm_eighth':
                    $element['properties']['width'] = '12.5%';
                    break;
                default:
                    break;
            }
        }

        return $element;
    }

    /**
     * Backup form action
     * 
     * @param object xml - XML object where to add params for saved item
     */
    public function backup($xml = false) {
        if (class_exists('FrmXMLController')) {

            $ids = array();
            if ($this->get('item') == '-2') {
                $ids[] = $this->get('item1');
                $ids[] = $this->get('item2');
            } else {
                $ids[] = $this->get('item');
            }

            $type = array();
            $type[] = 'forms';

            ob_start();
            FrmXMLController::generate_xml($type, compact('ids'));
            $backup = ob_get_clean();
            $xml->addChildCData('formidable', base64_encode($backup));
        }
    }

    /**
     * Import form action
     * 
     * @param object xml - XML object to parse data to import form
     */
    public function import($xml, $options = array()) {

        $updated_items = array();
        $new_form = isset($options['formidable_item_new_form']) && $options['formidable_item_new_form'] ? true : false;

        if (class_exists('FrmXMLHelper') && class_exists('FrmField') && $this->get('item')) {

            if (isset($xml->formidable) && $xml->formidable) {

                $tmp = tempnam($this->helper->get('tmp_dir'), 'e2pdf');
                file_put_contents($tmp, base64_decode((String) $xml->formidable));

                if ($new_form) {
                    add_filter('frm_match_xml_form', array($this, 'filter_frm_match_xml_form'), 10, 2);
                }
                $result = FrmXMLHelper::import_xml($tmp);

                if ($new_form) {
                    remove_filter('frm_match_xml_form', array($this, 'filter_frm_match_xml_form'), 10);
                }
                unlink($tmp);

                FrmXMLHelper::parse_message($result, $message, $errors);

                if ($errors) {
                    return array(
                        'errors' => $errors
                    );
                } else {
                    if ($this->get('item') == '-2') {
                        if ($this->get('item1') && isset($result['forms'][$this->get('item1')])) {
                            $updated_items[$this->get('item1')] = $result['forms'][$this->get('item1')];
                        }
                        if ($this->get('item2') && isset($result['forms'][$this->get('item2')])) {
                            $updated_items[$this->get('item2')] = $result['forms'][$this->get('item2')];
                        }
                    } elseif (isset($result['forms'][$this->get('item')])) {
                        $updated_items[$this->get('item')] = $result['forms'][$this->get('item')];
                    }
                }
            }
        }
        return $updated_items;
    }

    public function after_import($old_template_id, $new_template_id) {

        if ($this->get('item') && $old_template_id && $new_template_id && $old_template_id != $new_template_id && class_exists('FrmForm')) {
            $forms = array();
            if ($this->get('item') == '-2') {
                if ($this->get('item1')) {
                    $forms[] = $this->get('item1');
                }
                if ($this->get('item2')) {
                    $forms[] = $this->get('item2');
                }
            } else {
                $forms[] = $this->get('item');
            }

            foreach ($forms as $form_id) {
                $form = FrmForm::getOne($form_id);
                if ($form) {
                    if (isset($form->options['success_msg'])) {
                        $success_msg = $this->replace_template_id($form->options['success_msg'], $old_template_id, $new_template_id);
                        if ($success_msg != $form->options['success_msg']) {
                            $update = array(
                                'options' => array(
                                    'success_msg' => $success_msg
                                )
                            );
                            FrmForm::update($form_id, $update);
                        }
                    }

                    if (class_exists('FrmFormAction')) {
                        $actions = FrmFormAction::get_action_for_form($form_id, 'email');
                        foreach ($actions as $action) {
                            if (isset($action->post_content['email_message'])) {
                                $email_message = $this->replace_template_id($action->post_content['email_message'], $old_template_id, $new_template_id);
                                if ($email_message != $action->post_content['email_message']) {
                                    $action->post_content['email_message'] = $email_message;
                                    FrmFormAction::save_settings($action);
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    public function replace_template_id($message, $old_template_id, $new_template_id) {
        $old_template_id = (int) $old_template_id;
        $new_template_id = (int) $new_template_id;

        $message = preg_replace('/\[(e2pdf-download|e2pdf-view|e2pdf-save|e2pdf-attachment|e2pdf-adobesign|e2pdf-zapier)(.*) id=(|\'|")' . $old_template_id . '(|\'|")(.*)\]/i', '[${1}${2} id=${3}' . $new_template_id . '${4}${5}]', $message);
        return $message;
    }

    /**
     * Replace Old Formidable shortcodes to new values on pages
     * 
     * @param array $pages - List of pages to replace shortcodes
     * @param array $shortcodes_list - List of new/old shortcodes to replace
     * 
     * @return array - List of updated pages
     */
    public function pages_replace_shortcodes($pages = array(), $shortcodes_list = array()) {

        foreach ($pages as $page_key => $page) {
            //replace page actions and conditions shortcodes
            if (isset($page['actions']) && !empty($page['actions'])) {
                foreach ($page['actions'] as $action_key => $action_value) {
                    if (isset($action_value['change'])) {
                        $pages[$page_key]['actions'][$action_key]['change'] = $this->replace_shortcodes($action_value['change'], $shortcodes_list);
                    }

                    if (isset($action_value['conditions']) && !empty($action_value['conditions'])) {
                        foreach ($action_value['conditions'] as $condition_key => $condition_value) {
                            if (isset($condition_value['if'])) {
                                $pages[$page_key]['actions'][$action_key]['conditions'][$condition_key]['if'] = $this->replace_shortcodes($condition_value['if'], $shortcodes_list);
                            }

                            if (isset($condition_value['value'])) {
                                $pages[$page_key]['actions'][$action_key]['conditions'][$condition_key]['value'] = $this->replace_shortcodes($condition_value['value'], $shortcodes_list);
                            }
                        }
                    }
                }
            }

            foreach ($page['elements'] as $element_key => $element_value) {
                $pages[$page_key]['elements'][$element_key]['value'] = $this->replace_shortcodes($element_value['value'], $shortcodes_list);

                if (isset($element_value['properties']['options'])) {
                    $pages[$page_key]['elements'][$element_key]['properties']['options'] = $this->replace_shortcodes($element_value['properties']['options'], $shortcodes_list);
                }

                if (isset($element_value['properties']['group'])) {
                    $pages[$page_key]['elements'][$element_key]['properties']['group'] = $this->replace_shortcodes($element_value['properties']['group'], $shortcodes_list);
                }

                //replace element actions and conditions shortcodes
                if (isset($element_value['actions']) && !empty($element_value['actions'])) {
                    foreach ($element_value['actions'] as $action_key => $action_value) {
                        if (isset($action_value['change'])) {
                            $pages[$page_key]['elements'][$element_key]['actions'][$action_key]['change'] = $this->replace_shortcodes($action_value['change'], $shortcodes_list);
                        }

                        if (isset($action_value['conditions']) && !empty($action_value['conditions'])) {
                            foreach ($action_value['conditions'] as $condition_key => $condition_value) {
                                if (isset($condition_value['if'])) {
                                    $pages[$page_key]['elements'][$element_key]['actions'][$action_key]['conditions'][$condition_key]['if'] = $this->replace_shortcodes($condition_value['if'], $shortcodes_list);
                                }

                                if (isset($condition_value['value'])) {
                                    $pages[$page_key]['elements'][$element_key]['actions'][$action_key]['conditions'][$condition_key]['value'] = $this->replace_shortcodes($condition_value['value'], $shortcodes_list);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $pages;
    }

    public function actions_replace_shortcodes($actions = array(), $shortcodes_list = array()) {

        foreach ($actions as $action_key => $action_value) {
            if (isset($action_value['change'])) {
                $actions[$action_key]['change'] = $this->replace_shortcodes($action_value['change'], $shortcodes_list);
            }

            if (isset($action_value['conditions']) && !empty($action_value['conditions'])) {
                foreach ($action_value['conditions'] as $condition_key => $condition_value) {
                    if (isset($condition_value['if'])) {
                        $actions[$action_key]['conditions'][$condition_key]['if'] = $this->replace_shortcodes($condition_value['if'], $shortcodes_list);
                    }

                    if (isset($condition_value['value'])) {
                        $actions[$action_key]['conditions'][$condition_key]['value'] = $this->replace_shortcodes($condition_value['value'], $shortcodes_list);
                    }
                }
            }
        }

        return $actions;
    }

    /**
     * Replace Old Formidable shortcodes to new values
     * 
     * @param string $content - Value that can contains old shortcodes
     * @param array $shortcodes_list - List of new/old shortcodes to replace
     * 
     * @return string - Updated value
     */
    public function replace_shortcodes($content = "", $shortcodes_list = array()) {

        if (false === strpos($content, '[')) {
            return $content;
        }

        foreach ($shortcodes_list as $list_key => $list_value) {
            $content = preg_replace("/(\[|\[(?:[^\]]*?)\s|\[(?:[^\]]*?)(?:field_id|e2pdf-frm-data-values id|e2pdf-frm-lookup-values id)=(?:\'|\"|)){$list_key}(\:(?:.*?)\]|\s(?:.*?)\]|(?:\'|\")(?:.*?)\]|\])/", '${1}' . $list_value . '${2}', $content);
        }

        return $content;
    }

    /**
     * Verify if item and dataset exists
     * 
     * @return bool - item and dataset exists
     */
    public function verify() {

        $verify = false;
        if (class_exists('FrmEntry') && class_exists('FrmForm')) {
            if ($this->get('item') == '-2') {
                if ($this->get('dataset')) {
                    if ($this->get('item1')) {
                        $entry = FrmEntry::getOne($this->get('dataset'));
                        if (is_object($entry) && $entry->form_id == $this->get('item1')) {
                            $verify = true;
                        } else {
                            $this->set('item1', '');
                            $this->set('dataset', '');
                        }
                    }
                }
                if ($this->get('dataset2')) {
                    if ($this->get('item2')) {
                        $entry = FrmEntry::getOne($this->get('dataset2'));
                        if (is_object($entry) && $entry->form_id == $this->get('item2')) {
                            $verify = true;
                        } else {
                            $this->set('item2', '');
                            $this->set('dataset2', '');
                        }
                    }
                }
            } else {
                if ($this->get('item') && $this->get('dataset')) {
                    $entry = FrmEntry::getOne($this->get('dataset'));
                    if (is_object($entry) && $entry->form_id == $this->get('item')) {
                        $verify = true;
                    }
                }
            }
        }
        return $verify;
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
                'form_key' => '',
                'name' => $template->get('title'),
                'description' => '',
                'status' => 'published',
                'options' => array(
                    'success_msg' => sprintf(__('Success. [e2pdf-download id="%s"]', 'e2pdf'), $template->get('ID'))
                )
            );

            if ($item = FrmForm::create($form)) {
                $template->set('item', $item);

                $checkboxes = array();
                $radios = array();

                $pages = $template->get('pages');

                foreach ($pages as $page_key => $page) {
                    if (isset($page['elements']) && !empty($page['elements'])) {
                        foreach ($page['elements'] as $element_key => $element) {
                            $field_values = array();
                            if ($element['type'] == 'e2pdf-input' || ($element['type'] == 'e2pdf-signature' && !class_exists('FrmSigAppHelper'))) {
                                $field_values = FrmFieldsHelper::setup_new_vars('text', $item);
                            } elseif ($element['type'] == 'e2pdf-signature' && class_exists('FrmSigAppHelper')) {
                                $field_values = FrmFieldsHelper::setup_new_vars('signature', $item);
                            } elseif ($element['type'] == 'e2pdf-textarea') {
                                $field_values = FrmFieldsHelper::setup_new_vars('textarea', $item);
                            } elseif ($element['type'] == 'e2pdf-select') {
                                $field_values = FrmFieldsHelper::setup_new_vars('select', $item);
                                $field_values['options'] = array();
                                if (isset($element['properties']['options'])) {
                                    $field_options = explode("\n", $element['properties']['options']);
                                    foreach ($field_options as $option) {
                                        $field_values['options'][] = $option;
                                    }
                                }
                            } elseif ($element['type'] == 'e2pdf-checkbox') {
                                $field_key = array_search($element['name'], array_column($checkboxes, 'name'));
                                if ($field_key !== false) {
                                    $checkboxes[$field_key]['options'][] = $element['properties']['option'];
                                    $pages[$page_key]['elements'][$element_key]['value'] = '[' . $checkboxes[$field_key]['element_id'] . ']';
                                } else {
                                    $field_values = FrmFieldsHelper::setup_new_vars('checkbox', $item);
                                    $field_values['options'] = array();
                                }
                            } elseif ($element['type'] == 'e2pdf-radio') {
                                if (isset($element['properties']['group']) && $element['properties']['group']) {
                                    $element['name'] = $element['properties']['group'];
                                } else {
                                    $element['name'] = $element['element_id'];
                                }

                                $field_key = array_search($element['name'], array_column($radios, 'name'));
                                if ($field_key !== false) {
                                    $radios[$field_key]['options'][] = $element['properties']['option'];
                                    $pages[$page_key]['elements'][$element_key]['value'] = '[' . $radios[$field_key]['element_id'] . ']';
                                } else {
                                    $field_values = FrmFieldsHelper::setup_new_vars('radio', $item);
                                    $field_values['options'] = array();
                                }
                            }

                            if (!empty($field_values) && $field_id = FrmField::create($field_values)) {
                                $field = FrmField::getOne($field_id);
                                $labels = array();

                                if ($auto_form_shortcode) {
                                    $labels[] = get_option('e2pdf_formidable_use_keys') ? '[' . $field->field_key . ']' : '[' . $field->id . ']';
                                }

                                if ($auto_form_label && $auto_form_label == 'value' && isset($element['value']) && $element['value']) {
                                    $labels[] = $element['value'];
                                } elseif ($auto_form_label && $auto_form_label == 'name' && isset($element['name']) && $element['name']) {
                                    $labels[] = $element['name'];
                                }

                                if (!empty($labels)) {
                                    $update = array(
                                        'name' => implode(' ', $labels)
                                    );
                                    FrmField::update($field_id, $update);
                                }

                                if ($element['type'] == 'e2pdf-textarea') {
                                    $pages[$page_key]['elements'][$element_key]['value'] = get_option('e2pdf_formidable_use_keys') ? '[' . $field->field_key . '  wpautop=0]' : '[' . $field->id . '  wpautop=0]';
                                } else {
                                    $pages[$page_key]['elements'][$element_key]['value'] = get_option('e2pdf_formidable_use_keys') ? '[' . $field->field_key . ']' : '[' . $field->id . ']';
                                }

                                if (isset($element['properties']['esig'])) {
                                    unset($pages[$page_key]['elements'][$element_key]['properties']['esig']);
                                }

                                if ($element['type'] == 'e2pdf-checkbox') {
                                    $checkboxes[] = array(
                                        'name' => $element['name'],
                                        'element_id' => $field_id,
                                        'field_id' => $field->id,
                                        'options' => array(
                                            $element['properties']['option'],
                                        )
                                    );
                                } elseif ($element['type'] == 'e2pdf-radio') {
                                    $radios[] = array(
                                        'name' => $element['name'],
                                        'element_id' => $field_id,
                                        'field_id' => $field->id,
                                        'options' => array(
                                            $element['properties']['option'],
                                        )
                                    );
                                }
                            }
                        }
                    }
                }

                foreach ($checkboxes as $element) {
                    $update = array(
                        'options' => $element['options']
                    );
                    FrmField::update($element['field_id'], $update);
                }

                foreach ($radios as $element) {
                    $update = array(
                        'options' => $element['options']
                    );
                    FrmField::update($element['field_id'], $update);
                }

                $template->set('pages', $pages);
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

        if ($item && class_exists('FrmformsController')) {

            if (class_exists('FrmProFieldsHelper')) {
                add_filter('frm_get_paged_fields', array($this, 'filter_remove_pagebreaks'), 9, 1);
            }
            add_filter('frm_show_new_entry_page', array($this, 'filter_frm_show_new_entry_page'), 99);
            $source = FrmformsController::show_form($item, '', true, true);
            if (class_exists("FrmProFieldsHelper")) {
                remove_filter('frm_get_paged_fields', array($this, 'filter_remove_pagebreaks'), 9);
            }
            remove_filter('frm_show_new_entry_page', array($this, 'filter_frm_show_new_entry_page'), 99);

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
                    'frm_ajax_loading',
                    'wp-editor-tools',
                    'quicktags-toolbar',
                    'frm_button_submit',
                    'frm_range_value',
                    'star-rating',
                    'frm_repeat_buttons',
                    'frm_save_draft',
                    'frm_final_submit'
                );
                foreach ($remove_by_class as $key => $class) {
                    $elements = $xpath->query("//*[contains(@class, '{$class}')]");
                    foreach ($elements as $element) {
                        $element->parentNode->removeChild($element);
                    }
                }

                $remove_by_tag = array(
                    'link',
                    'style',
                    'script'
                );
                foreach ($remove_by_tag as $key => $tag) {
                    $elements = $xpath->query("//{$tag}");
                    foreach ($elements as $element) {
                        $element->parentNode->removeChild($element);
                    }
                }

                $remove_classes = array(
                    'wp-editor-container',
                    'frm_logic_form',
                    'frm_pos_none'
                );

                foreach ($remove_classes as $key => $class) {
                    $elements = $xpath->query("//*[contains(@class, '{$class}')]");
                    foreach ($elements as $element) {
                        $element = $xml->set_node_value($element, 'class', str_replace($class, '', $xml->get_node_value($element, 'class')));
                    }
                }

                $remove_styles = array(
                    'frm_toggle_container'
                );

                foreach ($remove_styles as $key => $class) {
                    $elements = $xpath->query("//*[contains(@class, '{$class}')]");
                    foreach ($elements as $element) {
                        $element = $xml->set_node_value($element, 'style', '');
                    }
                }

                /*
                 * Metas patterns to replace field names
                 * @since 0.01.42
                 */
                $metas_pattern = array(
                    '/item_meta\[(?:.*?)\]\[0\]\[(.*?)\](?:\[\])?/i' => '$1:1',
                    '/item_meta\[(.*?)\](\[\])?/i' => '$1',
                );

                // Replace names
                $metas = $xpath->query("//*[contains(@name, 'item_meta')]");
                foreach ($metas as $element) {
                    $field_id = preg_replace(array_keys($metas_pattern), $metas_pattern, $xml->get_node_value($element, 'name'));
                    if (get_option('e2pdf_formidable_use_keys') && class_exists('FrmField')) {
                        if (strpos($field_id, ':') !== false) {
                            $repeat_data = explode(":", $field_id);
                            if (isset($repeat_data['0'])) {
                                $field_data = FrmField::getOne($repeat_data['0']);
                                if ($field_data) {
                                    $field_id = $field_data->field_key . ":1";
                                }
                            }
                        } else {
                            $field_data = FrmField::getOne($field_id);
                            if ($field_data) {
                                $field_id = $field_data->field_key;
                            }
                        }
                    }
                    $element = $xml->set_node_value($element, 'name', $field_id);
                }

                $frm_combo_inputs_container = $xpath->query("//*[contains(@class, 'frm_combo_inputs_container')]");
                foreach ($frm_combo_inputs_container as $element) {
                    $inputs = $xpath->query(".//input", $element);
                    $selects = $xpath->query(".//select", $element);

                    foreach ($selects as $key => $sub_element) {
                        $field_data = array();
                        preg_match('/(.*?)\[(.*?)\]/i', $xml->get_node_value($sub_element, 'name'), $field_data);
                        if (!empty($field_data) && isset($field_data[1]) && isset($field_data[2])) {
                            $field_id = $field_data[1] . ' show="' . $field_data[2] . '"';
                            $sub_element = $xml->set_node_value($sub_element, 'name', $field_id);
                        }
                    }

                    foreach ($inputs as $key => $sub_element) {
                        $field_data = array();
                        preg_match('/(.*?)\[(.*?)\]/i', $xml->get_node_value($sub_element, 'name'), $field_data);
                        if (!empty($field_data) && isset($field_data[1]) && isset($field_data[2])) {
                            $field_id = $field_data[1] . ' show="' . $field_data[2] . '"';
                            $sub_element = $xml->set_node_value($sub_element, 'name', $field_id);
                        }
                    }
                }

                $frm_dropzone = $xpath->query("//*[contains(@class, 'frm_dropzone')]/parent::*");
                foreach ($frm_dropzone as $element) {
                    $dropzone = $xpath->query(".//*[contains(@class, 'frm_dropzone')]", $element)->item(0);
                    $input = $xpath->query(".//input", $element)->item(0);
                    if ($input && $dropzone) {
                        $input_cloned = $input->cloneNode(true);

                        $input_cloned = $xml->set_node_value($input_cloned, 'type', 'text');
                        $input_cloned = $xml->set_node_value($input_cloned, 'value', __('File Upload', 'e2pdf'));
                        $input_cloned = $xml->set_node_value($input_cloned, 'style', 'width: 100%; height: 200px; text-align: center;');

                        if (strpos($xml->get_node_value($input_cloned, 'name'), ':') !== false) {
                            $input_cloned = $xml->set_node_value($input_cloned, 'name', $xml->get_node_value($input_cloned, 'name') . ' size="full" show_image="0" add_link="0"');
                        } else {
                            $input_cloned = $xml->set_node_value($input_cloned, 'name', $xml->get_node_value($input_cloned, 'name') . ' size="full"');
                        }

                        $input->parentNode->replaceChild($input_cloned, $input);
                        $dropzone->parentNode->removeChild($dropzone);
                    }
                }

                // Replace signature
                $sigpad = $xpath->query("//*[contains(@class, 'sigPad')]");
                foreach ($sigpad as $element) {
                    $input = $xpath->query(".//input", $element)->item(0);
                    if ($input) {
                        $input_cloned = $input->cloneNode(true);

                        $field_id = preg_replace('/\[(.*?)\]/i', '', $xml->get_node_value($input_cloned, 'name'));
                        if (get_option('e2pdf_formidable_use_keys') && class_exists("FrmField")) {
                            $field_data = FrmField::getOne($field_id);
                            if ($field_data) {
                                $field_id = $field_data->field_key;
                            }
                        }

                        $input_cloned = $xml->set_node_value($input_cloned, 'style', 'width: 300px; height: 100px;');
                        $input_cloned = $xml->set_node_value($input_cloned, 'name', $field_id);
                        $element->parentNode->replaceChild($input_cloned, $element);
                    }
                }

                // Replace names
                $fields = $xpath->query("//input");
                foreach ($fields as $element) {

                    $image_options = false;
                    $separate_value = false;

                    if (class_exists('FrmField') && ($xml->get_node_value($element, 'type') == 'checkbox' || $xml->get_node_value($element, 'type') == 'radio')) {

                        $field_id = $xml->get_node_value($element, 'name');
                        if (false !== strpos($xml->get_node_value($element, 'name'), ':')) {
                            $field_id = substr($xml->get_node_value($element, 'name'), 0, strpos($xml->get_node_value($element, 'name'), ":"));
                        }
                        if (preg_match("/\[(other(.*?[^\]]))\]/", $field_id)) {
                            $field_id = preg_replace('/\[(other(.*?[^\]]))\]/', '', $field_id);
                        }

                        $field_data = FrmField::getOne($field_id);

                        if (isset($field_data->field_options['image_options']) && $field_data->field_options['image_options']) {
                            $image_options = true;
                        }

                        if (isset($field_data->field_options['separate_value']) && $field_data->field_options['separate_value']) {
                            $separate_value = true;
                        }

                        if (isset($field_data->type) && $field_data->type === 'data' && isset($field_data->field_options['form_select'])) {
                            $dynamic_form_id = false;
                            $dynamic_field_id = false;

                            $dynamic_field_id = $field_data->field_options['form_select'];
                            $dynamic_field_data = FrmField::getOne($dynamic_field_id);
                            if ($dynamic_field_data) {
                                $dynamic_form_id = $dynamic_field_data->form_id;
                            }

                            $options = array();
                            if (class_exists('FrmEntry') && class_exists('FrmEntryMeta')) {
                                $where = array(
                                    'it.form_id' => $dynamic_form_id
                                );
                                $entries_tmp = FrmEntry::getAll($where, ' ORDER BY id ASC');
                                foreach ($entries_tmp as $key => $entry) {
                                    $options[] = array(
                                        'label' => FrmEntryMeta::get_meta_value($entry, $dynamic_field_id),
                                        'value' => $key,
                                    );
                                }
                            }
                            $field_data->options = $options;
                        }

                        if (isset($field_data->options) && is_array($field_data->options)) {

                            $field_options = $field_data->options;
                            $field_value = $xml->get_node_value($element, 'value');

                            $field_key = false;
                            foreach ($field_options as $fv_key => $fv) {
                                if (isset($fv['value']) && $fv['value'] == $field_value) {
                                    $field_key = $fv_key;
                                    break;
                                }
                            }

                            if ($field_key !== false) {
                                if ($image_options && $separate_value) {
                                    $field_label = isset($field_options[$field_key]['value']) ? $field_options[$field_key]['value'] : $field_value;
                                } else {
                                    $field_label = isset($field_options[$field_key]['label']) ? $field_options[$field_key]['label'] : $field_value;
                                }
                                $element = $xml->set_node_value($element, 'value', $field_label);
                            } elseif ($xml->get_node_value($element, 'type') == 'radio') {
                                $element = $xml->set_node_value($element, 'value', '[' . $field_id . ' show="value" key="other"]');
                            } else {
                                $field_key = array_search($field_value, $field_options);
                                if ($field_key !== false) {
                                    $element = $xml->set_node_value($element, 'value', '[' . $field_id . ' show="value" key="' . $field_key . '"]');
                                }
                            }
                        }
                    }

                    $name = false;
                    if ($xml->get_node_value($element, 'type') == 'text') {
                        if (preg_match("/other\[(.*?[^\]])\]\[(other(.*?[^\]]))\]/", $xml->get_node_value($element, 'name'))) {
                            $name = preg_replace('/other\[(.*?[^\]])\]\[(other(.*?[^\]]))\]/', '$1 show="value" key="$2"', $xml->get_node_value($element, 'name'));
                        } elseif (preg_match("/other\[(.*?[^\]])\]/", $xml->get_node_value($element, 'name'))) {
                            $name = preg_replace('/other\[(.*?[^\]])\]/', '$1 show="value" key="other"', $xml->get_node_value($element, 'name'));
                        }
                    } elseif ($xml->get_node_value($element, 'type') == 'checkbox') {
                        if (preg_match("/\[(other(.*?[^\]]))\]/", $xml->get_node_value($element, 'name'))) {
                            $name = preg_replace('/\[(other(.*?[^\]]))\]/', '', $xml->get_node_value($element, 'name'));
                        } else {
                            if ($image_options) {
                                $name = $xml->get_node_value($element, 'name') . ' show="value"';
                            }
                        }
                    } elseif ($xml->get_node_value($element, 'type') == 'radio') {
                        if ($image_options) {
                            $name = $xml->get_node_value($element, 'name') . ' show="value"';
                        }
                    }

                    if (!$name) {
                        $name = $xml->get_node_value($element, 'name');
                    }

                    $element = $xml->set_node_value($element, 'name', '[' . $name . ']');
                }

                // Replace names
                $textareas = $xpath->query("//textarea");
                foreach ($textareas as $element) {
                    $element = $xml->set_node_value($element, 'name', '[' . $xml->get_node_value($element, 'name') . ' wpautop=0]');
                }

                // Replace names
                $selects = $xpath->query("//select");
                foreach ($selects as $element) {

                    if (class_exists('FrmField')) {

                        $options = $xpath->query(".//option", $element);
                        $field_id = $xml->get_node_value($element, 'name');

                        //Time Field names
                        if (false !== strpos($xml->get_node_value($element, 'name'), ':')) {
                            $field_id = substr($xml->get_node_value($element, 'name'), 0, strpos($xml->get_node_value($element, 'name'), ":"));
                        }
                        $field_id = str_replace(array('[H]', '[m]', '[A]'), "", $field_id);

                        $field_data = FrmField::getOne($field_id);
                        if (isset($field_data->type) && $field_data->type === 'lookup') {
                            foreach ($options as $option) {
                                $option->parentNode->removeChild($option);
                            }
                            $wrapper = $dom->createElement('option');
                            $wrapper_atts = array(
                                'value' => '[e2pdf-frm-lookup-values id="' . $field_id . '"]',
                            );
                            foreach ($wrapper_atts as $key => $value) {
                                $attr = $dom->createAttribute($key);
                                $attr->value = $value;
                                $wrapper->appendChild($attr);
                            }
                            $element->appendChild($wrapper);
                        } else if (isset($field_data->type) && $field_data->type === 'data') {
                            foreach ($options as $option) {
                                $option->parentNode->removeChild($option);
                            }
                            $wrapper = $dom->createElement('option');
                            $wrapper_atts = array(
                                'value' => '[e2pdf-frm-data-values id="' . $field_id . '"]',
                            );
                            foreach ($wrapper_atts as $key => $value) {
                                $attr = $dom->createAttribute($key);
                                $attr->value = $value;
                                $wrapper->appendChild($attr);
                            }
                            $element->appendChild($wrapper);
                        } else if (isset($field_data->type) && $field_data->type === 'time') {
                            $replace = array(
                                '[m]' => ' format="i"',
                                '[A]' => ' format="A"',
                            );
                            if (isset($field_data->field_options['clock']) && $field_data->field_options['clock'] == '12') {
                                $replace['[H]'] = ' format="g"';
                            } else {
                                $replace['[H]'] = ' format="H"';
                            }
                            $element = $xml->set_node_value($element, 'name', str_replace(array_keys($replace), $replace, $xml->get_node_value($element, 'name')));
                        }

                        if (isset($field_data->options) && is_array($field_data->options)) {
                            $field_options = $field_data->options;
                            foreach ($options as $option) {
                                $field_value = $xml->get_node_value($option, 'value');
                                foreach ($field_options as $field_option) {
                                    if (isset($field_option['value']) && $field_option['value'] === $field_value) {
                                        $field_label = isset($field_option['label']) ? $field_option['label'] : $field_value;
                                        $option = $xml->set_node_value($option, 'value', $field_label);
                                    }
                                }
                            }
                        }
                    }

                    $element = $xml->set_node_value($element, 'name', '[' . $xml->get_node_value($element, 'name') . ']');
                }

                // Show Total Formatted Fields
                $total_formatted = $xpath->query("//*[contains(@class, 'frm_total_formatted')]/parent::*");
                foreach ($total_formatted as $element) {
                    $elements = $xpath->query("//*[contains(@class, 'frm_hidden')]");
                    foreach ($elements as $element) {
                        $element = $xml->set_node_value($element, 'class', str_replace('frm_hidden', '', $xml->get_node_value($element, 'class')));
                    }
                }

                return $dom->saveHTML();
            }
        }
        return false;
    }

    /**
     * Convert Field name to Value
     * @since 0.01.34
     * 
     * @param string $name - Field name
     * 
     * @return bool|string - Converted value or false
     */
    public function auto_map($name = false) {

        $item = $this->get('item');

        if ($item && class_exists("FrmField")) {
            $where = array(
                'fi.form_id' => (int) $item,
                array('or' => 1, 'fi.field_key' => $name, 'fi.name' => $name)
            );
            $field = FrmField::getAll($where, 'id ASC', '1');

            if ($field && is_object($field)) {
                $field_id = get_option('e2pdf_formidable_use_keys') ? $field->field_key : $field->id;
                if ($field->type == 'textarea' || $field->type == 'rte') {
                    return "[" . $field_id . "  wpautop=0]";
                } else {
                    return "[" . $field_id . "]";
                }
            }
        }

        return false;
    }

    /**
     * Load additional shortcodes for this extension
     */
    public function load_shortcodes() {
        add_shortcode('e2pdf-frm-entry-values', array($this, 'shortcode_e2pdf_frm_entry_values'));
        add_shortcode('e2pdf-frm-lookup-values', array($this, 'shortcode_e2pdf_frm_lookup_values'));
        add_shortcode('e2pdf-frm-data-values', array($this, 'shortcode_e2pdf_frm_data_values'));
        add_shortcode('e2pdf-frm-repeatable', array($this, 'shortcode_e2pdf_frm_repeatable'));
    }

    /**
     * [e2pdf-frm-repeatable id='{entry_id}' field_id='{field_id}'] shortcode
     * 
     * @param array $atts - Atributes for shortcode
     * 
     * @return string - Output of shortcode
     */
    public function shortcode_e2pdf_frm_repeatable($atts = array()) {
        $id = (int) $atts['id'];
        $field_id = $atts['field_id'];

        $response = "[frm-field-value field_id='{$field_id}' entry='{$id}']";
        return $response;
    }

    /**
     * [e2pdf-frm-entry-values id='{form_id}' field_id='{field_id}' separator=''] shortcode
     * 
     * @param array $atts - Atributes for shortcode
     * 
     * @return string - Output of shortcodes
     */
    public function shortcode_e2pdf_frm_entry_values($atts = array()) {
        $form_id = (int) $atts['id'];
        $field_id = $atts['field_id'];
        $separator = isset($atts['separator']) ? $atts['separator'] : "\r\n";

        $response = '';
        $values = array();
        if ($form_id && $field_id && class_exists('FrmEntry') && class_exists('FrmEntryMeta')) {

            $where = array(
                'it.form_id' => $form_id
            );
            $entries_tmp = FrmEntry::getAll($where, ' ORDER BY id ASC');
            foreach ($entries_tmp as $key => $entry) {
                $values[] = FrmEntryMeta::get_meta_value($entry, $field_id);
            }
        }
        $response = implode($separator, $values);
        return $response;
    }

    /**
     * [e2pdf-frm-lookup-values id='{field_id}'] shortcode
     * 
     * @param array $atts - Atributes for shortcode
     * 
     * @return string - Output of shortcode
     */
    public function shortcode_e2pdf_frm_lookup_values($atts = array()) {

        $id = isset($atts['id']) ? $atts['id'] : false;
        $user_id = isset($atts['user_id']) ? $atts['user_id'] : '0';
        $separator = isset($atts['separator']) ? $atts['separator'] : "\r\n";

        $response = '';
        $values = array();

        if ($id && class_exists('FrmField') && class_exists('FrmAppHelper') && class_exists('FrmFieldsHelper')) {
            $field = FrmField::getOne($id);
            if ($field && $field->type === 'lookup') {

                $frm_filter = false;
                if (class_exists('FrmProFieldsHelper') && method_exists('FrmProFieldsHelper', 'add_default_field_settings')) {
                    if (!has_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings')) {
                        add_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings', 10, 2);
                        $frm_filter = true;
                    }
                }

                $args = array();
                if (is_admin()) {
                    $original_user_id = get_current_user_id();
                    wp_set_current_user($user_id);
                    if (!current_user_can('administrator')) {
                        add_filter('frm_lookup_is_current_user_filter_needed', array($this, 'filter_frm_lookup_is_current_user_filter_needed'), 10, 3);
                    }
                }

                $field_array = FrmAppHelper::start_field_array($field);
                FrmFieldsHelper::prepare_new_front_field($field_array, $field, $args);
                $field_array = array_merge($field->field_options, $field_array);
                if (isset($field_array['options']) && is_array($field_array['options'])) {
                    $values = $field_array['options'];
                }

                if (is_admin()) {
                    if (!current_user_can('administrator')) {
                        remove_filter('frm_lookup_is_current_user_filter_needed', array($this, 'filter_frm_lookup_is_current_user_filter_needed'), 10);
                    }
                    wp_set_current_user($original_user_id);
                }
                if ($frm_filter) {
                    remove_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings', 10);
                }
            }
        }

        $response = implode($separator, $values);
        return $response;
    }

    /**
     * [e2pdf-frm-data-values id='{field_id}'] shortcode
     * 
     * @param array $atts - Atributes for shortcode
     * 
     * @return string - Output of shortcode
     */
    public function shortcode_e2pdf_frm_data_values($atts = array()) {

        $id = isset($atts['id']) ? $atts['id'] : false;
        $user_id = isset($atts['user_id']) ? $atts['user_id'] : '0';
        $separator = isset($atts['separator']) ? $atts['separator'] : "\r\n";

        $response = '';
        $values = array();

        if ($id && class_exists('FrmField') && class_exists('FrmAppHelper') && class_exists('FrmFieldsHelper')) {
            $field = FrmField::getOne($id);
            if ($field && $field->type === 'data') {
                $frm_filter = false;
                if (class_exists('FrmProFieldsHelper') && method_exists('FrmProFieldsHelper', 'add_default_field_settings')) {
                    if (!has_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings')) {
                        add_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings', 10, 2);
                        $frm_filter = true;
                    }
                }

                $args = array();
                if (is_admin()) {
                    $original_user_id = get_current_user_id();
                    wp_set_current_user($user_id);
                    if (current_user_can('administrator')) {
                        $field->field_options['restrict'] = '0';
                    }
                }

                $field_array = FrmAppHelper::start_field_array($field);
                FrmFieldsHelper::prepare_new_front_field($field_array, $field, $args);

                $field_array = array_merge($field->field_options, $field_array);
                if (isset($field_array['options']) && is_array($field_array['options'])) {
                    $values = $field_array['options'];
                }

                if (is_admin()) {
                    wp_set_current_user($original_user_id);
                }
                if ($frm_filter) {
                    remove_filter('frm_default_field_options', 'FrmProFieldsHelper::add_default_field_settings', 10);
                }
            }
        }

        $response = implode($separator, $values);
        return $response;
    }

    /**
     * Get styles for generating Map Field function
     * 
     * @return array - List of css files to load
     */
    public function styles($item = false) {

        $styles = array();
        if (class_exists('FrmStylesHelper')) {
            $uploads = FrmStylesHelper::get_upload_base();
            $saved_css_path = '/formidable/css/formidablepro.css';
            if (is_readable($uploads['basedir'] . $saved_css_path)) {
                $url = $uploads['baseurl'] . $saved_css_path;
            } else {
                $url = admin_url('admin-ajax.php?action=frmpro_css');
            }
            $styles[] = $url;
            $styles[] = plugins_url('css/extension/formidable.css?v=' . time(), $this->helper->get('plugin_file_path'));
        }

        return $styles;
    }

    public function merged_items() {
        return true;
    }

}
