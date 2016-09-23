<?php 
/*$date="03/11/1897";
$timestamp=strtotime($date);
echo 'Timestamp '.$timestamp;
echo date('Y',$timestamp);
*/
global $wp_my_timeline_wpml_deafult_lang;
global $wp_my_timeline_has_wmpl;
global $wp_my_timeline_wpml_languages;
global $my_lang;
$my_has_id=false;
?>
<div class="wrap">
	<?php 
	include_once($this->path . '/pages/default_settings.php');
	global $wpdb;
	global $wp_my_timeline_saved_options;
	$wp_my_timeline_saved_options=array();
	if(isset($_GET['id'])) {
		global $wpdb;
		$my_has_id=true;
		$timeline = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ctimelines WHERE id='.$_GET['id']);
		$timeline = $timeline[0];
		$pageName = 'Edit timeline';
		$title = $timeline->name;
		foreach(explode('||',$timeline->settings) as $val) {
			$expl = explode('::',$val);
			$settings[$expl[0]] = $expl[1];
		}
		$wp_my_timeline_saved_options=$settings;
		$my_cats_timeline=array();
		$my_cats_timeline=wp_my_timeline_get_timeline_cats($timeline->settings);
		if(!empty($settings['my_lang']))
		$my_lang=$settings['my_lang'];
		else unset($my_lang);
	}else if(isset($_GET['my_clone_s'])){
		global $wpdb;
		$my_has_id=false;
		$timeline = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ctimelines WHERE id='.$_GET['my_clone_s']);
		$timeline = $timeline[0];
		$pageName = 'Ne timeline';
		$title = '';//$timeline->name;
		foreach(explode('||',$timeline->settings) as $val) {
			$expl = explode('::',$val);
		$settings[$expl[0]] = $expl[1];
		}
		$wp_my_timeline_saved_options=$settings;
		unset($timeline);
		if(!empty($settings['my_lang']))$my_lang=$settings['my_lang'];
		else unset($my_lang);
	
	}else if(isset($_GET['my_clone'])){
		global $wpdb;
		$my_has_id=false;
		$my_clone_posts=true;
		$timeline = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ctimelines WHERE id='.$_GET['my_clone']);
		$timeline = $timeline[0];
		$pageName = 'New timeline';
		$title ='';// $timeline->name;
		foreach(explode('||',$timeline->settings) as $val) {
			$expl = explode('::',$val);
		$settings[$expl[0]] = $expl[1];
		}
		$wp_my_timeline_saved_options=$settings;
		$my_cats_timeline=array();
		$my_cats_timeline=wp_my_timeline_get_timeline_cats($timeline->settings);
		
		if(!empty($settings['my_lang']))$my_lang=$settings['my_lang'];
		else unset($my_lang);
	}
	else {
		$pageName = 'New timeline';
		$title = '';
	}
	
	?>
	
	
	<input type="hidden" id="plugin-url" value="<?php echo $this->url; ?>"/>
	<h2><?php echo $pageName; ?>
		<a href="<?php echo admin_url( "admin.php?page=contenttimeline" ); ?>" class="add-new-h2">Cancel</a>
		<?php if(isset($timeline)&&(!empty($settings['load-post-dynamic']))){?>
		<?php 
		$my_id_12=@$_GET['id'];
		if(isset($_GET['my_clear_cache'])){
			$file_1=$this->path.'/tmp/update_'.$my_id_12.'.tmp';
			//echo $file_1;
			if(file_exists($file_1))unlink($file_1);
			}
		?>
		&nbsp;<a href="<?php echo admin_url( "admin.php?page=contenttimeline_edit&id=".$my_id_12.'&my_clear_cache=1' ); ?>" class="add-new-h2">Clear Load post cache</a>
		<?php }?>	
	</h2>
	<?php if(!empty($wp_my_timeline_has_wmpl)){
		?>
		<div class="my_wpml_switcher_div">
		<?php 
		//wp_my_timeline_debug_title("Langs", $wp_my_timeline_wpml_languages);
		//echo 'Current lang '.$my_lang;
		?>
		<ul class="my_wpml_switcher">
		<?php 
		if(!$my_has_id){
		foreach( $wp_my_timeline_wpml_languages as $k123=>$v123){
			?>
			<li>
			<?php if($v123['code']!=$my_lang){?>
			<a href="<?php echo admin_url( "admin.php?page=contenttimeline_edit&lang=".$v123['code']);?>"><?php echo $v123['display_name'];?></a>
			<?php }else {?>
			<span><?php echo __("Timeline language : ",'my_content_timeline').$v123['display_name'];?></span>
			<?php }?>
			</li>
			<?php 
		}
		}else {
		foreach( $wp_my_timeline_wpml_languages as $k123=>$v123){
			if($v123['code']==$my_lang){
			
		?>	
			<li>
			<span><?php echo __("Timeline language : ",'my_content_timeline').$v123['display_name'];?></span>
			</li>
			<?php 
			}
		}
		
			
		}
		
		?>
		</ul>
		</div>
	<?php }?>
	<div class="form_result"></div>
	<form name="post_form"  method="post" id="post_form">
	<?php if(!empty($wp_my_timeline_has_wmpl)){ 
	if(!empty($my_lang)){
		?>
	<input type="hidden" name="my_lang" value="<?php echo $my_lang;?>"/>
	<?php }else {
	if(!empty($wp_my_timeline_wpml_languages)){
										?>
										<h4><?php echo __("Select timeline language")?></h4>
										<select id="my_langs_wpml" name="my_lang">
											<?php foreach($wp_my_timeline_wpml_languages as $k12=>$v12){?>
											<option value="<?php echo $v12['code']?>" <?php if($wp_my_timeline_wpml_deafult_lang==$v12['code'])echo 'selected="selected"'?>><?php echo $v12['display_name']?></option>
											<?php }?>
										</select>
	<?php }}?>
	<?php }?>
	<?php if(!empty($my_cats_timeline)){?>
		<?php foreach($my_cats_timeline as $key=>$val){?>
		<input type="hidden" name="my_timeline_cats[]" id="my_timeline_cats" value="<?php echo esc_attr($val);?>"/>
		<?php }?>
	<?php }else {?>
	
	<?php }?>
	<input type="hidden" name="timeline_id" id="timeline_id" value="<?php if(isset($_GET['id']))echo $_GET['id']; ?>" />
	<div id="poststuf">
	
		<div id="post-body" class="metabox-holder columns-2" style="margin-right:300px; padding:0;">
		
			<div id="post-body-content">
				<div class="my_blue_header">
					<h2><?php echo __("Edit timeline",'my_content_timeline');?></h2>
					<div id="titlediv">
						<div id="titlewrap">
							<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title">Enter title here</label>
							<input type="text" placeholder="<?php echo __("Name Your Timeline",'my_content_timeline');?>" name="timeline_title" size="30" tabindex="1" value="<?php echo $title; ?>" id="title" autocomplete="off" />
						</div>
				</div>
				</div>
				<div class="my_blue_header">
				
				<h2 class="" style="">Items</h2>
				<div class="my_inner_timeline">
					<div class="my_add_item">
					<?php if(!isset($timeline)){?>
						<h3 id="my_add_first_item"><?php echo __("Add Your First Item",'my_content_timeline');?></h3>
					<?php }?>
					<p id="my_p_explain">
					<?php echo __("Add existing posts or entire categories",'my_content_timeline');?><br/>
					<?php echo __("as seperate timeline cards",'my_content_timeline');?>
					</p>
					<a id="tsort-add-new" class="" style="display:block; margin:4px 10px;" href="#">+ Add New item / Category</a>
					
					<div class="clear"></div>
					</div>
					<ul id="timeline-sortable">
					<?php 
					if (isset($timeline) && $timeline->items != '') {
						$explode = explode('||',$timeline->items);
						$itemsArray = array();
						//wp_my_timeline_debug_title("Items data", $explode);
						foreach ($explode as $it) {
							$ex2 = explode('::', $it);
							$key = substr($ex2[0],0,strpos($ex2[0],'-'));
							$subkey = substr($ex2[0],strpos($ex2[0],'-')+1);
							$itemsArray[$key][$subkey] = $ex2[1];
					}
					//wp_my_timeline_debug_title("Items data", $itemsArray);
						
					$my_old_post_ids=array();
					foreach ($itemsArray as $key => $arr) {
						$num = substr($key,4);
					?>
						
					
					<li id="<?php echo $key; ?>" class="sortableItem">
						<div class="tsort-plus">+</div>
						<div class="tsort-header">Item <?php echo $num; ?> <small><i>- <?php echo $arr['item-title']; ?></i></small> &nbsp;<a href="#" class="tsort-delete"><i>delete</i></a></div>
						<div class="tsort-content">
							<div class="tsort-dataid">
								<input name="<?php echo $key?>-my-post-id" id="<?php echo $key;?>-my-post-id" class="" type="hidden" value="<?php echo esc_attr($arr['my-post-id'])?>"/>								
'								
								<input type="checkbox" id="<?php echo $key; ?>-start-item" name="<?php echo $key; ?>-start-item" class="tsort-start-item alignright" <?php if(array_key_exists('start-item', $arr)) echo 'checked="checked"'; ?> />
								<label for="<?php echo $key; ?>-start-item" class="alignright">Start item &nbsp;</label> 
								<span class="timeline-help">? <span class="timeline-tooltip">Argument by which are elements organised (date - dd/mm/yyyy, Category - full category name). Different field is used for different categorizing type.</span></span>
								<label for="<?php echo $key; ?>-dataid">Date</label>
								<input style="margin-left:5px;" id="<?php echo $key; ?>-dataid" name="<?php echo $key; ?>-dataid" value="<?php echo $arr['dataid']; ?>" type="text" class="data_id" />
								<label style="margin-left:5px;" for="<?php echo $key; ?>-categoryid">Category</label>
								<input style="margin-left:5px;" id="<?php echo $key; ?>-categoryid" name="<?php echo $key; ?>-categoryid" value="<?php echo $arr['categoryid']; ?>" class="category_id" type="text"/>
								<label style="margin-left:5px;" for="<?php echo $key; ?>-node-name">Title of the timeline node (optional)</label>
								<input style="margin-left:5px;" id="<?php echo $key; ?>-node-name" name="<?php echo $key; ?>-node-name" value="<?php echo $arr['node-name']; ?>" type="text" />
								
							</div>
							<div class="tsort-item">
								<h3 style="padding-left:0;"><span class="timeline-help">? <span class="timeline-tooltip">Base item content (image, title and content).</span></span>Item Options</h3>
								<div class="tsort-image"><img id="<?php echo $key; ?>-item-image" src="<?php echo(($arr['item-image'] != '') ? bro_images::get_image($arr['item-image'], 258, 50) : $this->url . 'images/no_image.jpg'); ?>" /><a href="#" id="<?php echo $key; ?>-item-image-change" class="tsort-change">Change</a>
									<input id="<?php echo $key; ?>-item-image-input" name="<?php echo $key; ?>-item-image" type="hidden" value="<?php echo $arr['item-image']; ?>" />
									<a href="#" id="<?php echo $key; ?>-item-image-remove" class="tsort-remove">Remove</a>
								</div>
								<input class="tsort-title" name="<?php echo $key; ?>-item-title" value="<?php echo $arr['item-title']; ?>" type="text" />
								<div class="clear"></div>
								<textarea class="tsort-contarea" name="<?php echo $key; ?>-item-content"><?php echo $arr['item-content']; ?></textarea>
								<table style="width:100%;">
								<tr>
									<td style="width:120px;"><span class="timeline-help">? <span class="timeline-tooltip">"PrittyPhoto"(Lightbox) URL, it can be image, video or site url. Leave it empty to link to full-size image.</span></span><label for="<?php echo $key; ?>-item-prettyPhoto">PrettyPhoto URL</label></td>
									<td><input class="tsort-prettyPhoto" name="<?php echo $key; ?>-item-prettyPhoto" value="<?php echo $arr['item-prettyPhoto']; ?>" type="text" style="width:100%;" /></td>
								</tr>
								<tr>
									<td style="width:120px;"><span class="timeline-help">? <span class="timeline-tooltip">Link to post or some other page. Leave it empty for default behavior. Set it to "#" to disable "Read more" button on individual items.</span></span><label for="<?php echo $key; ?>-item-prettyPhoto">Button URL</label></td>
									<td><input class="tsort-link" name="<?php echo $key; ?>-item-link" value="<?php echo (isset($arr['item-link']) ? $arr['item-link'] : ''); ?>" type="text" style="width:100%;" /></td>
								</tr>
								</table>
								
							</div>
							<div class="tsort-itemopen">
								<h3 style="padding-left:0;"><span class="timeline-help">? <span class="timeline-tooltip">Opened item content (image, title and content).</span></span>Item Open Options</h3>
								<div class="tsort-image"><img id="<?php echo $key; ?>-item-open-image" src="<?php echo(($arr['item-open-image'] != '') ? bro_images::get_image($arr['item-open-image'], 258, 50) : $this->url . 'images/no_image.jpg'); ?>" /><a href="#" id="<?php echo $key; ?>-item-open-image-change" class="tsort-change">Change</a>
									<input id="<?php echo $key; ?>-item-open-image-input" name="<?php echo $key; ?>-item-open-image" type="hidden" value="<?php echo $arr['item-open-image']; ?>" />
									<a href="#" id="<?php echo $key; ?>-item-open-image-remove" class="tsort-remove">Remove</a>
								</div>
								<input class="tsort-title" name="<?php echo $key; ?>-item-open-title" value="<?php echo $arr['item-open-title']; ?>" type="text" />
								<div class="clear"></div>
								<textarea class="tsort-contarea" name="<?php echo $key; ?>-item-open-content"><?php echo $arr['item-open-content']; ?></textarea>
								<table style="width:100%;">
								<tr>
									<td style="width:120px;"><span class="timeline-help">? <span class="timeline-tooltip">"PrettyPhoto"(Lightbox) URL, it can be image, video or site url. LEAVE IT EMPTY TO DESPLAY FULL SIZED IMGAE.</span></span><label for="<?php echo $key; ?>-item-open-prettyPhoto">PrettyPhoto URL</label></td>
									<td><input class="tsort-prettyPhoto" name="<?php echo $key; ?>-item-open-prettyPhoto" value="<?php echo $arr['item-open-prettyPhoto']; ?>" type="text" style="width:100%;" /></td>
								</tr>
								
								</table>
								<label for="<?php echo $key; ?>-desable-scroll">Disable Scroll &nbsp;</label> 
								<input type="checkbox" id="<?php echo $key; ?>-desable-scroll" name="<?php echo $key; ?>-desable-scroll" <?php if(array_key_exists('desable-scroll',$arr)) echo 'checked="checked"'; ?> />
							</div>
						</div>
					</li>
					
					<?php 	
					}
				} ?>
				
				</ul>
				<div class="clear"></div>
				</div><!-- my_timeline_inner -->
				</div>
				<div id="style_preview">
				
				
				</div>
			
			</div>
		
			<div id="postbox-container-1" class="postbox-container">
				<div class="postbox">
					<div class="my_blue_header">
					<h2 class='hndle' style="cursor:auto"><span>Publish</span></h2>
					<div class="inside">
						<div id="save-progress" class="waiting ajax-saved" style="background-image: url(<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>)" ></div>
						<input name="preview-timeline" id="preview-timeline" value="Preview" class="alignleft" style="padding:3px 25px" type="submit" />
						<input name="save-timeline" id="save-timeline" value="Save timeline" class="alignright" style="padding:3px 15px" type="submit" />
						<img id="save-loader" src="<?php echo $this->url; ?>images/ajax-loader.gif" class="alignright" />
						<br class="clear" />		
					</div>
					</div>
				</div>
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php 
					ob_start();
					?>
					<div class="misc-pub-section timeline-pub-section">
								<?php /*
								<h3 style="margin-top:0; background:transparent;"><span class="timeline-help">? <span class="timeline-tooltip">Options for categorizing your posts.</span></span>Chronological Options</h3>
								*/ ?>
								
								<table class="fields-group">
								<?php /*
								<tr class="field-row">
									<td>
										<label for="hide-years">Hide Years</label>
									</td>
									<td>
										<input id="hide-years" name="hide-years" value="true" type="checkbox" <?php echo (($settings['hide-years']) ? 'checked="checked"' : '');?> />	
									</td>				
								</tr>
									
								<tr class="field-row">
									<td>
										<span class="timeline-help">? <span class="timeline-tooltip">Organize posts by date or some other criteria.</span></span>
										<label for="cat-type">Type</label>
									</td>
									<td>
										<select id="cat-type" name="cat-type">
											<option value="months" <?php echo (($settings['cat-type'] == 'months') ? 'selected="selected"' : ''); ?> >Months</option>
											<option value="categories" <?php echo (($settings['cat-type'] == 'categories') ? 'selected="selected"' : ''); ?>>Categories</option>
										</select>
										
										<?php 

?>
									</td>				
								</tr>
								
								
								*/ ?>
								<?php 
								/**
								 * Render Global settings
								 */
								?>
								
								<tr class="cat-display">
									<td>
										<span class="timeline-help">? <span class="timeline-tooltip">Number of posts per category/month (default 30).</span></span>
										<label for="number-per-cat">Number of posts</label>
									</td>
									<td>
										<input id="number-of-posts" name="number-of-posts" value="<?php if(isset($settings['number-of-posts']))echo $settings['number-of-posts']; ?>" size="5" type="text">
									</td>
								</tr>
								<tr class="cat-display">
									<td colspan="2" style="width:100%;">
									<h4 style="margin:0 0 5px 0; font-size:14px; border-bottom:1px solid #dddddd;">Categories:</h4>
									<?php /*
									<?php
									global $wp_my_timeline_has_wmpl;
									global $wp_my_timeline_wpml_languages;
									global $wp_my_timeline_wpml_deafult_lang;
									//if($wp_my_timeline_has_wmpl){
									?>
									<input type="hidden" id="my-wpml-has-wmpl" value="<?php if(!empty($wp_my_timeline_has_wmpl))echo '1';else echo '0';?>"/>
									<?php 
									if(!empty($wp_my_timeline_wpml_languages)){
										?>
										<select id="my_langs_wpml">
											<?php foreach($wp_my_timeline_wpml_languages as $k12=>$v12){?>
											<option value="<?php echo $v12->code?>" <?php if($wp_my_timeline_wpml_deafult_lang==$v12->code)echo 'selected="selected"'?>><?php echo $v12->display_naem?></option>
											<?php }?>
										</select>
									<?php foreach($wp_my_timeline_wpml_languages as $k12=>$v12){
											$categories=wp_my_timeline_getCatsWpml($v12->code);
											?>
											<select class="my_lang_codes_cats" data-lang="<?php echo $v12->code;?>">
											<?php foreach($categories as $k123=>$v123){?>
											<option value="<?php echo $v123->term_id;?>"><?php echo $v123->name?></option>
											<?php }?>
											
											</select>
											<?php
											$catString = '';
											foreach ($categories as $category) {
												$catString .= $category->name . '||';
												echo '
											<label for="cat-name-'.$category->term_id.'">'.$category->name.'</label>
											<input class="cat-name" name="cat-name-'.$category->term_id.'" value="'.$category->name.'" type="checkbox" '.((isset($settings['cat-name-'.$category->term_id]) && $settings['cat-name-'.$category->term_id]) ? 'checked="checked"' : '').'>';
											}
											if($catString != '') {
												echo '<input type="hidden" id="categories-hidden" value="'.substr($catString,0,strlen($catString)-2).'" />';
											}
										
										 }
									}else {*/
									?>
									<?php 
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
										$catString = '';
										foreach ($categories as $category) {
											$catString .= $category->name . '||';
											echo '
											<label for="cat-name-'.$category->term_id.'">'.$category->name.'</label>
											<input class="cat-name" name="cat-name-'.$category->term_id.'" value="'.$category->name.'" type="checkbox" '.((isset($settings['cat-name-'.$category->term_id]) && $settings['cat-name-'.$category->term_id]) ? 'checked="checked"' : '').'>';
											}
										if($catString != '') {
											echo '<input type="hidden" id="categories-hidden" value="'.substr($catString,0,strlen($catString)-2).'" />';
										}
									
									?>
									<?php //}?>
									</td>
								</tr>
								<tr class="cat-display">
									<td colspan="2" style="width:100%">
										<a href="#" id="cat-check-all" class="button button-highlighted alignleft" style="padding:0 27px" >Check all</a>
										<a href="#" id="cat-uncheck-all" class="button button-highlighted alignright" style="padding:0 27px" >Uncheck all</a>
										<div class="clear"></div>
									</td>
								</tr>
								
								
								
								</table>
								</div>
						<?php 
						global $wp_timeline_12_options;
						$append=ob_get_clean();
						wp_my_timeline_render_options($wp_timeline_12_options['global_settings'],__("Global Settings",'my_content_timeline'),$append);		
						wp_my_timeline_render_options($wp_timeline_12_options['vertical_settings'],__("Vertical Options",'my_content_timeline'));
						
							
						/**
						 * Render card item settings
						 */
						wp_my_timeline_render_options($wp_timeline_12_options['card_settings'],__("Card Settings",'my_content_timeline'));
						wp_my_timeline_render_options($wp_timeline_12_options['button_settings'],__("Button Settings",'my_content_timeline'));
						wp_my_timeline_render_options($wp_timeline_12_options['text_settings'],__("Font Settings",'my_content_timeline'));
						
					/*
					<div id="pbox1" class="postbox closed" >
						<div class="handlediv" title="Click to toggle"><br /></div>
						<div class="my_blue_header my_no_margin">
						<h2 class='hndle'><span>General Options</span></h2>
						<div class="inside">
							<?php global $wp_timeline_12_options;?>
							<table class="fields-group misc-pub-section">			
							<?php wp_my_timeline_render_options($wp_timeline_12_options['global_settings']);?>
							<?php /*
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Transition speed (default 500px).</span></span>
									<label for="scroll-speed" >Scroll Speed</label>
								</td>
								<td>
									<input id="scroll-speed" name="scroll-speed" value="<?php echo $settings['scroll-speed']; ?>" size="5" type="text">	
									<span class="unit">ms</span>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Transition easing function (default 'easeOutSine').</span></span>
									<label for="easing" >Easing</label>
								</td>
								<td>
									<select name="easing">
										<?php 
											$easingArray = array('easeInQuad', 'easeOutQuad','easeInOutQuad','easeInCubic','easeOutCubic','easeInOutCubic','easeInQuart','easeOutQuart','easeInOutQuart','easeInQuint','easeOutQuint','easeInOutQuint','easeInSine','easeOutSine','easeInOutSine','easeInExpo','easeOutExpo','easeInOutExpo','easeInCirc','easeOutCirc','easeInOutCirc','easeInElastic','easeOutElastic','easeInOutElastic','easeInBack','easeOutBack','easeInOutBack','easeInBounce','easeOutBounce','easeInOutBounce');
											foreach ($easingArray as $item) {
												echo '
										<option value="'.$item.'" '.(($item == $settings['easing']) ? 'selected="selected"' : '').'>'.$item.'</option>';
											}
											
										?>
										
									</select>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">If this option is enabled, it closes the timeline item after the transition to the next element takes place (default 'false').</span></span>
									<label for="item-transition-close" >Close item on transition</label>
								</td>
								<td>
										<input id="item-transition-close" name="item-transition-close" value="true" type="checkbox" <?php echo (($settings['item-transition-close']) ? 'checked="checked"' : '');?> />	
								</td>				
							</tr>
							
							
							
							</table>
							
							
							</div>
						</div>
					</div><!-- /GENERAL OPTIONS -->
					
					
					
					
					
					
					<div id="pbox2" class="postbox closed" >
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class='hndle'><span>Global Styling Options</span></h3>
						<div class="inside">
							<table class="fields-group misc-pub-section">	
							
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Width of the line element (default 920px).</span></span>
									<label for="line-width" >Timeline Width</label>
								</td>
								<td>
									<input id="line-width" name="line-width" value="<?php echo $settings['line-width']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Space between two items. If negative items overlap. (default 20px).</span></span>
									<label for="item-margin" >Item Margin</label>
								</td>
								<td>
									<input id="item-margin" name="item-margin" value="<?php echo $settings['item-margin']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>				
							</tr>
							
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Height of Item and Open item elements (default 360px).</span></span>
									<label for="item-height" >Item Height</label>
								</td>
								<td>
									<input id="item-height" name="item-height" value="<?php echo $settings['item-height']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Read more button ('Button' : adds read more button, 'Whole Item' : makes the entire item clickable, 'None' : Items don't open).</span></span>
									<label for="read-more">Read more</label>
								</td>
								<td>
									<select name="read-more" id="read-more">
										<option value="button" <?php if($settings['read-more'] == 'button') echo 'selected="selected"'; ?>>Button</option>
										<option value="whole-item" <?php if($settings['read-more'] == 'whole-item') echo 'selected="selected"'; ?>>Whole Item</option>
										<option value="none" <?php if($settings['read-more'] == 'none') echo 'selected="selected"'; ?>>None</option>
									</select>
								</td>				
							</tr>
							
								
							<tr>
								<td colspan="2">
									<span class="timeline-help">? <span class="timeline-tooltip">Color of description bar that appears when you hover date on the timeline.</span></span>
									<label for="node-desc-color">Node description color</label>
								</td>
							<tr>
							</tr>
								<td colspan="2">
									<input class="timeline_color_input"  id="node-desc-color" name="node-desc-color"  value="<?php echo $settings['node-desc-color']; ?>" type="text" style="background:#<?php echo $settings['node-desc-color']; ?>;">	
								</td>
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="hide-line">Hide Timeline</label>
								</td>
								<td>
									<input id="hide-line" name="hide-line" value="true" type="checkbox" <?php echo ($settings['hide-line'] ? 'checked="checked"' : '' ); ?>>	
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Color scheme of timeline element (default 'Light').</span></span>
									<label for="line-style">Line Style</label>
								</td>
								<td>
									<select name="line-style">
										<option value="flat" <?php echo (($settings['line-style'] == 'flat') ? 'selected="selected"' : '' ); ?>>Flat</option>
										<option value="light" <?php echo (($settings['line-style'] == 'light') ? 'selected="selected"' : '' ); ?>>Light</option>
										<option value="dark" <?php echo (($settings['line-style'] == 'dark') ? 'selected="selected"' : '' ); ?>>Dark</option>
									</select>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="hide-nav">Hide Navigation</label>
								</td>
								<td>
									<input id="hide-nav" name="hide-nav" value="true" type="checkbox" <?php echo ($settings['hide-nav'] ? 'checked="checked"' : '' ); ?>>	
								</td>				
							</tr>
							
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Color scheme of nav elements (default 'Light').</span></span>
									<label for="line-style">Nav Style</label>
								</td>
								<td>
									<select name="nav-style">
										<option value="flat" <?php echo (($settings['nav-style'] == 'flat') ? 'selected="selected"' : '' ); ?>>Flat</option>
										<option value="light" <?php echo (($settings['nav-style'] == 'light') ? 'selected="selected"' : '' ); ?>>Light</option>
										<option value="dark" <?php echo (($settings['nav-style'] == 'dark') ? 'selected="selected"' : '' ); ?>>Dark</option>
									</select>
								</td>				
							</tr>
							
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Shadow under elements (default 'show').</span></span>
									<label for="shadow">Shadow</label>
								</td>
								<td>
									<select name="shadow">
										<option value="show" <?php echo ((isset($settings['shadow']) && $settings['shadow'] == 'show') ? 'selected="selected"' : '' ); ?>>Show</option>
										<option value="on-hover" <?php echo ((isset($settings['shadow']) && $settings['shadow'] == 'on-hover') ? 'selected="selected"' : '' ); ?>>Show On Hover</option>
										<option value="hide" <?php echo ((isset($settings['shadow']) && $settings['shadow'] == 'hide') ? 'selected="selected"' : '' ); ?>>Hide</option>
									</select>
								</td>				
							</tr>
							
							
							
							</table>
						</div>
					</div><!-- /GLOBAL STYLING OPTIONS -->
					
					
					
					<div id="pbox3" class="postbox closed" >
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class='hndle'><span>Button styling Options</span></h3>
						<div class="inside">
							<table class="fields-group misc-pub-section">
								<tr class="field-row">
									<td>
										<span class="timeline-help">? <span class="timeline-tooltip">Button design.</span></span>
										<label for="button-type">Button type</label>
									</td>
									<td>
										<select name="button-type">
											<option value="flat" <?php echo (($settings['button-type'] == 'flat') ? 'selected="selected"' : '' ); ?>>Flat</option>
											<option value="flat-wide" <?php echo (($settings['button-type'] == 'flat-wide') ? 'selected="selected"' : '' ); ?>>Flat wide</option>
											<option value="sharp" <?php echo (($settings['button-type'] == 'sharp') ? 'selected="selected"' : '' ); ?>>Sharp</option>
										</select>
									</td>				
								</tr>
								<tr class="field-row">
									<td>
										<span class="timeline-help">? <span class="timeline-tooltip">Text of the 'Read more' button (default 'Read more').</span></span>
										<label for="more-text" >Read more button text</label>
									</td>
									<td>
										<input id="more-text" name="more-text" value="<?php echo $settings['more-text']; ?>" size="20" type="text">
									</td>				
								</tr>
								
								<tr class="field-row">
									<td>
										<span class="timeline-help">? <span class="timeline-tooltip">Text of the 'Close' element in open item (default 'Close').</span></span>
										<label for="close-text" >Close button text</label>
									</td>
									<td>
										<input id="close-text" name="close-text" value="<?php echo $settings['close-text']; ?>" size="20" type="text">
									</td>				
								</tr>
								<tr>
									<td colspan="2">
										<span class="timeline-help">? <span class="timeline-tooltip">Background color of 'Read More' and 'Close' buttons.</span></span>
										<label for="button-color">Button color</label>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<input class="timeline_color_input" id="button-color" name="button-color"  value="<?php echo $settings['button-color']; ?>" type="text">	
										
									</td>
								</tr>
									
								<tr>
									<td colspan="2">
										<span class="timeline-help">? <span class="timeline-tooltip">Hover color of 'Read More' and 'Close' buttons.</span></span>
										<label for="button-hover-color">Button hover color</label>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<input class="timeline_color_input" id="button-hover-color" name="button-hover-color"  value="<?php echo $settings['button-hover-color']; ?>" type="text">	
										
									</td>
								</tr>
							</table>
						</div>
					</div><!-- /BUTTON STYLING OPTIONS -->
					
					
					
					
					<div id="pbox4" class="postbox closed" >
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class='hndle'><span>Item Styling Options</span></h3>
						<div class="inside">
							<table class="fields-group misc-pub-section">			
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Width of items (default 240px).</span></span>
									<label for="item-width" >Width</label>
								</td>
								<td>
									<input id="item-width" name="item-width" value="<?php echo $settings['item-width']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Padding of items content.</span></span>
									<label for="item-content-padding" >Content Padding</label>
								</td>
								<td>
									<input id="item-content-padding" name="item-content-padding" value="<?php echo $settings['item-content-padding']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Height of images (Width is strached to element width).</span></span>
									<label for="item-image-height" >Image Height</label>
								</td>
								<td>
									<input id="item-image-height" name="item-image-height" value="<?php echo $settings['item-image-height']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>					
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-image-border-width" >Image Border Width</label>
								</td>
								<td>
									<input id="item-image-border-width" name="item-image-border-width" value="<?php echo $settings['item-image-border-width']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>					
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="show-date">Show date</label>
								</td>
								<td>
									<input id="show-date" name="show-date" value="true" type="checkbox" <?php echo ($settings['show-date'] ? 'checked="checked"' : '' ); ?>>	
								</td>				
							</tr>
								
							<tr>
								<td colspan="2">
									<label for="item-image-border-color">Image Border Color</label>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<input class="timeline_color_input" id="item-image-border-color" name="item-image-border-color"  value="<?php echo $settings['item-image-border-color']; ?>" type="text" style="background:#<?php echo $settings['item-image-border-color']; ?>;">	
								</td>
							</tr>
							</table>
							<div class="misc-pub-section timeline-pub-section">
								<h3 style="margin-top:0; background:transparent;"><span class="timeline-help">? <span class="timeline-tooltip">Font-family is inharited from your theme default fonts for H2 and default text.</span></span>Fonts</h3>	
							
							<table class="fields-group">	
							
							<tr class="field-row">
								<td>
									<label for="item-header-line-height" >Title Line Height</label>
								</td>
								<td>
									<input id="item-header-line-height" name="item-header-line-height" value="<?php echo $settings['item-header-line-height']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>		
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-header-font-size" >Title Font Size</label>
								</td>
								<td>
									<input id="item-header-font-size" name="item-header-font-size" value="<?php echo $settings['item-header-font-size']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>		
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-header-font-type" >Title Font Type</label>
								</td>
								<td>
									
									<select name="item-header-font-type">
										<option value="regular" <?php echo (($settings['item-header-font-type'] == 'regular') ? 'selected="selected"' : '' ); ?>>Regular</option>
										<option value="thick" <?php echo (($settings['item-header-font-type'] == 'thick') ? 'selected="selected"' : '' ); ?>>Thick</option>
										<option value="bold" <?php echo (($settings['item-header-font-type'] == 'bold') ? 'selected="selected"' : '' ); ?>>Bold</option>
										<option value="bold-italic" <?php echo (($settings['item-header-font-type'] == 'bold-italic') ? 'selected="selected"' : '' ); ?>>Bold-Italic</option>
										<option value="italic" <?php echo (($settings['item-header-font-type'] == 'italic') ? 'selected="selected"' : '' ); ?>>Italic</option>
									</select>
								</td>					
							</tr>
								
							<tr>
								<td colspan="2">
									<label for="item-header-font-color">Title Color</label>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<input class="timeline_color_input"  id="item-header-font-color" name="item-header-font-color"  value="<?php echo $settings['item-header-font-color']; ?>" type="text" style="background:#<?php echo $settings['item-header-font-color']; ?>;">
								</td>
							</tr>

							<tr class="field-row">
								<td>
									<label for="item-text-line-height" >Text Line Height</label>
								</td>
								<td>
									<input id="item-text-line-height" name="item-text-line-height" value="<?php echo $settings['item-text-line-height']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>		
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-text-font-size" >Text Font Size</label>
								</td>
								<td>
									<input id="item-text-font-size" name="item-text-font-size" value="<?php echo $settings['item-text-font-size']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>		
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-text-align" >Text alignment</label>
								</td>
								<td>
									
									<select name="item-text-align">
										<option value="left" <?php echo (($settings['item-text-align'] == 'left') ? 'selected="selected"' : '' ); ?>>Left</option>
										<option value="right" <?php echo (($settings['item-text-align'] == 'right') ? 'selected="selected"' : '' ); ?>>Right</option>
										<option value="center" <?php echo (($settings['item-text-align'] == 'center') ? 'selected="selected"' : '' ); ?>>Center</option>
									</select>
								</td>					
							</tr>
							<tr class="field-row">
								<td>
									<label for="item-text-font-type" >Text Font Type</label>
								</td>
								<td>
									
									<select name="item-text-font-type">
										<option value="regular" <?php echo (($settings['item-text-font-type'] == 'regular') ? 'selected="selected"' : '' ); ?>>Regular</option>
										<option value="thick" <?php echo (($settings['item-text-font-type'] == 'thick') ? 'selected="selected"' : '' ); ?>>Thick</option>
										<option value="bold" <?php echo (($settings['item-text-font-type'] == 'bold') ? 'selected="selected"' : '' ); ?>>Bold</option>
										<option value="bold-italic" <?php echo (($settings['item-text-font-type'] == 'bold-italic') ? 'selected="selected"' : '' ); ?>>Bold-Italic</option>
										<option value="italic" <?php echo (($settings['item-text-font-type'] == 'italic') ? 'selected="selected"' : '' ); ?>>Italic</option>
									</select>
								</td>					
							</tr>
								
							<tr>
								<td colspan="2">
									<label for="item-text-font-color">Text Color</label>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<input class="timeline_color_input" id="item-text-font-color" name="item-text-font-color"  value="<?php echo $settings['item-text-font-color']; ?>" type="text" style="background:#<?php echo $settings['item-text-font-color']; ?>;">
								</td>
							</tr>


							</table>
							</div>
							
							
							<div class="misc-pub-section timeline-pub-section">
								<h3 style="margin-top:0; background:transparent;"><span class="timeline-help">? <span class="timeline-tooltip">Base item background options.</span></span>Background</h3>
								
								<label>Color:</label><br />
								<input class="timeline_color_input" id="item-back-color" name="item-back-color"  value="<?php echo $settings['item-back-color']; ?>" type="text" style="background:#<?php echo $settings['item-back-color']; ?>;">
								<div class="clear"></div>
								<label>Image:</label>
								<div class="cw-image-select-holder">
									<input id="item-background-input" name="item-background" type="hidden" value="<?php echo $settings['item-background']; ?>" />
									<a href="#" id="item-background" class="cw-image-upload" style="<?php echo 'background: url(' . (($settings['item-background'] != '') ? $settings['item-background'] : $this->url . '/images/no_image.jpg') . ');'; ?>"></a>
									<small>Click on image to change, or <a href="#" class="remove-image">remove image</a></small>
								</div>
							</div>
							
						</div>
					</div><!-- /ITEM STYLING OPTIONS -->
					
					
					<div id="pbox5" class="postbox closed" >
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class='hndle'><span>Item Open Styling Options</span></h3>
						<div class="inside">
							<table class="fields-group misc-pub-section">			
							
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Width of open items (default 490px).</span></span>
									<label for="item-open-width" >Width</label>
								</td>
								<td>
									<input id="item-open-width" name="item-open-width" value="<?php echo $settings['item-open-width']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Padding of open items content.</span></span>
									<label for="item-open-content-padding" >Content Padding</label>
								</td>
								<td>
									<input id="item-open-content-padding" name="item-open-content-padding" value="<?php echo $settings['item-open-content-padding']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>				
							</tr>
							
							<tr class="field-row">
								<td>
									<span class="timeline-help">? <span class="timeline-tooltip">Height of images (Width is strached to element width).</span></span>
									<label for="item-open-image-height" >Image Height</label>
								</td>
								<td>
									<input id="item-open-image-height" name="item-open-image-height" value="<?php echo $settings['item-open-image-height']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>					
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-open-image-border-width" >Image Border Width</label>
								</td>
								<td>
									<input id="item-open-image-border-width" name="item-open-image-border-width" value="<?php echo $settings['item-open-image-border-width']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>					
							</tr>
								
							<tr>
								<td colspan="2">
									<label for="item-open-image-border-color">Image Border Color</label>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<input class="timeline_color_input" id="item-open-image-border-color" name="item-open-image-border-color"  value="<?php echo $settings['item-open-image-border-color']; ?>" type="text" style="background:#<?php echo $settings['item-open-image-border-color']; ?>;">
								</td>
							</tr>
							
							
							</table>
							<div class="misc-pub-section timeline-pub-section">
								<h3 style="margin-top:0; background:transparent;"><span class="timeline-help">? <span class="timeline-tooltip">Font-family is inharited from your theme default fonts for H2 and default text.</span></span>Fonts</h3>	
							
							<table class="fields-group">				
							<tr class="field-row">
								<td>
									<label for="item-open-header-line-height" >Title Line Height</label>
								</td>
								<td>
									<input id="item-open-header-line-height" name="item-open-header-line-height" value="<?php echo $settings['item-open-header-line-height']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>		
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-open-header-font-size" >Title Font Size</label>
								</td>
								<td>
									<input id="item-open-header-font-size" name="item-open-header-font-size" value="<?php echo $settings['item-open-header-font-size']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>		
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-open-header-font-type" >Title Font Type</label>
								</td>
								<td>
									
									<select name="item-open-header-font-type">
										<option value="regular" <?php echo (($settings['item-open-header-font-type'] == 'regular') ? 'selected="selected"' : '' ); ?>>Regular</option>
										<option value="thick" <?php echo (($settings['item-open-header-font-type'] == 'thick') ? 'selected="selected"' : '' ); ?>>Thick</option>
										<option value="bold" <?php echo (($settings['item-open-header-font-type'] == 'bold') ? 'selected="selected"' : '' ); ?>>Bold</option>
										<option value="bold-italic" <?php echo (($settings['item-open-header-font-type'] == 'bold-italic') ? 'selected="selected"' : '' ); ?>>Bold-Italic</option>
										<option value="italic" <?php echo (($settings['item-open-header-font-type'] == 'italic') ? 'selected="selected"' : '' ); ?>>Italic</option>
									</select>
								</td>					
							</tr>
								
							<tr>
								<td colspan="2">
									<label for="item-open-header-font-color">Title Color</label>
								</td>
							<tr>
							</tr>
								<td colspan="2">
									<input class="timeline_color_input" id="item-open-header-font-color" name="item-open-header-font-color"  value="<?php echo $settings['item-open-header-font-color']; ?>" type="text" style="background:#<?php echo $settings['item-open-header-font-color']; ?>;">
								</td>
							</tr>
											
							<tr class="field-row">
								<td>
									<label for="item-open-text-line-height" >Text Line Height</label>
								</td>
								<td>
									<input id="item-open-text-line-height" name="item-open-text-line-height" value="<?php echo $settings['item-open-text-line-height']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>		
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-open-text-font-size" >Text Font Size</label>
								</td>
								<td>
									<input id="item-open-text-font-size" name="item-open-text-font-size" value="<?php echo $settings['item-open-text-font-size']; ?>" size="5" type="text">	
									<span class="unit">px</span>
								</td>		
							</tr>
							
							<tr class="field-row">
								<td>
									<label for="item-open-text-font-type" >Text Font Type</label>
								</td>
								<td>
									
									<select name="item-open-text-font-type">
										<option value="regular" <?php echo (($settings['item-open-text-font-type'] == 'regular') ? 'selected="selected"' : '' ); ?>>Regular</option>
										<option value="thick" <?php echo (($settings['item-open-text-font-type'] == 'thick') ? 'selected="selected"' : '' ); ?>>Thick</option>
										<option value="bold" <?php echo (($settings['item-open-text-font-type'] == 'bold') ? 'selected="selected"' : '' ); ?>>Bold</option>
										<option value="bold-italic" <?php echo (($settings['item-open-text-font-type'] == 'bold-italic') ? 'selected="selected"' : '' ); ?>>Bold-Italic</option>
										<option value="italic" <?php echo (($settings['item-open-text-font-type'] == 'italic') ? 'selected="selected"' : '' ); ?>>Italic</option>
									</select>
								</td>					
							</tr>
								
							<tr>
								<td colspan="2">
									<label for="item-open-text-font-color">Text Color</label>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<input class="timeline_color_input" id="item-open-text-font-color" name="item-open-text-font-color"  value="<?php echo $settings['item-open-text-font-color']; ?>" type="text" style="background:#<?php echo $settings['item-open-text-font-color']; ?>;">
								</td>
							</tr>
							
							</table>
							</div>
							<div class="misc-pub-section timeline-pub-section">
								<h3 style="margin-top:0; background:transparent;"><span class="timeline-help">? <span class="timeline-tooltip">Open item background options.</span></span>Background</h3>
								
								<label>Color:</label><br />
								<input class="timeline_color_input" id="item-open-back-color" name="item-open-back-color"  value="<?php echo $settings['item-open-back-color']; ?>" type="text" style="background:<?php echo $settings['item-open-back-color']; ?>;">	
								<div class="clear"></div>
								<label>Image:</label>
								<div class="cw-image-select-holder">
									<input id="item-open-background-input" name="item-open-background" type="hidden" value="<?php echo $settings['item-open-background']; ?>" />
									<a href="#" id="item-open-background" class="cw-image-upload" style="<?php echo 'background: url(' . (($settings['item-open-background'] != '') ? $settings['item-open-background'] : $this->url . '/images/no_image.jpg') . ');'; ?>"></a>
									<small>Click on image to change, or <a href="#" class="remove-image">remove image</a></small>
								</div>
							</div>
						</div>
					</div><!-- /ITEM OPEN STYLING OPTIONS -->
					*/ ?>
					
					
					
					
				</div>
			</div>
			
			<div id="postbox-container-2" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable"></div>
			</div>
			
			<br class="clear"/>
			
		</div>
	
	</div>
	</form>

</div>