<?php

/**
 * E2pdf Shortcode Helper
 * 
 * @copyright  Copyright 2017 https://e2pdf.com
 * @license    GPLv3
 * @version    1
 * @link       https://e2pdf.com
 * @since      1.07.02
 */
if (!defined('ABSPATH')) {
    die('Access denied.');
}

class Helper_E2pdf_Shortcode {

    public function get_shortcode_regex($tagnames = null) {
        if (version_compare(get_bloginfo('version'), '4.4.0', '<')) {
            global $shortcode_tags;

            if (empty($tagnames)) {
                $tagnames = array_keys($shortcode_tags);
            }
            $tagregexp = join('|', array_map('preg_quote', $tagnames));

            return
                    '\\['
                    . '(\\[?)'
                    . "($tagregexp)"
                    . '(?![\\w-])'
                    . '('
                    . '[^\\]\\/]*'
                    . '(?:'
                    . '\\/(?!\\])'
                    . '[^\\]\\/]*'
                    . ')*?'
                    . ')'
                    . '(?:'
                    . '(\\/)'
                    . '\\]'
                    . '|'
                    . '\\]'
                    . '(?:'
                    . '('
                    . '[^\\[]*+'
                    . '(?:'
                    . '\\[(?!\\/\\2\\])'
                    . '[^\\[]*+'
                    . ')*+'
                    . ')'
                    . '\\[\\/\\2\\]'
                    . ')?'
                    . ')'
                    . '(\\]?)';
        } else {
            return get_shortcode_regex($tagnames);
        }
    }

}
