<?php
if(!defined('ABSPATH'))die('');
global $wp_my_timeline_load_timeout;
$wp_my_timeline_load_timeout=6*3600;
global $wp_my_timeline_limit_total_posts;
$wp_my_timeline_limit_total_posts=-1;
global $wp_my_timeline_has_wmpl;
$wp_my_timeline_has_wmpl=false;
global $wp_my_timeline_wpml_languages;
global $wp_my_timeline_wpml_deafult_lang;
global $wp_my_timeline_translations;
$wp_my_timeline_translations['months']=array(
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
);
function wp_my_timeline_clone_timeline_settings($id){
	
}
function wp_my_timeline_get_translations($lang){
	$t=get_option("_my_timeline_translations_1234");
	global $wp_my_timeline_translations;
	
	if(!empty($t[$lang])){
		return $t[$lang];
	}else {
	return $wp_my_timeline_translations;
		
	}
}
function wp_my_timeline_getCatsWpml($lang){
	global $wp_my_timeline_wpml_deafult_lang;
	if($lang==$wp_my_timeline_wpml_deafult_lang){
		$post_types=get_post_types('','names');
		$categories = array();
		foreach ($post_types as $post_type ) {
			if (!in_array($post_type, array('page', 'attachment', 'revision', 'nav_menu_item'))) {
				$newCats = get_categories(array('type' => $post_type));
				foreach ($newCats as $post_cat) {
					if (!in_array($post_cat, $categories)) {
						array_push($categories, $post_cat);
					}
				}
			}
		}
	}else {
		$pre=ICL_LANGUAGE_CODE;
		define(ICL_LANGUAGE_CODE,$lang);
		$post_types=get_post_types('','names');
		$categories = array();
		foreach ($post_types as $post_type ) {
			if (!in_array($post_type, array('page', 'attachment', 'revision', 'nav_menu_item'))) {
				$newCats = get_categories(array('type' => $post_type));
				foreach ($newCats as $post_cat) {
					if (!in_array($post_cat, $categories)) {
						array_push($categories, $post_cat);
					}
				}
			}
		}
		//apply_filters( 'wpml_elements_without_translations', array $element_ids, array $args )
	}
	return $categories;
}
/**
 * 
 * @param unknown $lang
 */
function wp_my_timeline_switch_to_lang($lang){
	if (class_exists('SitePress')) {	
	global $sitepress;
	$sitepress->switch_lang($lang);
	wp_my_timeline_debug_title("Switrch to lang", $lang);
	}
}
/**
 * Has wpml
 */
function wp_my_timeline_has_wmpl(){
	global $wp_my_timeline_has_wmpl;
	global $wp_my_timeline_wpml_deafult_lang;
	
	if (class_exists('SitePress')) {
		$wp_my_timeline_has_wmpl=true;
		//echo 'Has wpml';
		global $sitepress;
		$def=$sitepress->get_default_language();
		//wp_my_timeline_debug_title("Def", $def);
		$wp_my_timeline_wpml_deafult_lang=$def;
		wp_my_timeline_active_languages();
	}
	
}
/**
 * Active languages
 */
function wp_my_timeline_active_languages(){
	global $wp_my_timeline_has_wmpl;
	global $wp_my_timeline_wpml_languages;
	//wp_my_timeline_debug_title("Has wpml", $wp_my_timeline_has_wmpl);
	if($wp_my_timeline_has_wmpl){
		
		$langs=icl_get_languages('skip_missing=0&orderby=code');
		$wp_my_timeline_wpml_languages=$langs;
	}
	//wp_my_timeline_debug_title("Langs", $wp_my_timeline_wpml_languages);
}
function wp_my_timeline_debug_title($t,$o){
	?>
	<div class="my_timeline_debug">
	<h4><?php echo $t;?></h4>
	<pre><?php print_r($o);?></pre>
	</div>
	<?php
}
function wp_my_timeline_get_last_item_key($items){
	$my_id=0;
	$max=0;
	if(is_array($items)){
		foreach($items as $key=>$val){
			$my_id=$key;
			$my_id=str_replace('sort', '', $my_id);
			if($my_id>$max)$max=$my_id;
		}
	}
	
	return $max;
}
function wp_my_timeline_get_timestamp($y,$m,$d){
	$t=$d;
	$arr=array(
		31,28,31,30,31,30,31,31,30,31,30,31
	);
	$y1=$m-1;
	for($i=0;$i<$y1;$i++){
		$t+=$arr[$i];
	}
	$sum=array_sum($arr);
	$t+=$y*$sum;
	return $t;
	
}
function wp_my_timeline_cat_years(&$items){
	foreach($items as $key=>$val){
		$k=$key;
		$my_arr=explode("/",$val['dataid']);
		$month=$my_arr[1];
		$day=$my_arr[0];
		$year=$my_arr[2];
		$my_date=$month.'/'.$day.'/'.$year;
		//$my_timestamp=strtotime($my_date);
		//$timestamp=strtotime($val['dataid']);
		$timestamp=wp_my_timeline_get_timestamp($year,$month,$day);
		$items[$k]['my_timestamp']=$timestamp;
		$items[$k]['categoryid']='timeline';
		$items[$k]['node-name']=$year;
	}
	usort($items, "wp_my_timeline_cmp");
}
/**
 * 
 * @param unknown $a
 * @param unknown $b
 * @return number
 */
