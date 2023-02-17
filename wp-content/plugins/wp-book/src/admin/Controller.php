<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link  http://example.com
 * @since 1.0.0
 */

namespace WPBook\admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @author Your Name <email@example.com>
 */
class Controller
{

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $wp_book    The ID of this plugin.
     */
    private $wp_book;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $version    The current version of this plugin.
     */
    private $version;

    private $mpdf;

    private $templates;

    /**
     * Initialize the class and set its properties.
     *
     * @since 1.0.0
     * @param string $wp_book The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( $wp_book, $version ) 
    {

        $this->wp_book = $wp_book;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_styles() 
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in WPBook_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The WPBook_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->wp_book, plugins_url('wp-book/assets/css/wpbook.css'), array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() 
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in WPBook_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The WPBook_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style('select2', plugins_url('wp-book/assets/css/node_modules/select2/dist/css/select2.min.css'));
        wp_enqueue_style('select2', plugins_url('wp-book/assets/css/node_modules/jquery-ui/themes/base/accordion.css'));
        wp_enqueue_style('select2', plugins_url('wp-book/assets/css/node_modules/jquery-ui/themes/base/sortable.css'));

        wp_enqueue_script('select2', plugins_url('wp-book/assets/js/node_modules/select2/dist/js/select2.min.js'), array('jquery'));
        wp_enqueue_script($this->wp_book, plugins_url('wp-book/assets/js/wpbook.min.js'), array( 'jquery', 'select2', 'jquery-ui-sortable', 'jquery-ui-accordion' ), $this->version, false);

        $params = array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce($this->wp_book),
        );
        wp_localize_script($this->wp_book, 'wp_book_object', $params);

    }

    public function wp_book_settings_page() 
    {
        add_options_page(
            __('WP Book', 'wp-book'),
            __('WP Book', 'wp-book'),
            'manage_options',
            $this->wp_book,
            array( $this, 'wp_book_page' ),
            $this->wp_book
        );
    }

    public function wp_book_page()
    {
        ?>
     <div class="wrap">
      <h1><?php _e('Download PDF', 'wp-book');?></h1>

      <div class="wp-book-form-wrapper">
				
				<div class="container-panel">

					<form class="wp-book-form" id="wp-book-form">
        <?php $this->render_load_filters();?>
        <?php $this->render_post_container();?>
					</form>

				</div>

      </div>
     </div>
        <?php 
    }

    public function render_load_filters()
    {
        ?>
     <div class="left-panel">

      <h3><?php _e('Post Filters');?></h3>

      <div class="form-group">
				<label><?php _e('Post Types', 'wp-book');?></label>
				<select class="form-control filter-post-type wp-book-select2" onchange="load_material();">
        <?php foreach ( get_post_types(array( 'public' => true ), 'names') as $post_type ) { ?>
					<option <?php if($post_type == 'post' ) {?> selected="selected" <?php 
}?>><?php echo $post_type; ?></option>
        <?php 
}?>
				</select>
      </div>

      <div class="form-group">
				<label><?php _e('Number of posts', 'wp-book');?></label>
				<input type="number" value="10" class="form-control filter-post-number" onchange="load_material();" />
      </div>

      <div class="form-group">
				<label><?php _e('Order By', 'wp-book');?></label>
				<select class="form-control filter-post-order wp-book-select2" onchange="load_material();">
					<option selected="selected" value="ASC"><?php _e('ASC', 'wp-book');?></option>
					<option  value="DESC"><?php _e('DESC', 'wp-book');?></option>
				</select>
      </div>

      <div class="form-group">
				<label><?php _e('Sort', 'wp-book');?></label>
				<select class="form-control filter-post-order-by wp-book-select2" onchange="load_material();">
					<option selected="selected"><?php _e('None');?></option>
					<option value="ID"><?php _e('ID', 'wp-book');?></option>
					<option value="author"><?php _e('Author', 'wp-book');?></option>
					<option value="title"><?php _e('Title', 'wp-book');?></option>
					<option value="name"><?php _e('Name', 'wp-book');?></option>
					<option value="type"><?php _e('Type', 'wp-book');?></option>
					<option value="date"><?php _e('Published Date', 'wp-book');?></option>
					<option value="modified"><?php _e('Modified Time', 'wp-book');?></option>
					<option value="parent"><?php _e('Parent', 'wp-book');?></option>
					<option value="rand"><?php _e('Random', 'wp-book');?></option>
				</select>
      </div>

      <br/>

      <h3><?php _e('PDF Options');?></h3>

      <div class="form-group">
				<label><?php _e('Page Format', 'wp-book'); ?></label>
				<?php $page_sizes = $this->get_supported_mpdf_page_sizes(); ?>
				<select class="form-control wp-book-select2" name="page_format">
				<?php foreach ( $page_sizes as $page_size => $page_values ){ ?>
					<option value="<?php echo $page_size;?>"><?php echo $page_size;?> - <?php echo $page_values[0];?> x <?php echo $page_values[1];?></option>
				<?php 
}?>
				</select>
      </div>

      <div class="form-group">
				<label><?php _e('Page Orientation', 'wp-book');?></label>
				<?php $page_orientations = $this->get_supported_mpdf_page_orientation(); ?>
				<select class="form-control wp-book-select2" name="page_orientations">
				<?php foreach ( $page_orientations as $page_orientation => $page_orientation_value ){ ?>
					<option value="<?php echo $page_orientation;?>"><?php echo $page_orientation_value;?></option>
				<?php 
}?>
				</select>
      </div>

      <div class="form-group">
				<label><?php _e('Apply page breaks after each posts', 'wp-book');?></label>
				<input type="checkbox" name="page_breaks" checked="checked">
      </div>

      <div class="form-group">
				<label><?php _e('Header', 'wp-book');?></label>
				<textarea class="form-control" name="header"></textarea>
				<small>{PAGENO} , {DATE j-m-Y}</small>
      </div>

      <div class="form-group">
				<label><?php _e('Footer', 'wp-book');?></label>
				<textarea class="form-control" name="footer"></textarea>
				<small>{PAGENO} , {DATE j-m-Y}</small>
      </div>

      <div class="form-group">
				<label><?php _e('Template', 'wp-book'); ?></label>
				<?php $templates = $this->get_templates(); ?>
				<select class="form-control wp-book-select2" name="page_template">
				<?php foreach ( $templates as $template_name => $template_value ){ ?>
					<option value="<?php echo $template_name;?>"><?php echo $template_name;?></option>
				<?php 
}?>
				</select>
      </div>

     </div>
        <?php
    }

    public function render_post_container()
    {
        ?>
     <div class="right-panel">
      <h3><?php _e('PDF Material');?></h3>
      <div class="<?php echo $this->wp_book;?>-pdf-material-stage"></div>
      <input type="button" class="button button-primary" id="generate_book" value="<?php _e('Generate Book', 'wp-book');?>" />
      <a class="button button-primary download_book" target="_blank" href="#"><?php _e('Download PDF', 'wp-book');?></a>
     </div>
        <?php
    }

    public function load_posts_for_print()
    {
        check_ajax_referer($this->wp_book, 'security');

        $post_type = sanitize_text_field($_POST['post_type']);
        $posts_per_page = sanitize_text_field($_POST['posts_per_page']);
        $order = sanitize_text_field($_POST['order']);
        $order_by = sanitize_text_field($_POST['order_by']);

        $args = array(
        'post_type' => $post_type,
        'posts_per_page' => $posts_per_page,
        'order' => $order,
        'order_by' => $order_by
        );
        $query = get_posts($args);
        header('Content-Type: application/json');
        echo json_encode($query);
        
        die;
    }

    public function generate_book()
    {
        check_ajax_referer($this->wp_book, 'security');

        $$output = array();
        $pdf = $this->pdf_sanitize_array($_POST['material']);

        $pdf_options = array(
        'format' => $pdf['options']['page_format'],
        'orientation' => $pdf['options']['page_orientations'],
        );

        $upload_dir = $this->get_wp_book_upload_dir();
        $upload_url = $this->get_wp_book_upload_url();

        try {
            $this->mpdf = new \Mpdf\Mpdf($pdf_options);

            $template = $pdf['options']['page_template'];

            $stylesheet = $this->build_style($template);
            $this->mpdf->WriteHTML($stylesheet, 1);

            if($pdf['options']['header'] != '' ) {
                $this->mpdf->SetHTMLHeader($pdf['options']['header']);
            }

            if($pdf['options']['footer'] != '' ) {
                $this->mpdf->SetHTMLFooter($pdf['options']['footer']);
            }

            foreach( $pdf['material'] as $material ){
                $post = get_post($material);
                $this->mpdf->WriteHTML('<h2>' .$post->post_title .'</h2>');
                $this->mpdf->WriteHTML('<p>' . $post->post_content . '</p>');

                if($pdf['options']['page_breaks'] == 'on' ) {
                    $this->mpdf->AddPage();
                }

            }

            $file_name = md5(microtime()) . '.pdf';

            $this->mpdf->Output($upload_dir . DIRECTORY_SEPARATOR . $file_name, 'F');

            $output['file_name'] = $file_name;
            $output['upload_url'] = $upload_url .'/'. $file_name;
            $output['result'] = 'success';
            $output['notice_class'] = 'notice-success';
            $output['message'] = 'File Created Successfully';

        } catch (\Mpdf\MpdfException $e) {
            $output['result'] = 'failed';
            $output['notice_class'] = 'notice-error';
            $output['message'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($output);

        die;
    }

    //Helper
    public function get_wp_book_upload_dir()
    {
        $upload_dir   = wp_upload_dir();

        if (! empty( $upload_dir['basedir'] ) ) {
            $dirname = $upload_dir['basedir'].'/'.$this->wp_book;
            if (! file_exists($dirname) ) {
                wp_mkdir_p($dirname);
            }
            return $dirname;
        }    
    }

    //Helper
    public function get_wp_book_upload_url()
    {
        $upload_dir   = wp_upload_dir();
        return $upload_dir['baseurl'].'/'.$this->wp_book;
    }
    
    //Helper 
    public function sanitize_array( $array = array() )
    {        
        $output = array();
        if (is_array($array) ) {
            foreach ( $array as $key => $value ) {
                foreach( $value as $k => $v ){
                    $output[$value['name']] = sanitize_text_field($v);
                }
            }
        }
        return $output;
    }

    //Helper
    public function pdf_sanitize_array( $array = array() )
    {
        $output = array();
        if (is_array($array) ) {
            foreach ( $array as $key => $value ) {
                if ($value['name'] == 'material' ) {
                    $output['material'][] = sanitize_text_field($value['value']);
                }else{
                    $output['options'][$value['name']] = sanitize_text_field($value['value']);
                }
            }
        }
        return $output;
    }

    public function set_templates( $templates )
    {
        $this->templates = $templates;
    }

    public function get_templates()
    {
        return $this->templates;
    }

    public function build_style( $template )
    {
        $styles = $this->templates[$template];

        $css = '';
        foreach( $styles as $style_key => $style_value ){
            $css .= $style_key . '{';
            foreach( $style_value as $k => $v ){
                $css .= $k . ':' . $v . ';';
            }
            $css .= '}';
        }

        return $css;
    }

    //Helper 
    public function get_supported_mpdf_page_orientation()
    {
        return array(
        'P' => __('Portrait', 'wp-book'),
        'L' => __('landscape', 'wp-book')
        );
    }

    //Helper 
    public function get_supported_mpdf_page_sizes()
    {
        return array ( 
        'A4' => [595.28, 841.89],
        '4A0' => [4767.87, 6740.79],
        '2A0' => [3370.39, 4767.87],
        'A0' => [2383.94, 3370.39],
        'A1' => [1683.78, 2383.94],
        'A2' => [1190.55, 1683.78],
        'A3' => [841.89, 1190.55],
        'A5' => [419.53, 595.28],
        'A6' => [297.64, 419.53],
        'A7' => [209.76, 297.64],
        'A8' => [147.40, 209.76],
        'A9' => [104.88, 147.40],
        'A10' => [73.70, 104.88],
        'B0' => [2834.65, 4008.19],
        'B1' => [2004.09, 2834.65],
        'B2' => [1417.32, 2004.09],
        'B3' => [1000.63, 1417.32],
        'B4' => [708.66, 1000.63],
        'B5' => [498.90, 708.66],
        'B6' => [354.33, 498.90],
        'B7' => [249.45, 354.33],
        'B8' => [175.75, 249.45],
        'B9' => [124.72, 175.75],
        'B10' => [87.87, 124.72],
        'C0' => [2599.37, 3676.54],
        'C1' => [1836.85, 2599.37],
        'C2' => [1298.27, 1836.85],
        'C3' => [918.43, 1298.27],
        'C4' => [649.13, 918.43],
        'C5' => [459.21, 649.13],
        'C6' => [323.15, 459.21],
        'C7' => [229.61, 323.15],
        'C8' => [161.57, 229.61],
        'C9' => [113.39, 161.57],
        'C10' => [79.37, 113.39],
        'RA0' => [2437.80, 3458.27],
        'RA1' => [1729.13, 2437.80],
        'RA2' => [1218.90, 1729.13],
        'RA3' => [864.57, 1218.90],
        'RA4' => [609.45, 864.57],
        'SRA0' => [2551.18, 3628.35],
        'SRA1' => [1814.17, 2551.18],
        'SRA2' => [1275.59, 1814.17],
        'SRA3' => [907.09, 1275.59],
        'SRA4' => [637.80, 907.09],
        'LETTER' => [612.00, 792.00],
        'LEGAL' => [612.00, 1008.00],
        'LEDGER' => [1224.00, 792.00],
        'TABLOID' => [792.00, 1224.00],
        'EXECUTIVE' => [521.86, 756.00],
        'FOLIO' => [612.00, 936.00],
        'B' => [362.83, 561.26], // 'B' format paperback size 128x198mm
        'A' => [314.65, 504.57], // 'A' format paperback size 111x178mm
        'DEMY' => [382.68, 612.28], // 'Demy' format paperback size 135x216mm
        'ROYAL' => [433.70, 663.30], // 'Royal' format paperback size 153x234mm
        );
    }

}
