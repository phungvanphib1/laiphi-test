<?php

/**
 * E2pdf Shortcode Model
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

class Model_E2pdf_Shortcode extends Model_E2pdf_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * [e2pdf-attachment] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_attachment($atts = array()) {

        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_attachment_atts', $atts);

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : 0;
        $dataset2 = isset($atts['dataset2']) ? $atts['dataset2'] : 0;
        $pdf = isset($atts['pdf']) ? $atts['pdf'] : false;
        $apply = isset($atts['apply']) ? true : false;
        $uid = isset($atts['uid']) ? $atts['uid'] : false;
        $wc_order_id = isset($atts['wc_order_id']) ? $atts['wc_order_id'] : false;
        $wc_product_item_id = isset($atts['wc_product_item_id']) ? $atts['wc_product_item_id'] : false;

        /*
         * Formidable Forms Transient Entry
         */
        $ff_transient_entry = isset($atts['ff_transient_entry']) ? $atts['ff_transient_entry'] : false;

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        if ($uid) {
            $entry = new Model_E2pdf_Entry();
            if ($entry->load_by_uid($uid)) {
                $uid_params = $entry->get('entry');
                $template_id = isset($uid_params['template_id']) ? (int) $uid_params['template_id'] : 0;
                $dataset = isset($uid_params['dataset']) ? $uid_params['dataset'] : 0;
                $dataset2 = isset($uid_params['dataset2']) ? $uid_params['dataset2'] : 0;
                $pdf = isset($uid_params['pdf']) ? $uid_params['pdf'] : false;
            } else {
                return $response;
            }
        }

        if ($pdf) {
            if ($apply && !$this->helper->load('filter')->is_stream($pdf) && file_exists($pdf)) {
                $pdf = apply_filters('e2pdf_model_e2pdf_shortcode_attachment_path', $pdf, $atts);
                return $pdf;
            } else {
                return $response;
            }
        }

        if (!$apply || (!$dataset && !$dataset2) || !$template_id) {
            return $response;
        }

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;

            if ($dataset) {
                $uid_params['dataset'] = $dataset;
                $template->extension()->set('dataset', $dataset);
            }

            if ($dataset2) {
                $uid_params['dataset2'] = $dataset2;
                $template->extension()->set('dataset2', $dataset2);
            }

            if ($ff_transient_entry) {
                $template->extension()->set('ff_transient_entry', $ff_transient_entry);
            }

            if ($wc_order_id) {
                $template->extension()->set('wc_order_id', $wc_order_id);
            }

            if ($wc_product_item_id) {
                $template->extension()->set('wc_product_item_id', $wc_product_item_id);
            }

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_attachment_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

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
                    $template->set('meta_subject', $meta_subject);
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

                if ($wc_order_id) {
                    $uid_params['wc_order_id'] = $wc_order_id;
                }

                if ($wc_product_item_id) {
                    $uid_params['wc_product_item_id'] = $wc_product_item_id;
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                $template->fill($dataset, $dataset2, $entry->get('uid'));
                $request = $template->render();

                if (!isset($request['error']) && $entry->get('ID')) {
                    $tmp_dir = $this->helper->get('tmp_dir') . 'e2pdf' . md5($entry->get('uid')) . '/';

                    $this->helper->create_dir($tmp_dir);

                    if ($template->get('name')) {
                        $name = $template->get('name');
                    } else {
                        $name = $template->extension()->render($template->get_filename());
                    }

                    if ($template->get('format') == 'jpg') {
                        $file_name = $name . '.jpg';
                    } else {
                        $file_name = $name . '.pdf';
                    }

                    $file_name = $this->helper->load('convert')->to_file_name($file_name);
                    $file_path = $tmp_dir . $file_name;
                    file_put_contents($file_path, base64_decode($request['file']));

                    if (file_exists($file_path)) {
                        $entry->set('pdf_num', $entry->get('pdf_num') + 1);
                        $entry->save();
                        $file_path = apply_filters('e2pdf_model_e2pdf_shortcode_attachment_path', $file_path, $atts);
                        return $file_path;
                    }
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-download] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_download($atts = array()) {

        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_download_atts', $atts);

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : 0;
        $dataset2 = isset($atts['dataset2']) ? $atts['dataset2'] : 0;
        $uid = isset($atts['uid']) ? $atts['uid'] : false;
        $output = isset($atts['output']) ? $atts['output'] : false;
        $pdf = isset($atts['pdf']) ? $atts['pdf'] : false;
        $target = isset($atts['target']) ? $atts['target'] : '_blank';
        $site_url = isset($atts['site_url']) ? $atts['site_url'] : false;
        $esc_url_raw = isset($atts['esc_url_raw']) && $atts['esc_url_raw'] == 'true' ? true : false;
        $wc_product_download = isset($atts['wc_product_download']) && $atts['wc_product_download'] == 'true' ? true : false;
        $wc_order_id = isset($atts['wc_order_id']) ? $atts['wc_order_id'] : false;
        $wc_product_item_id = isset($atts['wc_product_item_id']) ? $atts['wc_product_item_id'] : false;
        $iframe_download = false;

        /*
         * Backward compatiability with old format since 1.13.07
         */
        if (isset($atts['iframe-download'])) {
            $atts['iframe_download'] = $atts['iframe-download'];
        }

        /*
         * Backward compatiability with old format since 1.09.05
         */
        if (isset($atts['button-title'])) {
            $atts['button_title'] = $atts['button-title'];
        }

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        if ($uid) {
            $entry = new Model_E2pdf_Entry();
            if ($entry->load_by_uid($uid)) {
                $uid_params = $entry->get('entry');
                $template_id = isset($uid_params['template_id']) ? (int) $uid_params['template_id'] : 0;
                $dataset = isset($uid_params['dataset']) ? $uid_params['dataset'] : 0;
                $dataset2 = isset($uid_params['dataset2']) ? $uid_params['dataset2'] : 0;
                $pdf = isset($uid_params['pdf']) ? $uid_params['pdf'] : false;
            } else {
                return $response;
            }
        }

        if ($pdf) {

            if (!$this->helper->load('filter')->is_stream($pdf) && file_exists($pdf)) {

                $uid_params = array();
                $uid_params['pdf'] = $pdf;

                if (array_key_exists('class', $atts)) {
                    $classes = explode(" ", $atts['class']);
                } else {
                    $classes = array();
                }

                $classes[] = 'e2pdf-download';
                $classes[] = 'e2pdf-format-pdf';

                $inline = 0;
                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if ($inline) {
                    $classes[] = 'e2pdf-inline';
                }

                $auto = 0;
                if (array_key_exists('auto', $atts)) {
                    $auto = $atts['auto'] == 'true' ? 1 : 0;
                }

                if ($auto) {
                    $classes[] = 'e2pdf-auto';
                    if (array_key_exists('iframe_download', $atts) && $atts['iframe_download'] == 'true' && !$inline) {
                        $classes[] = 'e2pdf-iframe-download';
                        $iframe_download = true;
                    }
                }

                if (array_key_exists('name', $atts)) {
                    $name = $atts['name'];
                    $uid_params['name'] = $name;
                }

                if (array_key_exists('button_title', $atts)) {
                    $button_title = $atts['button_title'];
                } else {
                    $button_title = __('Download', 'e2pdf');
                }

                if ($output && $output == 'button_title') {
                    return $button_title;
                }

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {

                    $url_data = array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid')
                    );

                    $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                    $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_download_url_data', $url_data, $atts);

                    if ($output && $output == 'url') {
                        if ($esc_url_raw) {
                            $url = esc_url_raw(
                                    $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                        'e2pdf_model_shortcode_site_url',
                                        'e2pdf_model_shortcode_e2pdf_download_site_url'
                                    ))
                            );
                        } else {
                            $url = esc_url(
                                    $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                        'e2pdf_model_shortcode_site_url',
                                        'e2pdf_model_shortcode_e2pdf_download_site_url'
                                    ))
                            );
                        }
                        $response = $url;
                    } else {
                        $url = esc_url(
                                $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                    'e2pdf_model_shortcode_site_url',
                                    'e2pdf_model_shortcode_e2pdf_download_site_url'
                                ))
                        );
                        $response = "<a rel='nofollow' id='e2pdf-download' class='" . implode(" ", $classes) . "' target='{$target}' href='{$url}'>{$button_title}</a>";
                        if ($iframe_download) {
                            $response .= "<iframe style='width:0;height:0;border:0; border:none;' src='{$url}'></iframe>";
                        }
                    }
                }
            }
            return $response;
        }

        if ((!$dataset && !$dataset2) || !$template_id) {
            return $response;
        }

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id, false)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;

            if ($dataset) {
                $uid_params['dataset'] = $dataset;
                $template->extension()->set('dataset', $dataset);
            }

            if ($dataset2) {
                $uid_params['dataset2'] = $dataset2;
                $template->extension()->set('dataset2', $dataset2);
            }

            if (array_key_exists('class', $atts)) {
                $classes = explode(" ", $atts['class']);
            } else {
                $classes = array();
            }
            $classes[] = 'e2pdf-download';

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_download_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                } else {
                    $inline = $template->get('inline');
                }

                if ($inline) {
                    $classes[] = 'e2pdf-inline';
                }

                if (array_key_exists('auto', $atts)) {
                    $auto = $atts['auto'] == 'true' ? 1 : 0;
                } else {
                    $auto = $template->get('auto');
                }

                if ($auto) {
                    $classes[] = 'e2pdf-auto';
                    if (array_key_exists('iframe_download', $atts) && $atts['iframe_download'] == 'true' && !$inline) {
                        $classes[] = 'e2pdf-iframe-download';
                        $iframe_download = true;
                    }
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    if ($template->set('format', $format)) {
                        $uid_params['format'] = $format;
                    }
                }

                if ($template->get('format') == 'jpg') {
                    $classes[] = 'e2pdf-format-jpg';
                } else {
                    $classes[] = 'e2pdf-format-pdf';
                }

                if (array_key_exists('button_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $button_title = $template->extension()->render($atts['button_title']);
                    } else {
                        $button_title = $template->extension()->convert_shortcodes($atts['button_title'], true);
                    }
                } elseif ($template->extension()->render($template->get('button_title')) !== '') {
                    $button_title = $template->extension()->render($template->get('button_title'));
                } else {
                    $button_title = __('Download', 'e2pdf');
                }

                if ($output && $output == 'button_title') {
                    return $button_title;
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
                }

                if (array_key_exists('name', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $name = $template->extension()->render($atts['name']);
                    } else {
                        $name = $template->extension()->convert_shortcodes($atts['name'], true);
                    }
                    $uid_params['name'] = $name;
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                if ($wc_order_id) {
                    $uid_params['wc_order_id'] = $wc_order_id;
                    $template->extension()->set('wc_order_id', $wc_order_id);
                }

                if ($wc_product_item_id) {
                    $uid_params['wc_product_item_id'] = $wc_product_item_id;
                    $template->extension()->set('wc_product_item_id', $wc_product_item_id);
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {

                    $url_data = array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid')
                    );

                    if ($wc_product_download) {

                        if (isset($uid_params['name'])) {
                            $name = $uid_params['name'];
                        } elseif ($template->get('name')) {
                            $name = $template->extension()->render($template->get('name'));
                        } else {
                            $name = $template->extension()->render($template->get_filename());
                        }

                        if (!$name && isset($uid_params['pdf'])) {
                            $name = basename($uid_params['pdf'], ".pdf");
                        }

                        $url_data['#saveName'] = '/' . $name . '.pdf';
                    }

                    $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                    $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_download_url_data', $url_data, $atts);

                    if ($output && $output == 'url') {
                        if ($esc_url_raw) {
                            $url = esc_url_raw(
                                    $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                        'e2pdf_model_shortcode_site_url',
                                        'e2pdf_model_shortcode_e2pdf_download_site_url'
                                    ))
                            );
                        } else {
                            $url = esc_url(
                                    $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                        'e2pdf_model_shortcode_site_url',
                                        'e2pdf_model_shortcode_e2pdf_download_site_url'
                                    ))
                            );
                        }
                        $response = $url;
                    } else {

                        $url = esc_url(
                                $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                    'e2pdf_model_shortcode_site_url',
                                    'e2pdf_model_shortcode_e2pdf_download_site_url'
                                ))
                        );
                        $response = "<a rel='nofollow' id='e2pdf-download' class='" . implode(" ", $classes) . "' target='{$target}' href='{$url}'>{$button_title}</a>";
                        if ($iframe_download) {
                            $response .= "<iframe style='width:0;height:0;border:0; border:none;' src='{$url}'></iframe>";
                        }
                    }
                }
            }
        }
        return $response;
    }

    /**
     * @since 0.01.44
     * 
     * [e2pdf-save] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_save($atts = array()) {

        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_save_atts', $atts);

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : 0;
        $dataset2 = isset($atts['dataset2']) ? $atts['dataset2'] : 0;
        $download = isset($atts['download']) && $atts['download'] == 'true' ? true : false;
        $view = isset($atts['view']) && $atts['view'] == 'true' ? true : false;
        $attachment = isset($atts['attachment']) && $atts['attachment'] == 'true' ? true : false;
        $zapier = isset($atts['zapier']) && $atts['zapier'] == 'true' ? true : false;
        $overwrite = isset($atts['overwrite']) && $atts['overwrite'] == 'false' ? false : true;
        $output = isset($atts['output']) ? $atts['output'] : false;
        $apply = isset($atts['apply']) ? true : false;
        $dir = isset($atts['dir']) ? $atts['dir'] : false;
        $create_dir = isset($atts['create_dir']) && $atts['create_dir'] == 'true' ? true : false;
        $create_index = isset($atts['create_index']) && $atts['create_index'] == 'false' ? false : true;
        $create_htaccess = isset($atts['create_htaccess']) && $atts['create_htaccess'] == 'false' ? false : true;

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        if (!$apply || (!$dataset && !$dataset2) || !$template_id) {
            return $response;
        }

        $template = new Model_E2pdf_Template();

        if ($template->load($template_id)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;

            if ($dataset) {
                $uid_params['dataset'] = $dataset;
                $template->extension()->set('dataset', $dataset);
            }

            if ($dataset2) {
                $uid_params['dataset2'] = $dataset2;
                $template->extension()->set('dataset2', $dataset2);
            }

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_save_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

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
                    $template->set('meta_subject', $meta_subject);
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

                if ($download || $view || $attachment || $zapier) {
                    $uid_params['pdf'] = false;
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($dir) {
                    if (!array_key_exists('filter', $atts)) {
                        $dir = $template->extension()->render($dir);
                    } else {
                        $dir = $template->extension()->convert_shortcodes($dir, true);
                    }

                    $save_dir = rtrim(trim($this->helper->load('convert')->to_file_dir($dir)), '/') . '/';

                    if (strpos($save_dir, '/') !== 0 && !preg_match('/^[A-Za-z]:/', $save_dir)) {
                        $save_dir = ABSPATH . $save_dir;
                    }

                    if ($create_dir) {
                        $this->helper->create_dir($save_dir, true, $create_index, $create_htaccess);
                    }

                    $htaccess = $save_dir . '.htaccess';
                    if ($create_htaccess && !file_exists($htaccess)) {
                        $this->helper->create_file($htaccess, "DENY FROM ALL");
                    }
                } else {
                    $tpl_dir = $this->helper->get('tpl_dir') . $template->get('ID') . "/";
                    $save_dir = $tpl_dir . "save/";
                    $this->helper->create_dir($tpl_dir, false, true);
                    $this->helper->create_dir($save_dir, false, $create_index, $create_htaccess);

                    $htaccess = $save_dir . '.htaccess';
                    if ($create_htaccess && !file_exists($htaccess)) {
                        $this->helper->create_file($htaccess, "DENY FROM ALL");
                    }
                }

                if ($template->get('name')) {
                    $name = $template->get('name');
                } else {
                    $name = $template->extension()->render($template->get_filename());
                }

                if ($template->get('format') == 'jpg') {
                    $file_name = $name . '.jpg';
                } else {
                    $file_name = $name . '.pdf';
                }

                $file_name = $this->helper->load('convert')->to_file_name($file_name);
                $file_path = $save_dir . $file_name;

                if ($overwrite || !file_exists($file_path)) {
                    $template->fill($dataset, $dataset2, $entry->get('uid'));
                    $request = $template->render();
                }

                if (($overwrite || !file_exists($file_path)) && isset($request['error'])) {
                    return false;
                } else {
                    if ($entry->get('ID')) {

                        if (is_dir($save_dir) && is_writable($save_dir)) {

                            if ($overwrite || !file_exists($file_path)) {
                                file_put_contents($file_path, base64_decode($request['file']));
                            }

                            if (!$this->helper->load('filter')->is_stream($file_path) && file_exists($file_path)) {

                                $file_path = apply_filters('e2pdf_model_e2pdf_shortcode_save_path', $file_path, $atts);
                                $entry->set('pdf_num', $entry->get('pdf_num') + 1);

                                if ($download || $view || $attachment || $zapier) {
                                    $uid_params['pdf'] = $file_path;
                                    $entry->set('entry', $uid_params);
                                    $atts['uid'] = $entry->get('uid');
                                }
                                $entry->save();

                                if ($download) {
                                    $response = $this->e2pdf_download($atts);
                                } elseif ($view) {
                                    $response = $this->e2pdf_view($atts);
                                } elseif ($attachment) {
                                    $response = $this->e2pdf_attachment($atts);
                                } elseif ($zapier) {
                                    $response = $this->e2pdf_zapier($atts);
                                } elseif ($output && $output == 'path') {
                                    $response = $file_path;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }

    /**
     * @since 1.13.20
     * 
     * [e2pdf-zapier] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_zapier($atts = array()) {

        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_atts', $atts);

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : 0;
        $dataset2 = isset($atts['dataset2']) ? $atts['dataset2'] : 0;
        $wc_order_id = isset($atts['wc_order_id']) ? $atts['wc_order_id'] : 0;
        $uid = isset($atts['uid']) ? $atts['uid'] : false;
        $pdf = isset($atts['pdf']) ? $atts['pdf'] : false;
        $site_url = isset($atts['site_url']) ? $atts['site_url'] : false;
        $webhook = isset($atts['webhook']) && $atts['webhook'] ? $atts['webhook'] : false;

        if (!$webhook) {
            return $response;
        }

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        if ($uid) {
            $entry = new Model_E2pdf_Entry();
            if ($entry->load_by_uid($uid)) {
                $uid_params = $entry->get('entry');
                $template_id = isset($uid_params['template_id']) ? (int) $uid_params['template_id'] : 0;
                $dataset = isset($uid_params['dataset']) ? $uid_params['dataset'] : 0;
                $dataset2 = isset($uid_params['dataset2']) ? $uid_params['dataset2'] : 0;
                $pdf = isset($uid_params['pdf']) ? $uid_params['pdf'] : false;
            } else {
                return $response;
            }
        }

        if ($pdf) {
            if (!$this->helper->load('filter')->is_stream($pdf) && file_exists($pdf)) {

                $uid_params = array();
                $uid_params['pdf'] = $pdf;

                if (array_key_exists('class', $atts)) {
                    $classes = explode(" ", $atts['class']);
                } else {
                    $classes = array();
                }

                $classes[] = 'e2pdf-download';

                $inline = 0;
                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if (array_key_exists('name', $atts)) {
                    $name = $atts['name'];
                    $uid_params['name'] = $name;
                } else {
                    $name = '';
                }

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {

                    $content_type = 'application/json';
                    $blog_charset = get_option('blog_charset');
                    if (!empty($blog_charset)) {
                        $content_type .= '; charset=' . get_option('blog_charset');
                    }

                    $url_data = array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid')
                    );

                    $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                    $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_url_data', $url_data, $atts);

                    $url = esc_url_raw(
                            $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                'e2pdf_model_shortcode_site_url',
                                'e2pdf_model_shortcode_e2pdf_zapier_site_url'
                            ))
                    );

                    if (!$name) {
                        $name = basename($uid_params['pdf']);
                    }

                    $data = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_data',
                            array_merge(
                                    array(
                                        'id' => $template_id,
                                        'dataset' => $dataset,
                                        'dataset2' => $dataset2,
                                        'wc_order_id' => $wc_order_id,
                                        'uid' => $entry->get('uid'),
                                        'name' => $name,
                                        'format' => 'pdf',
                                        'url' => $url
                                    ), $args
                            ), $atts);

                    $zapier_args = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_args',
                            array(
                                'method' => 'POST',
                                'body' => json_encode($data),
                                'headers' => array(
                                    'Content-Type' => $content_type,
                                )), $atts);

                    $result = wp_remote_post($webhook, $zapier_args);

                    $response = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_response', $response, $result, $atts);
                }
            }
            return $response;
        }

        if ((!$dataset && !$dataset2) || !$template_id) {
            return $response;
        }

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id, false)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;

            if ($dataset) {
                $uid_params['dataset'] = $dataset;
                $template->extension()->set('dataset', $dataset);
            }

            if ($dataset2) {
                $uid_params['dataset2'] = $dataset2;
                $template->extension()->set('dataset2', $dataset2);
            }

            if (array_key_exists('class', $atts)) {
                $classes = explode(" ", $atts['class']);
            } else {
                $classes = array();
            }
            $classes[] = 'e2pdf-download';

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                } else {
                    $inline = $template->get('inline');
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    if ($template->set('format', $format)) {
                        $uid_params['format'] = $format;
                    }
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
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

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                if ($wc_order_id) {
                    $uid_params['wc_order_id'] = $wc_order_id;
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {

                    $content_type = 'application/json';
                    $blog_charset = get_option('blog_charset');
                    if (!empty($blog_charset)) {
                        $content_type .= '; charset=' . get_option('blog_charset');
                    }

                    $url_data = array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid')
                    );

                    $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                    $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_url_data', $url_data, $atts);

                    $url = esc_url_raw(
                            $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                'e2pdf_model_shortcode_site_url',
                                'e2pdf_model_shortcode_e2pdf_zapier_site_url'
                            ))
                    );

                    $data = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_data',
                            array_merge(
                                    array(
                                        'id' => $template_id,
                                        'dataset' => $dataset,
                                        'dataset2' => $dataset2,
                                        'wc_order_id' => $wc_order_id,
                                        'uid' => $entry->get('uid'),
                                        'name' => $template->get('name') ? $template->get('name') : $template->extension()->render($template->get_filename()),
                                        'format' => $template->get('format'),
                                        'url' => $url
                                    ), $args
                            ), $atts);

                    $zapier_args = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_args',
                            array(
                                'method' => 'POST',
                                'body' => json_encode($data),
                                'headers' => array(
                                    'Content-Type' => $content_type,
                                )), $atts);

                    $result = wp_remote_post($webhook, $zapier_args);

                    $response = apply_filters('e2pdf_model_shortcode_e2pdf_zapier_response', $response, $result, $atts);
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-view] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_view($atts = array()) {

        $response = '';
        $name = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_view_atts', $atts);

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : 0;
        $dataset2 = isset($atts['dataset2']) ? $atts['dataset2'] : 0;
        $uid = isset($atts['uid']) ? $atts['uid'] : false;
        $width = isset($atts['width']) ? $atts['width'] : '100%';
        $height = isset($atts['height']) ? $atts['height'] : '500';
        $pdf = isset($atts['pdf']) ? $atts['pdf'] : false;
        $jpg = false;
        $page = isset($atts['page']) ? $atts['page'] : false;
        $zoom = isset($atts['zoom']) ? $atts['zoom'] : false;
        $nameddest = isset($atts['nameddest']) ? $atts['nameddest'] : false;
        $pagemode = isset($atts['pagemode']) ? $atts['pagemode'] : false;
        $responsive = isset($atts['responsive']) && ($atts['responsive'] == 'true' || $atts['responsive'] == 'page') ? true : false;
        $viewer = isset($atts['viewer']) && $atts['viewer'] ? $atts['viewer'] : false;
        $single_page_mode = isset($atts['single_page_mode']) && $atts['single_page_mode'] == 'true' ? true : false;
        $hide = isset($atts['hide']) ? $atts['hide'] : false;
        $background = isset($atts['background']) ? $atts['background'] : false;
        $border = isset($atts['border']) ? $atts['border'] : false;
        $site_url = isset($atts['site_url']) ? $atts['site_url'] : false;
        $theme = isset($atts['theme']) && $atts['theme'] == 'light' ? 'light' : 'dark';

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        $viewer_options = array();
        if ($page) {
            $viewer_options[] = 'page=' . $page;
        }

        if ($zoom) {
            $viewer_options[] = 'zoom=' . $zoom;
        }

        if ($nameddest) {
            $viewer_options[] = 'nameddest=' . $nameddest;
        }

        if ($pagemode) {
            $viewer_options[] = 'pagemode=' . $pagemode;
        }

        if (array_key_exists('class', $atts)) {
            $classes = explode(" ", $atts['class']);
        } else {
            $classes = array();
        }

        $classes[] = 'e2pdf-view';

        if ($responsive) {
            $classes[] = 'e2pdf-responsive';
            if ($atts['responsive'] == 'page') {
                $classes[] = 'e2pdf-responsive-page';
            }
        }

        if ($single_page_mode) {
            $classes[] = 'e2pdf-single-page-mode';
        }

        if ($theme == 'dark') {
            $classes[] = 'e2pdf-dark-theme';
        }

        if ($hide) {

            $hidden = array_map('trim', explode(',', $hide));

            if (in_array('toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-toolbar';
            }

            if (in_array('secondary-toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-secondary-toolbar';
            }

            if (in_array('left-toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-left-toolbar';
            }

            if (in_array('middle-toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-middle-toolbar';
            }

            if (in_array('right-toolbar', $hidden)) {
                $classes[] = 'e2pdf-hide-right-toolbar';
            }

            if (in_array('sidebar', $hidden)) {
                $classes[] = 'e2pdf-hide-sidebar';
            }

            if (in_array('search', $hidden)) {
                $classes[] = 'e2pdf-hide-search';
            }

            if (in_array('pageupdown', $hidden)) {
                $classes[] = 'e2pdf-hide-pageupdown';
            }

            if (in_array('pagenumber', $hidden)) {
                $classes[] = 'e2pdf-hide-pagenumber';
            }

            if (in_array('zoom', $hidden)) {
                $classes[] = 'e2pdf-hide-zoom';
            }

            if (in_array('scale', $hidden)) {
                $classes[] = 'e2pdf-hide-scale';
            }

            if (in_array('presentation', $hidden)) {
                $classes[] = 'e2pdf-hide-presentation';
            }

            if (in_array('openfile', $hidden)) {
                $classes[] = 'e2pdf-hide-openfile';
            }

            if (in_array('print', $hidden)) {
                $classes[] = 'e2pdf-hide-print';
            }

            if (in_array('download', $hidden)) {
                $classes[] = 'e2pdf-hide-download';
            }

            if (in_array('bookmark', $hidden)) {
                $classes[] = 'e2pdf-hide-bookmark';
            }

            if (in_array('firstlastpage', $hidden)) {
                $classes[] = 'e2pdf-hide-firstlastpage';
            }

            if (in_array('rotate', $hidden)) {
                $classes[] = 'e2pdf-hide-rotate';
            }

            if (in_array('cursor', $hidden)) {
                $classes[] = 'e2pdf-hide-cursor';
            }

            if (in_array('scroll', $hidden)) {
                $classes[] = 'e2pdf-hide-scroll';
            }

            if (in_array('spread', $hidden)) {
                $classes[] = 'e2pdf-hide-spread';
            }

            if (in_array('properties', $hidden)) {
                $classes[] = 'e2pdf-hide-properties';
            }

            if (in_array('loader', $hidden)) {
                $classes[] = 'e2pdf-hide-loader';
            }
        }

        if ($background !== false) {
            $classes[] = 'e2pdf-hide-background';
        }

        $styles = array();

        if ($background !== false) {
            $styles[] = "background:" . $background;
        }

        if ($border !== false) {
            $styles[] = "border:" . $border;
        }

        if ($uid) {
            $entry = new Model_E2pdf_Entry();
            if ($entry->load_by_uid($uid)) {
                $uid_params = $entry->get('entry');
                $template_id = isset($uid_params['template_id']) ? (int) $uid_params['template_id'] : 0;
                $dataset = isset($uid_params['dataset']) ? $uid_params['dataset'] : 0;
                $dataset2 = isset($uid_params['dataset2']) ? $uid_params['dataset2'] : 0;

                $template = new Model_E2pdf_Template();
                if ($uid_params['pdf'] && file_exists($uid_params['pdf']) && isset($uid_params['template_id']) && isset($uid_params['dataset']) && $template->load($uid_params['template_id'])) {

                    if (isset($uid_params['name'])) {
                        $template->set('name', $uid_params['name']);
                    } else {
                        $template->set('name', $template->extension()->render($template->get('name')));
                    }

                    if ($template->get('name')) {
                        $name = $template->get('name');
                    } else {
                        $name = $template->extension()->render($template->get_filename());
                    }

                    if (isset($uid_params['format'])) {
                        $template->set('format', $uid_params['format']);
                    }

                    if ($template->get('format') == 'jpg') {
                        if (!$name) {
                            $name = basename($uid_params['pdf'], ".jpg");
                        }

                        $url_data = array(
                            'page' => 'e2pdf-download',
                            'uid' => $entry->get('uid'),
                            'v' => $this->helper->get('version')
                        );

                        $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                        $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_viewer_url_data', $url_data, $atts);

                        $jpg = esc_url_raw(
                                $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                    'e2pdf_model_shortcode_site_url',
                                    'e2pdf_model_shortcode_e2pdf_view_site_url'
                                ))
                        );
                    } else {
                        if (!$name) {
                            $name = basename($uid_params['pdf'], ".pdf");
                        }

                        $url_data = array(
                            'page' => 'e2pdf-download',
                            'uid' => $entry->get('uid'),
                            'v' => $this->helper->get('version'),
                            'saveName' => $name
                        );

                        $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                        $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_viewer_url_data', $url_data, $atts);

                        $pdf = esc_url_raw(
                                $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                    'e2pdf_model_shortcode_site_url',
                                    'e2pdf_model_shortcode_e2pdf_view_site_url'
                                ))
                        );
                    }
                }
            } else {
                return $response;
            }
        }

        if ($pdf) {
            if (filter_var($pdf, FILTER_VALIDATE_URL)) {

                $file = urlencode($pdf);
                if (!empty($viewer_options)) {
                    $file .= '#' . implode('&', $viewer_options);
                }

                if ($viewer) {
                    $url = esc_url(add_query_arg('file', $file, $viewer));
                } else {
                    $url = esc_url(add_query_arg('file', $file, plugins_url('assets/pdf.js/web/viewer.html', $this->helper->get('plugin_file_path'))));
                }

                $md5_version = md5($this->helper->get('version'));
                $response = "<iframe name='" . $md5_version . "' onload='e2pdf.viewerOnLoad(this)' style='" . implode(";", $styles) . "' class='" . implode(" ", $classes) . "' width='{$width}' height='{$height}' src='{$url}'></iframe>";
            } else if (!$this->helper->load('filter')->is_stream($pdf) && file_exists($pdf)) {

                $uid_params['pdf'] = $pdf;

                if (array_key_exists('name', $atts)) {
                    $name = $atts['name'];
                    $uid_params['name'] = $name;
                }

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {
                    if (!$name) {
                        $name = basename($uid_params['pdf'], ".pdf");
                    }

                    $url_data = array(
                        'page' => 'e2pdf-download',
                        'uid' => $entry->get('uid'),
                        'v' => $this->helper->get('version'),
                        'saveName' => $name);

                    $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                    $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_viewer_url_data', $url_data, $atts);

                    $pdf_url = esc_url_raw(
                            $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                'e2pdf_model_shortcode_site_url',
                                'e2pdf_model_shortcode_e2pdf_view_site_url'
                            ))
                    );

                    $file = urlencode($pdf_url);
                    if (!empty($viewer_options)) {
                        $file .= '#' . implode('&', $viewer_options);
                    }

                    if ($viewer) {
                        $url = esc_url(add_query_arg('file', $file, $viewer));
                    } else {
                        $url = esc_url(add_query_arg('file', $file, plugins_url('assets/pdf.js/web/viewer.html', $this->helper->get('plugin_file_path'))));
                    }

                    $md5_version = md5($this->helper->get('version'));
                    $response = "<iframe name='" . $md5_version . "' onload='e2pdf.viewerOnLoad(this)' style='" . implode(";", $styles) . "' class='" . implode(" ", $classes) . "' width='{$width}' height='{$height}' src='{$url}'></iframe>";
                }
            }
            return $response;
        } elseif ($jpg) {
            if (filter_var($jpg, FILTER_VALIDATE_URL)) {
                $jpg_url = $jpg;
                $response = "<img style='" . implode(";", $styles) . "' class='" . implode(" ", $classes) . "' width='{$width}' src='{$jpg_url}'>";
            }
            return $response;
        }

        if (!$template_id || (!$dataset && !$dataset2)) {
            return $response;
        }

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id, false)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;

            if ($dataset) {
                $uid_params['dataset'] = $dataset;
                $template->extension()->set('dataset', $dataset);
            }

            if ($dataset2) {
                $uid_params['dataset2'] = $dataset2;
                $template->extension()->set('dataset2', $dataset2);
            }

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_view_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

                if (array_key_exists('inline', $atts)) {
                    $inline = $atts['inline'] == 'true' ? 1 : 0;
                    $uid_params['inline'] = $inline;
                }

                if (array_key_exists('flatten', $atts)) {
                    $flatten = (int) $atts['flatten'];
                    $uid_params['flatten'] = $flatten;
                }

                if (array_key_exists('format', $atts)) {
                    $format = $atts['format'];
                    if ($template->set('format', $format)) {
                        $uid_params['format'] = $format;
                    }
                }

                if (array_key_exists('password', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $password = $template->extension()->render($atts['password']);
                    } else {
                        $password = $template->extension()->convert_shortcodes($atts['password'], true);
                    }
                    $uid_params['password'] = $password;
                }

                if (array_key_exists('meta_title', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_title = $template->extension()->render($atts['meta_title']);
                    } else {
                        $meta_title = $template->extension()->convert_shortcodes($atts['meta_title'], true);
                    }
                    $uid_params['meta_title'] = $meta_title;
                }

                if (array_key_exists('meta_subject', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_subject = $template->extension()->render($atts['meta_subject']);
                    } else {
                        $meta_subject = $template->extension()->convert_shortcodes($atts['meta_subject'], true);
                    }
                    $uid_params['meta_subject'] = $meta_subject;
                }

                if (array_key_exists('meta_author', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_author = $template->extension()->render($atts['meta_author']);
                    } else {
                        $meta_author = $template->extension()->convert_shortcodes($atts['meta_author'], true);
                    }
                    $uid_params['meta_author'] = $meta_author;
                }

                if (array_key_exists('meta_keywords', $atts)) {
                    if (!array_key_exists('filter', $atts)) {
                        $meta_keywords = $template->extension()->render($atts['meta_keywords']);
                    } else {
                        $meta_keywords = $template->extension()->convert_shortcodes($atts['meta_keywords'], true);
                    }
                    $uid_params['meta_keywords'] = $meta_keywords;
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

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                if ($entry->get('ID')) {
                    if ($template->get('format') == 'jpg') {

                        $url_data = array(
                            'page' => 'e2pdf-download',
                            'uid' => $entry->get('uid'),
                            'v' => $this->helper->get('version')
                        );

                        $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                        $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_viewer_url_data', $url_data, $atts);

                        $jpg_url = esc_url_raw(
                                $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                    'e2pdf_model_shortcode_site_url',
                                    'e2pdf_model_shortcode_e2pdf_view_site_url'
                                ))
                        );

                        $response = "<img style='" . implode(";", $styles) . "' class='" . implode(" ", $classes) . "' width='{$width}' src='{$jpg_url}'>";
                    } else {
                        $save_name = $template->get('name') ? $template->get('name') . ".pdf" : $template->extension()->render($template->get_filename()) . ".pdf";

                        $url_data = array(
                            'page' => 'e2pdf-download',
                            'uid' => $entry->get('uid'),
                            'v' => $this->helper->get('version'),
                            'saveName' => $save_name);

                        $url_data = apply_filters('e2pdf_model_shortcode_url_data', $url_data, $atts);
                        $url_data = apply_filters('e2pdf_model_shortcode_e2pdf_viewer_url_data', $url_data, $atts);

                        $pdf_url = esc_url_raw(
                                $this->helper->get_frontend_pdf_url($url_data, $site_url, array(
                                    'e2pdf_model_shortcode_site_url',
                                    'e2pdf_model_shortcode_e2pdf_view_site_url'
                                ))
                        );

                        $file = urlencode($pdf_url);
                        if (!empty($viewer_options)) {
                            $file .= '#' . implode('&', $viewer_options);
                        }

                        if ($viewer) {
                            $url = esc_url(add_query_arg('file', $file, $viewer));
                        } else {
                            $url = esc_url(add_query_arg('file', $file, plugins_url('assets/pdf.js/web/viewer.html', $this->helper->get('plugin_file_path'))));
                        }

                        $md5_version = md5($this->helper->get('version'));
                        $response = "<iframe name='" . $md5_version . "' onload='e2pdf.viewerOnLoad(this)' style='" . implode(";", $styles) . "' class='" . implode(" ", $classes) . "' width='{$width}' height='{$height}' src='{$url}'></iframe>";
                    }
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-adobesign] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_adobesign($atts = array()) {

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_atts', $atts);

        $template_id = isset($atts['id']) ? (int) $atts['id'] : 0;
        $dataset = isset($atts['dataset']) ? $atts['dataset'] : 0;
        $dataset2 = isset($atts['dataset2']) ? $atts['dataset2'] : 0;

        $args = array();
        foreach ($atts as $att_key => $att_value) {
            if (substr($att_key, 0, 3) === "arg") {
                $args[$att_key] = $att_value;
            }
        }

        $response = '';

        if (!array_key_exists('apply', $atts) || (!$dataset && !$dataset2) || !$template_id) {
            return $response;
        }

        $template = new Model_E2pdf_Template();
        if ($template->load($template_id)) {

            $uid_params = array();
            $uid_params['template_id'] = $template_id;

            if ($dataset) {
                $uid_params['dataset'] = $dataset;
                $template->extension()->set('dataset', $dataset);
            }

            if ($dataset2) {
                $uid_params['dataset2'] = $dataset2;
                $template->extension()->set('dataset2', $dataset2);
            }

            $options = array();
            $options = apply_filters('e2pdf_model_shortcode_extension_options', $options, $template);
            $options = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_extension_options', $options, $template);

            foreach ($options as $option_key => $option_value) {
                $template->extension()->set($option_key, $option_value);
            }

            if ($template->extension()->verify()) {

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
                    $template->set('meta_subject', $meta_subject);
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

                $disable = array();
                if (array_key_exists('disable', $atts)) {
                    $disable = explode(',', $atts['disable']);
                }

                if (array_key_exists('user_id', $atts)) {
                    $uid_params['user_id'] = (int) $atts['user_id'];
                } else {
                    $uid_params['user_id'] = get_current_user_id();
                }

                $uid_params['args'] = $args;

                $entry = new Model_E2pdf_Entry();
                $entry->set('entry', $uid_params);
                if (!$entry->load_by_uid($entry->get('uid'))) {
                    $entry->save();
                }

                $template->fill($dataset, $dataset2, $entry->get('uid'));
                $request = $template->render();

                if (!isset($request['error']) && $entry->get('ID')) {

                    $tmp_dir = $this->helper->get('tmp_dir') . 'e2pdf' . md5($entry->get('uid')) . '/';
                    $this->helper->create_dir($tmp_dir);

                    if ($template->get('name')) {
                        $name = $template->get('name');
                    } else {
                        $name = $template->extension()->render($template->get_filename());
                    }

                    $file_name = $name . '.pdf';
                    $file_name = $this->helper->load('convert')->to_file_name($file_name);
                    $file_path = $tmp_dir . $file_name;
                    file_put_contents($file_path, base64_decode($request['file']));

                    if (file_exists($file_path)) {

                        $agreement_id = false;
                        $documents = array();
                        if (!in_array('post_transientDocuments', $disable)) {
                            $model_e2pdf_adobesign = new Model_E2pdf_AdobeSign();
                            $model_e2pdf_adobesign->set(array(
                                'action' => 'api/rest/v5/transientDocuments',
                                'headers' => array(
                                    'Content-Type: multipart/form-data',
                                ),
                                'data' => array(
                                    'File-Name' => $file_name,
                                    'Mime-Type' => 'application/pdf',
                                    'File' => class_exists('cURLFile') ? new cURLFile($file_path) : '@' . $file_path
                                ),
                            ));

                            if ($transientDocumentId = $model_e2pdf_adobesign->request('transientDocumentId')) {
                                $documents[] = array(
                                    'transientDocumentId' => $transientDocumentId
                                );
                            }
                            $model_e2pdf_adobesign->flush();
                        }

                        $documents = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_fileInfos', $documents, $atts, $template, $entry, $template->extension(), $file_path);

                        if (!in_array('post_agreements', $disable) && !empty($documents)) {

                            $output = false;
                            if (array_key_exists('output', $atts)) {
                                $output = $atts['output'];
                            }

                            $recipients = array();
                            if (array_key_exists('recipients', $atts)) {
                                $atts['recipients'] = $template->extension()->render($atts['recipients']);
                                $recipients_list = explode(',', $atts['recipients']);

                                foreach ($recipients_list as $recipient_info) {
                                    $recipients[] = array(
                                        'recipientSetMemberInfos' => array(
                                            'email' => trim($recipient_info)
                                        ),
                                        'recipientSetRole' => 'SIGNER'
                                    );
                                }
                            }

                            $data = array(
                                'documentCreationInfo' => array(
                                    'signatureType' => 'ESIGN',
                                    'recipientSetInfos' => $recipients,
                                    'signatureFlow' => 'SENDER_SIGNATURE_NOT_REQUIRED',
                                    'fileInfos' => $documents,
                                    'name' => $name
                                )
                            );

                            $data = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_post_agreements_data', $data, $atts, $template, $entry, $template->extension(), $file_path, $documents);

                            $model_e2pdf_adobesign = new Model_E2pdf_AdobeSign();
                            $model_e2pdf_adobesign->set(array(
                                'action' => 'api/rest/v5/agreements',
                                'data' => $data,
                            ));

                            $agreement_id = $model_e2pdf_adobesign->request('agreementId');
                            $model_e2pdf_adobesign->flush();
                        }

                        $response = apply_filters('e2pdf_model_shortcode_e2pdf_adobesign_response', $response, $atts, $template, $entry, $template->extension(), $file_path, $documents, $agreement_id);
                    }

                    $this->helper->delete_dir($tmp_dir);
                    return $response;
                }
            }
        }
        return $response;
    }

    /**
     * [e2pdf-format-number] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_format_number($atts = array(), $value = '') {

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_format_number_atts', $atts);

        $dec_point = isset($atts['dec_point']) ? $atts['dec_point'] : '.';
        $thousands_sep = isset($atts['thousands_sep']) ? $atts['thousands_sep'] : '';
        $decimal = isset($atts['decimal']) ? $atts['decimal'] : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : '';
        $implode = isset($atts['implode']) ? $atts['implode'] : '';

        $new_value = array();
        $value = array_filter((array) $value, 'strlen');

        foreach ($value as $v) {
            if ($explode && strpos($v, $explode) !== false) {
                $v = explode($explode, $v);
            }
            foreach ((array) $v as $n) {
                $n = str_replace(array(" ", ","), array("", "."), $n);
                $n = preg_replace('/\.(?=.*\.)/', '', $n);
                $n = floatval($n);

                if (!$decimal) {
                    $num = explode('.', $n);
                    $decimal = isset($num[1]) ? strlen($num[1]) : 0;
                }

                $n = number_format($n, $decimal, $dec_point, $thousands_sep);
                $new_value[] = $n;
            }
            unset($v);
        }

        $new_value = array_filter((array) $new_value, 'strlen');

        return implode($implode, $new_value);
    }

    /**
     * [e2pdf-format-date] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_format_date($atts = array(), $value = '') {

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_format_date_atts', $atts);

        $format = isset($atts['format']) ? $atts['format'] : get_option('date_format');
        $offset = isset($atts['offset']) ? $atts['offset'] : false;
        $function = isset($atts['function']) ? $atts['function'] : false;

        $timezone = null;
        if (isset($atts['timezone'])) {
            try {
                $timezone = new DateTimeZone($atts['timezone']);
            } catch (Exception $e) {
                $timezone = null;
            }
        }

        $gmt = isset($atts['gmt']) && $atts['gmt'] == 'true' ? true : false;
        $locale = isset($atts['locale']) && $atts['locale'] ? $atts['locale'] : false;

        if (!$value) {
            return '';
        }

        switch (trim(strtolower($value))) {
            case 'time':
                $value = time();
                break;
            case 'current_time':
                $value = current_time('timestamp', $gmt);
                break;
            default:
                $value = strtotime($value);
        }

        if ($locale && function_exists('switch_to_locale') && function_exists('restore_previous_locale')) {
            switch_to_locale($locale);
        }

        if ($offset) {
            if ($function == 'wp_date' && function_exists('wp_date')) {
                $value = wp_date($format, strtotime($offset, $value), $timezone);
            } elseif ($function == 'date_i18n') {
                $value = date_i18n($format, strtotime($offset, $value));
            } else {
                $value = date($format, strtotime($offset, $value));
            }
        } else {
            if ($function == 'wp_date' && function_exists('wp_date')) {
                $value = wp_date($format, $value, $timezone);
            } elseif ($function == 'date_i18n') {
                $value = date_i18n($format, $value);
            } else {
                $value = date($format, $value);
            }
        }

        if ($locale && function_exists('switch_to_locale') && function_exists('restore_previous_locale')) {
            restore_previous_locale();
        }

        return $value;
    }

    /**
     * [e2pdf-format-output] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_format_output($atts = array(), $value = '') {

        $content = $value;
        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_format_output_atts', $atts);

        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $explode_filter = isset($atts['explode_filter']) ? $atts['explode_filter'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : '';
        $output = isset($atts['output']) ? $atts['output'] : false;
        $filter = isset($atts['filter']) ? $atts['filter'] : false;
        $search = isset($atts['search']) ? explode("|||", $atts['search']) : array();
        $ireplace = isset($atts['ireplace']) ? explode("|||", $atts['ireplace']) : array();
        $replace = isset($atts['replace']) ? explode("|||", $atts['replace']) : array();
        $substr = isset($atts['substr']) ? $atts['substr'] : false;
        $sprintf = isset($atts['sprintf']) ? $atts['sprintf'] : false;
        $remove_tags = isset($atts['remove_tags']) ? $atts['remove_tags'] : false;
        $trim = isset($atts['trim']) ? $atts['trim'] : false;
        $rtrim = isset($atts['rtrim']) ? $atts['rtrim'] : false;
        $ltrim = isset($atts['ltrim']) ? $atts['ltrim'] : false;
        $strip_tags_allowed = isset($atts['strip_tags_allowed']) ? $atts['strip_tags_allowed'] : false;
        $pre = isset($atts['pre']) && $atts['pre'] ? $atts['pre'] : '';
        $after = isset($atts['after']) && $atts['after'] ? $atts['after'] : '';
        $strip_shortcodes_tags = isset($atts['strip_shortcodes_tags']) ? explode(",", $atts['strip_shortcodes_tags']) : array();
        $strip_shortcodes_tags_full = isset($atts['strip_shortcodes_tags_full']) ? explode(",", $atts['strip_shortcodes_tags_full']) : array();

        $filters = array();
        if ($filter) {
            if (strpos($filter, ',')) {
                $filters = explode(',', $filter);
            } else {
                $filters = array_filter((array) $filter, 'strlen');
            }
        }

        $explode_filters = array();
        if ($explode_filter) {
            if (strpos($explode_filter, ',')) {
                $explode_filters = explode(',', $explode_filter);
            } else {
                $explode_filters = array_filter((array) $explode_filter, 'strlen');
            }
        }

        if (!in_array('ireplace', $filters) && !in_array('replace', $filters)) {
            if (!empty($ireplace)) {
                $content = str_ireplace($search, $ireplace, $content);
            } elseif (!empty($replace)) {
                $content = str_replace($search, $replace, $content);
            }
        }

        if (!in_array('substr', $filters)) {
            if ($substr !== false) {
                $substr_start = false;
                $substr_length = false;
                if (strpos($substr, ',')) {
                    $substr_data = explode(',', $substr);
                    if (isset($substr_data[0])) {
                        $substr_start = trim($substr_data[0]);
                    }
                    if (isset($substr_data[1])) {
                        $substr_length = trim($substr_data[1]);
                    }
                } else {
                    $substr_start = trim($substr);
                }

                if ($substr_start !== false && $substr_length !== false) {
                    $content = substr($content, $substr_start, $substr_length);
                } elseif ($substr_start !== false) {
                    $content = substr($content, $substr_start);
                }
            }
        }

        if (!in_array('trim', $filters)) {
            if ($trim !== false) {
                $content = trim($content, $trim);
            }
        }

        if (!in_array('rtrim', $filters)) {
            if ($rtrim !== false) {
                $content = rtrim($content, $rtrim);
            }
        }

        if (!in_array('ltrim', $filters)) {
            if ($ltrim !== false) {
                $content = ltrim($content, $ltrim);
            }
        }

        if (!in_array('sprintf', $filters)) {
            if ($sprintf !== false) {
                $content = sprintf($sprintf, $content);
            }
        }

        $closed_tags = array(
            'style', 'script', 'title', 'head'
        );
        if (isset($atts['closed_tags']) && $atts['closed_tags']) {
            $closed_tags = array_merge($closed_tags, explode(',', $atts['closed_tags']));
        }

        $mixed_tags = array();
        if (isset($atts['mixed_tags']) && $atts['mixed_tags']) {
            $closed_tags = array_merge($closed_tags, explode(',', $atts['mixed_tags']));
        }

        $closed_tags = apply_filters('e2pdf_model_shortcode_wp_e2pdf_format_output_closed_tags', $closed_tags);
        $mixed_tags = apply_filters('e2pdf_model_shortcode_wp_e2pdf_format_output_mixed_tags', $mixed_tags);

        if (!in_array('remove_tags', $filters)) {
            if ($remove_tags) {
                $remove_tags_list = explode(',', $remove_tags);
                foreach ($remove_tags_list as $remove_tag) {
                    if (in_array($remove_tag, $mixed_tags)) {
                        $content = preg_replace('#<' . $remove_tag . '(.*?)>(.*?)</' . $remove_tag . '>#is', '', $content);
                        $content = preg_replace('#<' . $remove_tag . '([^>]+)?\>#is', '', $content);
                    } elseif (in_array($remove_tag, $closed_tags)) {
                        $content = preg_replace('#<' . $remove_tag . '(.*?)>(.*?)</' . $remove_tag . '>#is', '', $content);
                    } else {
                        $content = preg_replace('#<' . $remove_tag . '([^>]+)?\>#is', '', $content);
                    }
                }
            }
        }

        $new_value = array();
        $content = array_filter((array) $content, 'strlen');

        foreach ($content as $v) {

            if ($explode && strpos($v, $explode) !== false) {
                $v = explode($explode, $v);
                if (is_array($v) && !empty($explode_filters)) {
                    foreach ((array) $explode_filters as $sub_explode_filter) {
                        switch ($sub_explode_filter) {
                            case 'array_filter':
                                $v = array_filter($v);
                                break;
                            case 'array_values':
                                $v = array_values($v);
                                break;
                            default:
                                break;
                        }
                    }
                }
            }

            foreach ((array) $v as $n) {
                if (!empty($filters)) {
                    foreach ((array) $filters as $sub_filter) {
                        switch ($sub_filter) {
                            case 'trim':
                                if ($trim !== false) {
                                    $n = trim($n, $trim);
                                } else {
                                    $n = trim($n);
                                }
                                break;
                            case 'rtrim':
                                if ($rtrim !== false) {
                                    $n = rtrim($n, $rtrim);
                                } else {
                                    $n = rtrim($n);
                                }
                                break;
                            case 'ltrim':
                                if ($ltrim !== false) {
                                    $n = ltrim($n, $ltrim);
                                } else {
                                    $n = ltrim($n);
                                }
                                break;
                            case 'strip_tags':
                                if ($strip_tags_allowed !== false) {
                                    $n = strip_tags($n, $strip_tags_allowed);
                                } else {
                                    $n = strip_tags($n);
                                }
                                break;
                            case 'strtolower':
                                if (function_exists('mb_strtolower')) {
                                    $n = mb_strtolower($n);
                                } elseif (function_exists('strtolower')) {
                                    $n = strtolower($n);
                                }
                                break;
                            case 'ucfirst':
                                if (function_exists('mb_strtoupper') && function_exists('mb_strtolower')) {
                                    $fc = mb_strtoupper(mb_substr($n, 0, 1));
                                    $n = $fc . mb_substr($n, 1);
                                } else if (function_exists('ucfirst') && function_exists('strtolower')) {
                                    $n = ucfirst($n);
                                }
                                break;
                            case 'strtoupper':
                                if (function_exists('mb_strtoupper')) {
                                    $n = mb_strtoupper($n);
                                } elseif (function_exists('strtoupper')) {
                                    $n = strtoupper($n);
                                }
                                break;
                            case 'lines':
                                $n = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $n);
                                break;
                            case 'nl2br':
                                $n = nl2br($n);
                                break;
                            case 'wpautop':
                                $n = wpautop($n);
                                break;
                            case 'html_entity_decode':
                                $n = html_entity_decode($n);
                                break;
                            case 'strip_shortcodes':
                                if (false !== strpos($n, '&#091;')) {

                                    $n = str_replace("&#091;", "[", $n);
                                    if (!empty($strip_shortcodes_tags_full)) {
                                        foreach ($strip_shortcodes_tags_full as $strip_shortcode_tag) {
                                            $n = preg_replace('~(\[(' . $strip_shortcode_tag . ')[^\]]*\].*?\[\/' . $strip_shortcode_tag . '])~s', '', $n);
                                        }
                                    }

                                    if (!empty($strip_shortcodes_tags)) {
                                        foreach ($strip_shortcodes_tags as $strip_shortcode_tag) {
                                            $n = preg_replace(' ~\[/?' . $strip_shortcode_tag . '[^\]]*\]~s', '', $n);
                                        }
                                    }

                                $n = strip_shortcodes($n);
                                    $n = str_replace("[", "&#091;", $n);
                                }
                                break;

                            case 'strip_shortcodes_tags_full':
                                if (!empty($strip_shortcodes_tags_full) && false !== strpos($n, '&#091;')) {
                                    $n = str_replace("&#091;", "[", $n);
                                    foreach ($strip_shortcodes_tags_full as $strip_shortcode_tag) {
                                        $n = preg_replace('~(\[(' . $strip_shortcode_tag . ')[^\]]*\].*?\[\/' . $strip_shortcode_tag . '])~s', '', $n);
                                    }
                                    $n = str_replace("[", "&#091;", $n);
                                }
                                break;

                            case 'strip_shortcodes_tags':
                                if (!empty($strip_shortcodes_tags) && false !== strpos($n, '&#091;')) {
                                    $n = str_replace("&#091;", "[", $n);
                                    foreach ($strip_shortcodes_tags as $strip_shortcode_tag) {
                                        $n = preg_replace(' ~\[/?' . $strip_shortcode_tag . '[^\]]*\]~s', '', $n);
                                    }
                                    $n = str_replace("[", "&#091;", $n);
                                }
                                break;

                            case 'do_shortcode':
                                if (false !== strpos($n, '&#091;')) {
                                    $n = str_replace("&#091;", "[", $n);
                                    $n = do_shortcode($n);
                                    $n = str_replace("[", "&#091;", $n);
                                }
                                break;

                            case 'htmlentities':
                                $n = htmlentities($n);
                                break;
                            case 'substr':
                                if ($substr !== false) {
                                    $substr_start = false;
                                    $substr_length = false;
                                    if (strpos($substr, ',')) {
                                        $substr_data = explode(',', $substr);
                                        if (isset($substr_data[0])) {
                                            $substr_start = trim($substr_data[0]);
                                        }
                                        if (isset($substr_data[1])) {
                                            $substr_length = trim($substr_data[1]);
                                        }
                                    } else {
                                        $substr_start = trim($substr);
                                    }

                                    if ($substr_start !== false && $substr_length !== false) {
                                        $n = substr($n, $substr_start, $substr_length);
                                    } elseif ($substr_start !== false) {
                                        $n = substr($n, $substr_start);
                                    }
                                }
                                break;
                            case 'ireplace':
                                if (!empty($ireplace)) {
                                    $n = str_ireplace($search, $ireplace, $n);
                                }
                                break;
                            case 'replace':
                                if (!empty($replace)) {
                                    $n = str_replace($search, $replace, $n);
                                }
                                break;
                            case 'remove_tags':
                                if ($remove_tags) {
                                    $remove_tags_list = explode(',', $remove_tags);
                                    foreach ($remove_tags_list as $remove_tag) {
                                        if (in_array($remove_tag, $mixed_tags)) {
                                            $n = preg_replace('#<' . $remove_tag . '(.*?)>(.*?)</' . $remove_tag . '>#is', '', $n);
                                            $n = preg_replace('#<' . $remove_tag . '([^>]+)?\>#is', '', $n);
                                        } elseif (in_array($remove_tag, $closed_tags)) {
                                            $n = preg_replace('#<' . $remove_tag . '(.*?)>(.*?)</' . $remove_tag . '>#is', '', $n);
                                        } else {
                                            $n = preg_replace('#<' . $remove_tag . '([^>]+)?\>#is', '', $n);
                                        }
                                    }
                                }
                                break;
                            case 'sprintf':
                                if ($sprintf !== false) {
                                    $n = sprintf($sprintf, $n);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
                $new_value[] = $n;
            }
            unset($v);
        }

        if ($output) {
            if ($output == '{count}') {
                return apply_filters('e2pdf_model_shortcode_e2pdf_format_output', count($new_value), $atts, $value);
            } else {
                $o_search = array();
                $o_replace = array();

                foreach ($new_value as $key => $val) {
                    $o_search[] = "{" . $key . "}";
                    $o_replace[] = $val;
                }
                $output = str_replace($o_search, $o_replace, $output);
                return apply_filters('e2pdf_model_shortcode_e2pdf_format_output', preg_replace('~(?:{/?)[^/}]+/?}~s', "", $output), $atts, $value);
            }
        } else {
            if ($pre || $after) {
                $wrapped = array();
                foreach ($new_value as $key => $val) {
                    $wrapped[] = $pre . $val . $after;
                }
                return apply_filters('e2pdf_model_shortcode_e2pdf_format_output', implode($implode, $wrapped), $atts, $value);
            } else {
                return apply_filters('e2pdf_model_shortcode_e2pdf_format_output', implode($implode, $new_value), $atts, $value);
            }
        }
    }

    /**
     * [e2pdf-user] shortcode
     * 
     * @param array $atts - Attributes
     */
    function e2pdf_user($atts = array(), $value = '') {

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_user_atts', $atts);

        $id = isset($atts['id']) ? $atts['id'] : '0';

        if ($id == 'current') {
            $id = get_current_user_id();
        } elseif ($id == 'dynamic') {
            $id = $value;
        }

        $key = isset($atts['key']) ? $atts['key'] : 'ID';
        $meta = isset($atts['meta']) && $atts['meta'] == 'true' ? true : false;
        $path = isset($atts['path']) ? $atts['path'] : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : false;
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $size = isset($atts['size']) ? $atts['size'] : 'thumbnail';
        $raw = isset($atts['raw']) && $atts['raw'] == 'true' ? true : false;

        $response = '';

        $data_fields = apply_filters('e2pdf_model_shortcode_user_data_fields', array(
            'ID',
            'user_login',
            'user_nicename',
            'user_email',
            'user_url',
            'user_registered',
            'display_name',
            'roles'
        ));

        if (in_array($key, $data_fields) && !$meta) {
            $user = get_userdata($id);
            if (isset($user->$key)) {
                $user_meta = $user->$key;
            } elseif ($key == 'ID') {
                $user_meta = '0';
            }
        } elseif ($key == 'user_avatar' && !$meta) {
            $user_meta = get_avatar_url($id, $atts);
        } else {
            $user_meta = get_user_meta($id, $key, true);
        }

        if ($user_meta !== false) {

            if (is_object($user_meta)) {
                $user_meta = apply_filters('e2pdf_model_shortcode_e2pdf_user_object', $user_meta, $atts);
            }

            if ($explode && !is_array($user_meta)) {
                $user_meta = explode($explode, $user_meta);
            }

            if (is_array($user_meta)) {
                $user_meta = apply_filters('e2pdf_model_shortcode_e2pdf_user_array', $user_meta, $atts);
            }

            if ((is_array($user_meta) || is_object($user_meta)) && $path !== false) {
                $path_parts = explode('.', $path);
                $path_value = &$user_meta;
                $found = true;
                foreach ($path_parts as $path_part) {
                    if (is_array($path_value) && isset($path_value[$path_part])) {
                        $path_value = &$path_value[$path_part];
                    } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                        $path_value = &$path_value->$path_part;
                    } else {
                        $found = false;
                        break;
                    }
                }
                if ($found) {
                    $user_meta = $path_value;
                } else {
                    $user_meta = '';
                }
            }

            if ($attachment_url || $attachment_image_url) {
                if (!is_array($user_meta)) {
                    if (strpos($user_meta, ',') !== false) {
                        $user_meta = explode(',', $user_meta);
                        if ($implode === false) {
                            $implode = ',';
                        }
                    }
                }

                if (is_array($user_meta)) {
                    $attachments = array();
                    foreach ($user_meta as $user_meta_part) {
                        if (!is_array($user_meta_part)) {
                            if ($attachment_url) {
                                $image = wp_get_attachment_url($user_meta_part);
                            } elseif ($attachment_image_url) {
                                $image = wp_get_attachment_image_url($user_meta_part, $size);
                            }
                            if ($image) {
                                $attachments[] = $image;
                            }
                        }
                    }
                    $user_meta = $attachments;
                } else {
                    if ($attachment_url) {
                        $image = wp_get_attachment_url($user_meta);
                    } elseif ($attachment_image_url) {
                        $image = wp_get_attachment_image_url($user_meta, $size);
                    }
                    if ($image) {
                        $user_meta = $image;
                    } else {
                        $user_meta = '';
                    }
                }
            }

            if ($raw) {
                $response = $user_meta;
            } else {
                if (is_array($user_meta)) {
                    if ($implode !== false) {
                        if (!$this->helper->is_multidimensional($user_meta)) {
                            $response = implode($implode, $user_meta);
                        } else {
                            $response = serialize($user_meta);
                        }
                    } else {
                        $response = serialize($user_meta);
                    }
                } elseif (is_object($user_meta)) {
                    $response = serialize($user_meta);
                } else {
                    $response = $user_meta;
                }
            }
        }

        if ($raw) {
            return apply_filters('e2pdf_model_shortcode_e2pdf_user_raw', $response, $atts, $value);
        } else {
            return apply_filters('e2pdf_model_shortcode_e2pdf_user_response', $response, $atts, $value);
        }
    }

    /**
     * [e2pdf-content] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_content($atts = array(), $value = '') {

        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_content_atts', $atts);

        $id = isset($atts['id']) ? $atts['id'] : false;
        $key = isset($atts['key']) ? $atts['key'] : false;

        if ($id && $key) {
            $wp_post = get_post($id);
            if ($wp_post) {
                if (isset($wp_post->post_content) && $wp_post->post_content) {
                    $content = $this->helper->load('convert')->to_content_key($key, $wp_post->post_content);
                    remove_filter('the_content', 'wpautop');
                    $content = apply_filters('the_content', $content);
                    add_filter('the_content', 'wpautop');
                    $content = str_replace("</p>", "</p>\r\n", $content);
                    $response = $content;
                }
            }
        } elseif ($value) {
            $response = apply_filters('the_content', $value);
        }
        return $response;
    }

    /**
     * [e2pdf-exclude] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_exclude($atts = array(), $value = '') {

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_exclude_atts', $atts);

        $apply = isset($atts['apply']) ? true : false;

        if ($apply) {
            $response = '';
        } else {
            $response = apply_filters('the_content', $value);
        }

        return $response;
    }

    /**
     * [e2pdf-wp] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_wp($atts = array(), $value = '') {

        $post_meta = false;
        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_wp_atts', $atts);

        $id = isset($atts['id']) ? $atts['id'] : false;
        $key = isset($atts['key']) ? $atts['key'] : false;
        $subkey = isset($atts['subkey']) ? $atts['subkey'] : false;
        $path = isset($atts['path']) ? $atts['path'] : false;
        $names = isset($atts['names']) && $atts['names'] == 'true' ? true : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : false;
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $size = isset($atts['size']) ? $atts['size'] : 'thumbnail';
        $meta = isset($atts['meta']) && $atts['meta'] == 'true' ? true : false;
        $terms = isset($atts['terms']) && $atts['terms'] == 'true' ? true : false;
        $convert = isset($atts['convert']) ? $atts['convert'] : false;
        $output = isset($atts['output']) ? $atts['output'] : false;
        $raw = isset($atts['raw']) && $atts['raw'] == 'true' ? true : false;

        if ($id == 'dynamic') {
            $id = $value;
        }

        $data_fields = apply_filters('e2pdf_model_shortcode_wp_data_fields', array(
            'id',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_title',
            'post_excerpt',
            'post_status',
            'permalink',
            'post_permalink',
            'get_permalink',
            'get_post_permalink',
            'comment_status',
            'ping_status',
            'post_password',
            'post_name',
            'to_ping',
            'pinged',
            'post_modified',
            'post_modified_gmt',
            'post_content_filtered',
            'post_parent',
            'guid',
            'menu_order',
            'post_type',
            'post_mime_type',
            'comment_count',
            'filter',
            'post_thumbnail',
            'get_the_post_thumbnail',
            'get_the_post_thumbnail_url',
            'response_hook'
        ));

        if ($id && $key) {
            $wp_post = get_post($id);
            if ($wp_post) {
                if (in_array($key, $data_fields) && !$meta && !$terms) {
                    if ($key == 'post_author') {
                        if (isset($wp_post->post_author) && $wp_post->post_author) {
                            if (isset($atts['subkey'])) {
                                $atts['id'] = $wp_post->post_author;
                                $atts['key'] = $subkey;
                                $post_meta = $this->e2pdf_user($atts);
                            } else {
                                $post_meta = get_userdata($wp_post->post_author)->user_nicename;
                            }
                        }
                    } elseif ($key == 'id' && isset($wp_post->ID)) {
                        $post_meta = $wp_post->ID;
                    } elseif (($key == 'post_thumbnail' || $key == 'get_the_post_thumbnail_url') && isset($wp_post->ID)) {
                        $post_meta = get_the_post_thumbnail_url($wp_post->ID, $size);
                    } elseif ($key == 'get_the_post_thumbnail' && isset($wp_post->ID)) {
                        $post_meta = get_the_post_thumbnail($wp_post->ID, $size);
                    } elseif ($key == 'post_content' && isset($wp_post->post_content)) {
                        $content = $wp_post->post_content;

                        if (false !== strpos($content, '[')) {
                            $shortcode_tags = array(
                                'e2pdf-exclude',
                                'e2pdf-save'
                            );
                            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
                            $tagnames = array_intersect($shortcode_tags, $matches[1]);
                            if (!empty($tagnames)) {
                                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);
                                preg_match_all("/$pattern/", $content, $shortcodes);
                                foreach ($shortcodes[0] as $key => $shortcode_value) {
                                    $content = str_replace($shortcode_value, '', $content);
                                }
                            }
                        }

                        if ($output) {
                            global $post;
                            $tmp_post = $post;
                            $post = $wp_post;
                            if ($output == 'backend') {
                                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                                    \Elementor\Plugin::instance()->frontend->remove_content_filter();
                                }
                            } elseif ($output == 'frontend') {
                                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                                    \Elementor\Plugin::instance()->frontend->add_content_filter();
                                }
                            }
                        }

                        if (defined('ET_BUILDER_DIR') && 'on' === get_post_meta($id, '_et_pb_use_builder', true) && function_exists('et_builder_init_global_settings') && function_exists('et_builder_add_main_elements')) {
                            if (file_exists(ET_BUILDER_DIR . 'class-et-builder-value.php') && !class_exists('ET_Builder_Value')) {
                                require_once ET_BUILDER_DIR . 'class-et-builder-value.php';
                            }
                            require_once(ET_BUILDER_DIR . 'class-et-builder-element.php');
                            require_once(ET_BUILDER_DIR . 'functions.php');
                            require_once(ET_BUILDER_DIR . 'ab-testing.php');
                            require_once(ET_BUILDER_DIR . 'class-et-global-settings.php');
                            et_builder_add_main_elements();
                        }

                        $content = apply_filters('the_content', $content);
                        $content = str_replace("</p>", "</p>\r\n", $content);
                        $post_meta = $content;

                        if ($output) {
                            $post = $tmp_post;
                        }
                    } elseif ($key == 'get_permalink' || $key == 'permalink') {
                        $leavename = isset($atts['leavename']) && $atts['leavename'] == 'true' ? true : false;
                        $post_meta = get_permalink($id, $leavename);
                    } elseif ($key == 'get_post_permalink' || $key == 'post_permalink') {
                        $leavename = isset($atts['leavename']) && $atts['leavename'] == 'true' ? true : false;
                        $post_meta = get_post_permalink($id, $leavename);
                    } elseif ($key == 'response_hook') {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wp_response_hook', '', $id, $atts, $wp_post);
                    } elseif (isset($wp_post->$key)) {
                        $post_meta = $wp_post->$key;
                    }
                } elseif ($terms && $names) {
                    $post_terms = wp_get_post_terms($id, $key, array('fields' => 'names'));
                    if (!is_wp_error($post_terms) && is_array($post_terms)) {
                        if ($implode === false) {
                            $implode = ', ';
                        }
                        $post_meta = implode($implode, $post_terms);
                    }
                } elseif ($terms) {
                    $post_terms = wp_get_post_terms($id, $key);
                    if (!is_wp_error($post_terms)) {
                        $post_meta = json_decode(json_encode($post_terms), true);
                    }
                } else {
                    $post_meta = get_post_meta($id, $key, true);
                }

                if ($post_meta !== false) {

                    if (is_object($post_meta)) {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wp_object', $post_meta, $atts);
                    }

                    if ($explode && !is_array($post_meta)) {
                        $post_meta = explode($explode, $post_meta);
                    }

                    if (is_array($post_meta)) {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wp_array', $post_meta, $atts);
                    }

                    if ((is_array($post_meta) || is_object($post_meta)) && $path !== false) {
                        $path_parts = explode('.', $path);
                        $path_value = &$post_meta;
                        $found = true;
                        foreach ($path_parts as $path_part) {
                            if (is_array($path_value) && isset($path_value[$path_part])) {
                                $path_value = &$path_value[$path_part];
                            } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                                $path_value = &$path_value->$path_part;
                            } else {
                                $found = false;
                                break;
                            }
                        }
                        if ($found) {
                            $post_meta = $path_value;
                        } else {
                            $post_meta = '';
                        }
                    }

                    if ($attachment_url || $attachment_image_url) {
                        if (!is_array($post_meta)) {
                            if (strpos($post_meta, ',') !== false) {
                                $post_meta = explode(',', $post_meta);
                                if ($implode === false) {
                                    $implode = ',';
                                }
                            }
                        }

                        if (is_array($post_meta)) {
                            $attachments = array();
                            foreach ($post_meta as $post_meta_part) {
                                if (!is_array($post_meta_part)) {
                                    if ($attachment_url) {
                                        $image = wp_get_attachment_url($post_meta_part);
                                    } elseif ($attachment_image_url) {
                                        $image = wp_get_attachment_image_url($post_meta_part, $size);
                                    }
                                    if ($image) {
                                        $attachments[] = $image;
                                    }
                                }
                            }
                            $post_meta = $attachments;
                        } else {
                            if ($attachment_url) {
                                $image = wp_get_attachment_url($post_meta);
                            } elseif ($attachment_image_url) {
                                $image = wp_get_attachment_image_url($post_meta, $size);
                            }
                            if ($image) {
                                $post_meta = $image;
                            } else {
                                $post_meta = '';
                            }
                        }
                    }

                    if ($convert == 'id_to_term') {
                        if (!is_array($post_meta)) {
                            if (strpos($post_meta, ',') !== false) {
                                $post_meta = explode(',', $post_meta);
                                if ($implode === false) {
                                    $implode = ',';
                                }
                            }
                        }

                        if (is_array($post_meta)) {
                            $post_terms = array();
                            foreach ($post_meta as $post_meta_part) {
                                if (!is_array($post_meta_part)) {
                                    $post_term = get_term($post_meta_part);
                                    if ($post_term && !is_wp_error($post_term)) {
                                        if ($subkey) {
                                            if (isset($post_term->$subkey)) {
                                                $post_terms[] = $post_term->$subkey;
                                            }
                                        } else {
                                            $post_terms[] = $post_term;
                                        }
                                    }
                                }
                            }
                            $post_meta = $post_terms;
                        } else {
                            $post_term = get_term($post_meta);
                            if ($post_term && !is_wp_error($post_term)) {
                                if ($subkey) {
                                    if (isset($post_term->$subkey)) {
                                        $post_meta = $post_term->$subkey;
                                    }
                                } else {
                                    $post_meta = $post_term;
                                }
                            } else {
                                $post_meta = '';
                            }
                        }
                    }

                    if ($raw) {
                        $response = $post_meta;
                    } else {
                        if (is_array($post_meta)) {
                            if ($implode !== false) {
                                if (!$this->helper->is_multidimensional($post_meta)) {
                                    $response = implode($implode, $post_meta);
                                } else {
                                    $response = serialize($post_meta);
                                }
                            } else {
                                $response = serialize($post_meta);
                            }
                        } elseif (is_object($post_meta)) {
                            $response = serialize($post_meta);
                        } else {
                            $response = $post_meta;
                        }
                    }
                }
            }
        }

        if ($raw) {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wp_raw', $response, $atts, $value);
        } else {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wp_response', $response, $atts, $value);
        }
    }

    /**
     * [e2pdf-wp-term] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_wp_term($atts = array(), $value = '') {

        $post_meta = false;
        $response = '';

        $id = isset($atts['id']) ? $atts['id'] : false;
        $key = isset($atts['key']) ? $atts['key'] : false;
        $meta = isset($atts['meta']) && $atts['meta'] == 'true' ? true : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $path = isset($atts['path']) ? $atts['path'] : false;
        $raw = isset($atts['raw']) && $atts['raw'] == 'true' ? true : false;

        if ($id == 'dynamic') {
            $id = $value;
        }

        if ($id && $key) {
            $wp_post = get_term($id);
            if ($wp_post && !is_wp_error($wp_post)) {
                if (!$meta) {
                    if (isset($wp_post->$key)) {
                        $post_meta = $wp_post->$key;
                    }
                } else {
                    $post_meta = get_term_meta($id, $key, true);
                }

                if ($post_meta !== false) {

                    if (is_object($post_meta)) {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wp_term_object', $post_meta, $atts);
                    }

                    if ($explode && !is_array($post_meta)) {
                        $post_meta = explode($explode, $post_meta);
                    }

                    if (is_array($post_meta)) {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wp_term_array', $post_meta, $atts);
                    }

                    if ((is_array($post_meta) || is_object($post_meta)) && $path !== false) {
                        $path_parts = explode('.', $path);
                        $path_value = &$post_meta;
                        $found = true;
                        foreach ($path_parts as $path_part) {
                            if (is_array($path_value) && isset($path_value[$path_part])) {
                                $path_value = &$path_value[$path_part];
                            } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                                $path_value = &$path_value->$path_part;
                            } else {
                                $found = false;
                                break;
                            }
                        }
                        if ($found) {
                            $post_meta = $path_value;
                        } else {
                            $post_meta = '';
                        }
                    }

                    if ($attachment_url || $attachment_image_url) {
                        if (!is_array($post_meta)) {
                            if (strpos($post_meta, ',') !== false) {
                                $post_meta = explode(',', $post_meta);
                                if ($implode === false) {
                                    $implode = ',';
                                }
                            }
                        }

                        if (is_array($post_meta)) {
                            $attachments = array();
                            foreach ($post_meta as $post_meta_part) {
                                if (!is_array($post_meta_part)) {
                                    if ($attachment_url) {
                                        $image = wp_get_attachment_url($post_meta_part);
                                    } elseif ($attachment_image_url) {
                                        $image = wp_get_attachment_image_url($post_meta_part, $size);
                                    }
                                    if ($image) {
                                        $attachments[] = $image;
                                    }
                                }
                            }
                            $post_meta = $attachments;
                        } else {
                            if ($attachment_url) {
                                $image = wp_get_attachment_url($post_meta);
                            } elseif ($attachment_image_url) {
                                $image = wp_get_attachment_image_url($post_meta, $size);
                            }
                            if ($image) {
                                $post_meta = $image;
                            } else {
                                $post_meta = '';
                            }
                        }
                    }

                    if ($raw) {
                        $response = $post_meta;
                    } else {
                        if (is_array($post_meta)) {
                            if ($implode !== false) {
                                if (!$this->helper->is_multidimensional($post_meta)) {
                                    $response = implode($implode, $post_meta);
                                } else {
                                    $response = serialize($post_meta);
                                }
                            } else {
                                $response = serialize($post_meta);
                            }
                        } elseif (is_object($post_meta)) {
                            $response = serialize($post_meta);
                        } else {
                            $response = $post_meta;
                        }
                    }
                }
            }
        }

        return $response;
    }

    /**
     * [e2pdf-wp-posts] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_wp_posts($atts = array(), $value = '') {

        $post_meta = false;
        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_posts_atts', $atts);

        $path = isset($atts['path']) ? $atts['path'] : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : false;
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $size = isset($atts['size']) ? $atts['size'] : 'thumbnail';
        $raw = isset($atts['raw']) && $atts['raw'] == 'true' ? true : false;

        $args = array();

        if (isset($atts['fields'])) {
            $args['fields'] = $atts['fields'];
        }

        if (isset($atts['numberposts'])) {
            $args['numberposts'] = $atts['numberposts'];
        }

        if (isset($atts['posts_per_page'])) {
            $args['posts_per_page'] = $atts['posts_per_page'];
        }

        if (isset($atts['offset'])) {
            $args['offset'] = $atts['offset'];
        }

        if (isset($atts['category'])) {
            $category = $atts['category'] == 'dynamic' ? $value : $atts['category'];
            $args['category'] = $category;
        }

        if (isset($atts['category_name'])) {
            $args['category_name'] = $atts['category_name'];
        }

        if (isset($atts['tag'])) {
            $args['tag'] = $atts['tag'];
        }

        if (isset($atts['include'])) {
            $args['include'] = explode(',', $atts['include']);
        }

        if (isset($atts['exclude'])) {
            $args['exclude'] = explode(',', $atts['exclude']);
        }

        if (isset($atts['meta_key'])) {
            $args['meta_key'] = $atts['meta_key'];
        }

        if (isset($atts['meta_value'])) {
            $args['meta_value'] = $atts['meta_value'];
        }

        if (isset($atts['post_type'])) {
            $args['post_type'] = explode(',', $atts['post_type']);
        }

        if (isset($atts['post_mime_type'])) {
            $args['post_mime_type'] = explode(',', $atts['post_mime_type']);
        }

        if (isset($atts['post_status'])) {
            $args['post_status'] = $atts['post_status'];
        }

        if (isset($atts['post_parent'])) {
            $args['post_parent'] = $atts['post_parent'];
        }


        if (isset($atts['nopaging'])) {
            $args['nopaging'] = $atts['nopaging'] === 'true' ? true : false;
        }

        if (isset($atts['orderby'])) {
            $args['orderby'] = $atts['orderby'];
        }

        if (isset($atts['order'])) {
            $args['order'] = $atts['order'];
        }

        if (isset($atts['suppress_filters'])) {
            $args['suppress_filters'] = $args['suppress_filters'] === 'true' ? true : false;
        }

        $post_meta = get_posts(
                apply_filters('e2pdf_model_shortcode_e2pdf_posts_args', $args, $atts, $value)
        );

        if ($post_meta !== false) {

            if (is_object($post_meta)) {
                $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_posts_object', $post_meta, $atts);
            }

            if ($explode && !is_array($post_meta)) {
                $post_meta = explode($explode, $post_meta);
            }

            if (is_array($post_meta)) {
                $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_posts_array', $post_meta, $atts);
            }

            if ((is_array($post_meta) || is_object($post_meta)) && $path !== false) {
                $path_parts = explode('.', $path);
                $path_value = &$post_meta;
                $found = true;
                foreach ($path_parts as $path_part) {
                    if (is_array($path_value) && isset($path_value[$path_part])) {
                        $path_value = &$path_value[$path_part];
                    } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                        $path_value = &$path_value->$path_part;
                    } else {
                        $found = false;
                        break;
                    }
                }
                if ($found) {
                    $post_meta = $path_value;
                } else {
                    $post_meta = '';
                }
            }

            if ($attachment_url || $attachment_image_url) {
                if (!is_array($post_meta)) {
                    if (strpos($post_meta, ',') !== false) {
                        $post_meta = explode(',', $post_meta);
                        if ($implode === false) {
                            $implode = ',';
                        }
                    }
                }

                if (is_array($post_meta)) {
                    $attachments = array();
                    foreach ($post_meta as $post_meta_part) {
                        if (!is_array($post_meta_part)) {
                            if ($attachment_url) {
                                $image = wp_get_attachment_url($post_meta_part);
                            } elseif ($attachment_image_url) {
                                $image = wp_get_attachment_image_url($post_meta_part, $size);
                            }
                            if ($image) {
                                $attachments[] = $image;
                            }
                        }
                    }
                    $post_meta = $attachments;
                } else {
                    if ($attachment_url) {
                        $image = wp_get_attachment_url($post_meta);
                    } elseif ($attachment_image_url) {
                        $image = wp_get_attachment_image_url($post_meta, $size);
                    }
                    if ($image) {
                        $post_meta = $image;
                    } else {
                        $post_meta = '';
                    }
                }
            }

            if ($raw) {
                $response = $post_meta;
            } else {
                if (is_array($post_meta)) {
                    if ($implode !== false) {
                        if (!$this->helper->is_multidimensional($post_meta)) {
                            $response = implode($implode, $post_meta);
                        } else {
                            $response = serialize($post_meta);
                        }
                    } else {
                        $response = serialize($post_meta);
                    }
                } elseif (is_object($post_meta)) {
                    $response = serialize($post_meta);
                } else {
                    $response = $post_meta;
                }
            }
        }

        if ($raw) {
            return apply_filters('e2pdf_model_shortcode_e2pdf_posts_raw', $response, $atts, $value);
        } else {
            return apply_filters('e2pdf_model_shortcode_e2pdf_posts_response', $response, $atts, $value);
        }
    }

    /**
     * [e2pdf-wc-product] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_wc_product($atts = array(), $value = '') {

        $post_meta = false;
        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_atts', $atts);

        $id = isset($atts['id']) ? $atts['id'] : false;
        $index = isset($atts['index']) ? $atts['index'] : false;
        $key = isset($atts['key']) ? $atts['key'] : false;
        $path = isset($atts['path']) ? $atts['path'] : false;
        $names = isset($atts['names']) && $atts['names'] == 'true' ? true : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : false;
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $size = isset($atts['size']) ? $atts['size'] : 'thumbnail';
        $meta = isset($atts['meta']) && $atts['meta'] == 'true' ? true : false;
        $output = isset($atts['output']) ? $atts['output'] : false;

        $terms = isset($atts['terms']) && $atts['terms'] == 'true' ? true : false;
        $parent = isset($atts['parent']) && $atts['parent'] == 'true' ? true : false;
        $wc_order_id = isset($atts['wc_order_id']) ? $atts['wc_order_id'] : false;
        $wc_product_item_id = isset($atts['wc_product_item_id']) ? $atts['wc_product_item_id'] : false;
        $wc_price = isset($atts['wc_price']) && $atts['wc_price'] == 'true' ? true : false;
        $download_index = isset($atts['download_index']) ? $atts['download_index'] : false;
        $raw = isset($atts['raw']) && $atts['raw'] == 'true' ? true : false;

        $attribute = isset($atts['attribute']) ? $atts['attribute'] : false;
        $order = isset($atts['order']) && $atts['order'] == 'true' ? true : false;
        $order_item_meta = isset($atts['order_item_meta']) && $atts['order_item_meta'] == 'true' ? true : false;
        $wc_filter = isset($atts['wc_filter']) && $atts['wc_filter'] == 'true' ? true : false;

        //backward compatibility 1.14.07
        if ($attribute === 'true' && $key != 'get_attribute') {
            $attribute = $key;
            $key = 'get_attribute';
        }

        $data_fields = apply_filters('e2pdf_model_shortcode_wc_product_data_fields', array(
            'id',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_title',
            'post_excerpt',
            'post_status',
            'permalink',
            'post_permalink',
            'get_post_permalink',
            'comment_status',
            'ping_status',
            'post_password',
            'post_name',
            'to_ping',
            'pinged',
            'post_modified',
            'post_modified_gmt',
            'post_content_filtered',
            'post_parent',
            'guid',
            'menu_order',
            'post_type',
            'post_mime_type',
            'comment_count',
            'filter',
            'post_thumbnail',
            'get_the_post_thumbnail',
            'get_the_post_thumbnail_url',
            'response_hook'
        ));

        $product_fields = apply_filters('e2pdf_model_shortcode_wc_product_product_fields', array(
            'get_name',
            'get_type',
            'get_slug',
            'get_date_created',
            'get_date_modified',
            'get_status',
            'get_featured',
            'get_catalog_visibility',
            'get_description',
            'get_short_description',
            'get_sku',
            'get_price',
            'get_regular_price',
            'get_sale_price',
            'get_date_on_sale_from',
            'get_date_on_sale_to',
            'get_total_sales',
            'get_tax_status',
            'get_tax_class',
            'get_manage_stock',
            'get_stock_quantity',
            'get_stock_status',
            'get_backorders',
            'get_low_stock_amount',
            'get_sold_individually',
            'get_weight',
            'get_length',
            'get_width',
            'get_height',
            'get_dimensions',
            'get_upsell_ids',
            'get_cross_sell_ids',
            'get_parent_id',
            'get_reviews_allowed',
            'get_purchase_note',
            'get_attributes',
            'get_variation_attributes',
            'get_default_attributes',
            'get_menu_order',
            'get_post_password',
            'get_category_ids',
            'get_tag_ids',
            'get_virtual',
            'get_gallery_image_ids',
            'get_shipping_class_id',
            'get_downloads',
            'get_download_expiry',
            'get_downloadable',
            'get_download_limit',
            'get_image_id',
            'get_rating_counts',
            'get_average_rating',
            'get_review_count',
            'get_title',
            'get_permalink',
            'get_children',
            'get_stock_managed_by_id',
            'get_price_html',
            'get_formatted_name',
            'get_min_purchase_quantity',
            'get_max_purchase_quantity',
            'get_image',
            'get_shipping_class',
            'get_attribute',
            'get_variation_attribute',
            'get_rating_count',
            'get_file',
            'get_file_download_path',
            'get_price_suffix',
            'get_availability',
        ));

        $product_item_fields = apply_filters('e2pdf_model_shortcode_wc_product_item_fields', array(
            'get_order_id',
            'get_name',
            'get_type',
            'get_quantity',
            'get_image',
            'get_tax_status',
            'get_tax_class',
            'get_formatted_meta_data',
            'get_formatted_cart_item_data', // only cart
            'get_product_id',
            'get_variation_id',
            'get_subtotal',
            'get_subtotal_tax',
            'get_total',
            'get_total_tax',
            'get_taxes',
            'get_item_download_url',
            'get_item_downloads',
            'get_tax_status',
            'get_product_price',
            'item_response_hook',
            'item_cart_response_hook',
            'cart_response_hook',
        ));

        $product_order_fields = apply_filters('e2pdf_model_shortcode_wc_product_order_item_fields', array(
            'get_item_subtotal',
            'get_item_total',
            'get_item_tax',
            'get_line_total',
            'get_line_tax',
            'get_formatted_line_subtotal',
            'order_response_hook',
        ));

        $wc_product = false;
        $wc_order = false;
        $wc_order_items = array();
        $wc_order_product = false;
        $wc_order_item_id = false;
        $wc_order_item = false;
        $wc_order_item_index = 0;

        if ($wc_order_id && ($index !== false || $id !== false)) {
            if ($wc_order_id == 'cart') {
                if (function_exists('WC') && isset(WC()->cart) && WC()->cart && is_object(WC()->cart)) {
                    WC()->cart->calculate_totals();
                    $wc_order = WC()->cart;
                    $wc_order_items = WC()->cart->get_cart();
                }
            } else {
                $wc_order = wc_get_order($wc_order_id);
                if ($wc_order) {
                    $wc_order_items = $wc_order->get_items();
                }
            }

            foreach ($wc_order_items as $item_id => $item) {
                if ($wc_order_id == 'cart') {
                    $wc_product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $item_id);
                    if ($item['variation_id']) {
                        $product_id = $item['variation_id'];
                    } else {
                        $product_id = apply_filters('woocommerce_cart_item_product_id', $item['product_id'], $item, $item_id);
                    }
                } else {
                    $wc_product = $item->get_product();
                    if ($item->get_variation_id()) {
                        $product_id = $item->get_variation_id();
                    } else {
                        $product_id = $item->get_product_id();
                    }
                }

                if ($wc_order_id != 'cart' || ($wc_product && $wc_product->exists() && $item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $item, $item_id))) {
                    if (($index !== false && $wc_order_item_index == $index) || ($wc_product_item_id === false && $id && $product_id == $id) || ($wc_product_item_id && $id && $product_id == $id && $wc_product_item_id == $item_id)) {
                        $id = $product_id;
                        $wc_order_product = $wc_product;
                        $wc_order_item_id = $item_id;
                        $wc_order_item = $item;
                        break;
                    }
                    $wc_order_item_index++;
                }
            }
        }

        if ($key) {
            if ($id) {
                $wp_post = get_post($id);
                if (!$wc_order_id) {
                    $wc_product = wc_get_product($id);
                }
                if ($parent && $wp_post && get_post_type($id) == 'product_variation') {
                    $variation = wc_get_product($id);
                    if ($variation) {
                        $id = $variation->get_parent_id();
                        $wp_post = get_post($variation->get_parent_id());
                        if (!$wc_order_id) {
                            $wc_product = wc_get_product($id);
                        }
                    } else {
                        $wp_post = false;
                    }
                }
            } else {
                $wp_post = false;
            }

            if (in_array($key, $data_fields) && !$meta && !$terms && !$order_item_meta && !$order) {
                if ($wp_post) {
                    if ($key == 'post_author') {
                        $post_meta = isset($wp_post->post_author) && $wp_post->post_author ? get_userdata($wp_post->post_author)->user_nicename : '';
                    } elseif ($key == 'id' && isset($wp_post->ID)) {
                        $post_meta = $wp_post->ID;
                    } elseif (($key == 'post_thumbnail' || $key == 'get_the_post_thumbnail_url') && isset($wp_post->ID)) {
                        $post_meta = get_the_post_thumbnail_url($wp_post->ID, $size);
                    } elseif ($key == 'get_the_post_thumbnail' && isset($wp_post->ID)) {
                        $post_meta = get_the_post_thumbnail($wp_post->ID, $size);
                    } elseif ($key == 'post_content' && isset($wp_post->post_content)) {
                        $content = $wp_post->post_content;

                        if (false !== strpos($content, '[')) {
                            $shortcode_tags = array(
                                'e2pdf-exclude',
                                'e2pdf-save'
                            );
                            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
                            $tagnames = array_intersect($shortcode_tags, $matches[1]);
                            if (!empty($tagnames)) {
                                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);
                                preg_match_all("/$pattern/", $content, $shortcodes);
                                foreach ($shortcodes[0] as $key => $shortcode_value) {
                                    $content = str_replace($shortcode_value, '', $content);
                                }
                            }
                        }

                        if ($output) {
                            global $post;
                            $tmp_post = $post;
                            $post = $wp_post;
                            if ($output == 'backend') {
                                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                                    \Elementor\Plugin::instance()->frontend->remove_content_filter();
                                }
                            } elseif ($output == 'frontend') {
                                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                                    \Elementor\Plugin::instance()->frontend->add_content_filter();
                                }
                            }
                        }

                        if (defined('ET_BUILDER_DIR') && 'on' === get_post_meta($id, '_et_pb_use_builder', true) && function_exists('et_builder_init_global_settings') && function_exists('et_builder_add_main_elements')) {
                            require_once(ET_BUILDER_DIR . 'class-et-builder-element.php');
                            require_once(ET_BUILDER_DIR . 'functions.php');
                            require_once(ET_BUILDER_DIR . 'ab-testing.php');
                            require_once(ET_BUILDER_DIR . 'class-et-global-settings.php');
                            et_builder_add_main_elements();
                        }

                        $content = apply_filters('the_content', $content);
                        $content = str_replace("</p>", "</p>\r\n", $content);
                        $post_meta = $content;

                        if ($output) {
                            $post = $tmp_post;
                        }
                    } elseif ($key == 'permalink') {
                        $leavename = isset($atts['leavename']) && $atts['leavename'] == 'true' ? true : false;
                        $post_meta = get_permalink($id, $leavename);
                    } elseif ($key == 'get_post_permalink' || $key == 'post_permalink') {
                        $leavename = isset($atts['leavename']) && $atts['leavename'] == 'true' ? true : false;
                        $post_meta = get_post_permalink($id, $leavename);
                    } elseif ($key == 'response_hook') {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_response_hook', '', $id, $atts, $wp_post);
                    } elseif (isset($wp_post->$key)) {
                        $post_meta = $wp_post->$key;
                    }
                }
            } elseif (in_array($key, $product_fields) && !$meta && !$terms && !$order_item_meta && !$order) {
                if ($wc_product && is_object($wc_product) && (method_exists($wc_product, $key) || $key == 'get_variation_attribute')) {
                    if ($key == 'get_attributes' || $key == 'get_variation_attributes') {
                        $wc_parent_product = false;
                        $parent_attributes = array();
                        $child_attributes = array();
                        if ($key == 'get_variation_attributes') {
                            $post_meta = $wc_product->get_attributes($wc_product);
                        } else {
                            if ($wc_product->is_type('variation')) {
                                $wc_parent_product = wc_get_product($wc_product->get_parent_id());
                            }
                            if ($wc_parent_product) {
                                if (isset($atts['hidden']) && $atts['hidden'] == 'true') {
                                    $parent_attributes = $wc_parent_product->get_attributes();
                                } else {
                                    $parent_attributes = array_filter($wc_parent_product->get_attributes(), 'wc_attributes_array_filter_visible');
                                }
                                $child_attributes = $wc_product->get_attributes($wc_product);
                                $post_meta = array_merge($parent_attributes, $child_attributes);
                            } else {
                                if (isset($atts['hidden']) && $atts['hidden'] == 'true') {
                                    $post_meta = $wc_product->get_attributes();
                                } else {
                                    $post_meta = array_filter($wc_product->get_attributes(), 'wc_attributes_array_filter_visible');
                                }
                            }
                        }

                        if (is_array($post_meta) && !empty($post_meta)) {
                            $exclude = array();
                            if (isset($atts['exclude'])) {
                                $exclude = explode(',', $atts['exclude']);
                            }
                            foreach ($exclude as $excluded) {
                                if (array_key_exists($excluded, $post_meta)) {
                                    unset($post_meta[$excluded]);
                                }
                            }
                        }

                        if ($wc_filter) {
                            foreach ($post_meta as $attribute_key => $attribute) {
                                if ($attribute && is_a($attribute, 'WC_Product_Attribute')) {
                                    $values = array();
                                    if ($attribute->is_taxonomy()) {
                                        $attribute_taxonomy = $attribute->get_taxonomy_object();
                                        if ($wc_parent_product) {
                                            $attribute_values = wc_get_product_terms($wc_parent_product->get_id(), $attribute->get_name(), array('fields' => 'all'));
                                        } else {
                                            $attribute_values = wc_get_product_terms($wc_product->get_id(), $attribute->get_name(), array('fields' => 'all'));
                                        }
                                        foreach ($attribute_values as $attribute_value) {
                                            $value_name = esc_html($attribute_value->name);
                                            if ($attribute_taxonomy->attribute_public) {
                                                $values[] = '<a href="' . esc_url(get_term_link($attribute_value->term_id, $attribute->get_name())) . '" rel="tag">' . $value_name . '</a>';
                                            } else {
                                                $values[] = $value_name;
                                            }
                                        }
                                    } else {
                                        $values = $attribute->get_options();
                                        foreach ($values as &$value) {
                                            $value = make_clickable(esc_html($value));
                                        }
                                    }

                                    $product_attributes['attribute_' . sanitize_title_with_dashes($attribute->get_name())] = array(
                                        'label' => wc_attribute_label($attribute->get_name()),
                                        'value' => apply_filters('woocommerce_attribute', wpautop(wptexturize(implode(', ', $values))), $attribute, $values),
                                    );
                                } else {
                                    $attribute_value = $wc_product->get_attribute($attribute_key);
                                    $attribute_value = implode(', ', array_map('trim', explode('|', $attribute_value)));
                                    $attribute_value = apply_filters('e2pdf_model_shortcode_wc_product_get_attribute_value', $attribute_value, $wc_product);
                                    $product_attributes['attribute_' . $attribute_key] = array(
                                        'label' => wc_attribute_label($attribute_key, $wc_product),
                                        'value' => $attribute_value,
                                    );
                                }
                            }

                            if ($key != 'get_variation_attributes') {
                                if ($wc_parent_product) {
                                    $product_attributes = apply_filters('woocommerce_display_product_attributes', $product_attributes, $wc_parent_product);
                                } else {
                                    $product_attributes = apply_filters('woocommerce_display_product_attributes', $product_attributes, $wc_product);
                                }
                            }
                            $post_meta = $product_attributes;
                        }
                    } else if ($key == 'get_attribute' || $key == 'get_variation_attribute') {
                        if ($attribute) {

                            $wc_parent_product = false;
                            $parent_attributes = array();
                            $child_attributes = array();

                            if ($key == 'get_variation_attribute') {
                                $attributes = $wc_product->get_attributes($wc_product);
                            } else {
                                if ($wc_product->is_type('variation')) {
                                    $wc_parent_product = wc_get_product($wc_product->get_parent_id());
                                }
                                if ($wc_parent_product) {
                                    $parent_attributes = $wc_parent_product->get_attributes();
                                    $child_attributes = $wc_product->get_attributes($wc_product);
                                    $attributes = array_merge($parent_attributes, $child_attributes);
                                } else {
                                    $attributes = $wc_product->get_attributes();
                                }
                            }

                            if (isset($atts['show']) && $atts['show'] == 'label') {
                                if (isset($attributes[$attribute]) || isset($attributes['pa_' . $attribute])) {
                                    $wc_attribute = isset($attributes[$attribute]) ? $attributes[$attribute] : $attributes['pa_' . $attribute];
                                    if ($wc_attribute && is_a($wc_attribute, 'WC_Product_Attribute')) {
                                        $post_meta = wc_attribute_label($wc_attribute->get_name());
                                    } else {
                                        $post_meta = wc_attribute_label($attribute, $wc_product);
                                    }
                                }
                            } elseif (isset($atts['show']) && $atts['show'] == 'value') {
                                if ($wc_filter) {
                                    if (isset($attributes[$attribute]) || isset($attributes['pa_' . $attribute])) {
                                        $wc_attribute = isset($attributes[$attribute]) ? $attributes[$attribute] : $attributes['pa_' . $attribute];
                                        if ($wc_attribute && is_a($wc_attribute, 'WC_Product_Attribute')) {
                                            if ($wc_attribute->is_taxonomy()) {
                                                $attribute_taxonomy = $wc_attribute->get_taxonomy_object();
                                                if ($wc_parent_product) {
                                                    $attribute_values = wc_get_product_terms($wc_parent_product->get_id(), $wc_attribute->get_name(), array('fields' => 'all'));
                                                } else {
                                                    $attribute_values = wc_get_product_terms($wc_product->get_id(), $wc_attribute->get_name(), array('fields' => 'all'));
                                                }
                                                foreach ($attribute_values as $attribute_value) {
                                                    $value_name = esc_html($attribute_value->name);
                                                    if ($attribute_taxonomy->attribute_public) {
                                                        $values[] = '<a href="' . esc_url(get_term_link($attribute_value->term_id, $wc_attribute->get_name())) . '" rel="tag">' . $value_name . '</a>';
                                                    } else {
                                                        $values[] = $value_name;
                                                    }
                                                }
                                            } else {
                                                $values = $wc_attribute->get_options();
                                                foreach ($values as &$value) {
                                                    $value = make_clickable(esc_html($value));
                                                }
                                            }
                                            $post_meta = apply_filters('woocommerce_attribute', wpautop(wptexturize(implode(', ', $values))), $wc_attribute, $values);
                                            $post_meta = apply_filters('e2pdf_model_shortcode_wc_product_get_attribute_value', $post_meta, $wc_product);
                                        } else {
                                            if ($wc_parent_product) {
                                                if (isset($child_attributes[$attribute]) || isset($child_attributes['pa_' . $attribute])) {
                                                    $post_meta = $wc_product->get_attribute($attribute);
                                                    $post_meta = implode(', ', array_map('trim', explode('|', $post_meta)));
                                                    $post_meta = apply_filters('e2pdf_model_shortcode_wc_product_get_attribute_value', $post_meta, $wc_product);
                                                } else {
                                                    $post_meta = $wc_parent_product->get_attribute($attribute);
                                                    $post_meta = implode(', ', array_map('trim', explode('|', $post_meta)));
                                                    $post_meta = apply_filters('e2pdf_model_shortcode_wc_product_get_attribute_value', $post_meta, $wc_parent_product);
                                                }
                                            } else {
                                                $post_meta = $wc_product->get_attribute($attribute);
                                                $post_meta = implode(', ', array_map('trim', explode('|', $post_meta)));
                                                $post_meta = apply_filters('e2pdf_model_shortcode_wc_product_get_attribute_value', $post_meta, $wc_product);
                                            }
                                        }
                                    }
                                } else {
                                    if ($wc_parent_product) {
                                        if (isset($child_attributes[$attribute]) || isset($child_attributes['pa_' . $attribute])) {
                                            $post_meta = $wc_product->get_attribute($attribute);
                                            $post_meta = implode(', ', array_map('trim', explode('|', $post_meta)));
                                            $post_meta = apply_filters('e2pdf_model_shortcode_wc_product_get_attribute_value', $post_meta, $wc_product);
                                        } else {
                                            $post_meta = $wc_parent_product->get_attribute($attribute);
                                            $post_meta = implode(', ', array_map('trim', explode('|', $post_meta)));
                                            $post_meta = apply_filters('e2pdf_model_shortcode_wc_product_get_attribute_value', $post_meta, $wc_parent_product);
                                        }
                                    } else {
                                        $post_meta = $wc_product->get_attribute($attribute);
                                        $post_meta = implode(', ', array_map('trim', explode('|', $post_meta)));
                                        $post_meta = apply_filters('e2pdf_model_shortcode_wc_product_get_attribute_value', $post_meta, $wc_product);
                                    }
                                }
                            } else {
                                if ($wc_parent_product) {
                                    if (isset($child_attributes[$attribute]) || isset($child_attributes['pa_' . $attribute])) {
                                        $post_meta = $wc_product->get_attribute($attribute);
                                    } else {
                                        $post_meta = $wc_parent_product->get_attribute($attribute);
                                    }
                                } else {
                                    $post_meta = $wc_product->get_attribute($attribute);
                                }
                            }
                        }
                    } elseif ($key == 'get_short_description' || $key == 'get_description') {
                        $content = $wc_product->$key();

                        if (false !== strpos($content, '[')) {
                            $shortcode_tags = array(
                                'e2pdf-exclude',
                                'e2pdf-save'
                            );
                            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
                            $tagnames = array_intersect($shortcode_tags, $matches[1]);
                            if (!empty($tagnames)) {
                                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);
                                preg_match_all("/$pattern/", $content, $shortcodes);
                                foreach ($shortcodes[0] as $key => $shortcode_value) {
                                    $content = str_replace($shortcode_value, '', $content);
                                }
                            }
                        }

                        if (isset($atts['wc_format_content']) && $atts['wc_format_content'] == 'true') {
                            $content = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_description', $content, $id);
                            $content = wc_format_content($content);
                        }

                        $post_meta = $content;
                    } elseif ($key == 'get_file_download_path') {
                        if ($download_index !== false) {
                            $downloads = $wc_product->get_downloads();
                            $download_item_index = 0;
                            foreach ($downloads as $download) {
                                if ($download_item_index == $download_index) {
                                    $post_meta = $wc_product->$key($download['id']);
                                    break;
                                }
                            }
                        }
                    } elseif ($key == 'get_image') {
                        $size = isset($atts['size']) ? $atts['size'] : 'woocommerce_thumbnail';
                        if (false !== strpos($size, 'x')) {
                            $image_size = explode('x', $size);
                            if (isset($image_size['0']) && isset($image_size['1'])) {
                                $image_width = absint($image_size['0']);
                                $image_height = absint($image_size['1']);
                                if ($image_width && $image_height) {
                                    $size = array(
                                        $image_width, $image_height
                                    );
                                }
                            }
                        }
                        $post_meta = $wc_product->$key($size);
                    } elseif ($key == 'get_file') {
                        if ($download_index !== false) {
                            $downloads = $wc_product->get_downloads();
                            $download_item_index = 0;
                            foreach ($downloads as $download) {
                                if ($download_item_index == $download_index) {
                                    $post_meta = $wc_product->$key($download['id']);
                                    break;
                                }
                            }
                        } else {
                            $post_meta = $wc_product->$key();
                        }
                    } else {
                        $post_meta = $wc_product->$key();
                    }
                }
            } elseif (in_array($key, $product_item_fields) && !$meta && !$terms && !$order_item_meta) {
                if ($wc_order_id == 'cart' && $wc_order_item && is_array($wc_order_item)) {
                    if ($key == 'item_cart_response_hook') {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_item_cart_response_hook', '', $id, $atts, $wc_order_product, $wc_order_item, $wc_order_item_id, $wc_order_item_index);
                    } elseif ($key == 'cart_response_hook') {
                        // backward compatibility fix 1.16.09
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_cart_response_hook', '', $id, $atts, $wc_order_product, $wc_order_item, $wc_order_item_id, $wc_order_item_index);
                    } elseif ($key == 'get_name') {
                        if ($wc_filter) {
                            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $wc_product->is_visible() ? $wc_product->get_permalink($wc_order_item) : '', $wc_order_item, $wc_order_item_id);
                            if (!$product_permalink) {
                                $post_meta = wp_kses_post(apply_filters('woocommerce_cart_item_name', $wc_product->get_name(), $wc_order_item, $wc_order_item_id) . '&nbsp;');
                            } else {
                                $post_meta = wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a target="_blank" href="%s">%s</a>', esc_url($product_permalink), $wc_product->get_name()), $wc_order_item, $wc_order_item_id));
                            }
                        } else {
                            $post_meta = $wc_product->$key($attribute);
                        }
                    } elseif ($key == 'get_image') {
                        if (false !== strpos($size, 'x')) {
                            $image_size = explode('x', $size);
                            if (isset($image_size['0']) && isset($image_size['1'])) {
                                $image_width = absint($image_size['0']);
                                $image_height = absint($image_size['1']);
                                if ($image_width && $image_height) {
                                    $size = array(
                                        $image_width, $image_height
                                    );
                                }
                            }
                        }
                        if ($wc_filter) {
                            $post_meta = apply_filters('woocommerce_cart_item_thumbnail', $wc_product->get_image($size), $wc_order_item, $wc_order_item_id);
                        } else {
                            $post_meta = $wc_product->get_image($size);
                        }
                    } elseif ($key == 'get_quantity') {
                        $post_meta = isset($wc_order_item['quantity']) ? $wc_order_item['quantity'] : '0';
                    } elseif ($key == 'get_subtotal') {
                        if ($wc_filter) {
                            $post_meta = apply_filters('woocommerce_cart_item_subtotal', $wc_order->get_product_subtotal($wc_product, $wc_order_item['quantity']), $wc_order_item, $wc_order_item_id);
                        } else {
                            $post_meta = $wc_order->get_product_subtotal($wc_product, $wc_order_item['quantity']);
                        }
                    } elseif ($key == 'get_product_price') {
                        if ($wc_filter) {
                            $post_meta = apply_filters('woocommerce_cart_item_price', $wc_order->get_product_price($wc_product), $wc_order_item, $wc_order_item_id);
                        } else {
                            $post_meta = $wc_order->get_product_price($wc_product);
                        }
                    } elseif ($key == 'get_formatted_meta_data' || $key == 'get_formatted_cart_item_data') {
                        if ($wc_filter || $key == 'get_formatted_cart_item_data') {
                            if (isset($atts['flat']) && $atts['flat'] == 'true') {
                                $flat = true;
                            } else {
                                $flat = false;
                            }

                            if (isset($atts['nl2br']) && $atts['nl2br'] == 'true') {
                                $post_meta = nl2br(wc_get_formatted_cart_item_data($wc_order_item, $flat));
                            } else {
                                $post_meta = wc_get_formatted_cart_item_data($wc_order_item, $flat);
                            }
                        } else {
                            $item_data = array();
                            if ($wc_order_item['data']->is_type('variation') && is_array($wc_order_item['variation'])) {
                                foreach ($wc_order_item['variation'] as $name => $value) {
                                    $taxonomy = wc_attribute_taxonomy_name(str_replace('attribute_pa_', '', urldecode($name)));
                                    if (taxonomy_exists($taxonomy)) {
                                        $term = get_term_by('slug', $value, $taxonomy);
                                        if (!is_wp_error($term) && $term && $term->name) {
                                            $value = $term->name;
                                        }
                                        $label = wc_attribute_label($taxonomy);
                                    } else {
                                        $value = apply_filters('woocommerce_variation_option_name', $value, null, $taxonomy, $wc_order_item['data']);
                                        $label = wc_attribute_label(str_replace('attribute_', '', $name), $wc_order_item['data']);
                                    }

                                    if ('' === $value || wc_is_attribute_in_product_name($value, $wc_order_item['data']->get_name())) {
                                        continue;
                                    }

                                    $item_data[] = array(
                                        'key' => $label,
                                        'value' => $value,
                                    );
                                }
                            }

                            $item_data = apply_filters('woocommerce_get_item_data', $item_data, $wc_order_item);

                            foreach ($item_data as $key => $data) {
                                if (isset($atts['hidden']) && $atts['hidden'] == 'false') {
                                    if (!empty($data['hidden'])) {
                                        unset($item_data[$key]);
                                        continue;
                                    }
                                }
                                $item_data[$key]['display_key'] = !empty($data['key']) ? $data['key'] : $data['name'];
                                $item_data[$key]['display_value'] = !empty($data['display']) ? $data['display'] : $data['value'];
                            }

                            $post_meta = $item_data;
                        }
                    } else {
                        $post_meta = isset($wc_order_item[$key]) ? $wc_order_item[$key] : '';
                    }
                } elseif ($wc_order_item && is_object($wc_order_item)) {
                    if ($key == 'item_response_hook') {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_item_response_hook', '', $id, $atts, $wc_order_product, $wc_order_item, $wc_order_item_id, $wc_order_item_index);
                    } elseif (method_exists($wc_order_item, $key)) {
                        if ($key == 'get_item_download_url') {
                            if ($download_index !== false) {
                                $downloads = $wc_order_item->get_item_downloads();
                                $download_item_index = 0;
                                foreach ($downloads as $download) {
                                    if ($download_item_index == $download_index) {
                                        $post_meta = $wc_order_item->$key($download['id']);
                                        break;
                                    }
                                }
                            }
                        } elseif ($key == 'get_item_downloads' && $download_index !== false) {
                            $download_item_index = 0;
                            foreach ($downloads as $download) {
                                if ($download_item_index == $download_index) {
                                    $post_meta = $download;
                                    break;
                                }
                            }
                        } else {
                            $post_meta = $wc_order_item->$key();
                        }
                    }
                }
            } elseif (in_array($key, $product_order_fields) && !$meta && !$terms && !$order_item_meta) {
                if ($wc_order_item) {
                    if ($key == 'order_response_hook') {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_order_response_hook', '', $id, $atts, $wc_order_product, $wc_order_item, $wc_order_item_id, $wc_order_item_index);
                    } elseif ($wc_order && is_object($wc_order) && method_exists($wc_order, $key)) {
                        if ($key == 'get_item_subtotal' || $key == 'get_item_total' || $key == 'get_line_total') {
                            $inc_tax = isset($atts['inc_tax']) && $atts['inc_tax'] == 'true' ? true : false;
                            $round = isset($atts['round']) && $atts['round'] == 'false' ? false : true;
                            $post_meta = $wc_order->$key($wc_order_item, $inc_tax, $round);
                        } elseif ($key == 'get_item_tax') {
                            $round = isset($atts['round']) && $atts['round'] == 'false' ? false : true;
                            $post_meta = $wc_order->$key($wc_order_item, $round);
                        } elseif ($key == 'get_formatted_line_subtotal') {
                            $tax_display = isset($atts['tax_display']) && $atts['tax_display'] ? $atts['tax_display'] : '';
                            $post_meta = $wc_order->$key($wc_order_item, $tax_display);
                        } else {
                            $post_meta = $wc_order->$key($wc_order_item);
                        }
                    }
                }
            } elseif ($order_item_meta) {
                if ($wc_order_item_id) {
                    $post_meta = wc_get_order_item_meta($wc_order_item_id, $key, true);
                }
            } elseif ($terms && $names) {
                if ($wp_post) {
                    $post_terms = wp_get_post_terms($id, $key, array('fields' => 'names'));
                    if (!is_wp_error($post_terms) && is_array($post_terms)) {
                        if ($implode === false) {
                            $implode = ', ';
                        }
                        $post_meta = implode($implode, $post_terms);
                    }
                }
            } elseif ($terms) {
                if ($wp_post) {
                    $post_terms = wp_get_post_terms($id, $key);
                    if (!is_wp_error($post_terms)) {
                        $post_meta = json_decode(json_encode($post_terms), true);
                    }
                }
            } else {
                $post_meta = $wp_post ? get_post_meta($id, $key, true) : false;
                if ($post_meta === false && $wc_order_item) {
                    $post_meta = $wc_order_item->get_meta($key);
                }
            }

            if ($post_meta !== false) {

                if (is_object($post_meta)) {
                    $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_object', $post_meta, $atts);
                }

                if ($explode && !is_array($post_meta)) {
                    $post_meta = explode($explode, $post_meta);
                }

                if (is_array($post_meta)) {
                    $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_array', $post_meta, $atts);
                }

                if ((is_array($post_meta) || is_object($post_meta)) && $path !== false) {
                    $path_parts = explode('.', $path);
                    $path_value = &$post_meta;
                    $found = true;
                    foreach ($path_parts as $path_part) {
                        if (is_array($path_value) && isset($path_value[$path_part])) {
                            $path_value = &$path_value[$path_part];
                        } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                            $path_value = &$path_value->$path_part;
                        } else {
                            $found = false;
                            break;
                        }
                    }
                    if ($found) {
                        $post_meta = $path_value;
                    } else {
                        $post_meta = '';
                    }
                }

                if ($attachment_url || $attachment_image_url) {
                    if (!is_array($post_meta)) {
                        if (strpos($post_meta, ',') !== false) {
                            $post_meta = explode(',', $post_meta);
                            if ($implode === false) {
                                $implode = ',';
                            }
                        }
                    }

                    if (is_array($post_meta)) {
                        $attachments = array();
                        foreach ($post_meta as $post_meta_part) {
                            if (!is_array($post_meta_part)) {
                                if ($attachment_url) {
                                    $image = wp_get_attachment_url($post_meta_part);
                                } elseif ($attachment_image_url) {
                                    $image = wp_get_attachment_image_url($post_meta_part, $size);
                                }
                                if ($image) {
                                    $attachments[] = $image;
                                }
                            }
                        }
                        $post_meta = $attachments;
                    } else {
                        if ($attachment_url) {
                            $image = wp_get_attachment_url($post_meta);
                        } elseif ($attachment_image_url) {
                            $image = wp_get_attachment_image_url($post_meta, $size);
                        }
                        if ($image) {
                            $post_meta = $image;
                        } else {
                            $post_meta = '';
                        }
                    }
                }

                if ($wc_price) {
                    if (is_array($post_meta) || is_object($post_meta)) {
                        
                    } else {
                        if (isset($atts['currency'])) {
                            $post_meta = wc_price($post_meta, $atts['currency']);
                        } else {
                            if (!$wc_order && $wc_order_id) {
                                $wc_order = wc_get_order($wc_order_id);
                            }
                            if ($wc_order && is_object($wc_order) && method_exists($wc_order, 'get_currency')) {
                                $post_meta = wc_price($post_meta, $wc_order->get_currency());
                            } else {
                                $post_meta = wc_price($post_meta);
                            }
                        }
                    }
                }

                if ($raw) {
                    $response = $post_meta;
                } else {
                    if (is_array($post_meta)) {
                        if ($implode !== false) {
                            if (!$this->helper->is_multidimensional($post_meta)) {
                                $response = implode($implode, $post_meta);
                            } else {
                                $response = serialize($post_meta);
                            }
                        } else {
                            $response = serialize($post_meta);
                        }
                    } elseif (is_object($post_meta)) {
                        $response = serialize($post_meta);
                    } else {
                        $response = $post_meta;
                    }
                }
            }
        }

        if ($raw) {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_raw', $response, $atts, $value);
        } else {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wc_product_response', $response, $atts, $value);
        }
    }

    /**
     * [e2pdf-wc-order] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_wc_order($atts = array(), $value = '') {

        $post_meta = false;
        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_wc_order_atts', $atts);

        $id = isset($atts['id']) ? $atts['id'] : false;
        $key = isset($atts['key']) ? $atts['key'] : false;
        $subkey = isset($atts['subkey']) ? $atts['subkey'] : false;
        $index = isset($atts['index']) ? $atts['index'] : false;
        $path = isset($atts['path']) ? $atts['path'] : false;
        $names = isset($atts['names']) && $atts['names'] == 'true' ? true : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : false;
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $size = isset($atts['size']) ? $atts['size'] : 'thumbnail';
        $meta = isset($atts['meta']) && $atts['meta'] == 'true' ? true : false;
        $order_item_meta = isset($atts['order_item_meta']) && $atts['order_item_meta'] == 'true' ? true : false;
        $terms = isset($atts['terms']) && $atts['terms'] == 'true' ? true : false;
        $wc_price = isset($atts['wc_price']) && $atts['wc_price'] == 'true' ? true : false;
        $raw = isset($atts['raw']) && $atts['raw'] == 'true' ? true : false;
        $output = isset($atts['output']) ? $atts['output'] : false;

        $data_fields = apply_filters('e2pdf_model_shortcode_wc_order_data_fields', array(
            'id',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_title',
            'post_excerpt',
            'post_status',
            'permalink',
            'post_permalink',
            'get_post_permalink',
            'comment_status',
            'ping_status',
            'post_password',
            'post_name',
            'to_ping',
            'pinged',
            'post_modified',
            'post_modified_gmt',
            'post_content_filtered',
            'post_parent',
            'guid',
            'menu_order',
            'post_type',
            'post_mime_type',
            'comment_count',
            'filter',
            'post_thumbnail',
            'get_the_post_thumbnail',
            'get_the_post_thumbnail_url',
            'response_hook',
        ));

        $order_fields = apply_filters('e2pdf_model_shortcode_wc_order_order_fields', array(
            'cart',
            'get_id',
            'get_order_key',
            'get_order_number',
            'get_formatted_order_total',
            'get_cart_tax',
            'get_currency',
            'get_discount_tax',
            'get_discount_to_display',
            'get_discount_total',
            'get_shipping_tax',
            'get_shipping_total',
            'get_subtotal',
            'get_subtotal_to_display',
            'get_total',
            'get_total_discount',
            'get_total_tax',
            'get_total_refunded',
            'get_total_tax_refunded',
            'get_total_shipping_refunded',
            'get_item_count_refunded',
            'get_total_qty_refunded',
            'get_remaining_refund_amount',
            'get_item_count',
            'get_shipping_method',
            'get_shipping_to_display',
            'get_date_created',
            'get_date_modified',
            'get_date_completed',
            'get_date_paid',
            'get_customer_id',
            'get_user_id',
            'get_customer_ip_address',
            'get_customer_user_agent',
            'get_created_via',
            'get_customer_note',
            'get_billing_first_name',
            'get_billing_last_name',
            'get_billing_company',
            'get_billing_address_1',
            'get_billing_address_2',
            'get_billing_city',
            'get_billing_state',
            'get_billing_postcode',
            'get_billing_country',
            'get_billing_email',
            'get_billing_phone',
            'get_shipping_first_name',
            'get_shipping_last_name',
            'get_shipping_company',
            'get_shipping_address_1',
            'get_shipping_address_2',
            'get_shipping_city',
            'get_shipping_state',
            'get_shipping_postcode',
            'get_shipping_country',
            'get_shipping_address_map_url',
            'get_formatted_billing_full_name',
            'get_formatted_shipping_full_name',
            'get_formatted_billing_address',
            'get_formatted_shipping_address',
            'get_payment_method',
            'get_payment_method_title',
            'get_transaction_id',
            'get_checkout_payment_url',
            'get_checkout_order_received_url',
            'get_cancel_order_url',
            'get_cancel_order_url_raw',
            'get_cancel_endpoint',
            'get_view_order_url',
            'get_edit_order_url',
            'get_status',
            'get_coupons',
            'get_fees',
            'get_taxes',
            'get_shipping_methods',
            'get_coupon_codes',
            'get_items_tax_classes',
            'get_total_fees',
            'get_order_item_totals',
            'get_tax_totals',
            'get_items',
        ));

        if ($id && $key) {
            $wp_post = get_post($id);
            if ($wp_post) {
                if (in_array($key, $data_fields) && !$meta && !$order_item_meta && !$terms) {
                    if ($key == 'post_author') {
                        $post_meta = isset($wp_post->post_author) && $wp_post->post_author ? get_userdata($wp_post->post_author)->user_nicename : '';
                    } elseif ($key == 'id' && isset($wp_post->ID)) {
                        $post_meta = $wp_post->ID;
                    } elseif (($key == 'post_thumbnail' || $key == 'get_the_post_thumbnail_url') && isset($wp_post->ID)) {
                        $post_meta = get_the_post_thumbnail_url($wp_post->ID, $size);
                    } elseif ($key == 'get_the_post_thumbnail' && isset($wp_post->ID)) {
                        $post_meta = get_the_post_thumbnail($wp_post->ID, $size);
                    } elseif ($key == 'post_content' && isset($wp_post->post_content)) {
                        $content = $wp_post->post_content;

                        if (false !== strpos($content, '[')) {
                            $shortcode_tags = array(
                                'e2pdf-exclude',
                                'e2pdf-save'
                            );
                            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
                            $tagnames = array_intersect($shortcode_tags, $matches[1]);
                            if (!empty($tagnames)) {
                                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);
                                preg_match_all("/$pattern/", $content, $shortcodes);
                                foreach ($shortcodes[0] as $key => $shortcode_value) {
                                    $content = str_replace($shortcode_value, '', $content);
                                }
                            }
                        }

                        if ($output) {
                            global $post;
                            $tmp_post = $post;
                            $post = $wp_post;
                            if ($output == 'backend') {
                                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                                    \Elementor\Plugin::instance()->frontend->remove_content_filter();
                                }
                            } elseif ($output == 'frontend') {
                                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                                    \Elementor\Plugin::instance()->frontend->add_content_filter();
                                }
                            }
                        }

                        if (defined('ET_BUILDER_DIR') && 'on' === get_post_meta($id, '_et_pb_use_builder', true) && function_exists('et_builder_init_global_settings') && function_exists('et_builder_add_main_elements')) {
                            require_once(ET_BUILDER_DIR . 'class-et-builder-element.php');
                            require_once(ET_BUILDER_DIR . 'functions.php');
                            require_once(ET_BUILDER_DIR . 'ab-testing.php');
                            require_once(ET_BUILDER_DIR . 'class-et-global-settings.php');
                            et_builder_add_main_elements();
                        }

                        $content = apply_filters('the_content', $content);
                        $content = str_replace("</p>", "</p>\r\n", $content);
                        $post_meta = $content;

                        if ($output) {
                            $post = $tmp_post;
                        }
                    } elseif ($key == 'permalink') {
                        $leavename = isset($atts['leavename']) && $atts['leavename'] == 'true' ? true : false;
                        $post_meta = get_permalink($id, $leavename);
                    } elseif ($key == 'get_post_permalink' || $key == 'post_permalink') {
                        $leavename = isset($atts['leavename']) && $atts['leavename'] == 'true' ? true : false;
                        $post_meta = get_post_permalink($id, $leavename);
                    } elseif ($key == 'response_hook') {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_order_response_hook', '', $id, $atts, $wp_post);
                    } elseif (isset($wp_post->$key)) {
                        $post_meta = $wp_post->$key;
                    }
                } elseif (in_array($key, $order_fields) && !$meta && !$order_item_meta && !$terms) {
                    $order = wc_get_order($id);
                    if ($order) {
                        if ($key == 'cart') {
                            $items = $order->get_items();
                            $content = '';
                            if ($items) {
                                $show_products = isset($atts['show_products']) && $atts['show_products'] == 'false' ? false : true;
                                $show_image = isset($atts['show_image']) && $atts['show_image'] == 'false' ? false : true;
                                $show_sku = isset($atts['show_sku']) && $atts['show_sku'] == 'false' ? false : true;
                                $show_name = isset($atts['show_name']) && $atts['show_name'] == 'false' ? false : true;
                                $show_quantity = isset($atts['show_quantity']) && $atts['show_quantity'] == 'false' ? false : true;
                                $show_price = isset($atts['show_price']) && $atts['show_price'] == 'false' ? false : true;
                                $show_subtotal = isset($atts['show_subtotal']) && $atts['show_subtotal'] == 'false' ? false : true;
                                $show_meta = isset($atts['show_meta']) && $atts['show_meta'] == 'false' ? false : true;

                                $show_totals = isset($atts['show_totals']) && $atts['show_totals'] == 'false' ? false : true;
                                $show_totals_subtotal = isset($atts['show_totals_subtotal']) && $atts['show_totals_subtotal'] == 'false' ? false : true;
                                $show_totals_discount = isset($atts['show_totals_discount']) && $atts['show_totals_discount'] == 'false' ? false : true;
                                $show_totals_payment_method = isset($atts['show_totals_payment_method']) && $atts['show_totals_payment_method'] == 'false' ? false : true;

                                $show_totals_coupons = isset($atts['show_totals_coupons']) && $atts['show_totals_coupons'] == 'false' ? false : true;
                                $show_totals_shipping = isset($atts['show_totals_shipping']) && $atts['show_totals_shipping'] == 'false' ? false : true;
                                $show_totals_shipping_destination = isset($atts['show_totals_shipping_destination']) && $atts['show_totals_shipping_destination'] == 'false' ? false : true;
                                $show_totals_shipping_package = isset($atts['show_totals_shipping_package']) && $atts['show_totals_shipping_package'] == 'false' ? false : true;
                                $show_totals_fees = isset($atts['show_totals_fees']) && $atts['show_totals_fees'] == 'false' ? false : true;
                                $show_totals_taxes = isset($atts['show_totals_taxes']) && $atts['show_totals_taxes'] == 'false' ? false : true;
                                $show_totals_total = isset($atts['show_totals_total']) && $atts['show_totals_total'] == 'false' ? false : true;
                                $show_comment = isset($atts['show_comment']) && $atts['show_comment'] == 'false' ? false : true;

                                if (isset($atts['size'])) {
                                    $size = $atts['size'];
                                } elseif (isset($atts['image_size'])) {
                                    $size = $atts['image_size'];
                                } else {
                                    $size = '32x32';
                                }

                                if (false !== strpos($size, 'x')) {
                                    $image_size = explode('x', $size);
                                    if (isset($image_size['0']) && isset($image_size['1'])) {
                                        $image_width = absint($image_size['0']);
                                        $image_height = absint($image_size['1']);
                                        if ($image_width && $image_height) {
                                            $size = array(
                                                $image_width, $image_height
                                            );
                                        }
                                    }
                                }

                                $plain_text = isset($atts['plain_text']) ? $plain_text : false;

                                if ($show_products) {
                                    $content .= "<table split='true' border='1' bordercolor='#eeeeee' cellpadding='5' class='e2pdf-wc-cart-products'>";
                                    $content .= "<tr bgcolor='#eeeeee' class='e2pdf-wc-cart-products-header'>";
                                    if ($show_image) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-image'>" . apply_filters('e2pdf_model_shortcode_wc_order_cart_header_image_text', '', $atts, $value) . "</td>";
                                    }
                                    if ($show_name) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-name'>" . apply_filters('e2pdf_model_shortcode_wc_order_cart_header_name_text', __('Product', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    if ($show_sku) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-sku'>" . apply_filters('e2pdf_model_shortcode_wc_order_cart_header_sku_text', __('SKU', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    if ($show_quantity) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-quantity'>" . apply_filters('e2pdf_model_shortcode_wc_order_cart_header_quantity_text', __('Quantity', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    if ($show_price) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-price'>" . apply_filters('e2pdf_model_shortcode_wc_order_cart_header_pricey_text', __('Price', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    if ($show_subtotal) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-subtotal'>" . apply_filters('e2pdf_model_shortcode_wc_order_cart_header_pricey_text', __('Subtotal', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    $content .= "</tr>";

                                    $item_index = 0;
                                    foreach ($items as $item_id => $item) {

                                        $product = $item->get_product();
                                        $sku = '';
                                        $purchase_note = '';
                                        $image = '';

                                        $woocommerce_order_item_visible = apply_filters('woocommerce_order_item_visible', true, $item);
                                        if (!apply_filters('e2pdf_woocommerce_order_item_visible', $woocommerce_order_item_visible, $item)) {
                                            continue;
                                        }

                                        if (is_object($product)) {
                                            $sku = $product->get_sku();
                                            $purchase_note = $product->get_purchase_note();
                                            $image = $product->get_image($size);
                                        }

                                        $even_odd = $item_index % 2 ? 'e2pdf-wc-cart-product-odd' : 'e2pdf-wc-cart-product-even';
                                        $content .= "<tr class='e2pdf-wc-cart-product " . $even_odd . "'>";

                                        if ($show_image) {
                                            $content .= "<td align='center' class='e2pdf-wc-cart-product-image'>" . apply_filters('woocommerce_order_item_thumbnail', $image, $item) . "</td>";
                                        }

                                        if ($show_name) {
                                            $content .= "<td class='e2pdf-wc-cart-product-name'>";

                                            $is_visible = $product && $product->is_visible();
                                            $product_permalink = apply_filters('woocommerce_order_item_permalink', $is_visible ? $product->get_permalink($item) : '', $item, $order);
                                            $content .= apply_filters('woocommerce_order_item_name', $product_permalink ? sprintf('<a target="_blank" href="%s">%s</a>', $product_permalink, $item->get_name()) : $item->get_name(), $item, $is_visible);

                                            if ($show_meta) {
                                                $wc_display_item_meta = wc_display_item_meta(
                                                        $item,
                                                        array(
                                                            'echo' => false,
                                                            'before' => '',
                                                            'separator' => '',
                                                            'after' => '',
                                                            'label_before' => "<div size='8px' class='e2pdf-wc-cart-product-meta'>",
                                                            'lable_after' => '</div>'
                                                        )
                                                );

                                                if ($wc_display_item_meta) {
                                                    $content .= str_replace(array('<p>', '</p>'), array('', ''), $wc_display_item_meta);
                                                }
                                            }

                                            $content .= "</td>";
                                        }

                                        if ($show_sku) {
                                            $content .= "<td class='e2pdf-wc-cart-product-sku'>" . $sku . "</td>";
                                        }

                                        if ($show_quantity) {
                                            $qty = $item->get_quantity();
                                            $refunded_qty = $order->get_qty_refunded_for_item($item_id);
                                            if ($refunded_qty) {
                                                $qty_display = '<del>' . esc_html($qty) . '</del> ' . esc_html($qty - ( $refunded_qty * -1 )) . '';
                                            } else {
                                                $qty_display = esc_html($qty);
                                            }
                                            $content .= "<td class='e2pdf-wc-cart-product-quantity'>" . apply_filters('woocommerce_email_order_item_quantity', $qty_display, $item) . "</td>";
                                        }

                                        if ($show_price) {
                                            $content .= "<td class='e2pdf-wc-cart-product-price'>" . wc_price($order->get_item_subtotal($item, false, true), array('currency' => $order->get_currency())) . "</td>";
                                        }

                                        if ($show_subtotal) {
                                            $content .= "<td class='e2pdf-wc-cart-product-subtotal'>" . $order->get_formatted_line_subtotal($item) . "</td>";
                                        }

                                        $content .= "</tr>";
                                        $item_index++;
                                    }
                                    $content .= "</table>";
                                }

                                if ($show_comment && $order->get_customer_note()) {
                                    $content .= "<table split='true' size='8px' margin-top='1' border='1' bordercolor='#eeeeee' cellpadding='5' class='e2pdf-wc-cart-comment'>";
                                    $content .= "<tr>";
                                    $content .= "<td>" . nl2br(wptexturize($order->get_customer_note())) . "</td>";
                                    $content .= "</tr>";
                                    $content .= "</table>";
                                }

                                if ($show_totals) {
                                    $item_totals = apply_filters('e2pdf_model_shortcode_wc_order_item_totals', $order->get_order_item_totals(), $atts, $value);
                                    if (!empty($item_totals)) {
                                        $total_index = 0;
                                        $content .= "<table split='true' cellpadding='5' class='e2pdf-wc-cart-totals'>";
                                        foreach ($item_totals as $total_key => $total) {
                                            if (
                                                    ($total_key == 'cart_subtotal' && !$show_totals_subtotal) ||
                                                    ($total_key == 'discount' && !$show_totals_discount) ||
                                                    ($total_key == 'shipping' && !$show_totals_shipping) ||
                                                    ($total_key == 'payment_method' && !$show_totals_payment_method) ||
                                                    ($total_key == 'order_total' && !$show_totals_total)
                                            ) {
                                                continue;
                                            }
                                            $even_odd = $total_index % 2 ? 'e2pdf-wc-cart-total-odd' : 'e2pdf-wc-cart-total-even';
                                            $content .= "<tr class='e2pdf-wc-cart-total e2pdf-wc-cart-total-" . $total_key . " " . $even_odd . "'>";
                                            $content .= "<td valign='top' width='60%' align='right' class='e2pdf-wc-cart-total-label'>" . $total['label'] . "</td>";
                                            $content .= "<td valign='top' align='right' class='e2pdf-wc-cart-total-value'>" . $total['value'] . "</td>";
                                            $content .= "</tr>";
                                            $total_index++;
                                        }
                                        $content .= "</table>";
                                    }
                                }

                                $post_meta = $content;
                            }
                        } else {
                            if ($order && is_object($order) && method_exists($order, $key)) {
                                if ($key == 'get_date_created' || $key == 'get_date_modified' || $key == 'get_date_completed' || $key == 'get_date_paid') {
                                    $post_meta = wc_format_datetime($order->$key(), get_option('date_format') . ', ' . get_option('time_format'));
                                } else if ($key == 'get_formatted_billing_address' || $key == 'get_formatted_shipping_address') {
                                    $empty_content = isset($atts['empty_content']) ? $atts['empty_content'] : '';
                                    $post_meta = $order->$key($empty_content);
                                } elseif ($key == 'get_status') {
                                    $post_meta = $order->$key();
                                    $wc_get_order_status_name = isset($atts['wc_get_order_status_name']) && $atts['wc_get_order_status_name'] == 'true' ? true : false;
                                    if ($wc_get_order_status_name) {
                                        $post_meta = wc_get_order_status_name($post_meta);
                                    }
                                } elseif ($key == 'get_items') {
                                    $types = isset($atts['types']) ? explode(',', $atts['types']) : array('line_item');
                                    $post_meta = $order->$key($types);
                                } else {
                                    $post_meta = $order->$key();
                                }
                            }
                        }
                    }
                } elseif ($terms && $names) {
                    $post_terms = wp_get_post_terms($id, $key, array('fields' => 'names'));
                    if (!is_wp_error($post_terms) && is_array($post_terms)) {
                        if ($implode === false) {
                            $implode = ', ';
                        }
                        $post_meta = implode($implode, $post_terms);
                    }
                } elseif ($terms) {
                    $post_terms = wp_get_post_terms($id, $key);
                    if (!is_wp_error($post_terms)) {
                        $post_meta = json_decode(json_encode($post_terms), true);
                    }
                } elseif ($order_item_meta) {
                    if ($subkey) {
                        $order = wc_get_order($id);
                        if ($order) {
                            $items = $order->get_items($key);
                            if ($items) {
                                if ($index !== false) {
                                    $i = 0;
                                    foreach ($items as $item_id => $item) {
                                        if ($i == $index) {
                                            $post_meta = wc_get_order_item_meta($item_id, $subkey, true);
                                            break;
                                        }
                                        $i++;
                                    }
                                } else {
                                    $item_metas = array();
                                    foreach ($items as $item_id => $item) {
                                        $item_metas[] = wc_get_order_item_meta($item_id, $subkey, true);
                                    }
                                    $post_meta = $item_metas;
                                }
                            }
                        }
                    } else {
                        $order = wc_get_order($id);
                        if ($order) {
                            $items = $order->get_items($key);
                            if ($items) {
                                global $wpdb;
                                $item_metas = array();
                                $i = 0;
                                foreach ($items as $item_id => $item) {
                                    if ($index !== false) {
                                        if ($i == $index) {
                                            $condition = array(
                                                'meta.order_item_id' => array(
                                                    'condition' => '=',
                                                    'value' => $item_id,
                                                    'type' => '%d'
                                                ),
                                            );
                                            $where = $this->helper->load('db')->prepare_where($condition);
                                            $meta_data = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT `meta`.`meta_key` FROM " . $wpdb->prefix . "woocommerce_order_itemmeta `meta`" . $where['sql'] . "", $where['filter']), ARRAY_A);
                                            if (!empty($meta_data)) {
                                                foreach ($meta_data as $meta_key) {
                                                    $item_metas[$i][$meta_key['meta_key']] = wc_get_order_item_meta($item_id, $meta_key['meta_key'], true);
                                                }
                                            }
                                            break;
                                        }
                                    } else {
                                        $condition = array(
                                            'meta.order_item_id' => array(
                                                'condition' => '=',
                                                'value' => $item_id,
                                                'type' => '%d'
                                            ),
                                        );
                                        $where = $this->helper->load('db')->prepare_where($condition);
                                        $meta_data = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT `meta`.`meta_key` FROM " . $wpdb->prefix . "woocommerce_order_itemmeta `meta`" . $where['sql'] . "", $where['filter']), ARRAY_A);
                                        if (!empty($meta_data)) {
                                            foreach ($meta_data as $meta_key) {
                                                $item_metas[$i][$meta_key['meta_key']] = wc_get_order_item_meta($item_id, $meta_key['meta_key'], true);
                                            }
                                        }
                                    }
                                    $i++;
                                }
                                $post_meta = $item_metas;
                            }
                        }
                    }
                } else {
                    $post_meta = get_post_meta($id, $key, true);
                }

                if ($post_meta !== false) {

                    if (is_object($post_meta)) {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_order_object', $post_meta, $atts);
                    }

                    if ($explode && !is_array($post_meta)) {
                        $post_meta = explode($explode, $post_meta);
                    }

                    if (is_array($post_meta)) {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_order_array', $post_meta, $atts);
                    }

                    if ((is_array($post_meta) || is_object($post_meta)) && $path !== false) {
                        $path_parts = explode('.', $path);
                        $path_value = &$post_meta;
                        $found = true;
                        foreach ($path_parts as $path_part) {
                            if (is_array($path_value) && isset($path_value[$path_part])) {
                                $path_value = &$path_value[$path_part];
                            } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                                $path_value = &$path_value->$path_part;
                            } else {
                                $found = false;
                                break;
                            }
                        }
                        if ($found) {
                            $post_meta = $path_value;
                        } else {
                            $post_meta = '';
                        }
                    }

                    if ($attachment_url || $attachment_image_url) {
                        if (!is_array($post_meta)) {
                            if (strpos($post_meta, ',') !== false) {
                                $post_meta = explode(',', $post_meta);
                                if ($implode === false) {
                                    $implode = ',';
                                }
                            }
                        }

                        if (is_array($post_meta)) {
                            $attachments = array();
                            foreach ($post_meta as $post_meta_part) {
                                if (!is_array($post_meta_part)) {
                                    if ($attachment_url) {
                                        $image = wp_get_attachment_url($post_meta_part);
                                    } elseif ($attachment_image_url) {
                                        $image = wp_get_attachment_image_url($post_meta_part, $size);
                                    }
                                    if ($image) {
                                        $attachments[] = $image;
                                    }
                                }
                            }
                            $post_meta = $attachments;
                        } else {
                            if ($attachment_url) {
                                $image = wp_get_attachment_url($post_meta);
                            } elseif ($attachment_image_url) {
                                $image = wp_get_attachment_image_url($post_meta, $size);
                            }
                            if ($image) {
                                $post_meta = $image;
                            } else {
                                $post_meta = '';
                            }
                        }
                    }

                    if ($wc_price) {
                        if (is_array($post_meta) || is_object($post_meta)) {
                            
                        } else {
                            if (isset($atts['currency'])) {
                                $post_meta = wc_price($post_meta, $atts['currency']);
                            } else {
                                if (!$order) {
                                    $order = wc_get_order($id);
                                }
                                if ($order) {
                                    $post_meta = wc_price($post_meta, $order->get_currency());
                                } else {
                                    $post_meta = wc_price($post_meta);
                                }
                            }
                        }
                    }

                    if ($raw) {
                        $response = $post_meta;
                    } else {
                        if (is_array($post_meta)) {
                            if ($implode !== false) {
                                if (!$this->helper->is_multidimensional($post_meta)) {
                                    $response = implode($implode, $post_meta);
                                } else {
                                    $response = serialize($post_meta);
                                }
                            } else {
                                $response = serialize($post_meta);
                            }
                        } elseif (is_object($post_meta)) {
                            $response = serialize($post_meta);
                        } else {
                            $response = $post_meta;
                        }
                    }
                }
            }
        }

        if ($raw) {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wc_order_raw', $response, $atts, $value);
        } else {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wc_order_response', $response, $atts, $value);
        }
    }

    /**
     * [e2pdf-foreach] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_foreach($atts = array(), $value = '') {

        $response = array();
        $implode = isset($atts['implode']) ? $atts['implode'] : '';

        if (isset($atts['shortcode'])) {
            $foreach_shortcode = str_replace('-', '_', $atts['shortcode']);
            if (method_exists($this, $foreach_shortcode)) {
                $atts['raw'] = 'true';
                $data = $this->$foreach_shortcode($atts, '');
                if ($data && (is_string($data) || is_numeric($data))) {
                    $data = array($data);
                }
                if (is_array($data) && count($data) > 0) {
                    $index = 0;
                    foreach ($data as $data_key => $data_value) {
                        $sub_value = $this->e2pdf_foreach_value($value, $data_key, $data_value, $index);
                        $response[] = $this->e2pdf_foreach_index($atts, $sub_value, 1);
                        $index++;
                    }
                }
            }
        }

        return implode($implode, $response);
    }

    /**
     * [e2pdf-foreach-x] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_foreach_index($attributes = array(), $value = '', $foreach = 1) {

        if (false !== strpos($value, '[')) {
            $shortcode_tags = array(
                'e2pdf-foreach-' . $foreach
            );

            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $value, $matches);
            $tagnames = array_intersect($shortcode_tags, $matches[1]);

            foreach ($matches[1] as $key => $shortcode) {
                if (strpos($shortcode, ':') !== false) {
                    $shortcode_tags[] = $shortcode;
                }
            }

            if (!empty($tagnames)) {
                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);

                preg_match_all("/$pattern/", $value, $shortcodes);

                foreach ($shortcodes[0] as $key => $shortcode_value) {
                    $shortcode = array();
                    $shortcode[1] = $shortcodes[1][$key];
                    $shortcode[2] = $shortcodes[2][$key];
                    $shortcode[3] = $shortcodes[3][$key];
                    $shortcode[4] = $shortcodes[4][$key];
                    $shortcode[5] = $shortcodes[5][$key];
                    $shortcode[6] = $shortcodes[6][$key];

                    $atts = shortcode_parse_atts($shortcode[3]);
                    if (isset($atts['shortcode'])) {

                        $response = array();
                        $implode = isset($atts['implode']) ? $atts['implode'] : '';

                        $foreach_shortcode = str_replace('-', '_', $atts['shortcode']);
                        if ($attributes['shortcode'] == $atts['shortcode']) {
                            if (!isset($atts['id']) && isset($attributes['id'])) {
                                $atts['id'] = $attributes['id'];
                            }
                            if (!isset($atts['wc_order_id']) && isset($attributes['wc_order_id'])) {
                                $atts['wc_order_id'] = $attributes['wc_order_id'];
                            }
                        } else {
                            if ($attributes['shortcode'] == 'e2pdf-wc-order') {
                                if ($atts['shortcode'] == 'e2pdf-wc-product') {
                                    if (!isset($atts['wc_order_id']) && isset($attributes['id'])) {
                                        $atts['wc_order_id'] = $attributes['id'];
                                    }
                                }
                            } elseif ($attributes['shortcode'] == 'e2pdf-wc-cart') {
                                if ($atts['shortcode'] == 'e2pdf-wc-product') {
                                    if (!isset($atts['wc_order_id']) && isset($attributes['id'])) {
                                        $atts['wc_order_id'] = "cart";
                                    }
                                }
                            }
                        }

                        if (method_exists($this, $foreach_shortcode)) {
                            $atts['raw'] = 'true';
                            $data = $this->$foreach_shortcode($atts, '');
                            if (is_array($data) && count($data) > 0) {
                                $index = 0;
                                foreach ($data as $data_key => $data_value) {
                                    $sub_value = $this->e2pdf_foreach_value($shortcode['5'], $data_key, $data_value, $index, $foreach);
                                    $response[] = $this->e2pdf_foreach_index($attributes, $sub_value, $foreach + 1);
                                    $index++;
                                }
                            }
                        }
                    }
                    $value = str_replace($shortcode_value, implode($implode, $response), $value);
                }
            }
        }

        return $value;
    }

    /**
     * [e2pdf-foreach] inner shortcodes support
     *
     */
    function e2pdf_foreach_value($value, $data_key, $data_value, $index, $foreach = 0) {
        if ($value) {
            $foreach_index = '';
            if ($foreach) {
                $foreach_index = '-' . $foreach;
            }
            $evenodd = $index % 2 == 0 ? '0' : '1';
            $replace = array(
                '[e2pdf-foreach-index' . $foreach_index . ']' => $index,
                '[e2pdf-foreach-counter' . $foreach_index . ']' => $index + 1,
                '[e2pdf-foreach-key' . $foreach_index . ']' => $data_key,
                '[e2pdf-foreach-value' . $foreach_index . ']' => is_array($data_value) || is_object($data_value) ? serialize($data_value) : $data_value,
                '[e2pdf-foreach-evenodd' . $foreach_index . ']' => $evenodd
            );
            $value = str_replace(array_keys($replace), $replace, $value);

            $shortcode_tags = array(
                'e2pdf-foreach-value' . $foreach_index . '',
            );
            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $value, $matches);
            $tagnames = array_intersect($shortcode_tags, $matches[1]);
            if (!empty($tagnames)) {
                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);
                preg_match_all("/$pattern/", $value, $shortcodes);
                foreach ($shortcodes[0] as $key => $shortcode_value) {
                    $shortcode = array();
                    $shortcode[1] = $shortcodes[1][$key];
                    $shortcode[2] = $shortcodes[2][$key];
                    $shortcode[3] = $shortcodes[3][$key];
                    $shortcode[4] = $shortcodes[4][$key];
                    $shortcode[5] = $shortcodes[5][$key];
                    $shortcode[6] = $shortcodes[6][$key];

                    $atts = shortcode_parse_atts($shortcode[3]);
                    if (isset($atts['path'])) {

                        $path = $atts['path'];
                        $post_meta = $data_value;

                        if ((is_array($post_meta) || is_object($post_meta)) && $path !== false) {
                            $path_parts = explode('.', $path);
                            $path_value = &$post_meta;
                            $found = true;
                            foreach ($path_parts as $path_part) {
                                if (is_array($path_value) && isset($path_value[$path_part])) {
                                    $path_value = &$path_value[$path_part];
                                } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                                    $path_value = &$path_value->$path_part;
                                } else {
                                    $found = false;
                                    break;
                                }
                            }
                            if ($found) {
                                $post_meta = $path_value;
                            } else {
                                $post_meta = '';
                            }
                        }

                        if (is_array($post_meta) || is_object($post_meta)) {
                            $post_meta = serialize($post_meta);
                        }

                        $value = str_replace($shortcode_value, $post_meta, $value);
                    }
                }
            }
        }

        return $value;
    }

    /**
     * [e2pdf-if] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_if($atts = array(), $value = '') {

        $response = '';

        if (isset($atts['shortcode'])) {
            $shortcode = str_replace('-', '_', $atts['shortcode']);
            if (method_exists($this, $shortcode)) {

                if (isset($atts['equals'])) {
                    $condition = '=';
                    $val = $atts['equals'];
                } elseif (isset($atts['not_equal'])) {
                    $condition = '!=';
                    $val = $atts['not_equal'];
                } elseif (isset($atts['greater_than'])) {
                    $condition = '>';
                    $val = $atts['greater_than'];
                } elseif (isset($atts['greater_than_or_equal_to'])) {
                    $condition = '>=';
                    $val = $atts['greater_than_or_equal_to'];
                } elseif (isset($atts['less_than'])) {
                    $condition = '<';
                    $val = $atts['less_than'];
                } elseif (isset($atts['less_than_or_equal_to'])) {
                    $condition = '<=';
                    $val = $atts['less_than_or_equal_to'];
                } elseif (isset($atts['like'])) {
                    $condition = 'like';
                    $val = $atts['less_than_or_equal_to'];
                } elseif (isset($atts['not_like'])) {
                    $condition = 'not_like';
                    $val = $atts['not_like'];
                } elseif (isset($atts['is_array'])) {
                    $condition = 'is_array';
                    $val = $atts['is_array'];
                } elseif (isset($atts['in_array'])) {
                    $condition = 'in_array';
                    $val = $atts['in_array'];
                } elseif (isset($atts['array_key_exists'])) {
                    $condition = 'array_key_exists';
                    $val = $atts['array_key_exists'];
                } elseif (isset($atts['isset'])) {
                    $condition = 'isset';
                    $val = $atts['isset'];
                } else {
                    $condition = false;
                }

                $atts['raw'] = 'true';
                $if = $this->$shortcode($atts, '');

                switch ($condition) {
                    case '=':
                        $result = $if == $val ? true : false;
                        break;

                    case '!=':
                        $result = $if != $val ? true : false;
                        break;

                    case '>':
                        $result = $if > $val ? true : false;
                        break;

                    case '>=':
                        $result = $if >= $val ? true : false;
                        break;

                    case '<':
                        $result = $if < $val ? true : false;
                        break;

                    case '<=':
                        $result = $if <= $val ? true : false;
                        break;

                    case 'like':
                        if (empty($val) && empty($if)) {
                            $result = true;
                        } else {
                            $result = !empty($val) && strpos($if, $val) !== false ? true : false;
                        }
                        break;
                    case 'not_like':
                        if (empty($val) && empty($if)) {
                            $result = false;
                        } elseif (empty($val) && !empty($if)) {
                            $result = true;
                        } else {
                            $result = !empty($val) && strpos($if, $val) === false ? true : false;
                        }
                        break;
                    case 'is_array':
                        $result = is_array($if) ? true : false;
                        break;
                    case 'in_array':
                        $result = is_array($if) && in_array($val, $if) ? true : false;
                        break;
                    case 'array_key_exists':
                        $result = is_array($if) && array_key_exists($val, $if) ? true : false;
                        break;
                    case 'isset':
                        $result = false;
                        if ($if) {
                            if (is_array($result)) {
                                $result = isset($if[$val]);
                            } elseif (is_object($result)) {
                                $result = isset($if->$val);
                            }
                        }
                        break;
                    default:
                        $result = $if ? true : false;
                        break;
                }
            }
        }

        return $response;
    }

    /**
     * [e2pdf-wc-cart] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_wc_cart($atts = array(), $value = '') {

        $post_meta = false;
        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_wc_cart_atts', $atts);

        $id = isset($atts['id']) ? $atts['id'] : false;
        $key = isset($atts['key']) ? $atts['key'] : false;
        $path = isset($atts['path']) ? $atts['path'] : false;
        $names = isset($atts['names']) && $atts['names'] == 'true' ? true : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : false;
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $size = isset($atts['size']) ? $atts['size'] : 'thumbnail';
        $meta = isset($atts['meta']) && $atts['meta'] == 'true' ? true : false;
        $terms = isset($atts['terms']) && $atts['terms'] == 'true' ? true : false;
        $output = isset($atts['output']) ? $atts['output'] : false;
        $wc_price = isset($atts['wc_price']) && $atts['wc_price'] == 'true' ? true : false;
        $wc_filter = isset($atts['wc_filter']) && $atts['wc_filter'] == 'true' ? true : false;
        $raw = isset($atts['raw']) && $atts['raw'] == 'true' ? true : false;

        $data_fields = apply_filters('e2pdf_model_shortcode_wc_cart_data_fields', array(
            'id',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_title',
            'post_excerpt',
            'post_status',
            'permalink',
            'post_permalink',
            'get_post_permalink',
            'comment_status',
            'ping_status',
            'post_password',
            'post_name',
            'to_ping',
            'pinged',
            'post_modified',
            'post_modified_gmt',
            'post_content_filtered',
            'post_parent',
            'guid',
            'menu_order',
            'post_type',
            'post_mime_type',
            'comment_count',
            'filter',
            'post_thumbnail',
            'get_the_post_thumbnail',
            'get_the_post_thumbnail_url',
            'response_hook',
        ));

        $cart_fields = apply_filters('e2pdf_model_shortcode_wc_cart_cart_fields', array(
            'cart',
            'get_cart',
            'get_applied_coupons',
            'get_cart_total',
            'get_formatted_cart_totals',
            'get_cart_subtotal',
            'get_cart_tax',
            'get_cart_hash',
            'get_cart_contents_total',
            'get_cart_contents_tax',
            'get_cart_contents_taxes',
            'get_cart_contents_count',
            'get_cart_contents_weight',
            'get_cart_item_quantities',
            'get_cart_item_tax_classes',
            'get_cart_item_tax_classes_for_shipping',
            'get_cart_shipping_total',
            'get_coupon_discount_totals',
            'get_coupon_discount_tax_totals',
            'get_totals',
            'get_total',
            'get_total_tax',
            'get_total_ex_tax',
            'get_total_discount',
            'get_subtotal',
            'get_subtotal_tax',
            'get_discount_total',
            'get_discount_tax',
            'get_shipping_total',
            'get_shipping_tax',
            'get_shipping_taxes',
            'get_fees',
            'get_fee_total',
            'get_fee_tax',
            'get_fee_taxes',
            'get_displayed_subtotal',
            'get_tax_price_display_mode',
            'get_taxes',
            'get_taxes_total',
            'get_shipping_method_title',
            'get_payment_method_title',
        ));

        if ($id && $key) {
            $wp_post = get_post($id);
            if ($wp_post) {
                if (in_array($key, $data_fields) && !$meta && !$terms) {
                    if ($key == 'post_author') {
                        $post_meta = isset($wp_post->post_author) && $wp_post->post_author ? get_userdata($wp_post->post_author)->user_nicename : '';
                    } elseif ($key == 'id' && isset($wp_post->ID)) {
                        $post_meta = $wp_post->ID;
                    } elseif (($key == 'post_thumbnail' || $key == 'get_the_post_thumbnail_url') && isset($wp_post->ID)) {
                        $post_meta = get_the_post_thumbnail_url($wp_post->ID, $size);
                    } elseif ($key == 'get_the_post_thumbnail' && isset($wp_post->ID)) {
                        $post_meta = get_the_post_thumbnail($wp_post->ID, $size);
                    } elseif ($key == 'post_content' && isset($wp_post->post_content)) {
                        $content = $wp_post->post_content;
                        if (false !== strpos($content, '[')) {
                            $shortcode_tags = array(
                                'e2pdf-exclude',
                                'e2pdf-save'
                            );
                            preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
                            $tagnames = array_intersect($shortcode_tags, $matches[1]);
                            if (!empty($tagnames)) {
                                $pattern = $this->helper->load('shortcode')->get_shortcode_regex($tagnames);
                                preg_match_all("/$pattern/", $content, $shortcodes);
                                foreach ($shortcodes[0] as $key => $shortcode_value) {
                                    $content = str_replace($shortcode_value, '', $content);
                                }
                            }
                        }

                        if ($output) {
                            global $post;
                            $tmp_post = $post;
                            $post = $wp_post;
                            if ($output == 'backend') {
                                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                                    \Elementor\Plugin::instance()->frontend->remove_content_filter();
                                }
                            } elseif ($output == 'frontend') {
                                if (did_action('elementor/loaded') && class_exists('\Elementor\Plugin')) {
                                    \Elementor\Plugin::instance()->frontend->add_content_filter();
                                }
                            }
                        }

                        if (defined('ET_BUILDER_DIR') && 'on' === get_post_meta($id, '_et_pb_use_builder', true) && function_exists('et_builder_init_global_settings') && function_exists('et_builder_add_main_elements')) {
                            require_once(ET_BUILDER_DIR . 'class-et-builder-element.php');
                            require_once(ET_BUILDER_DIR . 'functions.php');
                            require_once(ET_BUILDER_DIR . 'ab-testing.php');
                            require_once(ET_BUILDER_DIR . 'class-et-global-settings.php');
                            et_builder_add_main_elements();
                        }

                        $content = apply_filters('the_content', $content);
                        $content = str_replace("</p>", "</p>\r\n", $content);
                        $post_meta = $content;

                        if ($output) {
                            $post = $tmp_post;
                        }
                    } elseif ($key == 'permalink') {
                        $leavename = isset($atts['leavename']) && $atts['leavename'] == 'true' ? true : false;
                        $post_meta = get_permalink($id, $leavename);
                    } elseif ($key == 'get_post_permalink' || $key == 'post_permalink') {
                        $leavename = isset($atts['leavename']) && $atts['leavename'] == 'true' ? true : false;
                        $post_meta = get_post_permalink($id, $leavename);
                    } elseif ($key == 'response_hook') {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_cart_response_hook', '', $id, $atts, $wp_post);
                    } elseif (isset($wp_post->$key)) {
                        $post_meta = $wp_post->$key;
                    }
                } elseif (in_array($key, $cart_fields) && !$meta && !$terms) {
                    if (function_exists('WC') && isset(WC()->cart) && WC()->cart && is_object(WC()->cart)) {
                        WC()->cart->calculate_totals();
                        if ($key == 'cart') {

                            $items = WC()->cart->get_cart();
                            $content = '';

                            if ($items) {
                                $show_products = isset($atts['show_products']) && $atts['show_products'] == 'false' ? false : true;
                                $show_image = isset($atts['show_image']) && $atts['show_image'] == 'false' ? false : true;
                                $show_sku = isset($atts['show_sku']) && $atts['show_sku'] == 'false' ? false : true;
                                $show_name = isset($atts['show_name']) && $atts['show_name'] == 'false' ? false : true;
                                $show_quantity = isset($atts['show_quantity']) && $atts['show_quantity'] == 'false' ? false : true;
                                $show_price = isset($atts['show_price']) && $atts['show_price'] == 'false' ? false : true;
                                $show_subtotal = isset($atts['show_subtotal']) && $atts['show_subtotal'] == 'false' ? false : true;
                                $show_meta = isset($atts['show_meta']) && $atts['show_meta'] == 'false' ? false : true;

                                $show_totals = isset($atts['show_totals']) && $atts['show_totals'] == 'false' ? false : true;
                                $show_totals_subtotal = isset($atts['show_totals_subtotal']) && $atts['show_totals_subtotal'] == 'false' ? false : true;
                                $show_totals_coupons = isset($atts['show_totals_coupons']) && $atts['show_totals_coupons'] == 'false' ? false : true;
                                $show_totals_shipping = isset($atts['show_totals_shipping']) && $atts['show_totals_shipping'] == 'false' ? false : true;
                                $show_totals_shipping_destination = isset($atts['show_totals_shipping_destination']) && $atts['show_totals_shipping_destination'] == 'false' ? false : true;
                                $show_totals_shipping_package = isset($atts['show_totals_shipping_package']) && $atts['show_totals_shipping_package'] == 'false' ? false : true;
                                $show_totals_fees = isset($atts['show_totals_fees']) && $atts['show_totals_fees'] == 'false' ? false : true;
                                $show_totals_taxes = isset($atts['show_totals_taxes']) && $atts['show_totals_taxes'] == 'false' ? false : true;
                                $show_totals_total = isset($atts['show_totals_total']) && $atts['show_totals_total'] == 'false' ? false : true;

                                if (isset($atts['size'])) {
                                    $size = $atts['size'];
                                } elseif (isset($atts['image_size'])) {
                                    $size = $atts['image_size'];
                                } else {
                                    $size = '32x32';
                                }

                                if (false !== strpos($size, 'x')) {
                                    $image_size = explode('x', $size);
                                    if (isset($image_size['0']) && isset($image_size['1'])) {
                                        $image_width = absint($image_size['0']);
                                        $image_height = absint($image_size['1']);
                                        if ($image_width && $image_height) {
                                            $size = array(
                                                $image_width, $image_height
                                            );
                                        }
                                    }
                                }

                                $plain_text = isset($atts['plain_text']) ? $plain_text : false;
                                if ($show_products) {

                                    $content .= "<table border='1' split='true' bordercolor='#eeeeee' cellpadding='5' class='e2pdf-wc-cart-products'>";
                                    $content .= "<tr bgcolor='#eeeeee' class='e2pdf-wc-cart-products-header'>";
                                    if ($show_image) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-image'>" . apply_filters('e2pdf_model_shortcode_wc_cart_cart_header_image_text', '', $atts, $value) . "</td>";
                                    }
                                    if ($show_name) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-name'>" . apply_filters('e2pdf_model_shortcode_wc_cart_cart_header_name_text', __('Product', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    if ($show_sku) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-sku'>" . apply_filters('e2pdf_model_shortcode_wc_cart_cart_header_sku_text', __('SKU', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    if ($show_quantity) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-quantity'>" . apply_filters('e2pdf_model_shortcode_wc_cart_cart_header_quantity_text', __('Quantity', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    if ($show_price) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-price'>" . apply_filters('e2pdf_model_shortcode_wc_cart_cart_header_pricey_text', __('Price', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    if ($show_subtotal) {
                                        $content .= "<td class='e2pdf-wc-cart-products-header-subtotal'>" . apply_filters('e2pdf_model_shortcode_wc_cart_cart_header_pricey_text', __('Subtotal', 'woocommerce'), $atts, $value) . "</td>";
                                    }
                                    $content .= "</tr>";

                                    $item_index = 0;
                                    foreach ($items as $item_id => $item) {

                                        $product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $item_id);
                                        $product_id = apply_filters('woocommerce_cart_item_product_id', $item['product_id'], $item, $item_id);

                                        if ($product && $product->exists() && $item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $item, $item_id)) {

                                            $sku = '';
                                            $purchase_note = '';
                                            $image = '';

                                            if (is_object($product)) {
                                                $sku = $product->get_sku();
                                                $purchase_note = $product->get_purchase_note();
                                                $image = $product->get_image($size);
                                            }

                                            $even_odd = $item_index % 2 ? 'e2pdf-wc-cart-product-odd' : 'e2pdf-wc-cart-product-even';
                                            $content .= "<tr class='e2pdf-wc-cart-product " . $even_odd . "'>";
                                            if ($show_image) {
                                                $content .= "<td align='center' class='e2pdf-wc-cart-product-image'>" . apply_filters('woocommerce_cart_item_thumbnail', $image, $item, $item_id) . "</td>";
                                            }
                                            if ($show_name) {
                                                $content .= "<td class='e2pdf-wc-cart-product-name'>";
                                                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink($item) : '', $item, $item_id);
                                                if (!$product_permalink) {
                                                    $content .= wp_kses_post(apply_filters('woocommerce_cart_item_name', $product->get_name(), $item, $item_id) . '&nbsp;');
                                                } else {
                                                    $content .= wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a target="_blank" href="%s">%s</a>', esc_url($product_permalink), $product->get_name()), $item, $item_id));
                                                }

                                                if ($show_meta) {
                                                    $wc_display_item_meta = wc_get_formatted_cart_item_data($item, true);

                                                    if ($wc_display_item_meta) {
                                                        $content .= "<div size='8px' class='e2pdf-wc-cart-product-meta'>" . nl2br($wc_display_item_meta) . "</div>";
                                                    }

                                                    // Backorder notification.
                                                    //	if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                                                    //		echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
                                                    //	}
                                                }

                                                $content .= "</td>";
                                            }

                                            if ($show_sku) {
                                                $content .= "<td class='e2pdf-wc-cart-product-sku'>" . $sku . "</td>";
                                            }

                                            if ($show_quantity) {
                                                $content .= "<td class='e2pdf-wc-cart-product-quantity'>" . $item['quantity'] . "</td>";
                                            }

                                            if ($show_price) {
                                                $content .= "<td class='e2pdf-wc-cart-product-price'>" . apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($product), $item, $item_id) . "</td>";
                                            }

                                            if ($show_subtotal) {
                                                $content .= "<td class='e2pdf-wc-cart-product-subtotal'>" . apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($product, $item['quantity']), $item, $item_id) . "</td>";
                                            }

                                            $content .= "</tr>";

                                            $item_index++;
                                        }
                                    }
                                    $content .= "</table>";
                                }

                                $item_totals = array();
                                if ($show_totals) {
                                    /*
                                     * Total Subtotal
                                     */
                                    if ($show_totals_subtotal) {
                                        $item_totals['subtotal'] = array(
                                            'label' => __('Subtotal', 'woocommerce'),
                                            'value' => WC()->cart->get_cart_subtotal()
                                        );
                                    }

                                    /*
                                     * Total Coupons
                                     */
                                    if ($show_totals_coupons) {
                                        $index_id = 0;
                                        foreach (WC()->cart->get_coupons() as $code => $coupon) {
                                            if (is_string($coupon)) {
                                                $coupon = new WC_Coupon($coupon);
                                            }

                                            $discount_amount_html = '';
                                            $amount = WC()->cart->get_coupon_discount_amount($coupon->get_code(), WC()->cart->display_cart_ex_tax);
                                            $discount_amount_html = '-' . wc_price($amount);

                                            if ($coupon->get_free_shipping() && empty($amount)) {
                                                $discount_amount_html = __('Free shipping coupon', 'woocommerce');
                                            }

                                            $item_totals['coupon_' . $index_id] = array(
                                                'label' => wc_cart_totals_coupon_label($coupon, false),
                                                'value' => $discount_amount_html
                                            );
                                            $index_id++;
                                        }
                                    }

                                    /*
                                     * Total Shipping
                                     */
                                    if ($show_totals_shipping) {
                                        if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {

                                            $packages = WC()->shipping()->get_packages();
                                            $first = true;

                                            $index_id = 0;
                                            foreach ($packages as $i => $package) {
                                                $chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
                                                if ($chosen_method) {
                                                    $product_names = array();
                                                    if (count($packages) > 1) {
                                                        foreach ($package['contents'] as $item_id => $values) {
                                                            $product_names[$item_id] = $values['data']->get_name() . ' &times;' . $values['quantity'];
                                                        }
                                                        $product_names = apply_filters('woocommerce_shipping_package_details_array', $product_names, $package);
                                                    }

                                                    $available_methods = $package['rates'];
                                                    $show_package_details = count($packages) > 1;
                                                    $package_details = implode(', ', $product_names);
                                                    $package_name = apply_filters('woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf(_x('Shipping %d', 'shipping packages', 'woocommerce'), ( $i + 1)) : _x('Shipping', 'shipping packages', 'woocommerce'), $i, $package);
                                                    $formatted_destination = WC()->countries->get_formatted_address($package['destination'], ', ');

                                                    $item_totals['shipping_' . $index_id] = array(
                                                        'label' => wp_kses_post($package_name),
                                                        'value' => ''
                                                    );

                                                    if ($available_methods) {
                                                        foreach ($available_methods as $method) {
                                                            if ($method->get_id() == $chosen_method) {
                                                                $item_totals['shipping_' . $index_id]['value'] .= "<div>" . wc_cart_totals_shipping_method_label($method) . "</div>";
                                                            }
                                                        }
                                                    }

                                                    if ($show_totals_shipping_destination) {
                                                        if ($formatted_destination) {
                                                            $item_totals['shipping_' . $index_id]['value'] .= "<div size='8px' class='e2pdf-wc-cart-total-shipping-destination'>" . sprintf(esc_html__('Shipping to %s.', 'woocommerce') . ' ', esc_html($formatted_destination)) . "</div>";
                                                        } else {
                                                            $item_totals['shipping_' . $index_id]['value'] .= "<div size='8px' class='e2pdf-wc-cart-total-shipping-destination'>" . wp_kses_post(apply_filters('woocommerce_shipping_estimate_html', __('Shipping options will be updated during checkout.', 'woocommerce'))) . '</div>';
                                                        }
                                                    }

                                                    if ($show_totals_shipping_package) {
                                                        if ($show_package_details) {
                                                            $item_totals['shipping_' . $index_id]['value'] .= "<div size='8px' class='e2pdf-wc-cart-total-shipping-package'>" . esc_html($package_details) . '</div>';
                                                        }
                                                    }

                                                    $index_id++;
                                                    $first = false;
                                                }
                                            }
                                        } elseif (WC()->cart->needs_shipping() && 'yes' === get_option('woocommerce_enable_shipping_calc')) {
                                            
                                        }
                                    }

                                    /*
                                     * Total Fees
                                     */
                                    if ($show_totals_fees) {
                                        $index_id = 0;
                                        foreach (WC()->cart->get_fees() as $fee) {
                                            $cart_totals_fee_html = WC()->cart->display_prices_including_tax() ? wc_price($fee->total + $fee->tax) : wc_price($fee->total);
                                            $item_totals['fee_' . $index_id] = array(
                                                'label' => esc_html($fee->name),
                                                'value' => apply_filters('woocommerce_cart_totals_fee_html', $cart_totals_fee_html, $fee)
                                            );
                                            $index_id++;
                                        }
                                    }

                                    /*
                                     * Total Taxes
                                     */
                                    if ($show_totals_taxes) {
                                        if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) {
                                            $taxable_address = WC()->customer->get_taxable_address();
                                            $estimated_text = '';

                                            if (WC()->customer->is_customer_outside_base() && !WC()->customer->has_calculated_shipping()) {
                                                /* translators: %s location. */
                                                $estimated_text = sprintf(' <small>' . esc_html__('(estimated for %s)', 'woocommerce') . '</small>', WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[$taxable_address[0]]);
                                            }

                                            if ('itemized' === get_option('woocommerce_tax_total_display')) {
                                                $index_id = 0;
                                                foreach (WC()->cart->get_tax_totals() as $code => $tax) {
                                                    $item_totals['tax_' . $index_id] = array(
                                                        'label' => esc_html($tax->label) . $estimated_text,
                                                        'value' => wp_kses_post($tax->formatted_amount)
                                                    );
                                                    $index_id++;
                                                }
                                            } else {
                                                $item_totals['tax_or_vat'] = array(
                                                    'label' => esc_html(WC()->countries->tax_or_vat()) . $estimated_text,
                                                    'value' => apply_filters('woocommerce_cart_totals_taxes_total_html', wc_price(WC()->cart->get_taxes_total()))
                                                );
                                            }
                                        }
                                    }

                                    /*
                                     * Total Total
                                     */
                                    if ($show_totals_total) {
                                        $total = WC()->cart->get_total();
                                        if (wc_tax_enabled() && WC()->cart->display_prices_including_tax()) {
                                            $tax_string_array = array();
                                            $cart_tax_totals = WC()->cart->get_tax_totals();

                                            if (get_option('woocommerce_tax_total_display') === 'itemized') {
                                                foreach ($cart_tax_totals as $code => $tax) {
                                                    $tax_string_array[] = sprintf('%s %s', $tax->formatted_amount, $tax->label);
                                                }
                                            } elseif (!empty($cart_tax_totals)) {
                                                $tax_string_array[] = sprintf('%s %s', wc_price(WC()->cart->get_taxes_total(true, true)), WC()->countries->tax_or_vat());
                                            }

                                            if (!empty($tax_string_array)) {
                                                $taxable_address = WC()->customer->get_taxable_address();
                                                $estimated_text = WC()->customer->is_customer_outside_base() && !WC()->customer->has_calculated_shipping() ? sprintf(' ' . __('estimated for %s', 'woocommerce'), WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[$taxable_address[0]]) : '';
                                                $total .= '<small class="includes_tax"> ('
                                                        . esc_html__('includes', 'woocommerce')
                                                        . ' '
                                                        . wp_kses_post(implode(', ', $tax_string_array))
                                                        . esc_html($estimated_text)
                                                        . ')</small>';
                                            }
                                        }

                                        $item_totals['total'] = array(
                                            'label' => __('Total', 'woocommerce'),
                                            'value' => apply_filters('woocommerce_cart_totals_order_total_html', $total)
                                        );
                                    }

                                    $item_totals = apply_filters('e2pdf_model_shortcode_wc_cart_item_totals', $item_totals, $atts, $value);

                                    if (!empty($item_totals)) {
                                        $total_index = 0;
                                        $content .= "<table split='true' cellpadding='5' class='e2pdf-wc-cart-totals'>";
                                        foreach ($item_totals as $total_key => $total) {
                                            $even_odd = $total_index % 2 ? 'e2pdf-wc-cart-total-odd' : 'e2pdf-wc-cart-total-even';
                                            $content .= "<tr class='e2pdf-wc-cart-total e2pdf-wc-cart-total-" . $total_key . " " . $even_odd . "'>";
                                            $content .= "<td valign='top' width='60%' align='right' class='e2pdf-wc-cart-total-label'>" . $total['label'] . ":</td>";
                                            $content .= "<td valign='top' align='right' class='e2pdf-wc-cart-total-value'>" . $total['value'] . "</td>";
                                            $content .= "</tr>";
                                            $total_index++;
                                        }
                                        $content .= "</table>";
                                    }
                                }
                            }
                            $post_meta = $content;
                        } elseif ($key == 'get_shipping_method_title') {
                            $packages = WC()->shipping()->get_packages();
                            foreach ($packages as $i => $package) {
                                $chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
                                if ($chosen_method) {
                                    $available_methods = $package['rates'];
                                    if ($available_methods) {
                                        foreach ($available_methods as $method) {
                                            if ($method->get_id() == $chosen_method) {
                                                $post_meta = wc_cart_totals_shipping_method_label($method);
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        } elseif ($key == 'get_payment_method_title') {
                            $chosen_method = isset(WC()->session->chosen_payment_method) ? WC()->session->chosen_payment_method : '';
                            $packages = WC()->payment_gateways->get_available_payment_gateways();
                            foreach ($packages as $i => $package) {
                                if ($i == $chosen_method) {
                                    $post_meta = $package->get_title();
                                    break;
                                }
                            }
                        } elseif ($key == 'get_formatted_cart_totals') {

                            $include = array(
                                'subtotal',
                                'coupons',
                                'shipping',
                                'shipping_destination',
                                'shipping_package',
                                'fees',
                                'taxes',
                                'total'
                            );

                            if (isset($atts['include'])) {
                                $include = explode(',', $atts['include']);
                            }

                            $exclude = array();
                            if (isset($atts['exclude'])) {
                                $exclude = explode(',', $atts['exclude']);
                            }
                            $include = array_diff($include, $exclude);

                            /*
                             * Total Subtotal
                             */
                            if (in_array('subtotal', $include)) {
                                $item_totals['subtotal'] = array(
                                    'label' => __('Subtotal', 'woocommerce'),
                                    'value' => WC()->cart->get_cart_subtotal()
                                );
                            }

                            /*
                             * Total Coupons
                             */
                            if (in_array('coupons', $include)) {
                                $index_id = 0;
                                foreach (WC()->cart->get_coupons() as $code => $coupon) {
                                    if (is_string($coupon)) {
                                        $coupon = new WC_Coupon($coupon);
                                    }

                                    $discount_amount_html = '';
                                    $amount = WC()->cart->get_coupon_discount_amount($coupon->get_code(), WC()->cart->display_cart_ex_tax);
                                    $discount_amount_html = '-' . wc_price($amount);

                                    if ($coupon->get_free_shipping() && empty($amount)) {
                                        $discount_amount_html = __('Free shipping coupon', 'woocommerce');
                                    }

                                    $item_totals['coupon_' . $index_id] = array(
                                        'label' => wc_cart_totals_coupon_label($coupon, false),
                                        'value' => $discount_amount_html
                                    );
                                    $index_id++;
                                }
                            }

                            /*
                             * Total Shipping
                             */
                            if (in_array('shipping', $include)) {
                                if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {

                                    $packages = WC()->shipping()->get_packages();
                                    $first = true;

                                    $index_id = 0;
                                    foreach ($packages as $i => $package) {
                                        $chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
                                        if ($chosen_method) {
                                            $product_names = array();
                                            if (count($packages) > 1) {
                                                foreach ($package['contents'] as $item_id => $values) {
                                                    $product_names[$item_id] = $values['data']->get_name() . ' &times;' . $values['quantity'];
                                                }
                                                $product_names = apply_filters('woocommerce_shipping_package_details_array', $product_names, $package);
                                            }

                                            $available_methods = $package['rates'];
                                            $show_package_details = count($packages) > 1;
                                            $package_details = implode(', ', $product_names);
                                            $package_name = apply_filters('woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf(_x('Shipping %d', 'shipping packages', 'woocommerce'), ( $i + 1)) : _x('Shipping', 'shipping packages', 'woocommerce'), $i, $package);
                                            $formatted_destination = WC()->countries->get_formatted_address($package['destination'], ', ');

                                            $item_totals['shipping_' . $index_id] = array(
                                                'label' => wp_kses_post($package_name),
                                                'value' => ''
                                            );

                                            if ($available_methods) {
                                                foreach ($available_methods as $method) {
                                                    if ($method->get_id() == $chosen_method) {
                                                        $item_totals['shipping_' . $index_id]['value'] .= "<div>" . wc_cart_totals_shipping_method_label($method) . "</div>";
                                                    }
                                                }
                                            }

                                            if (in_array('shipping_destination', $include)) {
                                                if ($formatted_destination) {
                                                    $item_totals['shipping_' . $index_id]['value'] .= "<div size='8px' class='e2pdf-wc-cart-total-shipping-destination'>" . sprintf(esc_html__('Shipping to %s.', 'woocommerce') . ' ', esc_html($formatted_destination)) . "</div>";
                                                } else {
                                                    $item_totals['shipping_' . $index_id]['value'] .= "<div size='8px' class='e2pdf-wc-cart-total-shipping-destination'>" . wp_kses_post(apply_filters('woocommerce_shipping_estimate_html', __('Shipping options will be updated during checkout.', 'woocommerce'))) . '</div>';
                                                }
                                            }

                                            if (in_array('shipping_package', $include)) {
                                                if ($show_package_details) {
                                                    $item_totals['shipping_' . $index_id]['value'] .= "<div size='8px' class='e2pdf-wc-cart-total-shipping-package'>" . esc_html($package_details) . '</div>';
                                                }
                                            }

                                            $index_id++;
                                            $first = false;
                                        }
                                    }
                                } elseif (WC()->cart->needs_shipping() && 'yes' === get_option('woocommerce_enable_shipping_calc')) {
                                    
                                }
                            }

                            /*
                             * Total Fees
                             */
                            if (in_array('fees', $include)) {
                                $index_id = 0;
                                foreach (WC()->cart->get_fees() as $fee) {
                                    $cart_totals_fee_html = WC()->cart->display_prices_including_tax() ? wc_price($fee->total + $fee->tax) : wc_price($fee->total);
                                    $item_totals['fee_' . $index_id] = array(
                                        'label' => esc_html($fee->name),
                                        'value' => apply_filters('woocommerce_cart_totals_fee_html', $cart_totals_fee_html, $fee)
                                    );
                                    $index_id++;
                                }
                            }

                            /*
                             * Total Taxes
                             */
                            if (in_array('taxes', $include)) {
                                if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) {
                                    $taxable_address = WC()->customer->get_taxable_address();
                                    $estimated_text = '';

                                    if (WC()->customer->is_customer_outside_base() && !WC()->customer->has_calculated_shipping()) {
                                        /* translators: %s location. */
                                        $estimated_text = sprintf(' <small>' . esc_html__('(estimated for %s)', 'woocommerce') . '</small>', WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[$taxable_address[0]]);
                                    }

                                    if ('itemized' === get_option('woocommerce_tax_total_display')) {
                                        $index_id = 0;
                                        foreach (WC()->cart->get_tax_totals() as $code => $tax) {
                                            $item_totals['tax_' . $index_id] = array(
                                                'label' => esc_html($tax->label) . $estimated_text,
                                                'value' => wp_kses_post($tax->formatted_amount)
                                            );
                                            $index_id++;
                                        }
                                    } else {
                                        $item_totals['tax_or_vat'] = array(
                                            'label' => esc_html(WC()->countries->tax_or_vat()) . $estimated_text,
                                            'value' => apply_filters('woocommerce_cart_totals_taxes_total_html', wc_price(WC()->cart->get_taxes_total()))
                                        );
                                    }
                                }
                            }

                            /*
                             * Total Total
                             */
                            if (in_array('total', $include)) {
                                $total = WC()->cart->get_total();
                                if (wc_tax_enabled() && WC()->cart->display_prices_including_tax()) {
                                    $tax_string_array = array();
                                    $cart_tax_totals = WC()->cart->get_tax_totals();

                                    if (get_option('woocommerce_tax_total_display') === 'itemized') {
                                        foreach ($cart_tax_totals as $code => $tax) {
                                            $tax_string_array[] = sprintf('%s %s', $tax->formatted_amount, $tax->label);
                                        }
                                    } elseif (!empty($cart_tax_totals)) {
                                        $tax_string_array[] = sprintf('%s %s', wc_price(WC()->cart->get_taxes_total(true, true)), WC()->countries->tax_or_vat());
                                    }

                                    if (!empty($tax_string_array)) {
                                        $taxable_address = WC()->customer->get_taxable_address();
                                        $estimated_text = WC()->customer->is_customer_outside_base() && !WC()->customer->has_calculated_shipping() ? sprintf(' ' . __('estimated for %s', 'woocommerce'), WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[$taxable_address[0]]) : '';
                                        $total .= '<small class="includes_tax"> ('
                                                . esc_html__('includes', 'woocommerce')
                                                . ' '
                                                . wp_kses_post(implode(', ', $tax_string_array))
                                                . esc_html($estimated_text)
                                                . ')</small>';
                                    }
                                }

                                $item_totals['total'] = array(
                                    'label' => __('Total', 'woocommerce'),
                                    'value' => apply_filters('woocommerce_cart_totals_order_total_html', $total)
                                );
                            }

                            $item_totals = apply_filters('e2pdf_model_shortcode_wc_cart_get_cart_totals', $item_totals, $atts, $value);
                            $post_meta = $item_totals;
                        } else {
                            if (method_exists(WC()->cart, $key)) {
                                if ($key == 'get_cart_subtotal' && $output == 'compound') {
                                    $post_meta = WC()->cart->$key(true);
                                } elseif ($key == 'get_total' && $output == 'edit') {
                                    $post_meta = WC()->cart->$key('edit');
                                } elseif ($key == 'get_taxes_total' && $output) {
                                    $compound = true;
                                    $display = true;
                                    $output_data = explode("|", $output);
                                    if (in_array('nocompound', $output_data)) {
                                        $compound = false;
                                    }
                                    if (in_array('nodisplay', $output_data)) {
                                        $display = false;
                                    }
                                    $post_meta = WC()->cart->$key($compound, $display);
                                } else {
                                    $post_meta = WC()->cart->$key();
                                }
                            }
                        }
                    }
                } elseif ($terms && $names) {
                    $post_terms = wp_get_post_terms($id, $key, array('fields' => 'names'));
                    if (!is_wp_error($post_terms) && is_array($post_terms)) {
                        if ($implode === false) {
                            $implode = ', ';
                        }
                        $post_meta = implode($implode, $post_terms);
                    }
                } elseif ($terms) {
                    $post_terms = wp_get_post_terms($id, $key);
                    if (!is_wp_error($post_terms)) {
                        $post_meta = json_decode(json_encode($post_terms), true);
                    }
                } else {
                    $post_meta = get_post_meta($id, $key, true);
                }

                if ($post_meta !== false) {

                    if (is_object($post_meta)) {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_cart_object', $post_meta, $atts);
                    }

                    if ($explode && !is_array($post_meta)) {
                        $post_meta = explode($explode, $post_meta);
                    }

                    if (is_array($post_meta)) {
                        $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_cart_array', $post_meta, $atts);
                    }

                    if ((is_array($post_meta) || is_object($post_meta)) && $path !== false) {
                        $path_parts = explode('.', $path);
                        $path_value = &$post_meta;
                        $found = true;
                        foreach ($path_parts as $path_part) {
                            if (is_array($path_value) && isset($path_value[$path_part])) {
                                $path_value = &$path_value[$path_part];
                            } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                                $path_value = &$path_value->$path_part;
                            } else {
                                $found = false;
                                break;
                            }
                        }
                        if ($found) {
                            $post_meta = $path_value;
                        } else {
                            $post_meta = '';
                        }
                    }

                    if ($attachment_url || $attachment_image_url) {
                        if (!is_array($post_meta)) {
                            if (strpos($post_meta, ',') !== false) {
                                $post_meta = explode(',', $post_meta);
                                if ($implode === false) {
                                    $implode = ',';
                                }
                            }
                        }

                        if (is_array($post_meta)) {
                            $attachments = array();
                            foreach ($post_meta as $post_meta_part) {
                                if (!is_array($post_meta_part)) {
                                    if ($attachment_url) {
                                        $image = wp_get_attachment_url($post_meta_part);
                                    } elseif ($attachment_image_url) {
                                        $image = wp_get_attachment_image_url($post_meta_part, $size);
                                    }
                                    if ($image) {
                                        $attachments[] = $image;
                                    }
                                }
                            }
                            $post_meta = $attachments;
                        } else {
                            if ($attachment_url) {
                                $image = wp_get_attachment_url($post_meta);
                            } elseif ($attachment_image_url) {
                                $image = wp_get_attachment_image_url($post_meta, $size);
                            }
                            if ($image) {
                                $post_meta = $image;
                            } else {
                                $post_meta = '';
                            }
                        }
                    }

                    if ($wc_price) {
                        if (is_array($post_meta) || is_object($post_meta)) {
                            
                        } else {
                            if (isset($atts['currency'])) {
                                $post_meta = wc_price($post_meta, $atts['currency']);
                            } else {
                                $post_meta = wc_price($post_meta);
                            }
                        }
                    }

                    if ($raw) {
                        $response = $post_meta;
                    } else {
                        if (is_array($post_meta)) {
                            if ($implode !== false) {
                                if (!$this->helper->is_multidimensional($post_meta)) {
                                    $response = implode($implode, $post_meta);
                                } else {
                                    $response = serialize($post_meta);
                                }
                            } else {
                                $response = serialize($post_meta);
                            }
                        } elseif (is_object($post_meta)) {
                            $response = serialize($post_meta);
                        } else {
                            $response = $post_meta;
                        }
                    }
                }
            }
        }

        if ($raw) {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wc_cart_raw', $response, $atts, $value);
        } else {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wc_cart_response', $response, $atts, $value);
        }
    }

    /**
     * [e2pdf-wc-cart] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_wc_customer($atts = array(), $value = '') {

        $post_meta = false;
        $response = '';

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_wc_customer', $atts);

        $key = isset($atts['key']) ? $atts['key'] : false;
        $path = isset($atts['path']) ? $atts['path'] : false;
        $explode = isset($atts['explode']) ? $atts['explode'] : false;
        $implode = isset($atts['implode']) ? $atts['implode'] : false;
        $size = isset($atts['size']) ? $atts['size'] : 'thumbnail';
        $attachment_url = isset($atts['attachment_url']) && $atts['attachment_url'] == 'true' ? true : false;
        $attachment_image_url = isset($atts['attachment_image_url']) && $atts['attachment_image_url'] == 'true' ? true : false;
        $raw = isset($atts['raw']) && $atts['raw'] == 'true' ? true : false;
        $output = isset($atts['output']) ? $atts['output'] : false;

        $cusomter_fields = apply_filters('e2pdf_model_shortcode_wc_customer_fields', array(
            'get_taxable_address',
            'is_vat_exempt',
            'get_is_vat_exempt',
            'has_calculated_shipping',
            'get_calculated_shipping',
            'get_avatar_url',
            'get_username',
            'get_email',
            'get_first_name',
            'get_last_name',
            'get_display_name',
            'get_role',
            'get_date_created',
            'get_date_modified',
            'get_billing',
            'get_billing_first_name',
            'get_billing_last_name',
            'get_billing_company',
            'get_billing_address',
            'get_billing_address_1',
            'get_billing_address_2',
            'get_billing_city',
            'get_billing_state',
            'get_billing_postcode',
            'get_billing_country',
            'get_billing_email',
            'get_billing_phone',
            'get_shipping',
            'get_shipping_first_name',
            'get_shipping_last_name',
            'get_shipping_company',
            'get_shipping_address',
            'get_shipping_address_1',
            'get_shipping_address_2',
            'get_shipping_city',
            'get_shipping_state',
            'get_shipping_postcode',
            'get_shipping_country',
            'get_is_paying_customer',
            'get_formatted_shipping_address',
            'get_formatted_billing_address'
        ));

        if (in_array($key, $cusomter_fields)) {
            if (function_exists('WC') && isset(WC()->customer) && WC()->customer && is_object(WC()->customer)) {
                if ($key == 'get_formatted_shipping_address') {
                    if (isset(WC()->countries) && isset(WC()->customer)) {
                        $post_meta = WC()->countries->get_formatted_address(WC()->customer->data['shipping']);
                    }
                } elseif ($key == 'get_formatted_billing_address') {
                    if (isset(WC()->countries) && isset(WC()->customer)) {
                        $post_meta = WC()->countries->get_formatted_address(WC()->customer->data['billing']);
                    }
                } else {
                    if (method_exists(WC()->customer, $key)) {
                        if ($output == 'edit') {
                            $post_meta = WC()->customer->$key('edit');
                        } else {
                            $post_meta = WC()->customer->$key();
                        }
                    }
                }
            }
        }

        if ($post_meta !== false) {

            if (is_object($post_meta)) {
                $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_customer_object', $post_meta, $atts);
            }

            if ($explode && !is_array($post_meta)) {
                $post_meta = explode($explode, $post_meta);
            }

            if (is_array($post_meta)) {
                $post_meta = apply_filters('e2pdf_model_shortcode_e2pdf_wc_customer_array', $post_meta, $atts);
            }

            if ((is_array($post_meta) || is_object($post_meta)) && $path !== false) {
                $path_parts = explode('.', $path);
                $path_value = &$post_meta;
                $found = true;
                foreach ($path_parts as $path_part) {
                    if (is_array($path_value) && isset($path_value[$path_part])) {
                        $path_value = &$path_value[$path_part];
                    } elseif (is_object($path_value) && isset($path_value->$path_part)) {
                        $path_value = &$path_value->$path_part;
                    } else {
                        $found = false;
                        break;
                    }
                }
                if ($found) {
                    $post_meta = $path_value;
                } else {
                    $post_meta = '';
                }
            }

            if ($attachment_url || $attachment_image_url) {
                if (!is_array($post_meta)) {
                    if (strpos($post_meta, ',') !== false) {
                        $post_meta = explode(',', $post_meta);
                        if ($implode === false) {
                            $implode = ',';
                        }
                    }
                }

                if (is_array($post_meta)) {
                    $attachments = array();
                    foreach ($post_meta as $post_meta_part) {
                        if (!is_array($post_meta_part)) {
                            if ($attachment_url) {
                                $image = wp_get_attachment_url($post_meta_part);
                            } elseif ($attachment_image_url) {
                                $image = wp_get_attachment_image_url($post_meta_part, $size);
                            }
                            if ($image) {
                                $attachments[] = $image;
                            }
                        }
                    }
                    $post_meta = $attachments;
                } else {
                    if ($attachment_url) {
                        $image = wp_get_attachment_url($post_meta);
                    } elseif ($attachment_image_url) {
                        $image = wp_get_attachment_image_url($post_meta, $size);
                    }
                    if ($image) {
                        $post_meta = $image;
                    } else {
                        $post_meta = '';
                    }
                }
            }

            if ($raw) {
                $response = $post_meta;
            } else {
                if (is_array($post_meta)) {
                    if ($implode !== false) {
                        if (!$this->helper->is_multidimensional($post_meta)) {
                            $response = implode($implode, $post_meta);
                        } else {
                            $response = serialize($post_meta);
                        }
                    } else {
                        $response = serialize($post_meta);
                    }
                } elseif (is_object($post_meta)) {
                    $response = serialize($post_meta);
                } else {
                    $response = $post_meta;
                }
            }
        }

        if ($raw) {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wc_customer_raw', $response, $atts, $value);
        } else {
            return apply_filters('e2pdf_model_shortcode_e2pdf_wc_customer_response', $response, $atts, $value);
        }
    }

    /**
     * [e2pdf-filter] shortcode
     * 
     * @param array $atts - Attributes
     * @param string $value - Content
     */
    function e2pdf_filter($atts = array(), $value = '') {

        $atts = apply_filters('e2pdf_model_shortcode_e2pdf_filter_atts', $atts);

        if ($value) {
            $search = array('[', ']', '&#091;', '&#093;');
            $replace = array('&#91;', '&#93;', '&#91;', '&#93;');
            $value = str_replace($search, $replace, $value);
            $value = esc_attr($value);
        }

        return $value;
    }

}