function wp_my_timeline_cmp($a,$b){
	if($a['my_timestamp']==$b['my_timestamp']){
		return 0;
	}
	if($a['my_timestamp']<$b['my_timestamp'])return -1;
	else return 1;
	
}
/**
 * 
 */
function wp_my_timeline_add_new_posts($id,$cats,&$items,$start_latest=false,$st_id){
	global $wp_my_timeline_limit_total_posts;
	$limit=-1;
	if($wp_my_timeline_limit_total_posts!=-1){
		if(count($items)>=$wp_my_timeline_limit_total_posts);
		$limit=$wp_my_timeline_limit_total_posts-count($items);
	}
	$cats_posts=array();
	$new_posts=array();
	global $my_ctimeline_object;
	if(!empty($cats)){
		foreach($cats as $key=>$val){
			$cats_posts[$key]=array();
		}
	}
	$my_debug=false;
	//if($id==5)$my_debug=true;
	$my_debug=false;
	if($my_debug){
	wp_my_timeline_debug_title('Cats', $cats);
	
	wp_my_timeline_debug_title('Old items', $items);
	
	wp_my_timeline_debug_title('Cats posts', $cats_posts);
	}
	$all_items_ids_12=array();
	foreach($items as $key=>$val){
		$post_id=$val['my-post-id'];
		$post_categories = get_the_category( $post_id );
		$term_id=$post_categories[0]->term_id;
		if(isset($cats_posts[$term_id])){
			$cats_posts[$term_id][]=$post_id;
		}
		$all_items_ids_12[]=$post_id;
	}
	if($my_debug){
	wp_my_timeline_debug_title('Cats posts', $cats_posts);
	}
	foreach ($cats as $cat_id=>$val){
		$the_query = new WP_Query( array( 'cat' => $cat_id, 'post_type' => 'any', 'posts_per_page'=>$limit, 'order' => 'ASC','post__not_in'=>$all_items_ids_12));
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			global $post;
			$post_id=get_the_ID();
			$thumb='';
			if ( has_post_thumbnail($post_id)) {
				$thumb=wp_get_attachment_url( get_post_thumbnail_id($post_id , 'full'));
			}
			$new_posts[]=array(
				'my-post-id'=>$post_id,
				'dataid'=>substr($post->post_date, 8, 2) . '/' . substr($post->post_date, 5, 2) . '/' . substr($post->post_date, 0, 4),
				'categoryid'=>$cats[$cat_id],
				'item-image'=>$thumb,
				'item-title'=>$post->post_title,
				'item-content'=>substr(wp_strip_all_tags($post->post_content),0,100),
				'item-open-content'=>$post->post_content,
				'item-open-title'=>$post->post_title,	
				'item-open-image'=>$thumb,
				
				'item-prettyPhoto'=>$thumb,	
				'item-open-prettyPhoto'=>$thumb,
				'node-name'=>'',
				'item-link'=>''									
		
			);
		}
		wp_reset_postdata();
	}
	if($my_debug){
	wp_my_timeline_debug_title('New posts', $new_posts);
	}
	global $wpdb;
	$wpdb->show_errors();
	//$st_id+=1;
	if(!empty($new_posts)){
		if(!$start_latest){
			
			$timeline = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ctimelines WHERE id='.$id);
			$timeline = $timeline[0];
			$items_str=$timeline->items;
			
			//$st_id=count($items)+1;
			
			$t_t=count($new_posts)-1;
			$my_c_12=0;
			$items_str.='||';
			foreach($new_posts as $key=>$val){
				$items_str.=wp_my_timeline_format_items_to_db($val, $st_id);
				$st_id++;
				if($my_c_12<$t_t)$items_str.='||';
				$my_c_12++;
			}
			$wpdb->update($wpdb->prefix.'ctimelines', array('items'=>$items_str), array('id'=>$id));
			if($my_debug){
			wp_my_timeline_debug_title("Items str", array('id'=>$id,'items_str'=>$items_str));
			}
		}else {
			$my_cats_ids=array();
			foreach($cats as $key=>$val){
				$my_cats_ids[]=$key;
			}
			$the_query = new WP_Query( array( 'category__in' => $my_cats_ids, 'post_type' => 'any', 'posts_per_page'=>1, 'order' => 'DESC'));
			$my_start_item_id='';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				global $post;
				$post_id=get_the_ID();
				$my_start_item_id=$post_id;
			}
			$items_str='';
			//$st_id=1;
			//$st_pre=1;
			foreach($items as $key=>$val){
				if(array_key_exists('start-item', $val)){
					unset($items[$key]['start-item']);
					
				}
				$st_pre=str_replace('sort', '', $key);
				$items_str.=wp_my_timeline_format_items_to_db($val, $st_pre);
				//$st_pre++;
				$items_str.='||';
			}
			$t_t=count($new_posts)-1;
			$my_c_12=0;
			foreach($new_posts as $key=>$val){
				if($val['my-post-id']==$my_start_item_id){
					$new_posts[$key]['start-item']='on';
					$val['start-item']='on';
				}
				$items_str.=wp_my_timeline_format_items_to_db($val, $st_id);
				$st_id++;
				if($my_c_12<$t_t)$items_str.='||';
				$my_c_12++;
			}
			if($my_debug){
			wp_my_timeline_debug_title("Items str", array('id'=>$id,'start-item'=>$my_start_item_id,'items_str'=>$items_str));
			}
			$wpdb->update($wpdb->prefix.'ctimelines', array('items'=>$items_str), array('id'=>$id));
			
		}
		
	}
	return $new_posts;
	
}
/**
 * Format array to save to db
 * @param unknown $arr
 * @param unknown $id
 * @return string
 */
