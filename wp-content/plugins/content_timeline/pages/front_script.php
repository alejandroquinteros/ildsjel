<?php
$my_disable_pretty_photo=false;
if(empty($settings['my-vertical-count'])){
	$settings['my-vertical-count']=6;
}
if(empty($settings['my-vertical-count-small'])){
	$settings['my-vertical-count-small']=1;
}
$my_vertical_limit=0;
if(empty($settings['my-vertical-limit'])){
	$settings['my-vertical-count']=0;
	$settings['my-vertical-count-small']=0;
}else $my_vertical_limit=1;


if(!empty($settings['disable_pretty_photo'])){
	$my_disable_pretty_photo=true;
}
$my_is_vertical=0;
if(!empty($settings['vertical'])){
	$my_is_vertical=1;
}

$my_debug=0;
if(isset($_GET['my_debug_123_123_1456'])){
	$my_debug=1;
}
$my_items_sizes=array();
$my_items_sizes['card']=array(
	'item_width'=>$settings['item-width'],
	'item_height'=>$settings['item-height'],	
	'margin'=>$settings['item-margin']		
);
$my_items_sizes['active']=array(
		'item_width'=>$settings['item-open-width'],
		'item_height'=>$settings['item-height'],
		'image_height'=>$settings['item-open-image-height']
);
//add mmtoths trasnaltions
if($settings['cat-type']=='months'){
	//$my_Cats='';
	global $wp_my_timeline_has_wmpl;
	if(!empty($wp_my_timeline_has_wmpl)){
		$my_lang=$settings['my_lang'];
		if($my_lang!='en'){
			$myCats1="";
			$t=wp_my_timeline_get_translations($my_lang);
			foreach($t['months'] as $k123=>$v123){
				if(strlen($myCats1))$myCats1.=",";
				$myCats1.="'".$v123."'";
			
			}
			$myCats="[".$myCats1."],";
		}
	}else {
		$t=wp_my_timeline_get_translations('en');
		//print_r($t);
		if(!empty($t)){
		foreach($t['months'] as $k123=>$v123){
			if(strlen($myCats1))$myCats1.=",";
			$myCats1.="'".$v123."'";
				
		}
		$myCats="[".$myCats1."],";
		}
	}
	
}
$my_p_123_1=5;
$my_p_123_2=5;
if($settings['shadow'] == 'on-hover') {
	$my_p_123=15;
	$my_p_123_1=52;
	$my_p_123_2=30;
}else {
	$my_p_123=5;
	if($settings['shadow']!='hide')
	$my_p_123=10;
	$my_p_123_1=$my_p_123;
	$my_p_123_2=$my_p_123;
}
if(empty($settings['autoplay'])){
	$settings['autoplay']=0;
}
if(empty($settings['autoplay-mob'])){
	$settings['autoplay-mob']=0;
}
if(empty($settings['autoplay-step'])){
	$settings['autoplay-step']=10000;
}
$my_pretty_photo='$this.find("a[rel^=\'prettyPhoto\']").prettyPhoto();';
$my_pretty_photo_1='e.element.find("a[rel^=\'prettyPhoto\']").prettyPhoto();';
$my_is_mobile=0;
if(wp_is_mobile() || $my_disable_pretty_photo){
	$my_pretty_photo='';
	$my_is_mobile=1;
	$my_pretty_photo_1='';
}

$my_is_years=0;
if(isset($my_timeline_by_years)){
	$my_is_years=1;
	unset($my_timeline_by_years);
}
if(!empty($my_disable_pretty_photo)){
	$frontHtml.='<script type="text/javascript">';
	$frontHtml.=" my_disable_roll_over=1;";
		$frontHtml.='</script>';	
}
$frontHtml .= '

