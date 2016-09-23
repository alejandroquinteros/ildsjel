<?php
if(!defined('ABSPATH'))die('');
$media='';
if(!empty($arr['item-image']))$media=$arr['item-image'];
$facebook_url=wp_my_timeline_share_post($my_post_id, $arr['item-title'], $arr['item-content'],$media,'facebook');
$twitter_url=wp_my_timeline_share_post($my_post_id, $arr['item-title'], $arr['item-content']);
$g_plus=wp_my_timeline_share_post($my_post_id, $arr['item-title'], $arr['item-content'],$media,'google');
$p=wp_my_timeline_share_post($my_post_id, $arr['item-title'], $arr['item-content'],$media,'pinterest');
?>
<div class="my_timeline_share_12">
	<ul data-id="<?php echo $id.'_'.$iteration?>">
		<li>
		<a href="<?php echo $facebook_url?>"><i class="fa fa-facebook"></i></a>
		</li>
		<li>
		<a href="<?php echo $twitter_url?>"><i class="fa fa-twitter"></i></a>
		</li>
		<li>
		<a href="<?php echo $p?>"><i class="fa fa-pinterest-p"></i></a>
		</li>
		<li>
		<a href="<?php echo $g_plus?>"><i class="fa fa-google-plus"></i></a>
		</li>	
	</ul>
</div>