<?php 
/**
 * CodePeople Post Map 
 * Version: 1.0.1
 * Author: CodePeople
 * Plugin URI: http://wordpress.dwbooster.com
*/

class CPM {
	//---------- VARIABLES ----------
	
	var $lang_array; // List of supported languages
	var $points = array(); // List of points to set on map
	var $checked_posts = array();
	var $points_str = ''; // List of points as javascript code
	var $map_id; // ID of map
    var $limit=0; // The number of pins allowed in map zero = unlimited
    var $defaultpost=''; // The post ID for centring the map, and display by default the infowindow
    var $extended = array();
    var $taxonomies = array(); // Taxonomies to use to display in the map's legend
    var $legend = false;
    var $legend_taxonomy = '';
    var $multiple = false;
    var $query_base = array( 
        'meta_query' => array(
            'relation' => 'OR',
            array(
              'key' => 'cpm_point'
            ),
            array(
              'key' => 'geo_latitude'
            ),
            array(
              'key' => 'geo_longitude'
            )
        ),
        
        'post_status' => 'publish',
        'orderby' => 'post_date', 
        'order' => 'DESC',
        'cache_results' => false,
        'fields' => 'ids'
    );
			
    
	//---------- CONSTRUCTOR ----------
	
	function __construct(){
		$this->map_id = "cpm_".wp_generate_password(6, false);
		$this->lang_array = array(
							"ar"=>__("ARABIC","codepeople-post-map"),
							"eu"=>__("BASQUE","codepeople-post-map"),
							"bg"=>__("BULGARIAN","codepeople-post-map"),
							"bn"=>__("BENGALI","codepeople-post-map"),
							"ca"=>__("CATALAN","codepeople-post-map"),
							"cs"=>__("CZECH","codepeople-post-map"),
							"da"=>__("DANISH","codepeople-post-map"),
							"de"=>__("GERMAN","codepeople-post-map"),
							"el"=>__("GREEK","codepeople-post-map"),
							"en"=>__("ENGLISH","codepeople-post-map"),
							"en-AU"=>__("ENGLISH (AUSTRALIAN)","codepeople-post-map"),
							"en-GB"=>__("ENGLISH (GREAT BRITAIN)","codepeople-post-map"),
							"es"=>__("SPANISH","codepeople-post-map"),
							"eu"=>__("BASQUE","codepeople-post-map"),
							"fa"=>__("FARSI","codepeople-post-map"),
							"fi"=>__("FINNISH","codepeople-post-map"),
							"fil"=>__("FILIPINO","codepeople-post-map"),
							"fr"=>__("FRENCH","codepeople-post-map"),
							"gl"=>__("GALICIAN","codepeople-post-map"),
							"gu"=>__("GUJARATI","codepeople-post-map"),
							"hi"=>__("HINDI","codepeople-post-map"),
							"hr"=>__("CROATIAN","codepeople-post-map"),
							"hu"=>__("HUNGARIAN","codepeople-post-map"),
							"id"=>__("INDONESIAN","codepeople-post-map"),
							"it"=>__("ITALIAN","codepeople-post-map"),
							"iw"=>__("HEBREW","codepeople-post-map"),
							"ja"=>__("JAPANESE","codepeople-post-map"),
							"kn"=>__("KANNADA","codepeople-post-map"),
							"ko"=>__("KOREAN","codepeople-post-map"),
							"lt"=>__("LITHUANIAN","codepeople-post-map"),
							"lv"=>__("LATVIAN","codepeople-post-map"),
							"ml"=>__("MALAYALAM","codepeople-post-map"),
							"mr"=>__("MARATHI","codepeople-post-map"),
							"nl"=>__("DUTCH","codepeople-post-map"),
							"no"=>__("NORWEGIAN","codepeople-post-map"),
							"or"=>__("ORIYA","codepeople-post-map"),
							"pl"=>__("POLISH","codepeople-post-map"),
							"pt"=>__("PORTUGUESE","codepeople-post-map"),
							"pt-BR"=>__("PORTUGUESE (BRAZIL)","codepeople-post-map"),
							"pt-PT"=>__("PORTUGUESE (PORTUGAL)","codepeople-post-map"),
							"ro"=>__("ROMANIAN","codepeople-post-map"),
							"ru"=>__("RUSSIAN","codepeople-post-map"),
							"sk"=>__("SLOVAK","codepeople-post-map"),
							"sl"=>__("SLOVENIAN","codepeople-post-map"),
							"sr"=>__("SERBIAN","codepeople-post-map"),
							"sv"=>__("SWEDISH","codepeople-post-map"),
							"tl"=>__("TAGALOG","codepeople-post-map"),
							"ta"=>__("TAMIL","codepeople-post-map"),
							"te"=>__("TELUGU","codepeople-post-map"),
							"th"=>__("THAI","codepeople-post-map"),
							"tr"=>__("TURKISH","codepeople-post-map"),
							"uk"=>__("UKRAINIAN","codepeople-post-map"),
							"vi"=>__("VIETNAMESE","codepeople-post-map"),
							"zh-CN"=>__("CHINESE (SIMPLIFIED)","codepeople-post-map"),
							"zh-TW"=>__("CHINESE (TRADITIONAL)","codepeople-post-map")
                                                
        ); 
	} // End __construct
	
	//---------- CREATE MAP ----------
	
