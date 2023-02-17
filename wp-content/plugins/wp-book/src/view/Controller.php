<?php

/**
 * The view-specific functionality of the plugin.
 *
 * @link  http://example.com
 * @since 1.0.0
 */

namespace WPBook\view;

/**
 * The view-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the view-specific stylesheet and JavaScript.
 *
 * @author Your Name <email@example.com>
 */
class Controller
{

    /**
     * Register the stylesheets for the view area.
     *
     * @since 1.0.0
     */
    public function get_all_templates() 
    {

        $templates = array(
        'Default' => array(
        'body' => array(
        'background' => '#FFF'
        ),
        'p' => array(
        'font-size' => '16px'
        )
        ),
        'Style 1' => array(
        'body' => array(
        'background' => '#f7f7f7'
        ),
        'p' => array(
        'font-size' => '16px'
        )
        ),
        'Dark' => array(
        'body' => array(
        'background' => '#000',
        'color' => '#ffffff'
        ),
        'p' => array(
        'font-size' => '16px',
        'color' => '#ffffff'
        )
        )
        );

        return $templates = apply_filters('wp_book_get_templates', $templates);

    }


}
