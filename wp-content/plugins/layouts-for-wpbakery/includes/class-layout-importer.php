<?php

/**
 * Class for importing a template.
 *
 * @package Layouts
 */

/**
 * Class for importing a template.
 *
 */
class Layouts_WPB_Importer {

    public function __construct() {
        if (!function_exists('wp_crop_image')) {
            include ABSPATH . 'wp-admin/includes/image.php';
        }
        $this->hooks();
    }

    /**
     * Initialize
     */
    public function hooks() {
        add_action('wp_ajax_handle_import', array($this, 'handle_import'));
        add_action('wp_ajax_nopriv_handle_import', array($this, 'handle_import'));
    }

    /**
     * Import template ajax action
     */
    public function handle_import() {

        $template_id = sanitize_text_field($_POST['template_id']);
        $with_page = sanitize_text_field($_POST['with_page']);

        $template = Layouts_WPB_Remote::lfw_get_instance()->get_template_content($template_id);
        
        // Check Error
        if (is_wp_error($template)) {
            return false;
        }
        
        // Check $template as string
        if (is_string($template) && !empty($template)) {
            echo $template;
            exit;
        }
        
        // Finally create the page or template.
        $page_id = $this->create_page($template, $with_page);
        echo $page_id;
        exit;
    }

    /**
     * Import template using page
     */
    private function create_page($template, $with_page) {

        if (!$template) {
            return _e('Invalid Template ID.', LFW_TEXTDOMAIN);
        }

        $string_content = '';
        if (!empty($template['template'])) {

            $new_content = array();
            $content_arr = explode('image="', $template['template']);
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $temp_array = array();
            foreach ($content_arr as $key => $value) {

                if (!empty($value)) {
                    $value_arr = explode('"', $value, 2);
                    if (!empty($value_arr) && !empty($value_arr[0]) && is_numeric($value_arr[0])) {

                        if (count($temp_array) > 0 && is_numeric(array_search($value_arr[0], array_column($temp_array, 'old')))) {
                            $exists_val = array_search($value_arr[0], array_column($temp_array, 'old'));
                            if (!empty($exists_val)) {
                                $new_val = $temp_array[$exists_val]['new'];
                                if (!empty($new_val)) {
                                    $new_image_arr = array_replace($value_arr, array(0 => $new_val));
                                    $value = implode('"', $new_image_arr);
                                }
                            }
                        } else {
                            //Get image url using id
                            $img_exist = $this->lfw_get_new_image($value_arr[0]);
                            if (empty($img_exist['img_exist']) && !empty($img_exist['image_url'])) {
                                //insert media in my wordpress
                                $attach_id = $this->lfw_insert_media_from_url($img_exist['image_url']);
                                $value = $this->lfw_get_new_image_string($attach_id, $value_arr, $temp_array);
                            } else {
                                //Get attachment id using url
                                $attach_id = attachment_url_to_postid($img_exist['img_exist']);
                                $value = $this->lfw_get_new_image_string($attach_id, $value_arr, $temp_array);
                            }
                        }
                    }
                }
                $new_content[$key] = $value;
            }
            $string_content = implode('image="', $new_content);
        }

        if (!empty($with_page)) {

            // Create post object
            $args = array(
                'post_type' => 'page',
                'post_title' => $with_page,
                'post_content' => $string_content,
                'post_status' => 'draft',
                'meta_input' => array(
                    '_wpb_vc_js_status' => 'true',
                ),
            );

            // Insert the post into the database
            $post_id = wp_insert_post($args);
            $result = array();
            if (!is_wp_error($post_id)) {
                return $post_id;
            } else {
                return $post_id->get_error_message();
            }
        } else {
            $theme_mods = get_option('wpb_js_templates');

            // Merge new options and clean
            $import_templates = array();

            $new_id = uniqid('Template_');
            $data['name'] = $template['title'] . ' (' . current_time('mysql') . ')';
            $data['template'] = $string_content;

            $import_templates[$new_id] = $data;

            if ($theme_mods) {
                $theme_mods = array_merge($theme_mods, $import_templates);
            } else {
                $theme_mods = $import_templates;
            }

            // Update templates theme options
            $update = update_option('wpb_js_templates', $theme_mods);

            if ($update) {
                return esc_html__('Template is successfully imported!', LFW_TEXTDOMAIN);
            } else {
                return esc_html__('Error: Templates exists!', LFW_TEXTDOMAIN);
            }
        }
    }

    /**
     * Get existing Image in local Media
     */
    private function lfw_get_new_image($img_id) {
        $img_exist = '';
        $image_url = $this->lfw_get_image_url($img_id);
        
        // Check $image_url not empty
        if (!empty($image_url)) {
            $slug = basename($image_url);
            $file_name = sanitize_file_name(pathinfo($slug, PATHINFO_FILENAME));
            $img_exist = $this->lfw_get_attachment_url_by_slug($file_name);
            if (empty($img_exist)) {
                $img_exist = $this->lfw_get_attachment_url_by_slug($file_name . '-1');
                if (empty($img_exist)) {
                    $img_exist = $this->lfw_get_attachment_url_by_slug($file_name . '-2');
                }
            }
        }
        return $result = array(
            'img_exist' => $img_exist,
            'image_url' => $image_url
        );
    }

    /**
     * Get Image url by id
     */
    private function lfw_get_image_url($img_id) {
        $image_url = Layouts_WPB_Remote::lfw_get_instance()->get_media_image($img_id);
        if (!empty($image_url) && is_string($image_url) && strpos($image_url, 'Missing Attachment') !== true) {
            return $image_url;
        } else {
            return '';
        }
    }

    /**
     * Check Image exist in local Media
     */
    private function lfw_get_attachment_url_by_slug($slug) {
        $args = array(
            'post_type' => 'attachment',
            'name' => $slug,
            'posts_per_page' => 1,
            'post_status' => 'inherit',
        );
        $_header = get_posts($args);
        $header = $_header ? array_pop($_header) : null;
        return $header ? wp_get_attachment_url($header->ID) : '';
    }

    /**
     * Media replace live media to local
     */
    private function lfw_get_new_image_string($attach_id, $value_arr, $temp_array) {
        $new_val = array(0 => $attach_id);
        $new_image_arr = array_replace($value_arr, $new_val);
        $new_image = implode('"', $new_image_arr);
        if (!in_array(array('old' => $value_arr[0], 'new' => $attach_id), $temp_array)) {
            array_push($temp_array, array('old' => $value_arr[0], 'new' => $attach_id));
        }
        return $new_image;
    }

    /**
     * Insert media using live url
     */
    private function lfw_insert_media_from_url($image_url) {
        $attachment_id = '';
        if (!empty($image_url) && strpos($image_url, 'Missing Attachment') !== true) {
            $filename = basename($image_url);
            $upload_file = wp_upload_bits($filename, null, file_get_contents($image_url));
            if (!$upload_file['error']) {
                $wp_filetype = wp_check_filetype($filename, null);
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attachment_id = wp_insert_attachment($attachment, $upload_file['file']);
                if (!is_wp_error($attachment_id)) {
                    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                    $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
                    wp_update_attachment_metadata($attachment_id, $attachment_data);
                }
            }
        }
        return $attachment_id;
    }

}

new Layouts_WPB_Importer();