function wp_my_timeline_format_items_to_db($arr,$id){
	$add_key='sort'.$id.'-';
	$str='';
	foreach($arr as $key=>$val){
		if(strlen($str)>0)$str.='||';
		$key_1=$add_key.$key;
		$str.=$key_1 . '::' . stripslashes($val);
	}
	//$str.='||';
	//wp_my_timeline_debug_title("Post data str", $str);
	return $str;
}
/**
  * Get cats ids	
 */
function wp_my_timeline_get_timeline_cats($settings){
	$ret=array();
	foreach(explode('||',$settings) as $val) {
		$expl = explode('::',$val);
		//if(substr($expl[0], 0, 18) == 'my_timeline_cats[]') {
		//echo 'key '.$expl[0].' value '.$expl[1];
		if(strpos($expl[0],'my_timeline_cats')===0){
			//echo 'key '.$expl[0].' value '.$expl[1];
			$term=get_term_by('name', $expl[1], 'category');
			//print_r($term);
			if(!empty($term)){
				$ret[$term->term_id]=$term->name;
			}
		}
	}
		return $ret;
}
/**
 * Share post
 * @param unknown $post_id
 * @param unknown $post_title
 * @param unknown $post_content
 * @param string $image
 * @param string $type
 * @return string
 */
function wp_my_timeline_share_post($post_id,$post_title,$post_content,$image='',$type='twitter'){
	if(!empty($post_id)){
		$url=get_permalink($post_id);
	}else $url=get_site_url();
	ob_start();
	switch($type){
		case 'twitter':
			$text=$post_title;
			if(strlen($text)<140){
				$left=140-strlen($text);
				if($left>40){
					$text.=' '.substr($post_content,0,$left);
				}
			}
		?>
		https://twitter.com/intent/tweet?url=<?php echo urlencode($url);?>&text=<?php echo urlencode($text);?>
		<?php 		
		break;
		case 'facebook':
			$text=substr($post_content,0,200);
		?>
		https://www.facebook.com/dialog/feed?caption=<?php echo urlencode($post_title)?>&link=<?php echo urlencode($url)?>&picture=<?php echo urlencode($image);?>&description=<?php echo urlencode($text);?>
		<?php 
		break;	 	
		case 'google':
			?>
			https://plus.google.com/share?url=<?php echo urlencode($url);?>
			<?php 
		break;	
		case 'pinterest':
			?>
			http://pinterest.com/pin/create/button/?url=<?php echo urlencode($url);?>&media=<?php echo urlencode($image);?>
			<?php 
		break;	  
	}
	$url=ob_get_clean();
	return $url;
}
/**
 * Get metadata
 * @param unknown $post_id
 */
