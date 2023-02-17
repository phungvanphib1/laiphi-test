<?php

/**
 * E2pdf Template Model
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      0.01.33
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Model_E2pdf_Entry extends Model_E2pdf_Model {

    private $table;
    private $key;
    private $entry = array();

    /*
     * On Template init
     */

    function __construct() {
        global $wpdb;
        parent::__construct();
        $this->table = $wpdb->prefix . 'e2pdf_entries';
        $this->key = hash('sha256', md5(NONCE_KEY));
    }

    /**
     * Load Template by ID
     * 
     * @param int $entry_id - ID of template
     */
    public function load($entry_id = false) {
        global $wpdb;

        $entry = false;
        if ($this->helper->get('cache')) {
            $entry = wp_cache_get($entry_id, 'e2pdf_entries');
        }

        if ($entry === false) {
            $this->helper->clear_cache();
            $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$this->get_table()}` WHERE ID = %d", $entry_id), ARRAY_A);
            if ($this->helper->get('cache')) {
                wp_cache_set($entry_id, $entry, 'e2pdf_entries');
            }
        }

        if ($entry) {
            $this->entry = $entry;
            $this->set('entry', unserialize($entry['entry']));
            return true;
        }
        return false;
    }

    /**
     * Load Entry by UID
     * 
     * @param int $entry_uid - ID of template
     */
    public function load_by_uid($entry_uid = false) {
        global $wpdb;
        if ($entry_uid) {

            $entry = false;
            if ($this->helper->get('cache')) {
                $entry = wp_cache_get($entry_uid, 'e2pdf_uid_entries');
            }

            if ($entry === false) {
                $this->helper->clear_cache();
                $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$this->get_table()}` WHERE uid = %s", $entry_uid), ARRAY_A);
                if ($this->helper->get('cache')) {
                    wp_cache_set($entry_uid, $entry, 'e2pdf_uid_entries');
                }
            }

            if ($entry) {
                $this->entry = $entry;
                $this->set('entry', unserialize($entry['entry']));
                return true;
            }
        }
        return false;
    }

    /**
     * Get loaded Template
     * 
     * @return object
     */
    public function get_entry() {
        return $this->entry;
    }

    /**
     * Set Entry attribute
     * 
     * @param string $key - Attribute Key 
     * @param string $value - Attribute Value 
     */
    public function set($key, $value) {
        $this->entry[$key] = $value;
    }

    /**
     * Get Entry attribute by Key
     * 
     * @param string $key - Attribute Key 
     * 
     * @return mixed
     */
    public function get($key) {
        if (isset($this->entry[$key])) {
            $value = $this->entry[$key];
            return $value;
        } else {
            switch ($key) {
                case 'pdf_num':
                    $value = 0;
                    break;

                case 'entry':
                    $value = array();
                    break;

                case 'uid':
                    $value = md5(md5(serialize($this->get('entry'))) . md5($this->key));
                    break;

                default:
                    $value = '';
                    break;
            }
            return $value;
        }
    }

    /**
     * Before save template
     */
    public function pre_save() {

        $entry = array(
            'uid' => $this->get('uid'),
            'entry' => serialize($this->get('entry')),
            'pdf_num' => $this->get('pdf_num')
        );
        return $entry;
    }

    /**
     * Save entry
     */
    public function save() {
        global $wpdb;

        $entry = $this->pre_save();

        if ($this->get('ID')) {
            $where = array(
                'ID' => $this->get('ID')
            );
            $wpdb->update($this->get_table(), $entry, $where);
        } else {
            $wpdb->insert($this->get_table(), $entry);
            $this->set('ID', $wpdb->insert_id);
        }

        if ($this->helper->get('cache') && $this->get('ID')) {
            wp_cache_delete($this->get('ID'), 'e2pdf_entries');
            wp_cache_delete($entry['uid'], 'e2pdf_uid_entries');
        }
    }

    /**
     * Delete loaded entry
     */
    public function delete() {
        global $wpdb;
        if ($this->get('ID')) {
            $where = array(
                'ID' => $this->get('ID')
            );
            $wpdb->delete($this->get_table(), $where);

            if ($this->helper->get('cache')) {
                wp_cache_delete($this->get('ID'), 'e2pdf_entries');
                wp_cache_delete($this->get('uid'), 'e2pdf_uid_entries');
            }
        }
    }

    public function get_table() {
        return $this->table;
    }

}
