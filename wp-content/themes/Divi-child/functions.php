<?php
function divi_child_enqueue_parent_styles() {
    $theme = wp_get_theme('Divi');
    $version = $theme->get('Version');
    
	wp_enqueue_style( 'child-style',get_stylesheet_directory_uri().'/style.css'. 'Divi',wp_get_theme()->get( 'Version' ) 
);
}
add_action( 'wp_enqueue_scripts', 'divi_child_enqueue_parent_styles' );




// add font
function ocean_add_custom_fonts(){
    return array('tradego20','Wingdings','Regular','Semibold');
}
