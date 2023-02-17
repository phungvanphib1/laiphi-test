<?php

/**
 * E2pdf Helper
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

class Helper_E2pdf_Helper {

    protected static $instance = NULL;
    private $helper;

    const CHMOD_DIR = 0755;
    const CHMOD_FILE = 0644;

    public static function instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set option by Key
     * 
     * @param string $key - Key of option
     * @param mixed $value - Value of option
     */
    public function set($key, $value) {
        if (!$this->helper) {
            $this->helper = new stdClass();
        }
        $this->helper->$key = $value;
    }

    /**
     * Add value to option by Key
     * 
     * @param string $key - Key of option
     *  @param mixed $value - Value of option
     */
    public function add($key, $value) {
        if (!$this->helper) {
            $this->helper = new stdClass();
        }

        if (isset($this->helper->$key)) {
            if (is_array($this->helper->$key)) {
                array_push($this->helper->$key, $value);
            }
        } else {
            $this->helper->$key = array();
            array_push($this->helper->$key, $value);
        }
    }

    /**
     * Unset option
     * 
     * @param string $key - Key of option
     */
    public function deset($key) {
        if (isset($this->helper->$key)) {
            unset($this->helper->$key);
        }
    }

    /**
     * Set option
     * 
     * @param string $key - Key of option
     * 
     * @return mixed - Get value of option by Key
     */
    public function get($key) {
        if (isset($this->helper->$key)) {
            return $this->helper->$key;
        } else {
            return '';
        }
    }

    /**
     * Get url path
     * 
     * @param string $url - Url path
     * 
     * @return string - Url path
     */
    public function get_url_path($url) {
        return plugins_url($url, $this->get('plugin_file_path'));
    }

    /**
     * Get url
     * 
     * @param array $data - Array list of url params
     * @param string $prefix -  Prefix of url
     * 
     * @return string Url
     */
    public function get_url($data = array(), $prefix = 'admin.php?') {
        $url = $prefix . http_build_query($data);
        return admin_url($url);
    }

    /**
     * Get Ip address
     * 
     * @return mixed - IP address or FALSE
     */
    public function get_ip() {
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = false;
        }
        return $ip;
    }

    /**
     * Remove dir and its content
     * 
     * @param string $dir - Path of directory to remove
     */
    public function delete_dir($dir) {
        if (!is_dir($dir)) {
            return;
        }
        if (substr($dir, strlen($dir) - 1, 1) != '/') {
            $dir .= '/';
        }
        $files = glob($dir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->delete_dir($file);
            } else {
                unlink($file);
            }
        }
        if (file_exists($dir . '.htaccess')) {
            unlink($dir . '.htaccess');
        }
        rmdir($dir);
    }

    public function create_dir($dir = false, $recursive = false, $create_index = true, $create_htaccess = false) {
        if ($dir && !file_exists($dir)) {
            if (mkdir($dir, self::CHMOD_DIR, $recursive)) {
                if ($create_index) {
                    $index = $dir . 'index.php';
                    if (!file_exists($index)) {
                        $this->create_file($index, "<?php\n// Silence is golden.\n?>");
                    }
                }
                if ($create_htaccess) {
                    $htaccess = $dir . '.htaccess';
                    if (!file_exists($htaccess)) {
                        $this->create_file($htaccess, "DENY FROM ALL");
                    }
                }
            }
        }
        return is_dir($dir);
    }

    public function create_file($file = false, $content = '') {
        if ($file && !file_exists($file)) {
            if (file_put_contents($file, $content)) {
                chmod($file, self::CHMOD_FILE);
            }
        }
        return is_file($file);
    }

    public function get_wp_upload_dir($key = 'basedir') {

        $wp_upload_dir = wp_upload_dir();
        if (defined('E2PDF_UPLOADS')) {
            $siteurl = get_option('siteurl');
            $upload_path = trim(get_option('upload_path'));

            if (empty($upload_path) || 'wp-content/uploads' === $upload_path) {
                $dir = WP_CONTENT_DIR . '/uploads';
            } elseif (0 !== strpos($upload_path, ABSPATH)) {
                // $dir is absolute, $upload_path is (maybe) relative to ABSPATH.
                $dir = path_join(ABSPATH, $upload_path);
            } else {
                $dir = $upload_path;
            }

            $url = get_option('upload_url_path');
            if (!$url) {
                if (empty($upload_path) || ( 'wp-content/uploads' === $upload_path ) || ( $upload_path == $dir )) {
                    $url = WP_CONTENT_URL . '/uploads';
                } else {
                    $url = trailingslashit($siteurl) . $upload_path;
                }
            }

            if (!(is_multisite() && get_site_option('ms_files_rewriting'))) {
                $dir = ABSPATH . E2PDF_UPLOADS;
                $url = trailingslashit($siteurl) . E2PDF_UPLOADS;
            }

            if (is_multisite() && !( is_main_network() && is_main_site() && defined('MULTISITE') )) {
                if (!get_site_option('ms_files_rewriting')) {
                    if (defined('MULTISITE')) {
                        $ms_dir = '/sites/' . get_current_blog_id();
                    } else {
                        $ms_dir = '/' . get_current_blog_id();
                    }
                    $dir .= $ms_dir;
                    $url .= $ms_dir;
                } elseif (!ms_is_switched()) {
                    $dir = ABSPATH . E2PDF_UPLOADS;
                    $url = trailingslashit($siteurl) . 'files';
                }
            }

            $basedir = $dir;
            $baseurl = $url;

            $subdir = '';
            if (get_option('uploads_use_yearmonth_folders')) {
                $time = current_time('mysql');
                $y = substr($time, 0, 4);
                $m = substr($time, 5, 2);
                $subdir = "/$y/$m";
            }

            $dir .= $subdir;
            $url .= $subdir;

            if (!file_exists($basedir)) {
                $this->create_dir($basedir, true, false, false);
            }

            $wp_upload_dir = array(
                'path' => $dir,
                'url' => $url,
                'subdir' => $subdir,
                'basedir' => $basedir,
                'baseurl' => $baseurl,
                'error' => false,
            );
        }

        if ($key && isset($wp_upload_dir[$key])) {
            return $wp_upload_dir[$key];
        } else {
            return '';
        }
    }

    public function get_upload_url($path = false) {
        if ($path) {
            return $this->get_wp_upload_dir('baseurl') . "/" . basename(untrailingslashit($this->get('upload_dir'))) . "/" . $path;
        } else {
            return $this->get_wp_upload_dir('baseurl') . "/" . basename(untrailingslashit($this->get('upload_dir')));
        }
    }

    /**
     * Check if array is multidimensional
     * 
     * @return boolean
     */
    public function is_multidimensional($a) {
        if (is_array($a)) {
            foreach ($a as $v) {
                if (is_array($v) || is_object($v)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get Capabilities
     * 
     * @return array()
     */
    public function get_caps() {
        $caps = array(
            'e2pdf' => array(
                'name' => __('Export', 'e2pdf'),
                'cap' => 'e2pdf',
            ),
            'e2pdf_templates' => array(
                'name' => __('Templates', 'e2pdf'),
                'cap' => 'e2pdf_templates'
            ),
            'e2pdf_settings' => array(
                'name' => __('Settings', 'e2pdf'),
                'cap' => 'e2pdf_settings'
            ),
            'e2pdf_license' => array(
                'name' => __('License', 'e2pdf'),
                'cap' => 'e2pdf_license'
            ),
            'e2pdf_debug' => array(
                'name' => __('Debug', 'e2pdf'),
                'cap' => 'e2pdf_debug'
            )
        );
        return $caps;
    }

    /**
     * Load sub-helper
     * 
     * @return object
     */
    public function load($helper) {
        $model = null;
        $class = "Helper_E2pdf_" . ucfirst($helper);
        if (class_exists($class)) {
            if (!$this->get($class)) {
                $this->set($class, new $class());
            }
            $model = $this->get($class);
        }
        return $model;
    }

    /**
     * Get Frontend Site URL
     * 
     * @return string
     */
    public function get_frontend_site_url() {
        return get_option('e2pdf_url_format') === 'home' ? home_url('/') : site_url('/');
    }

    /**
     * Get Frontend PDF URL
     * 
     * @return string
     */
    public function get_frontend_pdf_url($url_data = array(), $site_url = false, $filters = array()) {

        if ($site_url === false) {
            $site_url = $this->get_frontend_site_url();
        }

        $site_url = apply_filters('e2pdf_helper_get_frontend_pdf_url_pre_site_url', $site_url);

        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $site_url = apply_filters($filter, $site_url);
            }
        }

        $url_query = wp_parse_url($site_url, PHP_URL_QUERY);
        if ($url_query) {
            $site_url = str_replace('?' . $url_query, '', $site_url);
            $queries = explode('&', $url_query);
            foreach ($queries as $query) {
                $q = explode('=', $query);
                if (isset($q[0]) && isset($q[1])) {
                    $url_data[$q[0]] = $q[1];
                } elseif (isset($q[0])) {
                    $url_data[$q[0]] = '';
                }
            }
        }

        if (get_option('e2pdf_mod_rewrite') && get_option('e2pdf_mod_rewrite_url')) {
            $site_url = rtrim($site_url, '/') . '/' . get_option('e2pdf_mod_rewrite_url');
            if (isset($url_data['uid'])) {
                $site_url = str_replace('%uid%', $url_data['uid'], $site_url);
                unset($url_data['uid']);
            } else {
                $site_url = str_replace('%uid%', '', $site_url);
            }

            if (isset($url_data['page'])) {
                unset($url_data['page']);
            }
        }

        $site_url = apply_filters('e2pdf_helper_get_frontend_pdf_url_site_url', $site_url);
        $url_data = apply_filters('e2pdf_helper_get_frontend_pdf_url_url_data', $url_data);

        return add_query_arg($url_data, $site_url);
    }

    /**
     * Clear cache for some 3rd party cache plugins
     */
    public function clear_cache() {
        if (function_exists('w3tc_dbcache_flush')) {
            w3tc_dbcache_flush();
        }
        if (
                class_exists('SiteGround_Optimizer\Supercacher\Supercacher') &&
                class_exists('SitePress') &&
                get_option('siteground_optimizer_enable_memcached') &&
                function_exists('wp_cache_flush')
        ) {
            wp_cache_flush();
        }
    }

    /**
     * Initialize Database
     * 
     * @param string $db_prefix - Database Prefix
     * 
     * @return boolean
     */
    public function init_db($db_prefix) {
        global $wpdb;

        $srpk = $wpdb->get_row("SHOW VARIABLES LIKE 'sql_require_primary_key'", ARRAY_A);
        if ($srpk && isset($srpk['Value']) && $srpk['Value'] == 'ON') {
            $wpdb->query('SET SESSION sql_require_primary_key = 0;');
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_templates` (
        `ID` int(11) NOT NULL AUTO_INCREMENT,
        `uid` varchar(255) NOT NULL,
        `pdf` text,
        `title` text,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `flatten` enum('0','1','2') NOT NULL DEFAULT '0',
        `tab_order` enum('0','1','2','3') NOT NULL DEFAULT '0',
        `compression` int(1) NOT NULL DEFAULT '-1',
        `appearance` enum('0','1') NOT NULL DEFAULT '0',
        `width` int(11) NOT NULL DEFAULT '0',
        `height` int(11) NOT NULL DEFAULT '0',
        `extension` varchar(255) NOT NULL,
        `item` varchar(255) NOT NULL,
        `item1` varchar(255) NOT NULL,
        `item2` varchar(255) NOT NULL,
        `format` varchar(255) NOT NULL DEFAULT 'pdf',
        `resample` varchar(255) NOT NULL DEFAULT '100',
        `dataset_title` text NOT NULL,
        `dataset_title1` text NOT NULL,
        `dataset_title2` text NOT NULL,
        `button_title` text NOT NULL,
        `inline` enum('0','1') NOT NULL DEFAULT '0',
        `auto` enum('0','1') NOT NULL DEFAULT '0',
        `rtl` enum('0','1') NOT NULL DEFAULT '0',
        `name` text NOT NULL,
        `password` text NOT NULL,
        `meta_title` text NOT NULL,
        `meta_subject` text NOT NULL,
        `meta_author` text NOT NULL,
        `meta_keywords` text NOT NULL,
        `font` varchar(255) NOT NULL,
        `font_size` varchar(255) NOT NULL,
        `font_color` varchar(255) NOT NULL,
        `line_height` varchar(255) NOT NULL,
        `text_align` varchar(255) NOT NULL,
        `fonts` longtext NOT NULL,
        `trash` enum('0','1') NOT NULL DEFAULT '0',
        `activated` enum('0','1') NOT NULL DEFAULT '0',
        `locked` enum('0','1') NOT NULL DEFAULT '0',
        `author` int(11) NOT NULL,
        `actions` longtext NOT NULL,
            PRIMARY KEY (`ID`)
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if ($wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'blank';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` DROP COLUMN `blank`;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'meta_title';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `meta_title` text NOT NULL AFTER password;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'meta_subject';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `meta_subject` text NOT NULL AFTER meta_title;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'meta_author';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `meta_author` text NOT NULL AFTER meta_subject;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'meta_keywords';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `meta_keywords` text NOT NULL AFTER meta_author;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'actions';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `actions` longtext NOT NULL AFTER author;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'rtl';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `rtl` enum('0','1') NOT NULL DEFAULT '0' AFTER auto;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'text_align';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `text_align`  varchar(255) NOT NULL AFTER line_height;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'tab_order';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `tab_order` enum('0','1','2','3') NOT NULL DEFAULT '0' AFTER flatten;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'item1';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `item1`  varchar(255) NOT NULL AFTER item;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'item2';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `item2`  varchar(255) NOT NULL AFTER item1;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'dataset_title1';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `dataset_title1` text NOT NULL AFTER dataset_title;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'dataset_title2';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `dataset_title2` text NOT NULL AFTER dataset_title1;");
        }

        if ($wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` WHERE Field = 'format' and Type LIKE '%enum%';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` DROP COLUMN `format`;");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `format` varchar(255) NOT NULL DEFAULT 'pdf' AFTER item2;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_templates` LIKE 'resample';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_templates` ADD COLUMN `resample` varchar(255) NOT NULL DEFAULT '100' AFTER format;");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_entries` (
        `ID` bigint(20) NOT NULL AUTO_INCREMENT,
        `uid` varchar(255) NOT NULL,
        `entry` longtext,
        `pdf_num` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`ID`)
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_entries` LIKE 'pdf_num';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_entries` ADD COLUMN `pdf_num` int(11) NOT NULL DEFAULT '0';");
        }

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_entries` WHERE key_name = 'uid';");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `uid` ON `" . $db_prefix . "e2pdf_entries` (`uid`);");
        }

        if ($wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_entries` WHERE Field = 'ID' and Type LIKE 'int(11)';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_entries` CHANGE `ID` `ID` bigint(20) NOT NULL AUTO_INCREMENT;");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_datasets` (
        `ID` bigint(20) NOT NULL AUTO_INCREMENT,
        `extension` varchar(255) NOT NULL,
        `item` varchar(255) NOT NULL,
        `entry` longtext,
            PRIMARY KEY (`ID`)
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if ($wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_datasets` WHERE Field = 'ID' and Type LIKE 'int(11)';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_datasets` CHANGE `ID` `ID` bigint(20) NOT NULL AUTO_INCREMENT;");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_pages` (
        `page_id` int(11) NOT NULL DEFAULT '0',
        `template_id` int(11) NOT NULL DEFAULT '0',
        `properties` longtext NOT NULL,
        `actions` longtext NOT NULL,
        `revision_id` int(11) NOT NULL DEFAULT '0'
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_pages` LIKE 'actions';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_pages` ADD COLUMN `actions` longtext NOT NULL;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_pages` LIKE 'revision_id';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_pages` ADD COLUMN `revision_id` int(11) NOT NULL DEFAULT '0' AFTER actions;");
        }

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_pages` WHERE key_name = 'page_id';");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `page_id` ON `" . $db_prefix . "e2pdf_pages` (`page_id`);");
        }

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_pages` WHERE key_name = 'template_id';");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `template_id` ON `" . $db_prefix . "e2pdf_pages` (`template_id`);");
        }

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_pages` WHERE key_name = 'revision_id';");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `revision_id` ON `" . $db_prefix . "e2pdf_pages` (`revision_id`);");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_elements` (
        `page_id` int(11) NOT NULL DEFAULT '0',
        `template_id` int(11) NOT NULL DEFAULT '0',
        `element_id` int(11) NOT NULL DEFAULT '0',
        `name` text NOT NULL,
        `type` varchar(255) NOT NULL,
        `top` int(11) NOT NULL DEFAULT '0',
        `left` int(11) NOT NULL DEFAULT '0',
        `width` int(11) NOT NULL DEFAULT '0',
        `height` int(11) NOT NULL DEFAULT '0',
        `value` longtext NOT NULL,
        `properties` longtext NOT NULL,
        `actions` longtext NOT NULL,
        `revision_id` int(11) NOT NULL DEFAULT '0'
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_elements` LIKE 'actions';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_elements` ADD COLUMN `actions` longtext NOT NULL;");
        }

        if ($wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_pages` LIKE 'ID';") && $wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_elements` LIKE 'ID';")) {
            $wpdb->query("UPDATE `" . $db_prefix . "e2pdf_elements` ee INNER JOIN `" . $db_prefix . "e2pdf_pages` ep ON ee.page_id = ep.ID set ee.page_id = ep.page_id;");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_pages` DROP COLUMN `ID`;");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_elements` DROP COLUMN `ID`;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_elements` LIKE 'name';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_elements` ADD COLUMN `name` text NOT NULL AFTER element_id;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_elements` LIKE 'revision_id';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_elements` ADD COLUMN `revision_id` int(11) NOT NULL DEFAULT '0' AFTER actions;");
        }

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_elements` WHERE key_name = 'page_id';");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `page_id` ON `" . $db_prefix . "e2pdf_elements` (`page_id`);");
        }

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_elements` WHERE key_name = 'template_id'");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `template_id` ON `" . $db_prefix . "e2pdf_elements` (`template_id`); ");
        }

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_elements` WHERE key_name = 'revision_id'");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `revision_id` ON `" . $db_prefix . "e2pdf_elements` (`revision_id`); ");
        }

        $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $db_prefix . "e2pdf_revisions` (
        `revision_id` int(11) NOT NULL DEFAULT '0',
        `template_id` int(11) NOT NULL DEFAULT '0',
        `pdf` text,
        `title` text,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `flatten` enum('0','1','2') NOT NULL DEFAULT '0',
        `tab_order` enum('0','1','2','3') NOT NULL DEFAULT '0',
        `compression` int(1) NOT NULL DEFAULT '-1',
        `appearance` enum('0','1') NOT NULL DEFAULT '0',
        `width` int(11) NOT NULL DEFAULT '0',
        `height` int(11) NOT NULL DEFAULT '0',
        `extension` varchar(255) NOT NULL,
        `item` varchar(255) NOT NULL,
        `item1` varchar(255) NOT NULL,
        `item2` varchar(255) NOT NULL,
        `format` varchar(255) NOT NULL DEFAULT 'pdf',
        `resample` varchar(255) NOT NULL DEFAULT '100',
        `dataset_title` text NOT NULL,
        `dataset_title1` text NOT NULL,
        `dataset_title2` text NOT NULL,
        `button_title` text NOT NULL,
        `inline` enum('0','1') NOT NULL DEFAULT '0',
        `auto` enum('0','1') NOT NULL DEFAULT '0',
        `rtl` enum('0','1') NOT NULL DEFAULT '0',
        `name` text NOT NULL,
        `password` text NOT NULL,
        `meta_title` text NOT NULL,
        `meta_subject` text NOT NULL,
        `meta_author` text NOT NULL,
        `meta_keywords` text NOT NULL,
        `font` varchar(255) NOT NULL,
        `font_size` varchar(255) NOT NULL,
        `font_color` varchar(255) NOT NULL,
        `line_height` varchar(255) NOT NULL,
        `text_align` varchar(255) NOT NULL,
        `fonts` longtext NOT NULL,
        `author` int(11) NOT NULL,
        `actions` longtext NOT NULL
        ) CHARSET=utf8 COLLATE=utf8_general_ci");

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_revisions` WHERE key_name = 'revision_id';");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `revision_id` ON `" . $db_prefix . "e2pdf_revisions` (`revision_id`);");
        }

        $index = $wpdb->get_row("SHOW INDEX FROM `" . $db_prefix . "e2pdf_revisions` WHERE key_name = 'template_id';");
        if (is_null($index)) {
            $wpdb->query("CREATE INDEX `template_id` ON `" . $db_prefix . "e2pdf_revisions` (`template_id`);");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'actions';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `actions` longtext NOT NULL AFTER author;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'rtl';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `rtl` enum('0','1') NOT NULL DEFAULT '0' AFTER auto;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'text_align';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `text_align`  varchar(255) NOT NULL AFTER line_height;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'tab_order';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `tab_order` enum('0','1','2','3') NOT NULL DEFAULT '0' AFTER flatten;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'item1';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `item1`  varchar(255) NOT NULL AFTER item;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'item2';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `item2`  varchar(255) NOT NULL AFTER item1;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'dataset_title1';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `dataset_title1` text NOT NULL AFTER dataset_title;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'dataset_title2';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `dataset_title2` text NOT NULL AFTER dataset_title1;");
        }

        if ($wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` WHERE Field = 'format' and Type LIKE '%enum%';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` DROP COLUMN `format`;");
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `format` varchar(255) NOT NULL DEFAULT 'pdf' AFTER item2;");
        }

        if (!$wpdb->get_var("SHOW COLUMNS FROM `" . $db_prefix . "e2pdf_revisions` LIKE 'resample';")) {
            $wpdb->query("ALTER TABLE `" . $db_prefix . "e2pdf_revisions` ADD COLUMN `resample` varchar(255) NOT NULL DEFAULT '100' AFTER format;");
        }

        return true;
    }

}
