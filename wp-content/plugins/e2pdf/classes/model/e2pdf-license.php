<?php

/**
 * E2pdf License Model
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

class Model_E2pdf_License extends Model_E2pdf_Model {

    private $license;

    function __construct() {
        parent::__construct();
        $this->load_license();
    }

    public function get($key) {
        if (isset($this->license[$key])) {
            return $this->license[$key];
        } else {
            return false;
        }
    }

    public function load_license() {
        $model_e2pdf_api = new Model_E2pdf_Api;
        $model_e2pdf_api->set(array(
            'action' => 'license/info',
        ));
        $license = $model_e2pdf_api->request();
        $this->license = $license;
    }
    
    public function reload_license() {
        $this->load_license();
    }

}
