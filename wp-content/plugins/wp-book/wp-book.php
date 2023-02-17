<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       WP Book
 * Plugin URI:        https://wpsmartcare.com/wp-book/
 * Description:       PDF Download plugin.
 * Version:           1.0.0
 * Author:            Rajan V
 * Author URI:        https://wpsmartcare.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-book
 * Domain Path:       /languages
 */

namespace WPBook;

// If this file is called directly, abort.
if (! defined('WPINC') ) {
    die;
}

// We load Composer's autoload file
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-book-activator.php
 */
function activate_wp_book() 
{
    utils\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-book-deactivator.php
 */
function deactivate_wp_book() 
{
    utils\Deactivator::deactivate();
}

register_activation_hook(__FILE__, '\WPBook\activate_wp_book');
register_deactivation_hook(__FILE__, '\WPBook\deactivate_wp_book');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_wp_book() 
{
    $plugin = new Main();
    $plugin->run();
}
run_wp_book();