<script type="text/javascript">
my_is_mobile_global='.$my_is_mobile.';		
(function($){
var test = false;
$(window).load(function() {
	if(!test)
		timeline_init_'.$id.'($(document));			
});	';
/*
if($my_is_years){
$frontHtml.='
$(window).load(function(){
	setTimeout(function(){
		$(".t_line_node").each(function(index){
			var year = $(this).index()*20;
			$(this).attr("style", "left: "+year+"%;position: absolute; text-align: center;");
		});
	},300);	
	});';
}
*/ 
if(empty($my_is_vertical)){
$frontHtml.='				
function timeline_init_'.$id.'($this) {
	$this.find(".scrollable-content").mCustomScrollbar();'.$my_pretty_photo.'
	$this.find("#tl'.$id.'").timeline({';
	if(isset($myCats)){
		$frontHtml.='categories:'.$myCats;	
	}
	$frontHtml.='my_show_years:9,
		my_del:130,	
		vertical:'.$my_is_vertical.',	
		my_post_link:'.$my_post_link_val.',	
		my_is_years:'.$my_is_years.',	
		my_trigger_width:800,
		my_sizes		 :'.json_encode($my_items_sizes).',	
		my_id		 :'.$id.',	
		my_debug	 :'.$my_debug.',		
		is_mobile	 :  '.$my_is_mobile.',	
		autoplay     : '.$settings['autoplay'].',
		autoplay_mob :	'.$settings['autoplay-mob'].',
		autoplay_step:	'.$settings['autoplay-step'].',		
		itemMargin : '. $settings['item-margin'].',
		scrollSpeed : '.$settings['scroll-speed'].',
		easing : "'.$settings['easing'].'",
		openTriggerClass : '.$read_more.',
		swipeOn : '.$swipeOn.',
		startItem : "'. (!empty($start_item) ? $start_item : 'last') . '",
		yearsOn : '.(($settings['hide-years'] || ($settings['cat-type'] == 'categories'&&!$my_is_years )) ? 'false' :  'true').',
		hideTimeline : '.($settings['hide-line'] ? 'true' : 'false').',
		hideControles : '.($settings['hide-nav'] ? 'true' : 'false').',
		closeText : "'.$settings['close-text'].'"'.
		$cats.',
		closeItemOnTransition: '.($settings['item-transition-close'] ? 'true' : 'false').'
	});
	
	$this.find("#tl'.$id.'").on("ajaxLoaded.timeline", function(e){
		var scrCnt = e.element.find(".scrollable-content");
		scrCnt.height(scrCnt.parent().height() - scrCnt.parent().children("h2").height() - parseInt(scrCnt.parent().children("h2").css("margin-bottom")));
		scrCnt.mCustomScrollbar({theme:"light-thin"});
		'.$my_pretty_photo_1.'
		e.element.find(".timeline_rollover_bottom").timelineRollover("bottom");
	});
}
})(jQuery);
</script>';
}else {
	$frontHtml.='	
	function timeline_init_'.$id.'($this) {
		$this.find(".scrollable-content").mCustomScrollbar();'.$my_pretty_photo.'
				$this.find("#tl'.$id.'").VerticalTimeline({';
	if(isset($myCats)){
		$frontHtml.='categories:'.$myCats;
	}
	$frontHtml.='my_show_years:9,
			my_del:130,
			vertical_limit:'.$my_vertical_limit.',
			vertical:'.$my_is_vertical.',
			myMarginLeftRight:20,		
			myShow	:'.$settings['my-vertical-count'].',
			myShowSmall:'.$settings['my-vertical-count-small'].',
			myVerticalPadding:'.$my_p_123.',
			myVerticalPadding1:'.$my_p_123_1.',
			myVerticalPadding2:'.$my_p_123_2.',							
			my_post_link:'.$my_post_link_val.',
			my_is_years:'.$my_is_years.',
			my_trigger_width:800,
			my_sizes		 :'.json_encode($my_items_sizes).',
			my_id		 :'.$id.',
			my_debug	 :'.$my_debug.',
			is_mobile	 :  '.$my_is_mobile.',
			autoplay     : '.$settings['autoplay'].',
			autoplay_mob :	'.$settings['autoplay-mob'].',
			autoplay_step:	'.$settings['autoplay-step'].',
			itemMargin : '. $settings['item-margin'].',
			scrollSpeed : '.$settings['scroll-speed'].',
			easing : "'.$settings['easing'].'",
			openTriggerClass : '.$read_more.',
			swipeOn : '.$swipeOn.',
			startItem : "'. (!empty($start_item) ? $start_item : 'last') . '",
			yearsOn : '.(($settings['hide-years'] || ($settings['cat-type'] == 'categories'&&!$my_is_years )) ? 'false' :  'true').',
			hideTimeline : '.($settings['hide-line'] ? 'true' : 'false').',
			hideControles : '.($settings['hide-nav'] ? 'true' : 'false').',
			closeText : "'.$settings['close-text'].'"'.
		$cats.',
			closeItemOnTransition: '.($settings['item-transition-close'] ? 'true' : 'false').'
		});
	
		$this.find("#tl'.$id.'").on("ajaxLoaded.timeline", function(e){
			var scrCnt = e.element.find(".scrollable-content");
			scrCnt.height(scrCnt.parent().height() - scrCnt.parent().children("h2").height() - parseInt(scrCnt.parent().children("h2").css("margin-bottom")));
			scrCnt.mCustomScrollbar({theme:"light-thin"});
			'.$my_pretty_photo_1.'
					e.element.find(".timeline_rollover_bottom").timelineRollover("bottom");
		});
	}
	})(jQuery);
	</script>';
	
}
