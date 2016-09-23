<?php
global $wp_my_timeline_wpml_deafult_lang;
global $wp_my_timeline_has_wmpl;
global $wp_my_timeline_wpml_languages;
global $my_lang;
global $wp_my_timeline_translations;
$my_has_id=false;
if(!empty($wp_my_timeline_has_wmpl)){
if(!empty($_GET['lang'])){
	$my_lang=$_GET['lang'];
}else $my_lang='en';
$options=get_option("_my_timeline_translations_1234");
if(empty($options['en'])){
	$options['en']=$wp_my_timeline_translations;
}
if($my_lang!='en'){
	if(!empty($_POST['my_submit'])){
		$c=count($wp_my_timeline_translations['months']);
		for($i=0;$i<$c;$i++){
			$name='months_'.$i;
			$val=@$_POST[$name];
			if(empty($val)){
				$val=$wp_my_timeline_translations['months'][$i];
			}
			$arr[]=$val;
		}
		$options[$my_lang]['months']=$arr;
		update_option("_my_timeline_translations_1234", $options,false);
		$options=get_option("_my_timeline_translations_1234");
		
	}
}

?>
<div class="wrap">
	<h2><?php echo __("Timeline Tranlsations ",'my_content_timeline'); ?></h2>
	
	<?php if(!empty($wp_my_timeline_has_wmpl)){
		?>
		<div class="my_wpml_switcher_div">
		<?php 
		//wp_my_timeline_debug_title("Langs", $wp_my_timeline_wpml_languages);
		//echo 'Current lang '.$my_lang;
		?>
		<ul class="my_wpml_switcher">
		<?php 
	
		foreach( $wp_my_timeline_wpml_languages as $k123=>$v123){
			?>
			<li>
			<?php if($v123['code']!=$my_lang){?>
			<a href="<?php echo admin_url( "admin.php?page=contenttimeline_translations&lang=".$v123['code']);?>"><?php echo $v123['display_name'];?></a>
			<?php }else {?>
			<span><?php echo __("Translations : ",'my_content_timeline').$v123['display_name'];?></span>
			<?php }?>
			</li>
			<?php 
		}
		?>
		</ul>
		</div>
	<?php if($my_lang=='en'){?>
	
		<h4 style="color:red"><?php echo __("Please select above new language to add translations ",'my_content_timeline')?></h4>
	<?php }else {?>
		<form method="post">
			<ul>
			<li>
			<input type="submit" class="button button-large button-primary"  name="my_submit" value="<?php echo __("Save Translation",'my_content_timeline')?>"/>
			</li>
			
			<?php foreach($wp_my_timeline_translations as $k=>$v){?>
				<?php 
				if($k=='months'){
					?>
					<li><label><?php echo __("Months Translations : ",'my_content_timeline')?></label></li>
					<?php 
				}
				if(is_array($v)){
					foreach($v as $k1=>$v1){
						?>
						<li><label><?php echo $v1;?></label>
						<input type="text" value="<?php echo $options[$my_lang]['months'][$k1]?>" name="<?php echo $k.'_'.$k1?>"/>
						</li>
						<?php 
					}
				}
				?>
			<?php }?>
			<li>
			<input type="submit" class="button button-large button-primary" name="my_submit" value="<?php echo __("Save Translation",'my_content_timeline')?>"/>
			</li>
			</ul>
		</form>
	<?php }?>	
	<?php }else{?>
	<h2 style="color:red">No Wpml plugin installed</h2>
	<?php }?>	
</div>
<?php }else {
	$my_lang='en';
	$options=get_option("_my_timeline_translations_1234");
	//print_r($options);
	if(empty($options['en'])){
		$options['en']=$wp_my_timeline_translations;
	}
	//if($my_lang!='en'){
		if(!empty($_POST['my_submit'])){
			$c=count($wp_my_timeline_translations['months']);
			for($i=0;$i<$c;$i++){
				$name='months_'.$i;
				$val=@$_POST[$name];
				if(empty($val)){
					$val=$wp_my_timeline_translations['months'][$i];
				}
				$arr[]=$val;
			}
			$options[$my_lang]['months']=$arr;
			update_option("_my_timeline_translations_1234", $options,false);
			$options=get_option("_my_timeline_translations_1234");
	
		}
	//}
	?>
	<form method="post">
			<ul>
			<li>
			<input type="submit" class="button button-large button-primary"  name="my_submit" value="<?php echo __("Save Translation",'my_content_timeline')?>"/>
			</li>
			
			<?php foreach($wp_my_timeline_translations as $k=>$v){?>
				<?php 
				if($k=='months'){
					?>
					<li><label><?php echo __("Months Translations : ",'my_content_timeline')?></label></li>
					<?php 
				}
				if(is_array($v)){
					foreach($v as $k1=>$v1){
						?>
						<li><label><?php echo $v1;?></label>
						<input type="text" value="<?php echo $options[$my_lang]['months'][$k1]?>" name="<?php echo $k.'_'.$k1?>"/>
						</li>
						<?php 
					}
				}
				?>
			<?php }?>
			<li>
			<input type="submit" class="button button-large button-primary" name="my_submit" value="<?php echo __("Save Translation",'my_content_timeline')?>"/>
			</li>
			</ul>
		</form>

<?php }?>