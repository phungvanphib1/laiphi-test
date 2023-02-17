<?php

/**
 * E2pdf Adobe Sign Model
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      1.02.00
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Model_E2pdf_AdobeSign extends Model_E2pdf_Model {

    protected $oauth_url = 'https://secure.%s.echosign.com/';
    protected $api = null;
    protected $provider = array(
        'api_url' => false,
        'access_token' => false,
        'refresh_token' => false,
        'code' => false,
        'client_id' => false,
        'client_secret' => false
    );

    function __construct() {
        parent::__construct();

        $this->provider = array(
            'api_url' => get_option('e2pdf_adobesign_api_access_point'),
            'access_token' => get_transient('e2pdf_adobesign_access_token'),
            'refresh_token' => get_option('e2pdf_adobesign_refresh_token'),
            'code' => get_option('e2pdf_adobesign_code'),
            'client_id' => get_option('e2pdf_adobesign_client_id'),
            'client_secret' => get_option('e2pdf_adobesign_client_secret')
        );

        //refresh access_token
        if (!$this->provider['access_token'] &&
                $this->provider['api_url'] &&
                $this->provider['client_id'] &&
                $this->provider['client_secret'] &&
                $this->provider['refresh_token']
        ) {
            $this->set(array(
                'action' => 'oauth/refresh',
                'data' => array(
                    'grant_type' => 'refresh_token',
                    'client_id' => $this->provider['client_id'],
                    'client_secret' => $this->provider['client_secret'],
                    'refresh_token' => $this->provider['refresh_token'],
                ),
            ));
            if ($access_token = $this->request('access_token')) {
                set_transient('e2pdf_adobesign_access_token', $access_token, 1800);
                set_transient('e2pdf_adobesign_refresh_token', 'updated', 2592000);
                $this->provider['access_token'] = $access_token;
            }
            $this->flush();
        } else {
            set_transient('e2pdf_adobesign_refresh_token', 'unavailable', 2592000);
        }
    }

    /**
     * Request code for refresh_token request
     * 
     * @return array
     */
    public function request_code() {
        $response = array();
        if (!get_option('e2pdf_adobesign_region') && !$this->provider['client_id'] && !$this->provider['client_secret']) {
            set_transient('e2pdf_adobesign_access_token', false);
            set_transient('e2pdf_adobesign_refresh_token', 'unavailable', 2592000);
            update_option('e2pdf_adobesign_code', false);
            update_option('e2pdf_adobesign_api_access_point', false);
            update_option('e2pdf_adobesign_refresh_token', false);
        } elseif (!get_option('e2pdf_adobesign_region')) {
            $response['error'] = __('Region is required', 'e2pdf');
        } elseif (!$this->provider['client_id']) {
            $response['error'] = __('Cliend ID is required', 'e2pdf');
        } elseif (!$this->provider['client_secret']) {
            $response['error'] = __('Client Secret is required', 'e2pdf');
        } else {
            $this->oauth_url = sprintf($this->oauth_url, get_option('e2pdf_adobesign_region'));

            $location = $this->oauth_url . "public/oauth";
            $scopes = array();
            $options = Model_E2pdf_Options::get_options(false, array('adobesign_group'));

            foreach ($options as $group_key => $group) {
                foreach ($group['options'] as $option_key => $option_value) {
                    if (substr($option_value['key'], 0, 21) === "e2pdf_adobesign_scope" && $option_value['value']) {
                        $scopes[] = substr($option_value['key'], 22) . ":" . $option_value['value'];
                    }
                }
            }
            $data = array(
                'redirect_uri' => urlencode($this->helper->get_url(array('page' => 'e2pdf-settings', 'action' => 'adobesign'))),
                'response_type' => 'code',
                'client_id' => get_option('e2pdf_adobesign_client_id'),
                'scope' => implode("+", $scopes),
            );
            $location .= "?" . urldecode(http_build_query($data));
            $response['redirect'] = $location;
        }
        return $response;
    }

    /**
     * Request refresh_token request
     * 
     * @return array
     */
    public function request_refresh_token() {

        $response = array();
        if (!$this->provider['api_url']) {
            $response['error'] = __('API Url is not set or Incorrect', 'e2pdf');
        } elseif (!$this->provider['code']) {
            $response['error'] = __('Code is not set or Incorrect', 'e2pdf');
        } elseif (!$this->provider['client_id']) {
            $response['error'] = __('Cliend ID is required', 'e2pdf');
        } elseif (!$this->provider['client_secret']) {
            $response['error'] = __('Client Secret is required', 'e2pdf');
        } else {

            $this->set(array(
                'action' => 'oauth/token',
                'data' => array(
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $this->helper->get_url(array('page' => 'e2pdf-settings', 'action' => 'adobesign')),
                    'client_id' => $this->provider['client_id'],
                    'client_secret' => $this->provider['client_secret'],
                    'code' => $this->provider['code'],
                ),
            ));

            $request = $this->request();
            $this->flush();
            if (isset($request['access_token']) && isset($request['refresh_token'])) {
                set_transient('e2pdf_adobesign_access_token', $request['access_token'], 1800);
                set_transient('e2pdf_adobesign_refresh_token', 'updated', 2592000);
                update_option('e2pdf_adobesign_refresh_token', $request['refresh_token']);
            } elseif (isset($request['error'])) {
                $response['error'] = $request['error'];
            } else {
                $response['error'] = $response['error'] = __("Something went wrong!", "e2pdf");
            }
        }
        return $response;
    }

    /**
     * Request to Adobe Sign API
     * 
     * @return array
     */
    public function request($key = false) {
        if ($this->api->action && $this->provider['api_url']) {

            $response = array();
            $headers = array();
            $data = array();
            $json_data = "[]";

            if (!empty($this->api->data)) {
                $data = array_merge($data, $this->api->data);
            }

            if ($this->api->action != 'oauth/refresh' && $this->api->action != 'oauth/token') {
                if ($this->provider['access_token']) {
                    $headers[] = 'Authorization: Bearear';
                    $headers[] = 'Access-Token: ' . $this->provider['access_token'];
                } else {
                    $response['error'] = __("Access Token is not set or Incorrect", "e2pdf");
                    return $response;
                }
            }

            if ($this->api->action == 'oauth/refresh' || $this->api->action == 'oauth/token') {
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            } elseif ($this->api->action == 'api/rest/v5/transientDocuments') {
                $headers[] = 'Content-Type: multipart/form-data';
            } else {
                $json_data = json_encode($data);
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($json_data);
            }

            $request_url = $this->provider['api_url'] . $this->api->action;

            $timeout = get_option('e2pdf_connection_timeout');
            if ($timeout === false) {
                $timeout = 300;
            }

            $ch = curl_init($request_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

            if ($this->api->method) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->api->method);
            }

            if ($this->api->action == 'oauth/refresh' || $this->api->action == 'oauth/token') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } elseif ($this->api->action == 'api/rest/v5/transientDocuments') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            }

            $json = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_errno > 0) {
                $response['error'] = "[{$curl_errno}] {$curl_error}";
            } else {
                $result = json_decode($json, true);
                $response = $result;
                if (empty($response)) {
                    $response['error'] = __("Something went wrong!", "e2pdf");
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

}
