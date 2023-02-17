<?php

/**
 * E2pdf View Helper
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

class Helper_E2pdf_View {

    protected static $instance = null;
    private $view_dir;
    public $uri;
    public $page;
    public $controller;
    public $helper;
    public $notification;
    public $tpl;
    public $tpl_page;
    public $view = null;
    public $tpl_args = null;
    public $get = null;
    public $post = null;
    public $files = null;

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->view = new stdClass();
        $this->uri = home_url(add_query_arg(null, null));
        $this->get = new Helper_E2pdf_Get($this->uri);
        $this->post = new Helper_E2pdf_Post();
        $this->files = new Helper_E2pdf_Files();
        $this->page = $this->get->get_page();
        $this->helper = Helper_E2pdf_Helper::instance();
        $this->notification = new Model_E2pdf_Notification();
        $this->view_dir = $this->helper->get('plugin_dir') . 'classes/view/';
    }

    /**
     * Get Tpl Value
     * 
     * @param string $key - Tpl key
     * 
     * @return mixed - Tpl value
     */
    public function render($type, $element, $args = array()) {
        $this->tpl_args = new Helper_E2pdf_Tplargs($args);
        $this->tpl = $this->view_dir . $type . '/' . $element . '.php';
        if (file_exists($this->tpl)) {
            include($this->tpl);
        }
    }

    /**
     * Get Tpl Value
     * 
     * @param string $key - Tpl key
     * 
     * @return mixed - Tpl value
     */
    public function render_metabox($post, $metabox) {
        $this->render('metaboxes', $metabox['args']['tpl']);
    }

    /**
     * Get Tpl Value
     * 
     * @param string $key - Tpl key
     * 
     * @return mixed - Tpl value
     */
    public function render_page() {

        if (get_option('e2pdf_developer')) {
            if (in_array($this->helper->get_ip(), Model_E2pdf_Options::get_option('e2pdf_developer_ips'))) {
                error_reporting(E_ALL);
                ini_set('display_startup_errors', 1);
                ini_set('display_errors', 1);
            }
        }

        $this->tpl_page = $this->view_dir . 'page-' . $this->page . '.php';
        $controller = $this->page_to_controller($this->page);
        if (class_exists($controller)) {
            $this->controller = new $controller();
            if ($this->get->get('action')) {
                $method = $this->get->get('action') . '_action';
            } else {
                $method = 'index_action';
            }
            if (method_exists($this->controller, $method)) {
                $this->controller->$method();
                $this->view = $this->controller->view;
            } else {
                $this->error('404');
            }
        }

        if (file_exists($this->tpl_page)) {
            include($this->tpl_page);
        }
    }

    public function render_frontend_page() {
        if ($this->page == 'e2pdf-download' || get_query_var('e2pdf')) {
            $this->tpl_page = $this->view_dir . '/frontend/page-e2pdf-download.php';
            $controller = $this->page_to_controller('e2pdf-download', true);
            if (class_exists($controller)) {
                $this->controller = new $controller();
                if ($this->get->get('action')) {
                    $method = $this->get->get('action') . '_action';
                } else {
                    $method = 'index_action';
                }
                if (method_exists($this->controller, $method)) {
                    $this->controller->$method();
                } else {
                    $this->error('404');
                }
            }

            if (file_exists($this->tpl_page)) {
                include($this->tpl_page);
            }
        } elseif ($this->page == 'e2pdf-activation') {
            if (get_transient('e2pdf_activation_key')) {
                $activation_key = get_transient('e2pdf_activation_key');
                if ($this->get->get('key') == $activation_key) {
                    if (ob_get_length() > 0) {
                        while (@ob_end_clean());
                    }
                    header('Content-Type: text/plain');
                    echo $activation_key;
                    die();
                }
            }
        }
    }

    /**
     * Get Tpl Value
     * 
     * @param string $key - Tpl key
     * 
     * @return mixed - Tpl value
     */
    public function page_to_controller($page, $frontend = false) {

        $controller = array(
            'Controller'
        );

        if ($frontend) {
            $controller[] = 'Frontend';
        }

        $controller = array_merge($controller, explode('-', $page));
        $controller = array_map('ucfirst', $controller);

        return implode("_", $controller);
    }

    /**
     * Redirect to page
     * 
     * @param string $location - Full url where to redirect
     */
    public function redirect($location) {
        if (headers_sent()) {
            die("<script>window.location='{$location}';</script>");
        } else {
            wp_redirect($location);
            exit();
        }
    }

    /**
     * Add notification
     * 
     * @param string $type - Type of notification error|update
     * @param string $text - Text of notification
     */
    public function add_notification($type, $text) {
        $this->notification->add_notification($type, $text);
    }

    /**
     * Get notifications
     * 
     * @return array - List of notifications
     */
    public function get_notifications() {
        $notifications = $this->notification->get_notifications();
        return $notifications;
    }

    /**
     * Force Json response
     * 
     * @param array $data - Array of data
     * 
     * @return json
     */
    public function json_response($data = array()) {
        @header('Content-Type: application/json; charset=' . get_option('blog_charset'));
        if (function_exists('wp_json_encode')) {
            echo wp_json_encode($data, JSON_FORCE_OBJECT);
        } else {
            echo json_encode($data, JSON_FORCE_OBJECT);
        }
        wp_die();
    }

    /**
     * Force close browser tab from PHP
     */
    public function close_tab_response() {
        echo "
            <script>
                self.close();
            </script>
            ";
    }

    /**
     * Force Download response
     * 
     * @param string $format - Format of file
     * @param string $file - Base64 Encoded File
     * @param string $name - Name of file when download
     * @param string $disposition - Disposition of file inline|attachment
     * 
     * @return string
     */
    public function download_response($format, $file, $name = '', $disposition = '') {

        if (ob_get_length() > 0) {
            while (@ob_end_clean());
        }

        if (!$name) {
            $name = time();
        }
        $name = rawurlencode($this->helper->load('convert')->to_file_name($name));

        status_header(200);
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen(base64_decode($file)));
        header('X-Robots-Tag: noindex, nofollow');
        switch ($format) {
            case 'pdf':
                if (!$disposition) {
                    $disposition = 'inline';
                }
                if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
                    header("Content-Disposition: {$disposition}; filename*=UTF-8''{$name}.pdf");
                } else {
                    header("Content-Disposition: {$disposition}; filename=\"{$name}.pdf\"");
                }
                header("Content-type: application/pdf");
                break;
            case 'jpg':
                if (!$disposition) {
                    $disposition = 'attachment';
                }
                if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
                    header("Content-Disposition: {$disposition}; filename*=UTF-8''{$name}.jpg");
                } else {
                    header("Content-Disposition: {$disposition}; filename=\"{$name}.jpg\"");
                }
                header('Content-type: image/jpeg');
                break;
            case 'zip':
                if (!$disposition) {
                    $disposition = 'attachment';
                }
                if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
                    header("Content-Disposition: {$disposition}; filename*=UTF-8''{$name}.zip");
                } else {
                    header("Content-Disposition: {$disposition}; filename=\"{$name}.zip\"");
                }
                header('Content-type: application/zip');
                break;
            case 'xml':
                if (!$disposition) {
                    $disposition = 'attachment';
                }
                if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
                    header("Content-Disposition: {$disposition}; filename*=UTF-8''{$name}.xml");
                } else {
                    header("Content-Disposition: {$disposition}; filename=\"{$name}.xml\"");
                }
                header('Content-type: text/xml');
                break;
            case 'txt':
                if (!$disposition) {
                    $disposition = 'attachment';
                }
                if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
                    header("Content-Disposition: {$disposition}; filename*=UTF-8''{$name}.txt");
                } else {
                    header("Content-Disposition: {$disposition}; filename=\"{$name}.txt\"");
                }
                header('Content-type: text/html');
                break;
            case 'php':
                if (!$disposition) {
                    $disposition = 'attachment';
                }
                if (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false) {
                    header("Content-Disposition: {$disposition}; filename*=UTF-8''{$name}.php");
                } else {
                    header("Content-Disposition: {$disposition}; filename=\"{$name}.php\"");
                }
                header('Content-type: text/html');
                break;
            default:
                break;
        }
        echo base64_decode($file);
        die();
    }

    /**
     * Check if exists in array
     * 
     * @param string $value - Array Key
     * @param mixed $compare 
     * @param array $data - Array of data
     * 
     * @return bool
     */
    public function exist($value, $compare = false, $data = array()) {
        if (isset($data[$value]) && $value === $compare) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Force Error
     * 
     * @param string $code - Error Code
     */
    public function error($code = '404') {
        global $wp_query;
        if ($code === '404') {
            $wp_query->set_404();
            status_header(404);
            $this->redirect(site_url('wp-admin'));
            wp_die('404');
        }
    }

    /**
     * Set var available to view template
     * 
     * @param string $key - Key of value
     * @param mixed $value - Value
     */
    public function view($key, $value) {
        $this->view->$key = $value;
    }

    /**
     * Check if Array/Object Empty
     * 
     * @param mixed $data - Object/Array to check
     * 
     * @return bool
     */
    public function is_empty($data) {
        if (is_object($data) || is_array($data)) {

            if (is_object($data)) {
                $data = (array) $data;
            }
            if (count($data) > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verify nonce
     * 
     * @param string $nonce - Nonce value
     * @param string $key - Nonce Key
     */
    public function check_nonce($nonce, $key) {
        $message = __('Security Issue: Nonce Incorrect', 'e2pdf');
        if ($key === 'e2pdf_ajax') {
            if (!wp_verify_nonce($nonce, $key)) {
                $data['error'] = $message;
                $this->json_response($data);
            }
        } else {
            if (!wp_verify_nonce($nonce, $key)) {
                die('<div id="message" class="error e2pdf-notice">' . $message . '</div>');
            }
        }
    }

}
