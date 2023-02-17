<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  http://example.com
 * @since 1.0.0
 */

namespace WPBook;


/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since  1.0.0
 * @author Your Name <email@example.com>
 */
class Main
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;


    /**
     * The unique identifier of this plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string    $wp_book    The string used to uniquely identify this plugin.
     */
    protected $wp_book;


    /**
     * The current version of the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string    $version    The current version of the plugin.
     */
    protected $version;

    protected $templates;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct() 
    {

        $this->wp_book = 'wp-book';
        $this->version = '1.0.0';
        $this->loader = new utils\Loader();

        $view = new view\Controller();
        $this->templates = $view->get_all_templates();

        $this->set_locale();
        $this->define_admin_hooks();

    }


    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Internationalization class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function set_locale() 
    {

        $plugin_i18n = new utils\Internationalization();
        $plugin_i18n->set_domain($this->get_wp_book());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }


    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_admin_hooks() 
    {

        $plugin_admin = new admin\Controller($this->get_wp_book(), $this->get_version());

        $plugin_admin->set_templates($this->templates);

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'wp_book_settings_page');
        $this->loader->add_action('wp_ajax_generate_book', $plugin_admin, 'generate_book');
        $this->loader->add_action('wp_ajax_load_posts_for_print', $plugin_admin, 'load_posts_for_print');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 1.0.0
     */
    public function run() 
    {
        $this->loader->run();
    }


    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since  1.0.0
     * @return string    The name of the plugin.
     */
    public function get_wp_book() 
    {
        return $this->wp_book;
    }


    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since  1.0.0
     * @return Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() 
    {
        return $this->loader;
    }


    /**
     * Retrieve the version number of the plugin.
     *
     * @since  1.0.0
     * @return string    The version number of the plugin.
     */
    public function get_version() 
    {
        return $this->version;
    }

}
