<?php

/**
 * E2pdf Controller
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

class Controller_E2pdf extends Helper_E2pdf_View {

    /**
     * @url admin.php?page=e2pdf
     */
    public function index_action() {

        $users_tmp = get_users(array(
            'fields' => array(
                'ID', 'user_login'
            )
        ));

        $users = array(
            '0' => __('--- Select ---', 'e2pdf')
        );
        foreach ($users_tmp as $user) {
            $users[$user->ID] = $user->user_login;
        }

        $this->view('users', $users);
    }

    /**
     * @url admin.php?page=e2pdf&action=export
     */
    public function export_action() {

        $template_id = (int) $this->get->get('template_id');
        $dataset_id = $this->get->get('dataset_id') ? $this->get->get('dataset_id') : 0;
        $dataset_id2 = $this->get->get('dataset_id2') ? $this->get->get('dataset_id2') : 0;

        if ($template_id && ($dataset_id || $dataset_id2)) {

            $template = new Model_E2pdf_Template();
            if ($template->load($template_id)) {

                $disposition = 'inline';
                if ($this->post->get('disposition') === 'attachment') {
                    $disposition = 'attachment';
                }

                $uid = false;
                $atts = array(
                    'user_id' => 0,
                    'inline' => $disposition == 'inline' ? 'true' : 'false'
                );
                $args = array();

                if ($this->post->get('options')) {
                    foreach ($this->post->get('options') as $key => $value) {
                        $atts[$key] = stripslashes($value);
                    }
                }

                $uid_params = array();
                $uid_params['template_id'] = $template_id;

                if ($dataset_id) {
                    $uid_params['dataset'] = $dataset_id;
                    $template->extension()->set('dataset', $dataset_id);
                }
                if ($dataset_id2) {
                    $uid_params['dataset2'] = $dataset_id2;
                    $template->extension()->set('dataset2', $dataset_id2);
                }

                if (array_key_exists('user_id', $atts)) {
                    $user_id = (int) $atts['user_id'];
                    $uid_params['user_id'] = $user_id;
                    $template->extension()->set('user_id', $user_id);
                }

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                    $template->set('flatten', $flatten);
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    if ($template->set('format', $format)) {
                        $uid_params['format'] = $format;
                    }
                }

                if (array_key_exists('name', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $name = $template->extension()->render($atts['name']);
                    } else {
                        $name = $template->extension()->convert_shortcodes($atts['name'], true);
                    }
                    $uid_params['name'] = $name;
                    $template->set('name', $name);
                } else {
                    $template->set('name', $template->extension()->render($template->get('name')));
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                    $template->set('password', $password);
                } else {
                    $template->set('password', $template->extension()->render($template->get('password')));
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                    $template->set('meta_title', $meta_title);
                } else {
                    $template->set('meta_title', $template->extension()->render($template->get('meta_title')));
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                    $template->set('meta_title', $meta_subject);
                } else {
                    $template->set('meta_subject', $template->extension()->render($template->get('meta_subject')));
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                    $template->set('meta_author', $meta_author);
                } else {
                    $template->set('meta_author', $template->extension()->render($template->get('meta_author')));
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
                    $template->set('meta_keywords', $meta_keywords);
                } else {
                    $template->set('meta_keywords', $template->extension()->render($template->get('meta_keywords')));
                }

                /**
                  if ($wc_order_id) {
                  $uid_params['wc_order_id'] = $wc_order_id;
                  $template->extension()->set('wc_order_id', $wc_order_id);
                  }
                 */
                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if ($entry->load_by_uid($entry->get('uid'))) {
                    $uid = $entry->get('uid');
                } else {
                    $template->extension()->set('uid_params', $uid_params);
                }

                $template->fill($dataset_id, $dataset_id2, $uid);
                $request = $template->render();

                if (isset($request['error'])) {
                    $this->add_notification('error', $request['error']);
                    $this->render('blocks', 'notifications');
                    $this->index_action();
                } else {
                    $filename = $template->get_filename();
                    $file = $request['file'];
                    $this->download_response($template->get('format'), $file, $filename, $disposition);
                }
            } else {
                $this->add_notification('error', __("Template can't be loaded", 'e2pdf'));
                $this->render('blocks', 'notifications');
            }
        } else {
            $this->error('404');
        }
    }

    /**
     * Get activated templates list
     * 
     * @return array() - Activated templates list
     */
    public function get_active_templates() {
        global $wpdb;

        $model_e2pdf_template = new Model_E2pdf_Template();

        $condition = array(
            'trash' => array(
                'condition' => '<>',
                'value' => '1',
                'type' => '%d'
            ),
            'activated' => array(
                'condition' => '=',
                'value' => '1',
                'type' => '%d'
            )
        );

        $order_condition = array(
            'orderby' => 'id',
            'order' => 'desc',
        );

        $where = $this->helper->load('db')->prepare_where($condition);
        $orderby = $this->helper->load('db')->prepare_orderby($order_condition);

        $templates = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $model_e2pdf_template->get_table() . $where['sql'] . $orderby . "", $where['filter']));
        $export_templates = array();

        $export_templates[] = array(
            'key' => '0',
            'value' => __('--- Select ---', 'e2pdf')
        );

        if (!empty($templates)) {
            foreach ($templates as $key => $value) {
                $export_templates[] = array(
                    'key' => $value->ID,
                    'value' => $value->title
                );
            }
        }

        return $export_templates;
    }

    /**
     * Get entries for template
     * 
     * @return array() - Entries for template
     */
    public function get_datasets($template_id = false, $item = false, $dataset_title = false) {

        $datasets = array();

        $datasets[] = array(
            'key' => '',
            'value' => __('--- Select ---', 'e2pdf')
        );

        if ($template_id) {
            $template = new Model_E2pdf_Template();
            if ($template->load($template_id)) {
                if ($item) {
                    $datasets_tmp = $template->extension()->
                            datasets(
                            $item, $dataset_title ? $dataset_title : $template->get('dataset_title')
                    );
                } else {
                    $datasets_tmp = $template->extension()->
                            datasets(
                            $template->get('item'), $dataset_title ? $dataset_title : $template->get('dataset_title')
                    );
                }
                if ($datasets_tmp && is_array($datasets_tmp)) {
                    $datasets = array_merge($datasets, $datasets_tmp);
                }
            }
        }

        return $datasets;
    }

    /**
     * Get options to overwrite on template
     * 
     * @return array() - List of options to overwrite
     */
    public function get_options($template_id = false) {

        $options = array();

        if ($template_id) {
            $template = new Model_E2pdf_Template();
            $template->load($template_id);
            $options['name'] = $template->get('name');
            $options['password'] = $template->get('password');
            $options['flatten'] = $template->get('flatten');
            $options['format'] = $template->get('format');
            $options['user_id'] = get_current_user_id();
        }

        return $options;
    }

    /**
     * Get templates list via ajax
     * action: wp_ajax_e2pdf_templates
     * function: e2pdf_templates
     * 
     * @return json - Templates list
     */
    public function ajax_templates() {

        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $template_id = (int) $this->post->get('data');
        $template = new Model_E2pdf_Template();
        if ($template->load($template_id)) {
            $content['delete_items'] = $template->extension()->method('delete_items');
        }

        $content = array();
        if ($template->get('item') == '-2') {
            $content['datasets'] = $this->get_datasets($template_id);
            $content['datasets1'] = $this->get_datasets($template_id, $template->get('item1'), $template->get('dataset_title1'));
            $content['datasets2'] = $this->get_datasets($template_id, $template->get('item2'), $template->get('dataset_title2'));
        } else {
            $content['datasets'] = $this->get_datasets($template_id);
        }

        $content['options'] = $this->get_options($template_id);
        $content['url'] = $this->helper->get_url(array('page' => 'e2pdf-templates', 'action' => 'edit', 'id' => $template_id));

        $response = array(
            'content' => $content,
        );

        $this->json_response($response);
    }

    /**
     * Get entries list via ajax
     * action: wp_ajax_e2pdf_entry
     * function: e2pdf_entry
     * 
     * @return json - Entries list
     */
    public function ajax_dataset() {

        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $data = $this->post->get('data');

        $template_id = (int) $data['template'];
        $dataset_id = (int) $data['dataset'];

        $template = new Model_E2pdf_Template();

        $content = array(
            'export' => false,
            'view' => false,
            'delete_item' => false,
            'dataset' => false
        );

        if ($template->load($template_id)) {
            $dataset = $template->extension()->dataset($dataset_id);

            if ($dataset) {
                $content = array(
                    'export' => true,
                    'view' => isset($dataset->url) && $dataset->url ? true : false,
                    'delete_item' => $template->extension()->method('delete_item'),
                    'dataset' => $dataset
                );
            }
        }

        $response = array(
            'content' => $content,
        );

        $this->json_response($response);
    }

    /**
     * Get entries list via ajax
     * action: wp_ajax_e2pdf_entry
     * function: e2pdf_entry
     * 
     * @return json - Entries list
     */
    public function ajax_datasets() {

        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $data = $this->post->get('data');

        $template_id = (int) $data['template'];
        $dataset_id1 = (int) $data['dataset1'];
        $dataset_id2 = (int) $data['dataset2'];

        $template = new Model_E2pdf_Template();

        $content = array(
            'export' => false,
            'view' => false,
            'delete_item' => false,
            'dataset' => false
        );

        if ($template->load($template_id)) {

            $export = false;

            if ($dataset_id1) {
                $dataset = $template->extension()->dataset($dataset_id1);
                if ($dataset) {
                    $export = true;
                }
            }

            if ($dataset_id2) {
                $dataset = $template->extension()->dataset($dataset_id2);
                if ($dataset) {
                    $export = true;
                }
            }

            $content = array(
                'export' => $export,
                'view' => false,
                'delete_item' => false,
                'dataset' => $dataset
            );
        }

        $response = array(
            'content' => $content,
        );

        $this->json_response($response);
    }

    public function ajax_delete_item() {

        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $data = $this->post->get('data');

        $template_id = (int) $data['template'];
        $dataset_id = (int) $data['dataset'];

        if (!$template_id || !$dataset_id) {
            return;
        }

        $template = new Model_E2pdf_Template();

        $action = false;
        if ($template->load($template_id)) {
            $action = $template->extension()->delete_item($template_id, $dataset_id);
        }

        if ($action) {
            $response = array(
                'redirect' => $this->helper->get_url(array(
                    'page' => 'e2pdf',
                    'template_id' => $template_id
                        )
                )
            );

            $this->add_notification('update', __('Dataset removed successfully', 'e2pdf'));
        } else {
            $response = array(
                'error' => __("Dataset can't be removed!", 'e2pdf')
            );
        }

        $this->json_response($response);
    }

    public function ajax_delete_items() {
        $this->check_nonce($this->get->get('_nonce'), 'e2pdf_ajax');

        $data = $this->post->get('data');
        $template_id = (int) $data['template'];

        if (!$template_id) {
            return;
        }

        $template = new Model_E2pdf_Template();

        $action = false;
        if ($template->load($template_id)) {
            $action = $template->extension()->delete_items($template_id);
        }

        if ($action) {
            $response = array(
                'redirect' => $this->helper->get_url(array(
                    'page' => 'e2pdf',
                    'template_id' => $template_id
                ))
            );

            $this->add_notification('update', __('Datasets removed successfully', 'e2pdf'));
        } else {
            $response = array(
                'error' => __("Datasets can't be removed!", 'e2pdf')
            );
        }

        $this->json_response($response);
    }

}