function wp_my_timeline_get_post_meta_data($post_id){
	$ret['hearts']=0;
	$ret['comments']=0;
	if(empty($post_id))return $ret;
	$my_var=get_comments_number($post_id);
	//$my_var=rand(1E3, 1E9);
	$ret['comments']=wp_my_timeline_format_number($my_var);
	$my_var=get_post_meta($post_id,'_my_timeline_12_12_hearts',true);
	if(empty($my_var)){
		$my_var=0;
	}
	$ret['hearts']=wp_my_timeline_format_number($my_var);
	return $ret;
}
/**
 * 
 * @param unknown $var
 * @return Ambigous <string, unknown>
 */
function wp_my_timeline_format_number($var){
	$check=array(
		'T'=>1E12,
		'G'=>1E9,
		'M'=>1E6,
		'K'=>1E3			
	);
	$ret=$var;
	foreach($check as $k=>$v){
		if($var>$v){
			$t=floor($var/$v);
			if(strlen($t)>1){
				$ret=substr($t,0,1).$k;
			}else $ret=$t.$k;
		}
	}
	return $ret;	
}
/**
 * Get ost id by title
 * @param unknown $title
 * @return boolean
 */
function wp_timeline_get_post_id_by_title($title){
	global $wpdb;
	$query="SELECT ID FROM ".$wpdb->posts." WHERE post_status='publish' ";
	$query.=" AND post_title=%s";
	$query=$wpdb->prepare($query, $title);
	$res=$wpdb->get_results($query);
	if(count($res)>1)return false;
	else return $res[0]->ID;
	
}
/**
 * Render plugin options
 * @param unknown $options
 */
