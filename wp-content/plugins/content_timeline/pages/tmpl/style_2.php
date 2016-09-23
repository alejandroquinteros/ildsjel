<?php
if(!defined('ABSPATH'))die('');
$my_has_post_id=false;
if(!empty($arr['my-post-id'])){
	$my_post_id=$arr['my-post-id'];
	$my_has_post_id=true;
}else {
	$my_post_id=wp_timeline_get_post_id_by_title($arr['item-title']);
	if(!empty($my_post_id)){
		$my_has_post_id=true;
	}
}
$my_metadata=wp_my_timeline_get_post_meta_data($my_post_id);
?>
<div class="item" data-id="<?php echo $arr['dataid'] ?>" <?php $my_name=(($arr['node-name'] && $arr['node-name'] != '') ? ' data-name="'.$arr['node-name'].'"': ''); echo $my_name;?> data-description="<?php echo mb_substr($arr['item-title'],0,30);?>">
			<?php echo $image ;?>
			<div class="my_overlay">
			
			</div>
			<div class="my_timeline_content">
			
			<div class="my_category"><?php echo $arr['categoryid'];//$cats=wp_get_post_categories($id);print_r($cats);?></div>
			<?php if(isset($arr['item-link']) && $arr['item-link'] != '') {?>
			<a class="my_read_more" href="<?php echo $arr['item-link'];?>">
			<h2><?php echo $arr['item-title'];?></h2>
			</a>
			<?php }else {?>
			<?php 
			if($my_post_link_val==1&&!empty($my_has_post_id)){
			$my_link_12_1234=get_permalink($my_post_id);		
	?>
			
			<h2 class="my_read_more" data-href="<?php echo esc_attr($my_link_12_1234);?>" data-id="<?php echo $arr['dataid']?>"><?php echo $arr['item-title'];?></h2>
			<?php 
			}else{?>
			<h2 class="my_read_more" data-id="<?php echo $arr['dataid']?>"><?php echo $arr['item-title'];?></h2>
			<?php }?>
			<?php }?>
			<?php /*
			<span><?php echo $arr['item-content'];?></span>
			
			<?php echo $readMore; ?>
			*/ ?>
			<div class="my_share_items  my_clear">
				<?php echo $blogDate ;?>
				<?php if(!empty($arr['my-post-id'])){?>
			
				<div class="my_float_right my_align_right <?php if(!$settings['show-date']) echo 'my_share_no_date'?> my_share_items_1">
					<div class="my_share_item my_like_post" data-liked="0" data-id="<?php echo $id.'_'.$iteration?>" data-post-id="<?php if($my_has_post_id)echo $my_post_id?>" data-val="<?php echo $my_metadata['hearts'] ?>">
						<i class="my_icon ti-heart"></i><span>&nbsp;<?php echo $my_metadata['hearts']?></span>
					</div>
					<div class="my_share_item">
						<i class="my_icon ti-comment-alt"></i><span>&nbsp;<?php echo $my_metadata['comments']?></span>
					</div>
					<div class="my_share_item my_open_dialog">
						<i class="my_icon ti-share"></i>
						<span class="my_icon_1"><?php echo __("Share");?></span>
						<div class="my_timeline_share">
							
							<div>
							<?php $file=$this->path.'/pages/tmpl/share.php';
							require $file;
							?>
							<div class="my_timeline_share_arrow"></div>
							</div>
							
						</div>
						
					</div>
					
				
				</div>
				<?php }?>
			</div>
			
			</div>
			<?php if(empty($myIsVertical)){ ?>	
		</div>
		<?php }?>
		
		<div class="item_open <?php ($arr['item-image'] == '' ? ' item_open_noImg' : '') ;?>" data-id="<?php echo $arr['dataid'];?>" <?php $var=(!isset($tpreview) ? 'data-access="'.admin_url( 'admin-ajax.php' ).'?action=ctimeline_frontend_get&timeline='.$id.'&id='.$key.'"': '');echo $var;?>>
			<div class="item_open_content">
<?php 			
if(!isset($tpreview)) {
	?>
	 			<img class="ajaxloader" src="<?php echo $this->url .'images/loadingAnimation.gif';?>" alt="" />
	<?php  			
}else {
	if ($arr['item-open-image'] != '') {
					?>
			<a class="timeline_rollover_bottom con_borderImage" href="<?php (($arr['item-open-prettyPhoto'] != '')? $arr['item-open-prettyPhoto'] : $arr['item-open-image']);?>" rel="prettyPhoto[timeline]">'
	<?php 
	$image = '';
	if($arr['item-image'] != '') {
		$imgw = (int)$settings['item-open-width'];
		$imgh = (int)$settings['item-open-image-height'];
		$image = bro_images::get_image($arr['item-open-image'], $imgw, $imgh);
		$image = '<img src="'. $image .'" alt=""/>';
	}
			//$frontHtml .= '
			//'.$image. 
			echo $image;
			?>
			</a>
			<div class="timeline_open_content" style="height:<?php echo $open_content_height.'px';?>">
	<?php 	
				} 
				else { 
			?>
			<div class="timeline_open_content" style="height:<?php $my_w_12 =(intval($settings['item-height']) - 2*intval($settings['item-open-content-padding'])).'px';echo $my_w_12;?>">
			<?php 	
				}
				
				if ($arr['item-open-title'] != '') { 
				///	$frontHtml .= '
				?>
				<h2><?php echo $arr['item-open-title'];?></h2>
				<?php 
				} 
				//$frontHtml .= '
				?>
				<span <?php $my_c_12=(!isset($arr['desable-scroll']) || !$arr['desable-scroll'] ? ' class="scrollable-content"' : '');echo $my_c_12;?>>
				<?php echo stripslashes($arr['item-open-content']);?>
				</span>
				
			</div>
<?php 	
	}
?>
<?php if(!empty($myIsVertical)){ ?>	
		</div>
		<?php }?>
	</div>
	</div>
	
	<?php 

?>
