<?php
class ContentTimelineAdmin {
	
	var $main, $path, $name, $url;
	
	function __construct($file) {
		$this->main = $file;
		$this->init();
		return $this;
	}
	
	function init() {
		$this->path = dirname( __FILE__ );
		$this->name = basename( $this->path );
		$this->url = plugins_url( "/{$this->name}/" );
		global $wp_my_timeline_url;
		$wp_my_timeline_url=$this->url;
		require_once($this->path.'/functions/bro_images/bro_images.php');
		require_once($this->path.'/functions/functions.php');
		
		$this->activate();
		add_action('wp_ajax_nopriv_ctimeline_like_post', array(&$this, 'like_post'));
		add_action('wp_ajax_ctimeline_like_post', array(&$this, 'like_post'));
		add_action( 'plugins_loaded', array(&$this,'plugins_loaded') );
		add_filter('wpml_current_language',array(&$this,'my_wmpl_lang'),(PHP_INT_MAX-1));
		add_action('wp_footer',array(&$this,'wp_footer'));
		if( is_admin() ) {
			
			//Add action to admin_head
			add_action('admin_head',array(&$this,'admin_head'));
			//register_activation_hook( $this->main , array(&$this, 'activate') );
			
			add_action('admin_menu', array(&$this, 'admin_menu')); 
			
			// Ajax calls
			add_theme_support( 'post-thumbnails' );
			add_action('wp_ajax_ctimeline_save', array(&$this, 'ajax_save'));  
			add_action('wp_ajax_ctimeline_preview', array(&$this, 'ajax_preview'));
			add_action('wp_ajax_ctimeline_post_search', array(&$this, 'ajax_post_search'));
			add_action('wp_ajax_ctimeline_post_get', array(&$this, 'ajax_post_get'));
			add_action('wp_ajax_ctimeline_post_category_get', array(&$this, 'ajax_post_category_get'));
			add_action('wp_ajax_ctimeline_frontend_get', array(&$this, 'ajax_frontend_get'));
			add_action('wp_ajax_nopriv_ctimeline_frontend_get', array(&$this, 'ajax_frontend_get'));
			add_action('wp_ajax_ctimeline_image_test', array(&$this, 'ajax_image_test'));
			add_action('wp_ajax_nopriv_ctimeline_image_test', array(&$this, 'ajax_image_test'));
			add_action('wp_ajax_ctimeline_image_get', array(&$this, 'ajax_image_get'));
			
			
		}
		else {
			add_action('wp', array(&$this, 'frontend_includes'));
			add_shortcode('content_timeline', array(&$this, 'shortcode') );
			add_action('wp_head', array(&$this, 'wp_head'));
		}
	}
	function wp_footer(){
		?>
			<div class="my_timeline_hover_12_123" id="">
			</div>
			<?php 
		}
	function my_wmpl_lang($lang){
		if(is_admin()){
			global $wp_my_timeline_wpml_deafult_lang;
			global $wp_my_timeline_has_wmpl;
		$page=@$_GET['page'];
		if(!empty($page)){
			if($page=='contenttimeline_edit'){
				global $my_lang;
				$my_lang=$wp_my_timeline_wpml_deafult_lang;
				$my_lang=ICL_LANGUAGE_CODE;
				$my_get_lang=@$_GET['lang'];
				if(!empty($my_get_lang)){
					$my_lang=$my_get_lang;
						
				}
				//wp_my_timeline_switch_to_lang($my_lang);
				//wp_my_timeline_switch_to_lang($my_lang);
				return $my_lang;
				//wp_my_timeline_switch_to_lang($my_lang);
			}
		}
		}
		return $lang;
	
	}
	function plugins_loaded(){
		wp_my_timeline_has_wmpl();
		global $wp_my_timeline_wpml_deafult_lang;
		global $wp_my_timeline_has_wmpl;
		if($wp_my_timeline_has_wmpl){
	
		/*
			$page=@$_GET['page'];
		if(!empty($page)){
			if($page=='contenttimeline_edit'){
				global $my_lang;
				$my_lang=$wp_my_timeline_wpml_deafult_lang;
				$my_lang=ICL_LANGUAGE_CODE;
				$my_get_lang=@$_GET['my_lang'];
				if(!empty($my_get_lang)){
					$my_lang=$my_get_lang;
					
				}
				wp_my_timeline_switch_to_lang($my_lang);
			}
		}*/
		}
		//wp_my_timeline_active_languages();
		
	}
	function like_post(){
		$post_id=@$_POST['post_id'];
		$var=@$_POST['val'];
		$id=@$_POST['my_id'];
		$ret['id']=$id;
		$nonce=@$_POST['nonce'];
		if(!wp_verify_nonce($nonce,'ctimeline_like_post')){
			$ret['val']=$var;
			$ret['error']=1;
			$ret['msg']=__( "Error", 'my_content_timeline');
		}
		else if(empty($post_id)){
			$var++;
			$ret['val']=wp_my_timeline_format_number($var);
			$ret['error']=0;
			
		}else {
			$var=get_post_meta($post_id,'_my_timeline_12_12_hearts',true);
			if(empty($var))$var=0;
			$var++;
			update_post_meta($post_id, '_my_timeline_12_12_hearts', $var);
			$ret['val']=wp_my_timeline_format_number($var);
			$ret['error']=0;
		}
		echo json_encode($ret);
		die('');
		
	}
	function wp_head() {
		?>
		<script type="text/javascript">
		my_timeline_front_ajax_nonce="<?php echo wp_create_nonce('ctimeline_like_post');?>";
			my_timeline_front_ajax_url="<?php echo admin_url('admin-ajax.php');?>";
		</script>
		<?php 
		echo '<link href="http://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">';
	}
	function ajax_image_get() {
		if(isset($_GET['src'])) {
			header('Content-Type: image/png');
			$file = $_GET['src'];
			if(isset($_GET['w'])) $w = (int)$_GET['w']; else $w = 300;
			if(isset($_GET['h'])) $h = (int)$_GET['h']; else $h = 200;
			$file = bro_images::get_image($file,$w,$h, true);
			if(strpos(strtolower($file),'jpg')) {
				$im = imagecreatefromjpeg($file);	
				imagejpeg($im);
			}
			else {
				$im = imagecreatefrompng($file);	
				imagepng($im);
			}
			imagedestroy($im);
		}
		die();
	}
	