function wp_my_timeline_render_options($options,$title,$append=''){
	static $wp_my_timeline_options_index=0;
	$wp_my_timeline_options_index++;
	global $wp_my_timeline_saved_options;
	foreach($options as $key=>$val){
		if(isset($wp_my_timeline_saved_options[$key])){
			$options[$key]['value']=$wp_my_timeline_saved_options[$key];
		}
	}
	global $wp_version;
	$my_version=(float)$wp_version;
	$my_is_44=($my_version>= 4.4 ? true:false);
	?>
	
					<div id="pbox<?php echo $wp_my_timeline_options_index?>" class="postbox closed" >
						<div class="handlediv" title="Click to toggle">
						<?php if($my_is_44){?>
								<span class="toggle-indicator" aria-hidden="true"></span>
							<?php }else {?>
							<br />
							<?php }?>
						</div>
						<div class="my_blue_header my_no_margin">
						<h2 class='hndle'><span><?php echo $title;?></span></h2>
						<div class="inside">
						<table class="fields-group misc-pub-section">
						<?php 
	foreach($options as $key=>$val){?>
									
									<?php
										if($val['type']=='image'){
											?>
											<tr class="field-row" id="my_option_<?php echo $key?>">
											<td colspan="2">
												<span class="timeline-help">? <span class="timeline-tooltip"><?php echo $val['tooltip'];?></span></span>
											<label for="<?php echo esc_attr($key);?>"><?php echo $val['title']?></label>
									
											</td>
											</tr>
											<tr class="field-row">
											<td colspan="2">
												<div class="misc-pub-section timeline-pub-section">
												<?php echo wp_my_timeline_render_element($key, $val);?>
												</div>
											</td>
											</tr>
										<?php 	
										} 
										else if($val['type']=='my_line_1'){
										?>
										<tr class="field-row">
										<td colspan="2">
										<h3 class="my_submenu_1"><?php echo $val['title'];?></h3>
										</td>
										<?php 
										}
										else if($val['type']=='color'){
										?>
										<tr id="my_option_<?php echo $key?>">
										<td colspan="2">
										<span class="timeline-help">? <span class="timeline-tooltip"><?php echo $val['tooltip'];?></span></span>
											<label for="<?php echo esc_attr($key);?>"><?php echo $val['title']?></label>
										</td>
										<tr>
										<tr class="">
											<td colspan="2">
											<?php echo wp_my_timeline_render_element($key,$val);?>
											</td>
										</tr>
										<?php 
										}
										else if($val['type']=='my_line'){?>
										<tr class="field-row">
										<td colspan="2">
										<h3 class="my_submenu"><?php echo $val['title'];?></h3>
										</td>
									<?php }else{?>
									<tr class="field-row" id="my_option_<?php echo $key?>">
									<td>
										<span class="timeline-help">? <span class="timeline-tooltip"><?php echo $val['tooltip'];?></span></span>
										<label for="<?php echo  esc_attr($key);?>" ><?php echo $val['title'];?></label>
									</td>
									<td>
										<?php echo wp_my_timeline_render_element($key,$val);?>
										<?php if(isset($val['unit'])){?>
										<span class="unit"><?php echo $val['unit'];?></span>
										<?php }?>
									</td>
									<?php }?>
									</tr>
									
								<?php }
	?>
				</table>
			<?php if(!empty($append)){?>
			<?php echo $append;?>
				
			<?php }?>	
	</div>
							</div>
						</div>
<?php 										
}
/**
 * render html element
 * @param unknown $name
 * @param unknown $element
 * @return string
 */
function wp_my_timeline_render_element($name,$element){
	$type=$element['type'];
	if(isset($element['default'])){
		$value=$element['default'];
	}
	if(isset($element['value'])){
		$value=$element['value'];
	}
	ob_start();
	switch($type){
		case 'image':
			global $wp_my_timeline_url;
			if(!empty($value))$image=$value;
			else $image=$wp_my_timeline_url.'/images/no_image.jpg';
			?>
			<div class="cw-image-select-holder">
			<input id="<?php echo esc_attr($name).'-input'?>" name="<?php echo esc_attr($name)?>" type="hidden" value="<?php if(isset($value))echo $value; ?>" />
									<a href="#" id="<?php echo esc_attr($name);?>" class="cw-image-upload" style="<?php echo 'background: url(' . $image . ');'; ?>"></a>
									<small><?php echo __("Click on image to change, or");?> <a href="#" class="remove-image">remove image</a></small>
								</div>
			
			<?php 
		break;	
		case 'color':
			?>
			<input class="timeline_color_input"  id="<?php echo esc_attr($name)?>" name="<?php echo esc_attr($name)?>"  value="<?php if(isset($value))echo $value;?>" type="text" style="background:#<?php if(isset($value))echo $value; ?>;">	
							
			<?php 
		break;	
		case 'text':
			?>
		<input type="text" value="<?php if(isset($value))echo esc_attr($value);?>" name="<?php echo esc_attr($name);?>" id="<?php echo esc_attr($name)?>" size="<?php if(isset($element['size']))echo esc_attr($element['size']);?>"/>
			
			<?php 
		break;	
		case 'checkbox':
		?>
		<input type="checkbox" <?php if(!empty($value))echo 'checked="checked"'?> value="<?php if(isset($element['val']))echo esc_attr($element['val']);else echo '1';?>" name="<?php echo esc_attr($name);?>" id="<?php echo esc_attr($name)?>"/>
		<?php 
		break;	
		case 'select':
		?>
		<select name="<?php echo esc_attr($name);?>" id="<?php echo esc_attr($name)?>">
			<?php if(!empty($element['values'])){?>
				<?php foreach($element['values'] as $k=>$v){?>
				<option <?php if(isset($value)&&$value==$k)echo 'selected="selected"'?> value="<?php echo esc_attr($k)?>"><?php echo $v?></option>
				<?php }?>
			<?php }?>
		</select>
		<?php
		break; 
	}
	$html=ob_get_clean();
	return $html;
}