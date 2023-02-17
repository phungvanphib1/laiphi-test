<?php

/**
 * E2pdf Api Model
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

class Model_E2pdf_Api extends Model_E2pdf_Model {

    protected $api = NULL;

    function __construct() {
        parent::__construct();
    }

    /**
     * Submit API Request to Remote Server
     * 
     * @param string $key - Return Value by Key
     * 
     * @return mixed 
     */
    public function request($key = false, $api_server = false) {
        if ($this->api->action) {

            $data = array(
                'api_url' => $this->get_domain(),
                'api_license_key' => $this->get_license(),
                'api_processor' => get_option('e2pdf_processor') ? get_option('e2pdf_processor') : 0,
                'api_version' => apply_filters('e2pdf_api_version', '1.16.19'),
            );

            if (!$api_server) {
                if (get_option('e2pdf_api')) {
                    $api_server = apply_filters('e2pdf_api', get_option('e2pdf_api'));
                } else {
                    $api_server = apply_filters('e2pdf_api', 'api.e2pdf.com');
                }
            }

            $request_url = 'https://' . $api_server . '/' . $this->api->action;

            $timeout = get_option('e2pdf_connection_timeout');
            if ($timeout === false) {
                $timeout = 300;
            }

            $ch = curl_init($request_url);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->get_domain());
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

            if (!empty($this->api->data)) {
                $data = array_merge($data, $this->api->data);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

            $json = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_errno > 0) {
                if ($api_server == 'api.e2pdf.com') {
                    return $this->request($key, 'api2.e2pdf.com');
                } elseif ($api_server == 'api3.e2pdf.com') {
                    return $this->request($key, 'api4.e2pdf.com');
                } else {
                    $response['error'] = "[{$curl_errno}] {$curl_error}";
                }
            } else {

                if (($this->api->action === 'template/build' || $this->api->action === 'template/parse') && get_option('e2pdf_developer')) {
                    if (in_array($this->helper->get_ip(), Model_E2pdf_Options::get_option('e2pdf_developer_ips'))) {
                        $data = array(
                            'request_url' => $request_url,
                            'data' => $data,
                            'response' => $json
                        );
                        var_dump("<PRE>");
                        var_dump($data);
                        die();
                    }
                }

                $result = json_decode($json, true);
                $response = $result['response'];

                if (($this->api->action === 'common/activate' || $this->api->action === 'license/update' || $this->api->action === 'license/request' ) && isset($response['license_key'])) {
                    update_option('e2pdf_license', $response['license_key']);
                }

                if (empty($response)) {
                    if ($api_server == 'api.e2pdf.com') {
                        return $this->request($key, 'api2.e2pdf.com');
                    } elseif ($api_server == 'api3.e2pdf.com') {
                        return $this->request($key, 'api4.e2pdf.com');
                    } else {
                        $response['error'] = __("Something went wrong!", "e2pdf");
                    }
                }
            }

            if ($key) {
                if (isset($response[$key])) {
                    return $response[$key];
                } else {
                    return false;
                }
            } else {
                return $response;
            }
        }
        return false;
    }

    /**
     * Remove API Request options
     */
    public function flush() {
        $this->api = null;
    }

    /**
     * Set options for API Request
     * 
     * @param string $key - Key
     * @param mixed $value - Value
     */
    public function set($key, $value = false) {
        if (!$this->api) {
            $this->api = new stdClass();
        }
        if (is_array($key)) {
            foreach ($key as $attr => $value) {
                $this->api->$attr = $value;
            }
        } else {
            $this->api->$key = $value;
        }
    }

    /**
     * Get License
     * 
     * @return string - License Key
     */
    public function get_license() {
        return get_option('e2pdf_license');
    }

    /**
     * Get Domain
     * 
     * @return string - Domain
     */
    public function get_domain() {
        return site_url();
    }

}