	function activate() {
		global $wpdb;
	
		$table_name = $wpdb->prefix . 'ctimelines';
	
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$royal_sliders_sql = "CREATE TABLE IF NOT EXISTS " . $table_name ." (
						  id mediumint(9) NOT NULL AUTO_INCREMENT,
						  name tinytext NOT NULL COLLATE utf8_general_ci,
						  settings text NOT NULL COLLATE utf8_general_ci,
						  items longtext NOT NULL COLLATE utf8_general_ci,
						  PRIMARY KEY (id)
						);";	
		}
		else {
			$royal_sliders_sql = "ALTER TABLE " . $table_name . " 
						  MODIFY COLUMN items longtext";
		}
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$wpdb->query($royal_sliders_sql);
		
		bro_images::init();
	}	
	
	function admin_menu() {
		global $wp_my_timeline_has_wmpl;
		$ctmenu = add_menu_page( 'Content Timeline', 'Content Timeline', 'manage_options', 'contenttimeline', array(&$this, 'admin_page'));
		$submenu = add_submenu_page( 'contenttimeline', 'Content Timeline', 'Add New', 'manage_options', 'contenttimeline_edit', array(&$this, 'admin_edit_page'));
		//if(!empty($wp_my_timeline_has_wmpl)){
			$wpml = add_submenu_page( 'contenttimeline', 'Content Timeline', 'Translations', 'manage_options', 'contenttimeline_translations', array(&$this, 'admin_translations_page'));
				
		
		//}
		$recmenu = add_submenu_page( 'contenttimeline', 'Content Timeline', 'Recommended Themes', 'manage_options', 'contenttimeline_recommended', array(&$this, 'admin_recommended_page'));
		
		add_action('load-'.$ctmenu, array(&$this, 'admin_menu_scripts')); 
		add_action('load-'.$submenu, array(&$this, 'admin_menu_scripts'));
		add_action('load-'.$recmenu, array(&$this, 'admin_menu_scripts'));

		add_action('load-'.$ctmenu, array(&$this, 'admin_menu_styles')); 
		add_action('load-'.$submenu, array(&$this, 'admin_menu_styles'));
		add_action('load-'.$recmenu, array(&$this, 'admin_menu_styles'));
		if(isset($wpml)){
			add_action('load-'.$wpml, array(&$this, 'admin_menu_styles'));
		}
	}
	function admin_head(){
		if(isset($_GET['page'])){
		$page=@$_GET['page'];
		if(!empty($page)){
			if($page=='contenttimeline_edit'){
				include_once($this->path . '/pages/default_settings.php');
				global $wp_my_timeline_styles;
				global $wp_my_timeline_styles_values;
				?>
				<script type="text/javascript">
					<?php global $wp_my_timeline_has_wmpl;
					
					if(!empty($wp_my_timeline_has_wmpl)){
					?>
					my_ctimeline_has_wpml=1;
					<?php $lang=@$_GET['lang'];
					if(!isset($lang))$lang='en';
					?>
					my_ctimeline_lang="<?php echo $lang;?>";
					<?php 
					if($lang!='en'){
						$t=wp_my_timeline_get_translations($my_lang);
					?>
					my_ctimeline_months=<?php echo json_encode($t);?>;
					
					<?php 
					}
					?>
					<?php }else {?>
					my_ctimeline_has_wpml=0;
					<?php }?>
					wp_my_timeline_styles={};
					<?php foreach($wp_my_timeline_styles as $key=>$val){?>
					wp_my_timeline_styles['<?php echo $key?>']=<?php echo json_encode($wp_my_timeline_styles_values[$key])?>;
					<?php }?>
				</script>
				<?php 
			}
		}
		}
	}
	function admin_menu_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-color');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		
		wp_enqueue_script('post');
	    wp_enqueue_script( 'wp-color-picker');
		wp_enqueue_script('thickbox');
		wp_enqueue_script('jQuery-easing', $this->url . 'js/frontend/jquery.easing.1.3.js' );
		wp_enqueue_script('jQuery-timeline', $this->url . 'js/frontend/jquery.timeline.js' );
		wp_enqueue_script('jQuery--vertical-timeline', $this->url . 'js/frontend/jquery.Verticaltimeline.js' );
		
		
		wp_enqueue_script('jQuery-mousew', $this->url . 'js/frontend/jquery.mousewheel.min.js' );
		wp_enqueue_script('jQuery-customScroll', $this->url . 'js/frontend/jquery.mCustomScrollbar.min.js' );	
		wp_enqueue_script('ctimeline-admin-js', $this->url . 'js/ctimeline_admin.js', array('jquery'), '1.0', true );
		wp_enqueue_script('isotope-pkgd', $this->url . 'js/isotope.pkgd.min.js' );
	}
	
	function admin_menu_styles() {
	    wp_enqueue_style( 'wp-color-picker');
		wp_enqueue_style('thickbox');
		wp_enqueue_style( 'ctimeline-admin-css', $this->url . 'css/ctimeline_admin.css' );
		wp_enqueue_style( 'ctimeline-thick-css', $this->url . 'css/thickbox.css' );
		wp_enqueue_style( 'timeline-css', $this->url . 'css/frontend/timeline.css' );
		wp_enqueue_style( 'customScroll-css', $this->url . 'css/frontend/jquery.mCustomScrollbar.css' );	
	}

	function ajax_save() {
		$id = false;
		$settings = '';
		$items = '';
		if(array_key_exists('data', $_POST)) {
			$exp = explode('||',$_POST['data']);
		
			foreach( $exp as $ee) {
				$exp2 = explode('::',$ee);
				$key = $exp2[0];
				$value = $exp2[1];
				if ($key != 'action') {
					if ($key == 'timeline_id'){
						if ($value != '') {
							$id = (int)$value;
						}
					}
					else if ($key == 'timeline_title'){
						$name = stripslashes($value);
					}
					else if(strpos($key,'sort') === 0){
						$items .= $key . '::' . stripslashes($value) . '||';
					}
					else {
						$settings .= $key . '::' . stripslashes($value) . '||';
					}
				}
			}
			if ($items != '') $items = substr($items,0,-2);
			if ($settings != '') $settings = substr($settings,0,-2);
			global $wpdb;
			$table_name = $wpdb->prefix . 'ctimelines';
			if($id) {	
				$wpdb->update(
					$table_name,
					array(
						'name'=>$name,
						'settings'=>$settings,
						'items'=>$items),
					array( 'id' => $id ),
					array( 
						'%s',
						'%s',
						'%s'),
					array('%d')
				);
			}
			else {
				$wpdb->insert(
					$table_name,
					array(
						'name'=>$name,
						'settings'=>$settings,
						'items'=>$items),	
					array(
						'%s',
						'%s',
						'%s')						
					
				);
				$id = $wpdb->insert_id;
			}
			
				
			echo $id;
				
		}
		die();
	}
	
	function ajax_preview() {
		$tid = false;
		$tsettings = '';
		$titems = '';
		if(array_key_exists('data', $_POST)) {
			$exp = explode('||',$_POST['data']);
		
			foreach( $exp as $ee) {
				$exp2 = explode('::',$ee);
				$key = $exp2[0];
				$value = $exp2[1];
				if ($key != 'action') {
					if ($key == 'timeline_id'){
						if ($value != '') {
							$id = (int)$value;
						}
					}
					else if ($key == 'timeline_title'){
						$tname = $value;
					}
					else if(strpos($key,'sort') === 0){
						$titems .= $key . '::' . $value . '||';
					}
					else {
						$tsettings .= $key . '::' . $value . '||';
					}
				}
			}
			if ($titems != '') $titems = substr($titems,0,-2);
			if ($tsettings != '') $tsettings = substr($tsettings,0,-2);
			
			include_once($this->path . '/pages/content_timeline_preview.php');
		}
		die();
	}
	
	function ajax_post_search(){
		if(isset($_POST['query']) && !empty($_POST['query'])){
			$searchVal = strtolower($_POST['query']);
		}
		else {
			$searchVal = '';
		}
		
		$query_args = array( 'posts_per_page' => -1, 'post_type' => 'any');
		$query = new WP_Query( $query_args );
		
		foreach ( $query->posts as $match) {
			if($searchVal != ''){
				if(strpos(strtolower($match->post_name), $searchVal) !== false){
					$thumbn = wp_get_attachment_image_src( get_post_thumbnail_id($match->ID) , 'full');
					echo '<li><a href="'.$match->ID.'"><img style="margin-right:5px;" src="'.$this->url.'timthumb/timthumb.php?src='.$thumbn[0].'&w=150&h=150" width="32" height="32" alt="" /><span class="timelinePostCompleteName">'.$match->post_title .'</span><span class="clear"></span></a></li>';
				}
			}
		}
		die();
	}
	
	function ajax_post_get($post_id = false){
		$id = 0;
		if(isset($_POST['post_id']))
			$id = (int) $_POST['post_id'];
		if ($post_id) $id = $post_id;
		$post = get_post($id); 
		
		echo $post->post_title . '||';
		echo substr($post->post_date, 8, 2) . '/' . substr($post->post_date, 5, 2) . '/' . substr($post->post_date, 0, 4) . '||';
		global $my_cat_id_12;
		if(!isset($my_cat_id_12)){	
			$post_categories = get_the_category( $id );
			echo $post_categories[0]->name . '||';
		}else {
			$my_cat_name=get_cat_name($my_cat_id_12);
			echo $my_cat_name.'||';
		}
		$excerpt = $post->post_excerpt;
		if ($excerpt == '' && $post->post_content != '') {
			echo substr(wp_strip_all_tags($post->post_content),0,100) . '...';
		}
		
		echo $excerpt . '||';
		if ( has_post_thumbnail($id)) {
			echo wp_get_attachment_url( get_post_thumbnail_id($id , 'full'));
		}
		echo '||' . $post->post_content;
		echo '||'.$post_id;
		if(!$post_id) {
			die();
		}
		
	}
	
	function ajax_post_category_get() {
		/**
		 * Adjust one cat to post
		 */
		global $my_cat_id_12;
		$cat_name = $_POST['cat_name'];
		$term = get_term_by('name', $cat_name, 'category');
		$cat_id = $term->term_id;
		$my_cat_id_12=$cat_id;
		
		$the_query = new WP_Query( array( 'cat' => $cat_id, 'post_type' => 'any', 'posts_per_page'=>-1, 'order' => 'ASC'));
		$start = true;
		while ( $the_query->have_posts() ) : $the_query->the_post();
			if ($the_query->post->post_type != 'page') {
				if (!$start) {
					echo '||';
				}
				$start = false;
				$this->ajax_post_get($the_query->post->ID);
			}
		endwhile;

		die();
	}
	
	function ajax_frontend_get(){
		$timelineId = $_GET['timeline'];
		$id = $_GET['id'];
		
		
		global $wpdb;
		if($timelineId) {
			$timeline = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ctimelines WHERE id='.$timelineId);
			$timeline = $timeline[0];
			
			$cats = "[";
			$catArray = array();
			$ccNumbers = array();
			$catNumber = 0;
			
			foreach(explode('||',$timeline->settings) as $val) {
				$expl = explode('::',$val);
				if(substr($expl[0], 0, 8) == 'cat-name') {
					if($cats != "[") $cats .= ",";
					$cc = get_cat_name(intval(substr($expl[0], 9)));
					$cats .= "'".$cc."'";
					array_push ($catArray,$cc);
					array_push ($ccNumbers, 0);
					$catNumber++;
				}
				else {
					$settings[$expl[0]] = $expl[1];
				}
				
			}
			$cats .= "]";
			
			
			if ($timeline->items != '') {
				$explode = explode('||',$timeline->items);
				$open_content_height = intval($settings['item-height']) - intval($settings['item-open-image-height']) - 2*intval($settings['item-open-content-padding']) -intval($settings['item-open-image-border-width']) - 6;
				
				foreach ($explode as $it) {
					$ex2 = explode('::', $it);
					$key = substr($ex2[0],0,strpos($ex2[0],'-'));
					$subkey = substr($ex2[0],strpos($ex2[0],'-')+1);
					$itemsArray[$key][$subkey] = $ex2[1];
				}
				
				if($settings['cat-type']=='years'){
					wp_my_timeline_cat_years($itemsArray);
					//usort($itemsArray, "wp_my_timeline_cmp");
				}
				$arr = $itemsArray[$id];
				
				
				$frontHtml ='';
		
				if ($arr['item-open-image'] != '') {
					$frontHtml .= '
			<a class="timeline_rollover_bottom con_borderImage" href="'.(($arr['item-open-prettyPhoto'] != '')? $arr['item-open-prettyPhoto'] : $arr['item-open-image']).'" rel="prettyPhoto[timeline]">';
					$image = '';
					if(!empty($settings['vertical'])){
						$imgw = (int)$settings['item-width'];
						$imgh = (int)$settings['item-open-image-height'];
						$image = bro_images::get_image($arr['item-open-image'], $imgw, $imgh);
						$image = '<img src="'. $image .'" alt=""/>';
					}else {
						if($arr['item-image'] != '') {
							$imgw = (int)$settings['item-open-width'];
							$imgh = (int)$settings['item-open-image-height'];
							$image = bro_images::get_image($arr['item-open-image'], $imgw, $imgh);
							$image = '<img src="'. $image .'" alt=""/>';
						}
					}
			
					$frontHtml .= '
			'.$image. '</a>
			<div class="timeline_open_content" style="height: '. $open_content_height.'px">';
			
				} 
				else { 
					$frontHtml .= '
			<div class="timeline_open_content" style="height: '. (intval($settings['item-height']) - 2*intval($settings['item-open-content-padding'])).'px">';
				}
				
			
				if ($arr['item-open-title'] != '') { 
					$frontHtml .= '
				<h2>'.$arr['item-open-title'].'</h2>';
				} 
				$frontHtml .= '
				<span'.(!isset($arr['desable-scroll']) || !$arr['desable-scroll'] ? ' class="scrollable-content"' : '').'>
				' .$arr['item-open-content'].'
				</span>
			</div>';
			
				echo do_shortcode($frontHtml);
			}

			die();
		}
	}
	
	
		
	function admin_page() {
		include_once($this->path . '/pages/content_timeline_index.php');
	}
	function admin_translations_page(){
		include_once($this->path . '/pages/content_timeline_wpml.php');
	}
	function admin_edit_page() {
		include_once($this->path . '/pages/content_timeline_edit.php');
	}

	function admin_recommended_page() {
		include_once($this->path . '/pages/content_timeline_recommended.php');

	}
	
	function shortcode($atts) {
		extract(shortcode_atts(array(
			'id' => ''
		), $atts));
		
		require($this->path . '/pages/content_timeline_frontend.php');
		$frontHtml = preg_replace('/\s+/', ' ',$frontHtml);

		return do_shortcode($frontHtml);
	}
	
	function frontend_includes() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jQuery-easing', $this->url . 'js/frontend/jquery.easing.1.3.js' );
		//bilo min
		wp_enqueue_script('jQuery-timeline', $this->url . 'js/frontend/jquery.timeline.js' );
		wp_enqueue_script('jQuery--vertical-timeline', $this->url . 'js/frontend/jquery.Verticaltimeline.js' );
		
		wp_enqueue_script('jQuery-mousew', $this->url . 'js/frontend/jquery.mousewheel.min.js' );
		wp_enqueue_script('jQuery-customScroll', $this->url . 'js/frontend/jquery.mCustomScrollbar.min.js' );
		wp_enqueue_script('jQuery-ui');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('rollover', $this->url . 'js/frontend/rollover.js' );
		wp_enqueue_script('jquery-prettyPhoto', $this->url . 'js/frontend/jquery.prettyPhoto.js' );
		
		wp_enqueue_style( 'timeline-css', $this->url . 'css/frontend/timeline.css' );
		wp_enqueue_style( 'customScroll-css', $this->url . 'css/frontend/jquery.mCustomScrollbar.css' );
		wp_enqueue_style( 'prettyPhoto-css', $this->url . 'css/frontend/prettyPhoto.css' );
		$url=$this->url.'/css/frontend/awesome/font-awesome.css';
		wp_enqueue_style('my_timeline_font_awesome',$url);
		$url=$this->url.'/js/frontend/jquery.my_add_function.js';
		wp_enqueue_script('my_timeline_added_script',$url);
		//$url=$this->url.'/css/frontend/lineicons/styles.css';
		//wp_enqueue_style('my_timeline_lineicons_css',$url);
		$url=$this->url.'/css/frontend/themify-icons.css';
		wp_enqueue_style('my_timeline_lineicons_css',$url);
		
		
		
	}
}
?>