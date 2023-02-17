<?php

/**
 * E2pdf Revision Model
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      1.09.02
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Model_E2pdf_Revision extends Model_E2pdf_Model {

    private $template = array();
    private $extension = null;
    private $table;

    /*
     * On Revision init
     */

    function __construct() {
        global $wpdb;
        parent::__construct();
        $this->table = $wpdb->prefix . 'e2pdf_revisions';
    }

    /**
     * Load Revision by ID
     * 
     * @param int $template_id - ID of template
     */
    public function load($template_id, $full = true, $revision_id = 0) {
        global $wpdb;

        $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$this->get_table()}` WHERE template_id = %d AND revision_id = %d", $template_id, $revision_id), ARRAY_A);

        if ($template && $revision_id) {
            $this->template = $template;

            $extension = new Model_E2pdf_Extension();
            if ($this->get('extension')) {
                $extension->load($this->get('extension'));
            }
            if ($this->get('item')) {
                $extension->set('item', $this->get('item'));
            }
            if ($this->get('template_id')) {
                $extension->set('template_id', $this->get('template_id'));
            }
            $this->extension = $extension;

            if ($full) {
                $this->set('fonts', unserialize($template['fonts']));
                $this->set('actions', unserialize($template['actions']));

                $model_e2pdf_page = new Model_E2pdf_Page();
                $pages = $model_e2pdf_page->get_pages($this->get('template_id'), $revision_id);
                $this->set('pages', $pages);
            }
            return true;
        }
        return false;
    }

    public function revision($template_id = 0) {
        global $wpdb;
        if ($template_id) {
            $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM `" . $wpdb->prefix . "e2pdf_templates` WHERE ID = %d", $template_id), ARRAY_A);
            if ($template) {
                $this->template = $template;
                $this->set('fonts', unserialize($template['fonts']));
                $this->set('actions', unserialize($template['actions']));
                $this->set('template_id', $template_id);

                $condition = array(
                    'template_id' => array(
                        'condition' => '=',
                        'value' => $this->get('template_id'),
                        'type' => '%d'
                    )
                );
                $where = $this->helper->load('db')->prepare_where($condition);
                $wpdb->query($wpdb->prepare("UPDATE `" . $wpdb->prefix . "e2pdf_elements` set revision_id = revision_id + 1 " . $where['sql'] . "", $where['filter']));
                $wpdb->query($wpdb->prepare("UPDATE `" . $wpdb->prefix . "e2pdf_pages` set revision_id = revision_id + 1 " . $where['sql'] . "", $where['filter']));
                $wpdb->query($wpdb->prepare("UPDATE `" . $wpdb->prefix . "e2pdf_revisions` set revision_id = revision_id + 1 " . $where['sql'] . "", $where['filter']));

                $this->save();
            }
        }
        return false;
    }

    public function pre_save() {

        $template = array(
            'template_id' => $this->get('template_id'),
            'revision_id' => '1',
            'title' => $this->get('title'),
            'pdf' => $this->get('pdf'),
            'updated_at' => current_time('mysql', 1),
            'flatten' => $this->get('flatten'),
            'tab_order' => $this->get('tab_order'),
            'format' => $this->get('format'),
            'resample' => $this->get('resample'),
            'compression' => $this->get('compression'),
            'appearance' => $this->get('appearance'),
            'width' => $this->get('width'),
            'height' => $this->get('height'),
            'extension' => $this->get('extension'),
            'item' => $this->get('item'),
            'item1' => $this->get('item1'),
            'item2' => $this->get('item2'),
            'dataset_title' => $this->get('dataset_title'),
            'dataset_title1' => $this->get('dataset_title1'),
            'dataset_title2' => $this->get('dataset_title2'),
            'button_title' => $this->get('button_title'),
            'inline' => $this->get('inline'),
            'auto' => $this->get('auto'),
            'rtl' => $this->get('rtl'),
            'name' => $this->get('name'),
            'password' => $this->get('password'),
            'meta_title' => $this->get('meta_title'),
            'meta_subject' => $this->get('meta_subject'),
            'meta_author' => $this->get('meta_author'),
            'meta_keywords' => $this->get('meta_keywords'),
            'fonts' => serialize($this->get('fonts')),
            'font' => $this->get('font'),
            'font_size' => $this->get('font_size'),
            'font_color' => $this->get('font_color'),
            'line_height' => $this->get('line_height'),
            'text_align' => $this->get('line_height'),
            'author' => $this->get('author'),
            'actions' => serialize($this->get('actions')),
            'created_at' => $this->get('created_at'),
        );

        return $template;
    }

    /**
     * Get loaded Template
     * 
     * @return object
     */
    public function template() {
        return $this->template;
    }

    public function extension() {
        return $this->extension;
    }

    /**
     * Set Revision attribute
     * 
     * @param string $key - Attribute Key 
     * @param string $value - Attribute Value 
     */
    public function set($key, $value) {
        $this->template[$key] = $value;
    }

    /**
     * Get Revision attribute by Key
     * 
     * @param string $key - Attribute Key 
     * 
     * @return mixed
     */
    public function get($key) {
        if (isset($this->template[$key])) {
            $value = $this->template[$key];
            return $value;
        } else {
            switch ($key) {
                case 'title':
                    $value = __("(no title)", 'e2pdf');
                    break;
                case 'width':
                case 'height':
                case 'inline':
                case 'auto':
                case 'trash':
                case 'locked':
                case 'activated':
                case 'revision_id':
                case 'rtl':
                case 'tab_order':
                    $value = 0;
                    break;
                case 'flatten':
                case 'appearance':
                    $value = 1;
                    break;
                case 'compression':
                    $value = -1;
                    break;
                case 'format':
                    $value = 'pdf';
                    break;
                case 'resample':
                    $value = '100';
                    break;
                case 'text_align':
                    $value = 'left';
                    break;
                case 'fonts':
                case 'actions':
                    $value = array();
                    break;

                default:
                    $value = '';
                    break;
            }
            return $value;
        }
    }

    /**
     * Get Revisions table
     * 
     * @return string
     */
    public function get_table() {
        return $this->table;
    }

    /**
     * Delete loaded revision
     */
    public function delete() {
        global $wpdb;
        if ($this->get('template_id') && $this->get('revision_id')) {

            if ($this->get('pdf')) {
                $pdf_dir = $this->helper->get('pdf_dir') . $this->get('pdf') . "/";
                $this->helper->delete_dir($pdf_dir);
            }

            $where = array(
                'template_id' => $this->get('template_id'),
                'revision_id' => $this->get('revision_id')
            );
            $wpdb->delete($this->get_table(), $where);

            foreach ($this->get('pages') as $page) {
                $model_e2pdf_page = new Model_E2pdf_Page();
                $model_e2pdf_page->load($page['page_id'], $page['template_id'], $page['revision_id']);
                $model_e2pdf_page->delete();
            }
        }
    }

    public function save() {
        global $wpdb;

        $show_errors = false;
        if ($wpdb->show_errors) {
            $wpdb->show_errors(false);
            $show_errors = true;
        }

        if ($this->get('template_id')) {
            $template = $this->pre_save();
            $success = $wpdb->insert($this->get_table(), $template);
            if ($success === false) {
                $this->helper->init_db($wpdb->prefix);
                $wpdb->insert($this->get_table(), $template);
            }
        }

        if ($show_errors) {
            $wpdb->show_errors();
        }
    }

    public function flush() {
        if ($this->get('template_id')) {
            $revisions_limit = max(1, get_option('e2pdf_revisions_limit'));
            foreach ($this->revisions($this->get('template_id')) as $revision) {
                if ($revision['revision_id'] > $revisions_limit) {
                    $this->load($this->get('template_id'), true, $revision['revision_id']);
                    $this->delete();
                }
            }
        }
    }

    public function revisions($template_id = 0) {
        global $wpdb;

        $revisions = array();
        if ($template_id) {
            $condition = array(
                'template_id' => array(
                    'condition' => '=',
                    'value' => $template_id,
                    'type' => '%d'
                )
            );

            $where = $this->helper->load('db')->prepare_where($condition);
            $revisions = $wpdb->get_results($wpdb->prepare("SELECT `revision_id`, `updated_at` FROM " . $this->get_table() . $where['sql'] . " ORDER BY revision_id ASC", $where['filter']), ARRAY_A);
        }

        return $revisions;
    }

}