	/**
	 * Save a map object in database
	 * called by the action save_post
	 */
	function save_map($post_id){
		// authentication checks

		// make sure data came from our meta box
		if (!isset($_POST['cpm_map_noncename']) || !wp_verify_nonce($_POST['cpm_map_noncename'],__FILE__)) return $post_id;

		// check user permissions
		if ($_POST['post_type'] == 'page'){
			if (!current_user_can('edit_page', $post_id)) return $post_id;
		}
		else{
			if (!current_user_can('edit_post', $post_id)) return $post_id;
		}
		
		if(wp_is_post_revision($post_id))
			$post_id = wp_is_post_revision($post_id);
		
		// authentication passed, save data
        
		$default_icon = $this->get_configuration_option('default_icon');
		$new_cpm_points = ( isset( $_POST['cpm_point'] ) ) ? $_POST['cpm_point'] : array();
        $valid_cpm_points = array();
		$cpm_points_order = ( isset( $_POST[ 'cpm_points_order' ] ) ) ? explode( '|', $_POST[ 'cpm_points_order' ] . 0 . '|' ) : array();
		foreach( $cpm_points_order as $index )
		{
			if( strlen( $index ) && isset( $new_cpm_points[ $index ] ) )
			{
				$point = $new_cpm_points[ $index ];
				$point['address'] = esc_attr($point['address']);
				$point['name'] = esc_attr($point['name']);
				$point['description'] = esc_attr( $point['description'] );
				
				// The address is required, if address is empty the couple: latitude, longitude must be defined
				if(!empty($point['address']) || (!empty($point['latitude']) && !empty($point['longitude']))){
					if(empty($point['icon'])) $point['icon'] = $default_icon;
					$point['icon'] = str_replace( CPM_PLUGIN_URL, '', $point['icon'] );
					$valid_cpm_points[] = $point;
				}
			}
		}
		$new_cpm_map = (isset($_POST['cpm_map'])) ? $_POST['cpm_map'] : array();
		delete_post_meta($post_id,'cpm_map');
        $new_cpm_map['single'] = (isset($new_cpm_map['single'])) ? true : false;
        if($new_cpm_map['single']){
            $new_cpm_map['zoompancontrol'] 	= ($new_cpm_map['zoompancontrol'] == true);
            $new_cpm_map['mousewheel'] 		= ($new_cpm_map['mousewheel'] == true);
            $new_cpm_map['typecontrol'] 	= ($new_cpm_map['typecontrol'] == true);
            $new_cpm_map['streetviewcontrol'] 	= ($new_cpm_map['streetviewcontrol'] == true);
            $new_cpm_map['dynamic_zoom'] 	= (isset($new_cpm_map['dynamic_zoom']) && $new_cpm_map['dynamic_zoom']) ? true : false;
            $new_cpm_map['route'] 	        = (isset($new_cpm_map['route']) && $new_cpm_map['route']) ? true : false;
            $new_cpm_map['polyline'] 	    = (isset($new_cpm_map['polyline']) && $new_cpm_map['polyline']) ? true : false;
            $new_cpm_map['show_default'] 	= (isset($new_cpm_map['show_default']) && $new_cpm_map['show_default']) ? true : false;
            $new_cpm_map['show_window'] 	= (isset($new_cpm_map['show_window']) && $new_cpm_map['show_window']) ? true : false;
            $new_cpm_map['drag_map'] 	    = (isset($new_cpm_map['drag_map']) && $new_cpm_map['drag_map']) ? true : false;
            $new_cpm_map['map_stylized'] 	= (isset($new_cpm_map['map_stylized']) && $new_cpm_map['map_stylized']) ? true : false;
            $new_cpm_map['panoramioLayer'] 	= (isset($new_cpm_map['panoramioLayer']) && $new_cpm_map['panoramioLayer']) ? true : false;
            $new_cpm_map['MarkerClusterer'] = (isset($new_cpm_map['MarkerClusterer']) && $new_cpm_map['MarkerClusterer']) ? true : false;
            $new_cpm_map['legend'] = (isset($new_cpm_map['legend']) && $new_cpm_map['legend']) ? true : false;
            add_post_meta($post_id,'cpm_map',$new_cpm_map,TRUE);
        }    

		if( $this->get_configuration_option( 'exif_information' ) !== false )
		{
			$attachments = get_children( array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image') );
			foreach ( $attachments as $attachment ){
				$attachement_points = get_post_meta( $attachment->ID, 'cpm_point', TRUE );
				if( !empty( $attachement_points ) ){
					for( $i=0; $i<count($attachement_points); $i++)
					{
						$flag = -1;
						for( $j=0; $j<count($valid_cpm_points); $j++)
						{
							if( $valid_cpm_points[ $j ][ 'latitude' ].'' == $attachement_points[ $i ][ 'latitude' ].'' && $valid_cpm_points[ $j ][ 'longitude' ].'' == $attachement_points[ $i ][ 'longitude' ].'' )
							{
								$flag = $j;
								break;
							}
						}
						
						if( $flag == -1)
						{
							$valid_cpm_points[] = $attachement_points[ $i ];
						}	
					}	
				}
			}
		}	
        delete_post_meta($post_id,'cpm_point');
		
		if(count($valid_cpm_points)) add_post_meta($post_id,'cpm_point',$valid_cpm_points,TRUE);
	} // End save_map
	
	//---------- OPTIONS FOR CODEPEOPLE POST MAP ----------

	/**
	 * Get default configuration options
	 */
	function _default_configuration(){
		return array(
							'zoom' => '10',
                            'dynamic_zoom' => false,
							'width' => '450',
							'height' => '450',
							'margin' => '0',
							'align' => 'center',									
							'language' => 'en',
                            'drag_map' => true,
							'icons' => array(),
                            'default_icon' => CPM_PLUGIN_URL.'/images/icons/marker.png',
                            'your_location_icon' => CPM_PLUGIN_URL.'/images/icons/star.png',
                            'your_location_title' => 'You are here',
                            'your_location' => false,
							'type' => 'ROADMAP',
							'points' => 3,
							'display' => 'map',
							'mousewheel' => true,
							'zoompancontrol' => true,
                            'route' => false,
							'polyline' => false,
                            'mode' => 'DRIVING',
							'typecontrol' => true,
							'streetviewcontrol' => true,
							'highlight'	=> true,
							'highlight_class' => 'cpm_highlight',
                            'post_type' => array('post', 'page'),
                            'show_window' => true,
                            'show_default' => true,
							'get_direction' => false,
							'street_view_link' => false,
							'map_link' => false,
							'MarkerClusterer' => false,
							'exif_information' => false,
							'geolocation_information' => false,
							'legend' => false,
                            'legend_taxonomy' => '',
							'legend_title' => '',
							'legend_class' => '',
							'windowhtml' => "<div class='cpm-infowindow'>
                                                <div class='cpm-content'>
                                                    <a title='%link%' href='%link%'>%thumbnail%</a>
                                                    <a class='title' href='%link%'>%title%</a>
                                                    <div class='address'>%address%</div>
                                                    <div class='description'>%description%</div>
                                                </div>
                                                <div style='clear:both;'></div>
											</div>
											%additional%",
							'map_stylized' => false,
							'map_styles' => "[{\"stylers\": [{ \"hue\": \"#00ffe6\" },{ \"saturation\": -20 }]},{ \"featureType\": \"road\", \"elementType\": \"geometry\", \"stylers\": [{ \"lightness\": 100 },{ \"visibility\": \"simplified\" }]},{ \"featureType\": \"road\", \"elementType\": \"labels\", \"stylers\": [{ \"visibility\": \"off\" }]}]"
							);
	} // End _default_configuration
	
	/**
	 * Set default system and maps configuration
	 */
	function set_default_configuration($default = false){
		
		$cpm_default = $this->_default_configuration();
							
    	$options = get_option('cpm_config');
		if ($default || $options === false) {
			update_option('cpm_config', $cpm_default);
			$options = $cpm_default;
		}
		return $options;
	} // End set_default_configuration
	
	/**
	 * Get a part of option variable or the complete array
	 */
	function get_configuration_option($option = null){
		$default = $this->_default_configuration();
		
		if( isset( $option ) && $option == 'windowhtml' ){
			return $default[$option];
		}
		
		$options = get_option('cpm_config');
		
		if(!isset($options)){
			$options = $default;
		}
		
		if(isset($option)){
			return (isset($options[$option])) ? $options[$option] : ((isset($default[$option])) ? $default[$option] : null);
		}else{
			return $options;
		}	
		
	} // End get_configuration_option
	
	//---------- METADATA FORM METHODS ----------
	
	/**
	 * Private method to deploy the list of languages
	 */
	function _deploy_languages($options){
		print '<select name="cpm_map[language]" id="cpm_map_language">';
		foreach($this->lang_array as $key=>$value)
			print '<option value="'.$key.'" '.((isset($options['language']) && $options['language'] == $key) ? 'selected' : '').'>'.$value.'</option>';
		print '</select>';	
	} // End _deploy_languages
	
	/**
	 * Private method to get the list of icons
	 */
	function _deploy_icons($options = null, $i){ 

		$icon_path = CPM_PLUGIN_URL.'/images/icons/';
		$icon_dir = CPM_PLUGIN_DIR.'/images/icons/';	

		$icons_array = array();
		
		$default_icon = $this->get_configuration_option('default_icon');
		$selected_icon = (isset($options) && isset($options['icon'])) ? $options['icon'] : $default_icon;
		if( strpos($selected_icon, 'http') !== 0 ) $selected_icon = CPM_PLUGIN_URL.$selected_icon;
		
		if ($handle = opendir($icon_dir)) {
			
			while (false !== ($file = readdir($handle))) {
		
				$file_type = wp_check_filetype($file);
				$file_ext = $file_type['ext'];
				if ($file != "." && $file != ".." && ($file_ext == 'gif' || $file_ext == 'jpg' || $file_ext == 'png') ) {
					array_push($icons_array,$icon_path.$file);
				}
			}
		}
		?>
			<div class="cpm_label">
				<?php _e("Select the marker by clicking on the images", "codepeople-post-map"); ?> 
			</div>    	   
			<div id="cpm_icon_cont">
				<input type="hidden" name="cpm_point[<?php echo $i; ?>][icon]" value="<?php echo $selected_icon ?>" id="selected_icon" />			
				<?php foreach ($icons_array as $icon){ ?>
				  <div class="cpm_icon <?php if ($selected_icon == $icon) echo "cpm_selected" ?>">
				  <img src="<?php echo $icon ?>" /> 
				  </div>
				<?php } ?>
			</div> 
			<div id="icon_credit">
				<span><?php _e("Powered by","codepeople-post-map"); ?></span>
				<a href="http://mapicons.nicolasmollet.com" target="_blank">
					<img src="<?php echo CPM_PLUGIN_URL ?>/images/miclogo-88x31.gif" />
				</a>
			</div> 	
            <div class="clear"></div>
            <span class="cpm_more_info_hndl  cpm_blink_me" style="margin-left: 10px;"><a href="javascript:void(0);" onclick="cpm_display_more_info( this );">[ + more information]</a></span>
            <div class="cpm_more_info">
                <p>To use your own markers icons, you only should to upload the icons images to the following location:</p>
                <p>/wp-content/plugins/codepeople-post-map/images/icons</p>
                <p>and then select the icon's image from the list</p>
                <a href="javascript:void(0)" onclick="cpm_hide_more_info( this );">[ + less information]</a>
            </div>
		<?php
	} // End _deploy_icons
	
	/**
	 * Private method to insert the map form
	 */
	function _deploy_map_form($options = NULL, $single = false){
		$default_options = $this->_default_configuration();
		?>
		<h2><?php _e('Maps Configuration', 'codepeople-post-map'); ?></h2>
		<p  style="border:1px solid #E6DB55;margin-bottom:10px;padding:5px;background-color: #FFFFE0;"><?php _e('For any issues with the map, go to our <a href="http://wordpress.dwbooster.com/contact-us" target="_blank">contact page</a> and leave us a message.'); ?>
		<br/><br />
		<?php _e('If you want test the premium version of CP Google Maps go to the following links:<br/> <a href="http://demos.net-factor.com/cp-google-maps/wp-login.php" target="_blank">Administration area: Click to access the administration area demo</a><br/> <a href="http://demos.net-factor.com/cp-google-maps/" target="_blank">Public page: Click to access the CP Google Maps</a>'); ?>
		</p>
		<table class="form-table">
            <?php
                if($single){
            ?>    
                    <tr valign="top">
                        <th scope="row"><label for="cpm_map_single"><?php _e('Use particular settings for this map:', 'codepeople-post-map')?></label></th>
                        <td>
                            <input type="checkbox" name="cpm_map[single]" id="cpm_map_single" <?php echo ((isset($options['single'])) ? 'CHECKED' : '');?> />
                        </td>
                    </tr>
            <?php        
                }
            ?>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_zoom"><?php _e('Map zoom:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="text" size="4" name="cpm_map[zoom]" id="cpm_map_zoom" value="<?php echo ((isset($options['zoom'])) ? $options['zoom'] : '');?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_dynamic_zoom"><?php _e('Dynamic zoom:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="checkbox" name="cpm_map[dynamic_zoom]" id="cpm_map_dynamic_zoom" <?php echo ( ( isset($options['dynamic_zoom'] ) && $options['dynamic_zoom'] ) ? 'CHECKED' : '' ); ?> /> <?php _e( 'Allows to adjust the zoom dynamically to display all points on map', 'codepeople-post-map' ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_width"><?php _e('Map width:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="text" size="4" name="cpm_map[width]" id="cpm_map_width" value="<?php echo ((isset($options['width'])) ? $options['width'] : '');?>" />
                    <span class="cpm_more_info_hndl  cpm_blink_me" style="margin-left: 10px;"><a href="javascript:void(0);" onclick="cpm_display_more_info( this );">[ + more information]</a></span>
                    <div class="cpm_more_info">
                        <p>To insert the map in a responsive design (in a responsive design, the map's width should be adjusted with the page width):</p>
                        <p>the value of map's width should be defined as a percentage of container's width, for example, type the value: <strong>100%</strong></p>
                        <a href="javascript:void(0)" onclick="cpm_hide_more_info( this );">[ + less information]</a>
                    </div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_height"><?php _e('Map height:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="text" size="4" name="cpm_map[height]" id="cpm_map_height" value="<?php echo ((isset($options['height'])) ? $options['height'] : '');?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_margin"><?php _e('Map margin:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="text" size="4" name="cpm_map[margin]" id="cpm_map_margin" value="<?php echo ((isset($options['height'])) ? $options['margin'] : '');?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_align"><?php _e('Map align:', 'codepeople-post-map')?></label></th>
				<td>
					<select id="cpm_map_align" name="cpm_map[align]">
						<option value="left" <?php echo((isset($options['align']) && $options['align'] == 'left') ? 'selected': ''); ?>><?php _e('left'); ?></option>
						<option value="center" <?php echo((isset($options['align']) && $options['align'] == 'center') ? 'selected': ''); ?>><?php _e('center'); ?></option>
						<option value="right" <?php echo((isset($options['align']) && $options['align'] == 'right') ? 'selected': ''); ?>><?php _e('right'); ?></option>
					</select>	
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_type"><?php _e('Map type:', 'codepeople-post-map'); ?></label></th>
				<td>
					<select name="cpm_map[type]" id="cpm_map_type" >
						<option value="ROADMAP" <?php echo ((isset($options['type']) && $options['type']=='ROADMAP') ? 'selected' : '');?>><?php _e('ROADMAP - Displays a normal street map', 'codepeople-post-map');?></option>
						<option value="SATELLITE" <?php echo ((isset($options['type']) && $options['type']=='SATELLITE') ? 'selected' : '');?>><?php _e('SATELLITE - Displays satellite images', 'codepeople-post-map');?></option>
						<option value="TERRAIN" <?php echo ((isset($options['type']) && $options['type']=='TERRAIN') ? 'selected' : '');?>><?php _e('TERRAIN - Displays maps with physical features such as terrain and vegetation', 'codepeople-post-map');?></option>
						<option value="HYBRID" <?php echo ((isset($options['type']) && $options['type']=='HYBRID') ? 'selected' : '');?>><?php _e('HYBRID - Displays a transparent layer of major streets on satellite images', 'codepeople-post-map');?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_language"><?php _e('Map language:', 'codepeople-post-map');?></th>
				<td><?php $this->_deploy_languages($options); ?></td>
			</tr>
            <tr valign="top">
                <th scope="row"><label for="cpm_map_single"><?php _e('Allow drag the map:', 'codepeople-post-map')?></label></th>
                <td>
                    <input type="checkbox" name="cpm_map[drag_map]" id="cpm_drag_map" <?php echo ((!isset( $options['drag_map'] ) || $options['drag_map']) ? 'CHECKED' : '');?> />
                </td>
            </tr>
			
            <tr valign="top">
				<th scope="row"><label for="cpm_map_route"><?php _e('Display route:', 'codepeople-post-map');?></th>
				<td>
                    <input type="checkbox" id="cpm_map_route" name="cpm_map[route]" value="true" <?php echo ((isset($options['route']) && $options['route']) ? 'checked' : '');?>><span> <?php _e( 'Draws the route between the points in the same post', 'codepeople-post-map' ); ?> </span>
                    <span class="cpm_more_info_hndl  cpm_blink_me" style="margin-left: 10px;"><a href="javascript:void(0);" onclick="cpm_display_more_info( this );">[ + more information]</a></span><br />
					<input type="checkbox" id="cpm_map_polyline" name="cpm_map[polyline]" value="true" <?php echo ((isset($options['polyline']) && $options['polyline']) ? 'checked' : '');?>><span> <?php _e( 'Connect the points with polylines, even if there is not a route between points', 'codepeople-post-map' ); ?> </span>
                    <div class="cpm_more_info">
                        <p>The route will be draw only between the points associated to the same post. If are showing on map, points of multiple posts, its possible that exist multiple routes, not connected between them.</p>
                        <p>If a point in the same post is displayed disconnected from the route, is very probable that Google does not recognize a route with the travel mode selected, that connects all points. In that case, use a different travel mode.</p>
                        <a href="javascript:void(0)" onclick="cpm_hide_more_info( this );">[ + less information]</a>
                    </div>
                </td>
			</tr>
            				
            <tr valign="top">
				<th scope="row"><label for="cpm_travel_mode"><?php _e('Travel mode:', 'codepeople-post-map');?></th>
				<td>
                    <select id="cpm_travel_mode" name="cpm_map[mode]">
                        <option value="DRIVING" <?php echo ((isset($options['mode']) && $options['mode']=='DRIVING') ? 'selected' : '');?> >Driving</option>
                        <option value="BICYCLING" <?php echo ((isset($options['mode']) && $options['mode']=='BICYCLING') ? 'selected' : '');?> >Bicycling</option>
                        <option value="TRANSIT" <?php echo ((isset($options['mode']) && $options['mode']=='TRANSIT') ? 'selected' : '');?> >Public transit</option>
                        <option value="WALKING" <?php echo ((isset($options['mode']) && $options['mode']=='WALKING') ? 'selected' : '');?> >Walking</option>
                    </select>
                </td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="cpm_map_display"><?php _e('Display map in post/page:', 'codepeople-post-map'); ?></label></th>
				<td>
					<select name="cpm_map[display]" id="cpm_map_display" >
						<option value="icon" <?php echo ((isset($options['display']) && $options['display']=='icon') ? 'selected' : '');?>><?php _e('as icon', 'codepeople-post-map'); ?></option>
						<option value="map" <?php echo ((isset($options['display']) && $options['display']=='map') ? 'selected' : '');?>><?php _e('as full map', 'codepeople-post-map'); ?></option>
					</select>
				</td>
			</tr>
			
            <tr valign="top">
				<th scope="row"><label for="cpm_show_window"><?php _e('Show info bubbles:', 'codepeople-post-map');?></th>
				<td>
                    <input type="checkbox" id="cpm_show_window" name="cpm_map[show_window]" value="true" <?php echo ((isset($options['show_window']) && $options['show_window']) ? 'checked' : '');?>><span> <?php _e( 'Display the bubbles associated to the points', 'codepeople-post-map');?></span>
                </td>
			</tr>
            
            <tr valign="top">
				<th scope="row"><label for="cpm_show_default"><?php _e('Display a bubble by default:', 'codepeople-post-map');?></th>
				<td>
                    <input type="checkbox" id="cpm_show_default" name="cpm_map[show_default]" value="true" <?php echo ((isset($options['show_default']) && $options['show_default']) ? 'checked' : '');?>><span> <?php _e( 'Display a bubble opened by default', 'codepeople-post-map' ); ?></span>
                </td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="cpm_panoramioLayer"><?php _e('Display the Panoramio layer:', 'codepeople-post-map');?></th>
				<td>
                    <input type="checkbox" id="cpm_panoramioLayer" name="cpm_map[panoramioLayer]" value="true" <?php echo ((isset($options['panoramioLayer']) && $options['panoramioLayer']) ? 'checked' : '');?>><span> <?php _e( 'Display a layer with photos published in Panoramio', 'codepeople-post-map' ); ?></span>
                </td>
			</tr>
			
			<tr valign="top">
				<th scope="row"><label for="cpm_MarkerClusterer"><?php _e('Display a bundle of points in the same area, like a cluster:', 'codepeople-post-map');?></th>
				<td>
                    <input type="checkbox" id="cpm_MarkerClusterer" name="cpm_map[MarkerClusterer]" value="true" <?php echo ((isset($options['MarkerClusterer']) && $options['MarkerClusterer']) ? 'checked' : '');?>><span> <?php _e( 'Displays the number of points in the cluster', 'codepeople-post-map' ); ?></span>
                </td>
			</tr>
			
<?php
			if( !$single ){
?>            
				<tr valign="top">
					<th scope="row"><label for="cpm_featured_image"><?php _e('Display Featured Image by default:', 'codepeople-post-map');?></th>
					<td>
						<input type="checkbox" id="cpm_featured_image" name="cpm_map[featured_image]" value="true" <?php echo ((isset($options['featured_image']) && $options['featured_image']) ? 'checked' : '');?>><span> <?php _e( 'Displays the Featured Image in posts and pages in the infowindows, if the points don\'t have associated an image.', 'codepeople-post-map' ); ?></span>
					</td>
				</tr>
			
				<tr valign="top">
					<th scope="row"><label for="cpm_get_direction"><?php _e('Display the get directions link:', 'codepeople-post-map');?></th>
					<td>
						<input type="checkbox" id="cpm_get_direction" name="cpm_map[get_direction]" value="true" 
						<?php echo ( ( isset($options[ 'get_direction' ]) && $options['get_direction'] ) ? 'checked' : '' );
							?>><span> <?php _e( 'Display a link at  bottom of infowindow to get directions', 'codepeople-post-map' ); ?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="cpm_map_link"><?php _e('Display a link to Google Maps:', 'codepeople-post-map');?></th>
					<td>
						<input type="checkbox" id="cpm_map_link" name="cpm_map[map_link]" value="true" <?php echo (( isset($options['map_link']) && $options['map_link'] ) ? 'checked' : '');?>><span> <?php _e( 'Display a link at  bottom of infowindow to display on Google Maps', 'codepeople-post-map' ); ?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="cpm_street_view_link"><?php _e('Display a link to street view:', 'codepeople-post-map');?></th>
					<td>
						<input type="checkbox" id="cpm_street_view_link" name="cpm_map[street_view_link]" value="true" <?php echo (( isset($options['street_view_link']) && $options['street_view_link'] ) ? 'checked' : '');?>><span> <?php _e( 'Display a link at bottom of infowindow to load the corresponding street view', 'codepeople-post-map' ); ?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="cpm_your_location"><?php _e('Display the user\'s location:', 'codepeople-post-map');?></th>
					<td>
						<input type="checkbox" id="cpm_your_location" name="cpm_map[your_location]" value="true" <?php echo (( isset($options['your_location']) && $options['your_location'] ) ? 'checked' : '');?>><span> <?php _e( "Display an icon with the user's location on map", 'codepeople-post-map' ); ?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="cpm_your_location_title"><?php _e('Title of user\'s location:', 'codepeople-post-map');?></th>
					<td>
						<input type="text" id="cpm_your_location_title" name="cpm_map[your_location_title]" value="<?php echo ( ( isset($options['your_location_title']) && $options['your_location_title'] ) ? $options['your_location_title'] : $this->get_configuration_option('your_location_title') );?>" /><span> <?php _e('Title of user\'s location', 'codepeople-post-map');?></span>
					</td>
				</tr>
				
<?php
			}
?>            
            <tr valign="top">
				<th scope="row"><label for="wpGoogleMaps_description"><?php _e('Options', 'codepeople-post-map'); ?>:</label></th>
				<td>
					<input type="checkbox" name="cpm_map[streetviewcontrol]" id="cpm_map_streetviewcontrol" value="true" <?php echo ((isset($options['streetviewcontrol']) && $options['streetviewcontrol']) ? 'checked' : '');?> />
					<label for="cpm_map_streetviewcontrol"><?php _e('Display the street view control', 'codepeople-post-map'); ?></label><br />
					<input type="checkbox" name="cpm_map[mousewheel]" id="cpm_map_mousewheel" value="true" <?php echo ((isset($options['mousewheel']) && $options['mousewheel']) ? 'checked' : '');?> />
					<label for="cpm_map_mousewheel"><?php _e('Enable mouse wheel zoom', 'codepeople-post-map'); ?></label><br />
					<input type="checkbox" name="cpm_map[zoompancontrol]" id="cpm_map_zoompancontrol" value="true" <?php echo ((isset($options['zoompancontrol']) && $options['zoompancontrol']) ? 'checked' : '');?> />
					<label for="cpm_map_zoompancontrol"><?php _e('Enable zoom/pan controls', 'codepeople-post-map'); ?></label><br />
					<input type="checkbox" name="cpm_map[typecontrol]" id="cpm_map_typecontrol" value="true" <?php echo ((isset($options['typecontrol']) && $options['typecontrol']) ? 'checked' : '');?> />
					<label for="cpm_map_typecontrol"> <?php _e('Enable map type controls (Map, Satellite, or Hybrid)', 'codepeople-post-map'); ?> </label><br />
				</td>
			</tr>
            
			<tr valign="top">
				<th scope="row"><label for="cpm_map_points"><?php _e('Enter the number of posts to display on the map', 'codepeople-post-map'); ?>:</th>
				<td><input type="text" name="cpm_map[points]" id="cpm_map_points" value="<?php echo ((isset($options['points'])) ? $options['points'] : '');?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="cpm_map_stylized"><?php _e('Allow stylize the maps:', 'codepeople-post-map')?></label></th>
				<td valign="top">
					<input type="checkbox" id="cpm_map_stylized" name="cpm_map[map_stylized]" <?php echo ( isset( $options['map_stylized'] ) && $options['map_stylized'] ) ? 'CHECKED' : ''; ?> />
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<span>
					<?php
						_e( 'If you want change the maps\' styles, be sure to know how to create a JSON structure with the map\'s styles', 'codepeople-post-map')
					?>
					</span><br />
					<textarea id="cpm_map_styles" name="cpm_map[map_styles]" rows="10" cols="80" <?php echo ( !isset( $options['map_stylized'] ) || !$options['map_stylized'] ) ? 'DISABLED READONLY' : ''; ?> ><?php echo stripcslashes( ( isset( $options['map_styles'] ) ) ? $options['map_styles'] : $default_options['map_styles'] ); ?></textarea>
				</td>
			</tr>
            <tr>
				<th scope="row"><label for="cpm_map_legend"><?php _e('Display the map\'s legend:', 'codepeople-post-map')?></label></th>
				<td valign="top">
					<input type="checkbox" id="cpm_map_legend" name="cpm_map[legend]" <?php echo ( isset( $options['legend'] ) && $options['legend'] ) ? 'CHECKED' : ''; ?> />
				</td>
			</tr>
            <tr>
				<th scope="row"><label for="cpm_map_legend_taxonomy"><?php _e('Select the taxonomy to display on legend:', 'codepeople-post-map')?></label></th>
				<td valign="top">
                <?php
                    $taxonomies = get_taxonomies( '', 'names' );
                ?>
                    <select id="cpm_map_legend_taxonomy" name="cpm_map[legend_taxonomy]">
                        <option value=""><?php _e( 'Select a taxonomy', 'codepeople-post-map' ); ?></option>
                <?php   
                        foreach( $taxonomies as $taxonomy ){
                            $selected = ( isset( $options['legend_taxonomy'] ) && $taxonomy == $options['legend_taxonomy'] ) ? 'SELECTED' : '';
                            print '<option value="'.$taxonomy.'" '.$selected.'>'.$taxonomy.'</option>';
                        }
                ?>    
                    </select>
                </td>
			</tr>
			<tr>
				<th scope="row"><label for="cpm_map_legend_title"><?php _e('Enter a title for legend:', 'codepeople-post-map')?></label></th>
				<td valign="top">
					<input type="text" id="cpm_map_legend_title" name="cpm_map[legend_title]" value="<?php echo addcslashes( $options['legend_title'], '"' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cpm_map_legend_class"><?php _e('Enter a classname to be applied to the legend:', 'codepeople-post-map')?></label></th>
				<td valign="top">
					<input type="text" id="cpm_map_legend_class" name="cpm_map[legend_class]" value="<?php echo addcslashes( $options['legend_class'], '"' ); ?>" />
				</td>
			</tr>
			
		</table>
		<?php
	} // End _deploy_map_form
	
	/**
	 * Private method to print Maps form
	 */
	function _print_point_form($options, $i){
		global $post;
		
?>	
		<div class="point_form" id="point_form<?php echo (($i) ? $i : ''); ?>" style="border:1px solid #CCC; <?php echo (($i) ? 'display:none' : '');?>">
			<?php
				if($i) print('<input type="hidden" id="cpm_point_id" value="'.$i.'" />');	
			?>		
			<h3><?php _e('Map point description'); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="cpm_name"><?php _e('Location name:', 'codepeople-post-map')?></label></th>
					<td>
						<input type="text" size="40" style="width:95%;" name="cpm_point[<?php echo $i; ?>][name]" id="cpm_point_name" value="<?php echo ((isset($options['name'])) ? $options['name'] : '');?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="cpm_point_description"><?php _e('Location description:', 'codepeople-post-map')?></label></th>
					<td>
						<textarea style="width:95%;" name="cpm_point[<?php echo $i; ?>][description]" id="cpm_point_description"><?php echo ((isset($options['description'])) ? $options['description'] : '');?></textarea>
                        <br />
                        <em>It is possible to insert a link to another page in the infowindow associated to the point. Type the link tag to the other page in the point description box, similar to: <span style="white-space:nowrap;"><strong>&lt;a href="http://wordpress.dwbooster.com" &gt;CLICK HERE &lt;/a&gt;</strong></span></em>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
                        <?php _e("Select an image to attach to the point: ","codepeople-post-map"); ?>
					</th>
                    <td>
                        <input type="text" name="cpm_point[<?php echo $i; ?>][thumbnail]" value="<?php if( isset( $options["thumbnail"] )){ echo $options["thumbnail"];} ?>" id="cpm_point_thumbnail" />
                        <input class="button" type="button" value="Upload Images" onclick="cpm_thumbnail_selection(this);" />
                    </td>	
				</tr>
				<tr valign="top">
					<td colspan="2">
						<table>
							<tr valign="top">
								<td>
									<table>
										<tr valign="top">
											<th scope="row"><label for="cpm_point_address"><?php _e('Address:', 'codepeople-post-map'); ?></label></th>
											<td width="100%">
												<input type="text" style="width:100%" name="cpm_point[<?php echo $i; ?>][address]" id="cpm_point_address" value="<?php echo ((isset($options['address'])) ? $options['address'] : '');?>" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="cpm_point_latitude"><?php _e('Latitude:', 'codepeople-post-map')?></label></th>
											<td>
												<input style="width:100%" type="text" name="cpm_point[<?php echo $i; ?>][latitude]" id="cpm_point_latitude" value="<?php echo ((isset($options['latitude'])) ? $options['latitude'] : '');?>" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row"><label for="cpm_point_longitude"><?php _e('Longitude:', 'codepeople-post-map')?></label></th>
											<td>
												<input style="width:100%" type="text" name="cpm_point[<?php echo $i; ?>][longitude]" id="cpm_point_longitude" value="<?php echo ((isset($options['longitude'])) ? $options['longitude'] : '');?>" />
											</td>
										</tr>
										<tr valign="top">
											<th scope="row" style="text-align:right;"><p class="submit"><input type="button" name="cpm_point_verify" id="cpm_point_verify" value="<?php _e('Verify', 'codepeople-post-map'); ?>" onclick="cpm_checking_point(this);" /></p></th>
											<td>
												<label for="cpm_point_verify"><?php _e('Verify this latitude and longitude using Geocoding. This could overwrite the point address.', 'codepeople-post-map')?><span style="color:#FF0000">(<?php _e('Required: Press the button "verify" after complete the address.', 'codepeople-post-map'); ?>)</span></label>
											</td>
										</tr>
									</table>
								</td>
								<td width="50%">
									<div id="cpm_map_container<?php echo $i; ?>" class="cpm_map_container" style="height:250px; border:1px dotted #CCC;">
										<div style="margin:20px;">
										<?php _e('To correct the latitude and longitud directly on MAP, type the address and press the Verify button.'); ?>
										</div>
									</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
						<?php $this->_deploy_icons($options, $i); ?>
					</td>	
				</tr>
			</table>
			<div style="padding:10px;"><input class="add_point button-primary" type="button" value="<?php _e('Add/Update Point', 'codepeople-post-map'); ?>" /></div>
		</div>
<?php		
	}
	
	function _print_form($options){
		$cpm_point = $options['cpm_point'];
		$default_configuration = $this->_default_configuration();
		
		// create a custom nonce for submit verification later
		echo '<input type="hidden" name="cpm_map_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
		
	?>
		<script>
			var cpm_point_counter = <?php echo (count($cpm_point)+1); ?>;
			var cpm_default_marker = "<?php echo $default_configuration['default_icon']; ?>";
		</script>
		<p  style="font-weight:bold;"><?php _e('For more information go to the <a href="http://wordpress.dwbooster.com/content-tools/codepeople-post-map" target="_blank">CodePeople Post Map</a> plugin page'); ?></p>
		<p  style="border:1px solid #E6DB55;margin-bottom:10px;padding:5px;background-color: #FFFFE0;"><?php _e('For any issues with the map, go to our <a href="http://wordpress.dwbooster.com/contact-us" target="_blank">contact page</a> and leave us a message.'); ?></p>
        <p>
            <?php _e( 'To insert a map in the post follow the steps below', 'codepeople-post-map');?>:
        </p>
        <ol>
            <li><?php _e( 'Enter the point\'s information (the latitude and longitude are required, but are obtained pressing the "verify" button after type the address', 'codepeople-post-map' );?></li>
            <li><?php _e('Insert the shortcode in the post\'s content pressing the "insert the map tag" button', 'codepeople-post-map');?></li>
            <li><?php _e('If you want to use specific settings just for this map, press the "Show/Hide Map\'s Options" button, and modifies the settings for this map', 'codepeople-post-map'); ?></li>
            <li><?php _e( 'Don\'t forget to press the "Update" button for save the post and map data', 'codepeople-post-map');?></li>
        </ol>
		<div style="border:1px solid #CCC;margin-bottom:10px;min-height:60px;">
			<h3><?php _e('Map points'); ?></h3>
			<div>
			<ul  id="points_container" >
			<?php
				$cpm_point_order = '|';
				foreach($cpm_point as $index => $point){
					if(is_array($point)){
						$point[ 'address' ] = trim( $point[ 'address' ] );
						print '<li><div class="button" id="cpm_point'.($index+1).'" style="height:auto;display:inline-block;"><img class="handle" /><span onclick="cpm_edit_point('.($index+1).')">'.( ( empty( $point['address'] ) ) ? ( ( empty( $point['name'] ) ) ? '...' : $point['name'] ) : $point[ 'address' ] ).'</span><input type="button" value="X" onclick="cpm_delete_point('.($index+1).', this);"  /></div></li>';
						
						$cpm_point_order .= ( $index + 1 ).'|';
					}	
				}
			?>
			</ul>
			<input type="hidden" id="cpm_points_order" name="cpm_points_order" value="<?php echo $cpm_point_order; ?>" />
			</div>
		</div>
		<?php
		foreach($cpm_point as $index => $point){
			$this->_print_point_form($point, $index+1);
		}
		$this->_print_point_form(array(), 0);
		?>	
		<div style="border:1px solid #CCC; padding:10px; margin:10px 0;">
			<p>
				<?php
				_e( 'To insert this map in a post/page, press the <strong>"insert the map tag"</strong> button and save the post/page modifications.', 'codepeople-post-map' );
				?>
			</p>
			<div style="border:1px solid #CCC; padding:10px; padding:5px;">
				<p>
				<input type="checkbox" name="cpm_shape_selector" id="cpm_shape_selector" />
				<?php
				_e( 'Do you want display a <strong>shape</strong> on map?', 'codepeople-post-map' );
				?>
				</p>
				<table style="display:none;" id="cpm_shape_data">
					<tbody>
					<tr>
						<td valign="top">
							<table>
								<tbody>
								<tr>
									<th align="right" valign="top" style="white-space:nowrap;">
										<?php
											_e( 'stroke Weight: ', 'codepeople-post-map' ); 
										?>
									</th>
									<td>
										<input type="text" name="cpm_shape_stroke_weight" id="cpm_shape_stroke_weight" value="0"  onchange="shape_change_settings( this, 'strokeWeight');" style="width:150px;"  />
									</td>
								</tr>
								<tr>
									<th align="right"  valign="top" style="white-space:nowrap;" >
										<?php
											_e( 'Fill Color: ', 'codepeople-post-map' ); 
										?>
									</th>
									<td>
										<input type="text" name="cpm_shape_fill_color" id="cpm_shape_fill_color" value="#333333"  onchange="shape_change_settings( this, 'fillColor');shape_change_settings( this, 'strokeColor');" style="width:150px;" />
										<div id="cpm_shape_color_container"></div>
									</td>
								</tr>
								<tr>
									<th align="right"  valign="top" style="white-space:nowrap;">
										<?php
											_e( 'Fill Opacity: ', 'codepeople-post-map' ); 
										?>
									</th>
									<td>
										<input type="text" name="cpm_shape_fill_opacity" id="cpm_shape_fill_opacity" value="0.5" onchange="shape_change_settings( this, 'fillOpacity');" style="width:150px;"  />
										<br />
										<em>
										<?php
											_e( 'Use a number between 0 and 1, 0 is transparent. ', 'codepeople-post-map' ); 
										?>
										</em>
									</td>
								</tr>
								</tbody>
							</table>
						</td>
						<td style="width:50%;">
							<div id="cpm_shape_map_container" style="height:250px; border:1px dotted #CCC;"></div>
						</td>
					</tr>
					</tbody>
				</table>
			</div>
			<table class="form-table">
				<tr valign="top">
					<td scope="row" valign="top" style="vertical-align:top;width:350px;">
						<label><?php _e('If you want to display the map in page / post:', 'codepeople-post-map')?></label><br />
						<input type="button" class="button-primary" name="cpm_map_shortcode" id="cpm_map_shortcode" value="<?php _e('Insert the map tag', 'codepeople-post-map'); ?>" style="height:40px; padding-left:30px; padding-right:30px; font-size:1.5em;" /><br />
						<span class="cpm_more_info_hndl cpm_blink_me" style="margin-left: 10px;"><a href="javascript:void(0);" onclick="cpm_display_more_info( this );">[ + more information]</a></span>
						<div class="cpm_more_info">
							<p>It is possible to use attributes in the shortcode, like: width, height, zoom and the other maps attributes:</p>
							<p><strong>[codepeople-post-map width="450" height="500"]</strong></p>
							<p>The premium version of plugin allows to use a special attribute "cat" (referent to category), to display all points created in a category:</p>
							<p><strong>[codepeople-post-map cat="35"]</strong><br/>Note: the number 35 correspond to the ID of category.</p>
							<p>or all points on website, using as category ID the value "-1"</p>
							<p><strong>[codepeople-post-map cat="-1"]</strong></p>
							<p>The special attribute "tag", allow to display all points that belong to the posts with a specific tag assigned, for example "mytag":</p>
							<p><strong>[codepeople-post-map tag="mytag"]</strong></p>
							<br />
							<a href="javascript:void(0)" onclick="cpm_hide_more_info( this );">[ + less information]</a>
						</div>
					</td>
					<td valign="top" style="vertical-align:top;">
						<label><?php _e('To display the points that belong to any category','codepeople-post-map');?>:</label><br />
						<select id="pointsCategories" size="2" multiple="multiple" style="height:48px;width:100%;">
							<option value="-1">All points on website</option>
						<?php
							$categories = get_categories();
							foreach( $categories as $category )
							{
								print '<option value="'.$category->term_id.'">'.$category->name.'</option>';
							}
							
						?>
						</select>
					</td>
				</tr>
			</table>
		</div>	
		<div id="map_data">
			<?php $this->_deploy_map_form($options, true); ?>
		</div>	
        <p>&nbsp;</p>
		<?php
		$default_icon = $this->get_configuration_option('default_icon');
		echo '<input type="hidden" id="cpm_map_default_icon" value="' . $default_icon . '" />';
	} // End _print_form
	
	/**
	 * Form for maps insertion and update
	 */
	function insert_form(){
		global $post, $wpdb;
	
		$cpm_point = get_post_meta($post->ID, 'cpm_point', TRUE);
		$cpm_map = get_post_meta($post->ID, 'cpm_map', TRUE);
		$general_options = $this->get_configuration_option();
		$options = array_merge((array)$general_options, (array)$cpm_map);
		$options['post_id'] = $post->ID;
		$options['cpm_point'] = (array)$cpm_point;
		if(!isset($options['cpm_point'][0])) $options['cpm_point'] = array($options['cpm_point']);
		$this->_print_form($options);
	} // End insert_form
	
	//---------- LOADING RESOURCES ----------
	
	/*
	 * Load the required scripts and styles for ADMIN section of website
	 */
	function load_admin_resources(){
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );
		
		wp_enqueue_style(
			'admin_cpm_style',
			CPM_PLUGIN_URL.'/styles/cpm-admin-styles.css',
            array(),
            'pro'
		);
        
        wp_enqueue_script(
			'admin_cpm_script',
			CPM_PLUGIN_URL.'/js/cpm.admin.js',
			array('jquery', "jquery-ui-core", "jquery-ui-sortable", "jquery-ui-draggable", "jquery-ui-droppable", "farbtastic"),
            'pro',
            true
		);
	} // End load_admin_resources
	
	/**
	 * Load script and style files required for display google maps on public website
	 */
	function load_resources() {
        global $cpm_resources_loaded;
        if( !is_admin() && empty( $cpm_resources_loaded ) )
        {
            $cpm_resources_loaded = true;

            if( !wp_script_is( 'jquery' ) )
            {
                print "<script src='".($_SERVER['SERVER_PORT'] == 443 ? "https" : "http")."://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js'></script>";
            }
            
            print "<link rel='stylesheet' id='cpm_style-css'  href='".CPM_PLUGIN_URL.'/styles/cpm-styles.css'."' type='text/css' media='all' />";
            print "<script src='".CPM_PLUGIN_URL.'/js/markerclusterer_compiled.js?ver=pro'."'></script>";
            print "<script src='".CPM_PLUGIN_URL.'/js/cpm.js?ver=pro'."'></script>";
        }    
    } // End load_resources
	
	function load_header_resources(){
		echo '<style>.cpm-map img{ max-width: none !important;box-shadow:none !important;}</style>';
	} // End load_header_resources
	
	/**
	 * Print the settings page for entering the general settings data of maps
	 */
	function settings_page(){
		// Check if post exists and save the configuraton options
        $options = $this->get_configuration_option();
        $default_options = $this->_default_configuration();
		if (isset($_POST['cpm_map_noncename']) && wp_verify_nonce($_POST['cpm_map_noncename'],__FILE__)){
			$options = $_POST['cpm_map'];
            $options['drag_map'] = ( isset( $_POST['cpm_map'][ 'drag_map' ] ) ) ? true : false;
            $options['windowhtml'] = $this->get_configuration_option('windowhtml');
			if(isset($options['post_type'])){
                $options['post_type'] = array_merge($options['post_type'], $default_options['post_type']);
            }else{
                $options['post_type'] = $default_options['post_type'];
            }
			$options['map_stylized'] = (isset($options['map_stylized'])) ? true : false;
			$options['legend'] = (isset($options['legend'])) ? true : false;
			update_option('cpm_config', $options);
			echo '<div class="updated"><p><strong>'.__("Settings Updated").'</strong></div>';
		}
		
	?>
		<div class="wrap">
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<?php	
		$this->_deploy_map_form($options);
	?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="cpm_map_exif_information"><?php _e('Generate points dynamically from geolocation information included on images, when images are uploaded to WordPress:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="checkbox" name="cpm_map[exif_information]" id="cpm_map_exif_information" value="true" <?php echo ((isset($options['exif_information']) && $options['exif_information']) ? 'checked' : '');?> />
                    <?php _e('The geolocation information is added to the images from your mobiles or cameras, if they have associated GPS devices', 'codepeople-post-map');?>
				</td>
			</tr>
			
            <tr valign="top">
				<th scope="row"><label for="cpm_map_geolocation_information"><?php _e('Generate points dynamically from geolocation information included on posts:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="checkbox" name="cpm_map[geolocation_information]" id="cpm_map_geolocation_information" value="true" <?php echo ((isset($options['geolocation_information']) && $options['geolocation_information']) ? 'checked' : '');?> />
                    <?php _e('The geolocation information is added to the post from WordPress app in your mobile', 'codepeople-post-map');?>
				</td>
			</tr>
			
            <tr valign="top">
				<th scope="row"><label for="cpm_map_search"><?php _e('Use points information in search results:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="checkbox" name="cpm_map[search]" id="cpm_map_search" value="true" <?php echo ((isset($options['search']) && $options['search']) ? 'checked' : '');?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_highlight"><?php _e('Highlight post when mouse move over related point on map:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="checkbox" name="cpm_map[highlight]" id="cpm_map_highlight" value="true" <?php echo ((isset($options['highlight']) && $options['highlight']) ? 'checked' : '');?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="cpm_map_highlight_class"><?php _e('Highlight class:', 'codepeople-post-map')?></label></th>
				<td>
					<input type="input" name="cpm_map[highlight_class]" id="cpm_map_highlight_class" value="<?php echo ((isset($options['highlight_class'])) ? $options['highlight_class'] : '');?>" />
				</td>
			</tr>
            <tr valign="top">
				<th scope="row"><label for="cpm_map_post_type"><?php _e('Allow to associate a map to the post types:', 'codepeople-post-map')?></label></th>
				<td valign="top">
                <?php
                    $post_types = get_post_types(array('public' => true), 'names');
                ?>
                    <select id="cpm_map_post_type" multiple size="3" name="cpm_map[post_type][]">
                <?php   
                        foreach($post_types as $post_type){
                            $disabled = (in_array($post_type, $default_options['post_type'])) ? 'DISABLED' : '';
                            $selected = (isset($options['post_type']) && in_array($post_type, $options['post_type'])) ? 'SELECTED' : '';
                            print '<option value="'.$post_type.'" '.$disabled.' '.$selected.'>'.$post_type.'</option>';
                        }
                ?>    
                    </select>
                <?php
                    _e('Posts and Pages are selected by default', 'codepeople-post-map');
                ?>    
				</td>
			</tr>
		</table>
		<p  style="font-weight:bold;"><?php _e('For more information go to the <a href="http://wordpress.dwbooster.com/content-tools/codepeople-post-map" target="_blank">CodePeople Post Map</a> plugin page'); ?></p>
		<div class="submit"><input type="submit" class="button-primary" value="<?php _e('Update Settings', 'codepeople-post-map');?>" /></div>
		<?php 
			// create a custom nonce for submit verification later
			echo '<input type="hidden" name="cpm_map_noncename" value="' . wp_create_nonce(__FILE__) . '" />'; 
		?>
		</form>
		</div>
	<?php
	} // End settings_page
	
	//---------- SHORTCODE METHODS ----------
	/*
	 * Populate the attribute points
	 */
	function populate_points($post_id){
		if( is_admin() ) return;
		
		if( in_array( $post_id, $this->checked_posts ) ) return;
		$this->checked_posts[] = $post_id;
		
		$points = array();
		$post_points = get_post_meta($post_id, 'cpm_point', TRUE);
		
		if( !empty( $post_points ) )
        {
			$points = array_merge( $points, $post_points );
		}
        else
        {
            if( $this->get_configuration_option( 'geolocation_information' ) ){
                $latitude = get_post_meta( $post_id, 'geo_latitude', true );
                $longitude = get_post_meta( $post_id, 'geo_longitude', true );
                if(  !empty( $latitude ) && !empty( $longitude ) ){
                    $point = array(
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    );
                    array_push( $points, $point );
                }	
            }
        }
		
		if(!empty($points)){
			if(!isset($points[0])) $points = array($points);
            foreach($points as $point){
				$point['post_id'] = $post_id;
                $this->points[] = $point;
            }	
		}	
	} // End populate_points
	
    /**
     * Get the category and categories children
     */
    function get_categories( $v, $taxonomy = 'category' ){
        $r = array();
        $p = explode( ',', str_replace( ' ', '', $v ) ); // Parents
        
        foreach( $p as $c )
        {
            $r[] = $c;
            $children = get_categories(
                array(
                    'child_of' => $c,
                    'taxonomy' => $taxonomy
                )
            );
            
            foreach( $children as $child )
            {
                $r[] = $child->term_id;
            }
        }
        
        return $r;
    }
    
    /**
     * Get points from categories, tags or custom taxonomies
     */
    function get_special_map( $content = '', $cpm_map ){
	    global $wpdb, $post, $id;

        $r = '';    
        
		if( !empty( $this->extended[ 'cat' ] ) ) 
        {
            $cat = $this->get_categories( $this->extended[ 'cat' ] );
        }
        
		$tag = ( !empty( $this->extended[ 'tag' ] ) ) ?  str_replace( ' ', '', $this->extended[ 'tag' ] ) : '' ;
		
		$posts = array();
        
		$query_arg = array(
				'tag' => $tag,
				'numberposts' => -1
			);
        
        if( !empty( $cat ) && !in_array( '-1', $cat ) )
		{
			$query_arg[ 'category' ] = implode( ',', $cat );
		}

		if( !empty( $this->extended[ 'excludecat' ] ) )
		{
			$this->extended[ 'excludecat' ] = '-'.implode( ',-', $this->extended[ 'excludecat' ] );
            if( empty( $query_arg[ 'category' ] ) ) $query_arg[ 'category' ] = $this->extended[ 'excludecat' ];
			else $query_arg[ 'category' ] .= ','.$this->extended[ 'excludecat' ];
		}
        
		// Exclude posts
        if( !empty( $this->extended[ 'excludepost' ] ) )
        {
            $query_arg[ 'post__not_in' ] = $this->extended[ 'excludepost' ];
            // Is not necessary check for them in the print_points function
            unset( $this->extended[ 'excludepost' ] );
        }    
            
		if( !empty( $this->extended[ 'excludetag' ] ) ) $query_arg[ 'tag__not_in' ] = $this->extended[ 'excludetag' ];
		
		if( !empty( $this->extended[ 'taxonomy' ] ) )
		{
		
			$query_arg[ 'tax_query' ] = array( 'relation' => 'OR' );
			foreach( $this->extended[ 'taxonomy' ] as $taxonomy )
			{
				$query_arg[ 'tax_query' ][] = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $this->extended[ $taxonomy ],
					'operator' => 'IN'
				);
			}
		}
		
		if( !empty( $this->extended[ 'points' ] ) )
		{
			$this->extended[ 'points' ] = trim( $this->extended[ 'points' ] );
			if( is_numeric( $this->extended[ 'points' ] ) && $this->extended[ 'points' ] > 0 ) 
            {
                $query_arg[ 'numberposts' ] = $this->extended[ 'points' ]*1;
                $this->limit = $query_arg[ 'numberposts' ];
            }    
		}
        
        // Complete the query arguments with the common arguments of all queries for maps points
        $query_arg = array_merge( $this->query_base, $query_arg );
		
        // Get the current post
        if( isset( $id ) && ( empty( $query_arg[ 'post__not_in' ] ) || !in_array( $id, $query_arg[ 'post__not_in' ] ) ) )
        {
            $query_arg[ 'post__in' ] = array( $id );
            $posts = array_merge( $posts, get_posts( $query_arg ) );
            unset( $query_arg[ 'post__in' ] );
            if( !empty( $query_arg[ 'post__not_in' ] ) ) $query_arg[ 'post__not_in' ][] = $id;
            else $query_arg[ 'post__not_in' ] = array( $id );
        }
        
        $posts = array_merge( $posts, get_posts( $query_arg  ) );

        if(!empty($posts)){
            $r  = $this->_set_map_tag($cpm_map);
            $r .= $this->_set_map_config($cpm_map);
            $r .= $this->_set_map_shape($content);
            $r .="<noscript> codepeople-post-map require JavaScript </noscript>";
            
            foreach($posts as $_post){ 
				$this->populate_points($_post);
			}
		}
        
        return $r;
    }
        
	/**
	 * Replace each [codepeople-post-map] shortcode by the map
	 */
	function replace_shortcode( $atts, $content = '' ){
		global $post, $id, $cpm_objs, $cpm_in_loop;

        // Load the plugin resources.
        $this->load_resources();
		
        $cpm_obj = new CPM;
        $cpm_objs[] = $cpm_obj;
        
        $atts = (array)$atts;
		
		if( !empty( $atts[ 'defaultpost' ] ) )  $cpm_obj->defaultpost = str_replace( ' ', '', $atts[ 'defaultpost' ] );
		if( !empty( $atts[ 'excludepost' ] ) )  $atts[ 'excludepost' ] = explode( ',', str_replace( ' ', '', $atts[ 'excludepost' ] ) );
		if( !empty( $atts[ 'excludecat' ] ) )   $atts[ 'excludecat' ] = $this->get_categories( $atts[ 'excludecat' ] );
		if( !empty( $atts[ 'excludetag' ] ) )   $atts[ 'excludetag' ] = explode( ',', str_replace( ' ', '', $atts[ 'excludetag' ] ) );
		if( !empty( $atts[ 'taxonomy' ] ) )
		{
			$atts[ 'taxonomy' ] = explode( ',', str_replace( ' ', '', $atts[ 'taxonomy' ] ) );
			foreach( $atts[ 'taxonomy' ] as $index => $value )
			{
				if( empty( $atts[ $value ] ) )
				{
					unset( $atts[ 'taxonomy' ][ $index ] );
				}	
				else
				{
					$atts[ $value ] = $this->get_categories( $atts[ $value ], $value );
				}
			}
		}
		
		$cpm_obj->extended = $atts;
		
        if( isset( $id ) && ( is_singular() || !empty( $cpm_in_loop ) )){
            $cpm_map = get_post_meta($id, 'cpm_map', TRUE);
        }
        
        if(empty($cpm_map)){
            $cpm_map = $this->get_configuration_option();
        }
		
        if( !empty($cpm_map['legend']) ){
            $cpm_obj->legend = $cpm_map['legend'];
        }
		
		if( !empty( $cpm_map[ 'legend_taxonomy' ] ) ){
            $cpm_obj->legend_taxonomy = $cpm_map['legend_taxonomy'];
        }
        
        if( !empty( $atts[ 'legend' ] ) )
        {
            $cpm_obj->legend = true;
            $cpm_obj->legend_taxonomy = trim( $atts[ 'legend' ] );
        }
        
        // Complete the query_base to avoid duplicated code
        $post_types = $cpm_map[ 'post_type' ];
		if( empty( $post_types ) ){ 
            $post_types = $this->get_configuration_option( 'post_type' );
        }
        $cpm_obj->query_base[ 'post_type' ] = $post_types;
        
        
		if( !empty( $atts[ 'cat' ] ) || !empty( $atts[ 'tag' ] ) || !empty( $atts[ 'taxonomy' ] ) ){
            $cpm_obj->limit = 0;
		    if( !empty( $atts[ 'print' ] ) ){
				print $cpm_obj->get_special_map( $content, $cpm_map );
                $cpm_obj->print_points();
				return '';
			}
			
            return $cpm_obj->get_special_map( $content, $cpm_map );
		}
		
        if(!empty($cpm_map['points'])){
            $cpm_obj->limit = $cpm_map['points'];
        }
        
        if( !empty( $atts[ 'points' ] ) )
        {
            $atts[ 'points' ] = trim( $atts[ 'points' ] );
            if( is_numeric( $atts[ 'points' ] ) && $atts[ 'points' ] > 0 ) $cpm_obj->limit = $atts[ 'points' ];
        }
        
        if( isset( $id ) && ( is_singular() || !empty( $cpm_in_loop ) )  && empty( $atts[ 'widget' ] ) ){ // For maps in a post or page
			// Set the actual post only to avoid duplicates
			$posts = array( $id );
			
			$query_arg = array( 
				'post__not_in' => array( $id )
            );
			
            // Exclude posts
            if( !empty( $atts[ 'excludepost' ] ) )
            {
                $query_arg[ 'post__not_in' ] = array_merge( $query_arg[ 'post__not_in' ], $atts[ 'excludepost' ] );
                // Is not necessary check for them in the print_points function
                unset( $cpm_obj->extended[ 'excludepost' ] );
            }    
            
            // Exclude tags
			if( !empty( $atts[ 'excludetag' ] ) ) $query_arg[ 'tag__not_in' ] = $atts[ 'excludetag' ];
            
            // Exclude categories
			if( !empty( $atts[ 'excludecat' ] ) )
            {
                $query_arg[ 'category' ] = '-'.implode( ',-', $atts[ 'excludecat' ] );
            }    
            
            // Get POSTs in the same category
			$categories = get_the_category();
			$categories_ids = array();
			foreach($categories as $category){
				if( empty( $atts[ 'excludecat' ] ) || !in_array( $category->term_id, $atts[ 'excludecat' ] ) ) array_push( $categories_ids, $category->term_id);
			}
			
			if( !empty( $categories_ids ) ){
				$query_arg[ 'category' ] = ( ( $query_arg[ 'category' ] ) ? $query_arg[ 'category' ] . ',' : '' ).implode( ',', $categories_ids );
			}
			
            // Set limits, the current post was inserted directly in the array, so get the limits - 1
			if( !empty($cpm_obj->limit) ) $query_arg[ 'numberposts' ] = $cpm_obj->limit - 1;
			
            // Complete the query arguments with the common arguments of all queries for maps points
            $query_arg = array_merge( $this->query_base, $query_arg );
			$posts = array_merge( $posts, get_posts( $query_arg ) );

			foreach( $posts as $_post){
            	$cpm_obj->populate_points($_post, true);
			}	
			
		}else{
            $cpm_obj->multiple = true;
            
            // Create query of excluded posts
            $query_arg = array();
            if( !empty( $atts[ 'excludecat' ] ) || !empty( $atts[ 'excludetag' ] ) )
            {
                $query_arg[ 'tax_query' ] = array( 'relation' => 'OR' );
                if( !empty( $atts[ 'excludecat' ] ) )   $query_arg[ 'tax_query' ][] = array(
                                                                                          'taxonomy' => 'category',
                                                                                          'field' => 'term_id',
                                                                                          'terms' => $atts[ 'excludecat' ]
                                                                                        );    
                if( !empty( $atts[ 'excludetag' ] ) )   $query_arg[ 'tax_query' ][] = array(
                                                                                          'taxonomy' => 'post_tag',
                                                                                          'field' => 'slug',
                                                                                          'terms' => $atts[ 'excludetag' ]
                                                                                        );    
            }
            if( !empty( $query_arg ) )
            {
                $query_arg[ 'fields' ] = 'ids';
                $query_arg = array_merge( $cpm_obj->query_base, $query_arg );
                if( !empty( $cpm_obj->extended[ 'excludepost' ] ) ) $cpm_obj->extended[ 'excludepost' ] = array_merge( $cpm_obj->extended[ 'excludepost' ], get_posts( $query_arg ) );
                else $cpm_obj->extended[ 'excludepost' ] = get_posts( $query_arg );
            }    
        }
        
        $output  = $cpm_obj->_set_map_tag($cpm_map);
        $output .= $cpm_obj->_set_map_config($cpm_map);
        $output .= $cpm_obj->_set_map_shape($content);
        
        $output .="<noscript>
            codepeople-post-map require JavaScript
        </noscript>
        ";	
			
        if( !empty( $atts[ 'print' ] ) ){
            print $output;
            $cpm_obj->print_points();
            return '';
        }
        return $output;
	
	} // End replace_shortcode
	
	/*
	 * Generates the DIV tag where the map will be loaded
	 */
	function _set_map_tag($atts){
        $atts = array_merge($atts, $this->extended);
		extract($atts);
		
		$output ='<div id="'.$this->map_id.'" class="cpm-map" style="display:none; width:'.$width.(( strpos($width, '%') !== false ) ? '' : 'px').'; height:'.$height.(( strpos($height, '%') !== false ) ? '' : 'px').'; ';
        
		switch ($align) {
			case "left" :		  
				$output .= 'float:left; margin:'.$margin.'px;"';
			break;
			case "right" :		  
				$output .= 'float:right; margin:'.$margin.'px;"';
			break;
			case "center" :		  
				$output .= 'clear:both; overflow:hidden; margin:'.$margin.'px auto;"';
			break;
			default:
				$output .= 'clear:both; overflow:hidden; margin:'.$margin.'px auto;"';
			break;		  	  
		}
		$output .= "></div>";
		return $output;
	} // End _set_map_tag
	
	function _set_map_shape( $atts )
	{
		$output = "";
		$atts =  str_replace( array( '&#8220;', '&#8221;', '&#8243;' ), array( '"', '"', '"' ),  $atts );
		if( !is_null( json_decode( $atts ) ) )
		{
			$output = "<script>if(typeof cpm_global != 'undefined' && typeof cpm_global['".$this->map_id."'] != 'undefined' && typeof cpm_global['".$this->map_id."']['shapes'] != 'undefined'){ cpm_global['$this->map_id']['shapes'] = ".$atts."; }</script>";
		}	
		return $output;
	}
	
	/*
	 * Generates the javascript tag with map configuration
	 */
	function _set_map_config($atts){
        $atts = array_merge($atts, $this->extended);
		
		extract($atts);
		$default_language = $this->get_configuration_option('language');

		$output  = "<script type=\"text/javascript\">\n";
		
		if(isset($language)) 
			$output  .= 'var cpm_language = {"lng":"'.$language.'"};';
		elseif(isset($default_language))	
			$output  .= 'var cpm_language = {"lng":"'.$default_language.'"};';
	
		$output .= "var cpm_global = cpm_global || {};\n";
		$output .= "cpm_global['$this->map_id'] = {}; \n";
		$output .= "cpm_global['$this->map_id']['zoom'] = $zoom;\n";
		$output .= "cpm_global['$this->map_id']['dynamic_zoom'] = ".((isset($dynamic_zoom) && $dynamic_zoom) ? 'true' : 'false').";\n";
		$output .= "cpm_global['$this->map_id']['markers'] = new Array();\n";
		$output .= "cpm_global['$this->map_id']['shapes'] = {};\n";
		$output .= "cpm_global['$this->map_id']['display'] = '$display';\n"; 
        $output .= "cpm_global['$this->map_id']['drag_map'] = ".( ( !isset( $drag_map ) || $drag_map ) ? 'true' : 'false' ).";\n";
		$output .= "cpm_global['$this->map_id']['route'] = ".((isset($route) && $route) ? 'true' : 'false').";\n";
		$output .= "cpm_global['$this->map_id']['polyline'] = ".((isset($polyline) && $polyline) ? 'true' : 'false').";\n";
		$output .= "cpm_global['$this->map_id']['show_window'] = ".((isset($show_window) && $show_window) ? 'true' : 'false').";\n";
		$output .= "cpm_global['$this->map_id']['show_default'] = ".((isset($show_default) && $show_default) ? 'true' : 'false').";\n";
		$output .= "cpm_global['$this->map_id']['panoramioLayer'] = ".((isset($panoramioLayer) && $panoramioLayer) ? 'true' : 'false').";\n";
		$output .= "cpm_global['$this->map_id']['MarkerClusterer'] = ".((isset($MarkerClusterer) && $MarkerClusterer) ? 'true' : 'false').";\n";
        $output .= "cpm_global['$this->map_id']['mode'] = '".((isset($mode)) ? $mode : $this->get_configuration_option('mode'))."';\n";
		$output .= "cpm_global['$this->map_id']['highlight_class'] = '".$this->get_configuration_option('highlight_class')."';\n"; 
		$output .= "cpm_global['$this->map_id']['legend'] = ".((isset($legend) && $legend) ? 'true' : 'false').";\n";
		$output .= "cpm_global['$this->map_id']['legend_title'] = '".str_replace( "'", "\'", $legend_title )."';\n";
		$output .= "cpm_global['$this->map_id']['legend_class'] = '$legend_class';\n";
        
		$your_location 		 = $this->get_configuration_option( 'your_location' );
		$your_location_icon  = $this->get_configuration_option( 'your_location_icon' );
		$your_location_title = $this->get_configuration_option( 'your_location_title' );
		
		if( isset( $your_location ) && $your_location &&  !empty( $your_location_icon ) )
		{
			$output .= "cpm_global['$this->map_id']['your_location'] = true;\n";
			$output .= "cpm_global['$this->map_id']['your_location_icon'] = '".$your_location_icon."';\n"; 
			$output .= "cpm_global['$this->map_id']['your_location_title'] = '".( ( isset( $your_location_title ) && $your_location_title ) ? $your_location_title : '' )."';\n"; 
		}
		
		$map_styles = stripcslashes( trim( str_replace( "\n", "", $map_styles ) ) );

		if( isset( $map_stylized ) && $map_stylized && strlen( $map_styles ) )
		$output .= "cpm_global['$this->map_id']['map_styles'] = ".$map_styles.";\n"; 

		$highlight = $this->get_configuration_option('highlight');
		$output .= "cpm_global['$this->map_id']['highlight'] = ".(($highlight && !is_singular()) ? 'true' : 'false').";\n"; 
		$output .= "cpm_global['$this->map_id']['type'] = '$type';\n";	
		  
		// Define controls
		$output .= "cpm_global['$this->map_id']['mousewheel'] = ".((isset($mousewheel) && $mousewheel) ? 'true' : 'false').";\n";	  
		$output .= "cpm_global['$this->map_id']['zoompancontrol'] = ".((isset($zoompancontrol) && $zoompancontrol) ? 'true' : 'false').";\n";	  
		$output .= "cpm_global['$this->map_id']['typecontrol'] = ".((isset($typecontrol) && $typecontrol) ? 'true' : 'false').";\n";	  
		$output .= "cpm_global['$this->map_id']['streetviewcontrol'] = ".((isset($streetviewcontrol) && $streetviewcontrol) ? 'true' : 'false').";\n";	  
		$output .= "</script>";
		
		return $output;
	} // End _set_map_config
	
	/*
	 * Generates the javascript code of map points
	 */
	function _set_map_point($point, $index){
		$icon = (!empty($point['icon'])) ? $point['icon'] : $this->get_configuration_option('default_icon');
		if( strpos( $icon, 'http' ) !== 0 ) $icon = CPM_PLUGIN_URL.$icon;
        $obj = new stdClass;
        $obj->address = str_replace(array('&quot;', '&lt;', '&gt;', '&#039;', '&amp;'), array('\"', '<', '>', "'", '&'), $point['address']);
        $obj->lat   = $point['latitude'];
        $obj->lng   = $point['longitude'];
        $obj->info  = str_replace(array('&quot;', '&lt;', '&gt;', '&#039;', '&amp;'), array('\"', '<', '>', "'", '&'), $this->_get_windowhtml($point));
        $obj->icon  = $icon;
        $obj->post  = $point['post_id'];
        $obj->taxonomies = ( !empty( $this->taxonomies ) && !empty( $this->taxonomies[ $point[ 'post_id' ] ] ) ) ? $this->taxonomies[ $point[ 'post_id' ] ] : array();
        $obj->default = ( !empty( $point[ 'default' ] ) ) ? true : false;
        
		return 'cpm_global["'.$this->map_id.'"]["markers"]['.$index.'] = '.json_encode( $obj ).';';

	} // End _set_map_point
	
	/*
	 * Generates the javascript code of map points, only called from webpage of multiples posts
	 */
	function print_points(){ // posible values 'echo' 'return'
        global $id;
        
		$limit = abs($this->limit);
		
        $str = '';
        $current_post = '';
        $count_posts = 0;
        $count_points = 0;
        
        foreach($this->points as $point){
            if( isset( $this->extended[ 'excludepost' ] ) && in_array( $point['post_id'], $this->extended[ 'excludepost' ] ) ) continue;
            if($current_post != $point['post_id']){
                $current_post = $point['post_id'];
                $count_posts++;
                if( !empty($limit) && $count_posts > $limit) break;
                if( !empty( $this->legend ) && !empty( $this->legend_taxonomy ) ) $this->taxonomies[ $point['post_id'] ] = wp_get_post_terms( $point['post_id'], $this->legend_taxonomy, array( "fields" => "names" ) );
                if( !empty( $this->defaultpost) && $this->defaultpost == $current_post )
                {
                    $point[ 'default' ] = 1;
                    $this->defaultpost = '';
                }
            }    
            
            $str .=  $this->_set_map_point($point, $count_points);
            $count_points++;
        }
        
        if(strlen($str)){
            $str = "<script>if(typeof cpm_global != 'undefined' && typeof cpm_global['".$this->map_id."'] != 'undefined' && typeof cpm_global['".$this->map_id."']['markers'] != 'undefined'){ ".$str." }</script>";
        }
        $this->points = array();
        print $str;
	} // End print_points
	
    function _get_img_id($url){
        global $wpdb;
        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "posts" . " WHERE guid='%s';", $url )); 
        return $attachment[0];
    } // End get_img_id
    
	/**
	 * Get the html info associated to point marker
	 */
	function _get_additional_information( $point ){
		$r = '';
		$map_link 		    =  $this->get_configuration_option( 'map_link' );	
		$get_direction 	    =  $this->get_configuration_option( 'get_direction' );	
		$street_view_link 	=  $this->get_configuration_option( 'street_view_link' );	
		
		if( $get_direction || $map_link || $street_view_link ){
			$z = $this->get_configuration_option( 'language' );
			if( !empty( $z ) ){
				$z = '&z='.$z;
			}
			$daddr = $near = $point[ 'address' ];
			if( !empty( $daddr ) ){ 
				$daddr = '&daddr='.urlencode( $daddr ); 
				$near  = '&near='.urlencode( $near ); 
			}
			
			$r .= '<div class="cpm-infowindow-additional">';
			if( $get_direction ) $r .= '<a target="_blank" href="http://maps.google.com/maps?f=d'.$daddr.'&q='.$point['latitude'].','.$point['longitude'].'&language='.$this->get_configuration_option( 'language' ).$z.'">'.__( "Get directions", "codepeople-post-map" ).'</a>&nbsp;&nbsp;';
			if( $map_link ) $r .= '<a target="_blank" href="http://maps.google.com/maps?f=q'.$near.'&q='.$point['latitude'].','.$point['longitude'].'&language='.$this->get_configuration_option( 'language' ).$z.'">'.__( "Display on map", "codepeople-post-map" ).'</a>&nbsp;&nbsp;';
			if( $street_view_link ) $r .= '<a id="cpm_display_streetview_btn" lat="'.$point['latitude'].'" lng="'.$point['longitude'].'" href="javascript:void(0);">'.__( "Display street view", "codepeople-post-map" ).'</a>&nbsp;&nbsp;';
				
			$r .= '</div>';
			
		}
		
		return $r;
	}
	
    function _get_windowhtml(&$point) {
    
		$windowhtml = "";
		$windowhtml_frame = $this->get_configuration_option('windowhtml');
		$point_title = (!empty($point['name'])) ? $point['name'] : get_the_title($point['post_id']);
		$point_link = (!empty($point['post_id'])) ? get_permalink($point['post_id']) : '';
		
		if (isset($point['thumbnail']) && $point['thumbnail'] != "") {
			$point_img_url = $point['thumbnail'];
            if(preg_match("/attachment_id=(\d+)/i", $point['thumbnail'], $matches)){
            	$thumb = wp_get_attachment_image_src($matches[1], 'thumbnail');
				if(is_array($thumb))$point_thumbnail = $thumb[0];
			}else{
                $point_thumbnail = $point['thumbnail'];
			}
            if( !empty( $point_thumbnail ) ) $point_img_url = $point_thumbnail;
		}else
		{
			$featured_image = $this->get_configuration_option( 'featured_image' );
			if( !empty( $featured_image ) )
			{	
				$featured_image_url = wp_get_attachment_url( get_post_thumbnail_id( $point['post_id'] ) );
				if( $featured_image_url ) $point_img_url = $featured_image_url;
			}	
		}	
		
		$point_description = ($point['description'] != "") ? $point['description'] : $this->_get_excerpt($point['post_id']);
		$point_address = $point['address'];

		if( !empty( $point_img_url ) ) {
			$point_img = "<img src='".$point_img_url."' align='right' style='margin:8px 0 0 8px !important; width:90px; height:90px'/>";
			$html_width = "310px";
		} else {
			$point_img = "";
			$html_width = "auto";
		}				
					
		$find = array("%title%","%link%","%thumbnail%", "%excerpt%","%description%","%address%","%width%","%additional%","\r\n","\f","\v","\t","\r","\n","\\","\"");
		$replace  = array($point_title,$point_link,$point_img,"",$point_description,$point_address,$html_width,$this->_get_additional_information($point),"","","","","","","","'");
		
		$windowhtml = str_replace( $find, $replace, $windowhtml_frame);
					
		return $windowhtml;
		
	} // End _get_windowhtml
	
	/**
	 * Get the excerpt from content
	 */
	function _get_excerpt($post_id) { // Fakes an excerpt if needed

		$content_post = get_post($post_id);
		$content = $content_post->post_content;

		if ( '' != $content ) {
			return wp_trim_words( strip_shortcodes( $content ), 10 );
        }
		return $content;
	} // End _get_excerpt
	
	//---------- SEARCH METHODS ----------
	
	function search($where)
	{
		$options = $this->get_configuration_option();
		if (is_search() && !empty($options['search']) && $options['search']) {
			global $wpdb, $wp;
		
		$parts = explode(' ', $wp->query_vars['s']);
		foreach($parts as $part){
			$where = preg_replace(
				"/(\($wpdb->posts.post_title (LIKE '%$part%')\))/i",
				"$1 OR ($wpdb->postmeta.meta_key = 'cpm_point' AND $wpdb->postmeta.meta_value $2)",
				$where
			);
		}	
		add_filter('posts_distinct_request', array(&$this, 'search_distinct'));
		add_filter('posts_join_request', array(&$this, 'search_join'));
		}
		return $where;
	}	// End search

	function search_distinct($distinct) {
		$distinct = "DISTINCT";
		return $distinct;
	} // End search_distinct
	
	function search_join($join)
	{
		global $wpdb;
		return $join .= " LEFT JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
	} // End search_join
	
	/*
		Set a link to contact page
	*/
	function customizationLink($links) { 
		$settings_link = '<a href="http://wordpress.dwbooster.com/contact-us" target="_blank">'.__('Request custom changes').'</a>'; 
		array_unshift($links, $settings_link); 
		return $links; 
	} // End customizationLink
} // End CPM class

/****************** WIDGETS ********************/
/**
 * CP_PostMapWidget Class
 */
class CP_PostMapWidget extends WP_Widget {
    
    /** constructor */
    function CP_PostMapWidget() {
        parent::WP_Widget(false, $name = 'CP Google Maps');	
        
    }

    function widget($args, $instance) {		
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        
        $defaults = array( 'attributes' => '' );
		$instance_p = wp_parse_args( (array) $instance, $defaults ); 
        
        $attributes   = trim( $instance_p[ 'attributes' ] );
		
        if( strpos( $attributes, 'width') === false )
		{
			$attributes .= ' width="100%"';
		}
		
		echo $before_widget; 
		if ( $title ) echo $before_title . $title . $after_title; 
		echo do_shortcode( '[codepeople-post-map widget="1" ' . str_replace('\'', '\\\'', $attributes) . ']' );
		echo $after_widget; 
		
    }

    function update($new_instance, $old_instance) {				
        $instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] 		= strip_tags( $new_instance['title'] );
		$instance['attributes']   = strip_tags( $new_instance['attributes'] );
		return $instance;
    }

    function form( $instance ) {
        /* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'attributes' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); 
        
        $title        = $instance[ 'title' ];
        $attributes   = $instance[ 'attributes' ];
        
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
            <p>
                <label for="<?php echo $this->get_field_id('attributes'); ?>"><?php _e('Enter the map\'s attributes:', 'codepeople-post-map'); ?><br />
                    <input style="width:100%;" id="<?php echo $this->get_field_id( 'attributes' ); ?>" name="<?php echo $this->get_field_name( 'attributes' ); ?>" value="<?php echo esc_attr( $attributes ); ?>" />
                </label>
				<em><?php _e( 'To display the points that belong to an category, use the attribute: cat="2", where 2 is the category\'s ID. To display the points that belong to multiple categories, separate each one by the comma symbol: cat="2,5,8". To display all points on website, use the value -1 for category\'s ID, cat="-1". If you want to display only the points associated with the current post, don\'t use the "cat" attribute.', 'codepeople-post-map' ); ?></em>
            </p>
		<?php 
    }

} // class CP_PostMapWidget	
	
?>