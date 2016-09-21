<?php
/*
Plugin Name: CP Google Maps 
Version: 5.0.1
Author: <a href="http://www.codepeople.net">CodePeople</a>
Plugin URI: http://wordpress.dwbooster.com/content-tools/codepeople-post-map
Description: CP Google Maps Allows to associate geocode information to posts and display it on map. CP Google Maps display the post list as markers on map. The scale of map is determined by the markers, to display distant points is required to load a map with smaller scales. To get started: 1) Click the "Activate" link to the left of this description. 2) Go to your <a href="options-general.php?page=codepeople-post-map.php">CP Google Maps configuration</a> page and configure the maps settings. 3) Go to post edition page to enter the geolocation information.
 */

define('CPM_PLUGIN_DIR', WP_PLUGIN_DIR."/".dirname(plugin_basename(__FILE__)));
define('CPM_PLUGIN_URL', plugins_url()."/".dirname(plugin_basename(__FILE__)));

require (CPM_PLUGIN_DIR.'/include/functions.php');

// Create  a CPM object that contain main plugin logic
register_activation_hook(__FILE__, 'codepeople_post_map_regiter');
if(!function_exists('codepeople_post_map_regiter')){
    function codepeople_post_map_regiter(){
        $cpm_master_obj = new CPM;
        $cpm_master_obj->set_default_configuration(true);
    }
}

add_action( 'init', 'cpm_init', 0);
add_action( 'admin_init', 'cpm_admin_init' );

function cpm_rational_to_decimal( $rational ) {
	$parts = explode('/', $rational);
	return $parts[0] / ( $parts[1] ? $parts[1] : 1);
}

function cpm_add_attachment( $post_id ){
	global $cpm_master_obj;
	
	if( !$cpm_master_obj->get_configuration_option('exif_information') ){ 
		return;
	}
	
	if ( 0 != $post_id )
		$path = get_attached_file($post_id);
	if( !empty( $path ) ){
		$size = getimagesize( $path, $info );
		if ( is_callable( 'exif_read_data' ) && in_array( $size[2], array( IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM ) ) ) {
			$exif_data = exif_read_data( $path );
			
			$point_data = array(
                            'thumbnail' => wp_get_attachment_url( $post_id )
                          );
			if ( isset( $exif_data['GPSLatitudeRef'] ) ) $ref = ( 'N' == strtoupper( $exif_data['GPSLatitudeRef'] ) ) ? 1 : -1;
			else $ref = 1;
			
			if ( isset( $exif_data['GPSLatitude'] ) ) {
				$rational = $exif_data['GPSLatitude'];
				$degrees = cpm_rational_to_decimal( $rational[0] );
				$minutes = cpm_rational_to_decimal( $rational[1] );
				$seconds = cpm_rational_to_decimal( $rational[2] );
				$point_data['latitude'] = $ref * ( $degrees + $minutes / 60 + $seconds  / 3600 );
			}
			
			if ( isset( $exif_data['GPSLongitudeRef'] ) ) $ref = ( 'E' == strtoupper( $exif_data['GPSLongitudeRef'] ) ) ? 1 : -1;
			else $ref = 1;
			
			if ( isset( $exif_data['GPSLongitude'] ) ) {
				$rational = $exif_data['GPSLongitude'];
				$degrees = cpm_rational_to_decimal( $rational[0] );
				$minutes = cpm_rational_to_decimal( $rational[1] );
				$seconds = cpm_rational_to_decimal( $rational[2] );
				$point_data['longitude'] = $ref * ( $degrees + $minutes / 60 + $seconds / 3600 );
			}
			
			if( isset( $point_data[ 'latitude' ] ) && isset( $point_data[ 'longitude' ] ) ) {
				add_post_meta( $post_id, 'cpm_point', array( $point_data ), TRUE );
			}	
		}		
	}	
}

function cpm_admin_init(){
	global $cpm_master_obj;
	
	load_plugin_textdomain( 'codepeople-post-map', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );	
	
	// Set default configuration
	$cpm_master_obj->set_default_configuration();	
	
	// Insert the map's insertion form below the posts and pages editor
	$form_title = __('Associate an address to the post for Google Maps association', 'codepeople-post-map');
	$post_types = $cpm_master_obj->get_configuration_option('post_type');
    
    foreach($post_types as $post_type){
        add_meta_box('codepeople_post_map_form', $form_title, array($cpm_master_obj, 'insert_form'), $post_type, 'normal');
    }
	add_action('save_post', array(&$cpm_master_obj, 'save_map'));
	
	$plugin = plugin_basename(__FILE__);
	add_filter('plugin_action_links_'.$plugin, array(&$cpm_master_obj, 'customizationLink'));
}

function cpm_init(){

	global $cpm_master_obj, $cpm_objs;
	$cpm_master_obj = new CPM;
	$cpm_objs = array();
	
	add_action( 'widgets_init', 'cpm_load_widgets' );
	add_shortcode('codepeople-post-map', array(&$cpm_master_obj, 'replace_shortcode'));
	add_action( 'the_post', 'cpm_populate_points' );
	add_action( 'wp_footer', 'cpm_print_points' );
	// Allow to search for metadata associated to the post
	add_action('posts_where_request', array(&$cpm_master_obj, 'search'));	
	
	// Actions and filter to extract 
	add_action( 'add_attachment', 'cpm_add_attachment' ); // $post_id
    add_action( 'loop_start', 'cpm_loop_start' );
    add_action( 'loop_end', 'cpm_loop_end' );
}

if( !function_exists( 'cpm_loop_start' ) )
    {
        function cpm_loop_start()
        {
            global $cpm_in_loop;
            $cpm_in_loop = true;
        }
    }
if( !function_exists( 'cpm_loop_end' ) )
    {
        function cpm_loop_end()
        {
            global $cpm_in_loop;
            $cpm_in_loop = false;
        }
    }
if (!function_exists("cpm_settings")) { 
		function cpm_settings() { 
			global $cpm_master_obj; 
			
			if (!isset($cpm_master_obj)) { 
				return; 
			} 
			
			if (function_exists('add_options_page')) { 
				add_options_page('CodePeople Post Map', 'CodePeople Post Map', 'manage_options', basename(__FILE__), array(&$cpm_master_obj, 'settings_page')); 
			} 
		}    
	}
	
add_action('admin_enqueue_scripts', array(&$cpm_master_obj, 'load_admin_resources'), 1);
add_action('wp_head', array(&$cpm_master_obj, 'load_header_resources'), 10);
add_action('admin_menu', 'cpm_settings');

if( !function_exists( 'cpm_load_widgets' ) ){
	function cpm_load_widgets(){
		register_widget( 'CP_PostMapWidget' );
	}
}

if( !function_exists( 'cpm_populate_points' ) ){
	function cpm_populate_points( $post ){
		global $cpm_master_obj;
		$cpm_master_obj->populate_points( $post->ID );
	}
}

if( !function_exists( 'cpm_print_points' ) ){
	function cpm_print_points(){
		global $cpm_objs, $cpm_master_obj;
        
        foreach( $cpm_objs as $cpm_obj ){
            if( !empty( $cpm_obj->multiple ) )
            {
                $cpm_obj->points = $cpm_master_obj->points;
            }
			$cpm_obj->print_points();
		}
	}
}

?>