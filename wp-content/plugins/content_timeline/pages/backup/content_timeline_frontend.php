<?php 

$title = '';
$settings = array( 
	'scroll-speed' => '500',
	'easing' => 'easeOutSine',
	'hide-years' => false,
	'cat-type' => 'months',
	'number-of-posts' => '30',
	
	// style
	'line-width' => '920',
	'item-width' => '240',
	'item-open-width' => '490',
	'item-margin' => '20',
	'item-height' => '360',
	'read-more' => 'button',
	'close-text' => 'Close',
	'hide-line' => false,
	'line-style' => 'light',
	'hide-nav' => false,
	'nav-style' => 'light',
	'shdow' => 'show',
	'item-back-color' => '#ffffff',
	'item-background' => '',
	'item-open-back-color' => '#ffffff',
	'item-open-background' => '',
	
	'button-hover-color' => '#1986ac',
	'item-image-height' => '150',
	'item-image-border-width' => '5',
	'item-image-border-color' => '#1986ac',
	'item-open-image-height' => '150',
	'item-open-content-padding' => '10',
	'item-open-image-border-width' => '5',
	'item-open-image-border-color' => '#1986ac'
	
);
global $wpdb;
if($id) {
	global $wpdb;
	$timeline = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'ctimelines WHERE id='.$id);
	$timeline = $timeline[0];
}
$title = $timeline->name;
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

$frontHtml = '
<style type="text/css">
#tl'. $id. ' .timeline_line,
#content #tl'. $id. ' .timeline_line{
 	width: '.$settings['line-width'].'px;
} 

#tl'.$id.' .t_line_view,
#content #tl'.$id.' .t_line_view {
 	width: '.$settings['line-width'].'px;
} 
 
#tl'.$id.' .t_line_m,
#content #tl'.$id.' .t_line_m {
	width: '. (((int)$settings['line-width'])/2-2).'px;
}
 
#tl'. $id.' .t_line_m.right,
#content #tl'. $id.' .t_line_m.right {
	left: '. (((int)$settings['line-width'])/2-1).'px;
	width: '. (((int)$settings['line-width'])/2-1).'px;
}
 
#tl'. $id.' .item,
#content #tl'. $id.' .item {
 	width: '. $settings['item-width'].'px;
	height: '. $settings['item-height'].'px;
	background:'. $settings['item-back-color'].' url('. $settings['item-background'].') repeat;';
	
if($settings['shadow'] == 'show') {
	$frontHtml.= '
	-moz-box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	-webkit-box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	
	zoom: 1;
	filter: progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=0, Direction=0),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=5, Direction=90),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=5, Direction=180),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=0, Direction=270);
	
	';
	}
else {
	$frontHtml.= '
	-moz-box-shadow: 0 0 0 #000000;
	-webkit-box-shadow: 0 0 0 #000000;
	box-shadow: 0 0 0 #000000;';
}
$frontHtml.=' 
}';
if($settings['shadow'] == 'on-hover') {
	$frontHtml.= '
#tl'. $id . ' .item:hover,
#content #tl'. $id . ' .item:hover {
	-moz-box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	-webkit-box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	
	zoom: 1;
	filter: progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=0, Direction=0),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=5, Direction=90),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=5, Direction=180),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=0, Direction=270);
	
	
}';
}

$frontHtml.= '
#tl'. $id .' .item_open,
#content #tl'. $id .' .item_open{
 	width: '. $settings['item-open-width'].'px;
	height: '. $settings['item-height'].'px;
	background:'. $settings['item-open-back-color'].' url('. $settings['item-open-background'].') repeat; ';
 	
	
if($settings['shadow'] == 'show' || $settings['shadow'] == 'on-hover') {
	$frontHtml.= '
	-moz-box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	-webkit-box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	box-shadow: 0px 0px 6px rgba(0,0,0,0.5);
	
	zoom: 1;
	filter: progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=0, Direction=0),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=5, Direction=90),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=5, Direction=180),
		progid:DXImageTransform.Microsoft.Shadow(Color=#888888, Strength=0, Direction=270);
	
	';
	}
	else {
	$frontHtml.= '
	-moz-box-shadow: 0 0 0 #000000;
	-webkit-box-shadow: 0 0 0 #000000;
	box-shadow: 0 0 0 #000000;';
	} 
$frontHtml.= '
 }'. '
 
#tl'. $id.' .item .con_borderImage,
#content #tl'. $id.' .item .con_borderImage {
 	border:0px;
 	border-bottom: '. $settings['item-image-border-width'].'px solid '. $settings['item-image-border-color'].' ; 
}
 
#tl'. $id.' .item_open .con_borderImage,
#content #tl'. $id.' .item_open .con_borderImage {
 	border-bottom: '. $settings['item-open-image-border-width'].'px solid '. $settings['item-open-image-border-color'].' ; 
}

#tl'. $id.' .item_open_cwrapper,
#content #tl'. $id.' .item_open .con_borderImage {
 	width: '. $settings['item-open-width'].'px;
}

#tl'. $id.' .item_open .t_close:hover,
#content #tl'. $id.' .item_open .t_close:hover{
	background:'. $settings['button-hover-color'].';
}

#tl'. $id.' .item .read_more:hover,
#content #tl'. $id.' .item .read_more:hover{
	background:'. $settings['button-hover-color'].';
}


#tl'. $id.' .item .read_more,
#content #tl'. $id.' .item .read_more,
#tl'. $id.' .item_open .t_close,
#content #tl'. $id.' .item_open .t_close {

	/* transparent background */
	filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#44000000\', endColorstr=\'#44000000\'); 
}

#tl'. $id.' .t_node_desc,
#content #tl'. $id.' .t_node_desc,
 {

	/* IE transparent background */
	filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#cc1a86ac\', endColorstr=\'#cc1a86ac\'); 
}



#tl'. $id.' .timeline_open_content,
#content #tl'. $id.' .timeline_open_content {
	padding:'. $settings['item-open-content-padding'].'px;
}



</style>
';

if($settings['read-more'] == 'whole-item') {
	$read_more = "'.item'";
	$swipeOn = 'false';
}
else if ($settings['read-more'] == 'button') {
	$read_more = "'.read_more'";
	$swipeOn = 'true';
}
else {
	$read_more = "'none'";
	$swipeOn = 'true';
}

if($settings['cat-type'] == 'categories') {
	$cats = ',
		categories : '.$cats . ', 
		numberOfSegments : [';
	$cats .= $settings['number-of-posts'];
	for ($i = 1; $i < $catNumber; $i++) {
		$cats .= ', '. $settings['number-of-posts'];
	}
	$cats .= ']';
}
else {
	$cats = '';
}

$frontHtml .='

<!-- BEGIN TIMELINE -->
<div id="tl'. $id.'" class="timeline'. ($settings['line-style'] == 'dark' ? ' darkLine' : ''). ($settings['nav-style'] == 'dark' ? ' darkNav' : '').'">';

if ($timeline->items != '') {
	$explode = explode('||',$timeline->items);
	$open_content_height = intval($settings['item-height']) - intval($settings['item-open-image-height']) - 2*intval($settings['item-open-content-padding']) -intval($settings['item-open-image-border-width']) - 6;
	
	$itemsArray = array();
	foreach ($explode as $it) {
		$ex2 = explode('::', $it);
		$key = substr($ex2[0],0,strpos($ex2[0],'-'));
		$subkey = substr($ex2[0],strpos($ex2[0],'-')+1);
		$itemsArray[$key][$subkey] = $ex2[1];
	}
	foreach ($itemsArray as $key => $arr) {
		$num = substr($key,4);
		
		if($settings['cat-type'] == 'categories') {
			$index = array_search($arr['categoryid'],$catArray);
			$ccNumbers[$index]++;
			$arr['dataid'] = ($ccNumbers[$index] < 10 ? '0'.$ccNumbers[$index] : $ccNumbers[$index]).'/'.$arr['categoryid'];
		}
		if($arr['start-item']) {
			$start_item = $arr['dataid'];
			
		}
	


$frontHtml .='

		<div class="item" data-id="'. $arr['dataid'].'" data-description="'. substr($arr['item-title'],0,30).'">
			<img class="con_borderImage" src="'. $this->url . 'timthumb/timthumb.php?src=' . $arr['item-image'] . '&w='.((int)$settings['item-width']-10).'&h='.((int)$settings['item-image-height']-10).'" alt=""/>
			<h2>'.$arr['item-title'].'</h2>
			<span>'.$arr['item-content'].'</span>
			'.(($settings['read-more'] == 'button') ? '<div class="read_more" data-id="'.$arr['dataid'].'">Read more</div>' : '').'
		</div>
		<div class="item_open" data-id="'.$arr['dataid'].'">';
		
		if ($arr['item-open-image'] != '') {
			$frontHtml .= '
			<img class="con_borderImage" src="'. $this->url . 'timthumb/timthumb.php?src=' . $arr['item-open-image'] . '&w='.((int)$settings['item-open-width']-10).'&h='.((int)$settings['item-open-image-height']-10).'" alt=""/>
			<div class="timeline_open_content'.(!$arr['desable-scroll'] ? ' scrollable-content' : '').'" style="height: '. $open_content_height.'px">';
			
		} 
		else { 
			$frontHtml .= '
			<div class="timeline_open_content'.(!$arr['desable-scroll'] ? ' scrollable-content' : '').'" style="height: '. (intval($settings['item-height']) - 2*intval($settings['item-open-content-padding'])).'px">';
		}
			
		if ($arr['item-open-title'] != '') { 
			$frontHtml .= '
				<h2>'.$arr['item-open-title'].'</h2>';
		
		} 
			$frontHtml .= '
				' . $arr['item-open-content'].'
			</div>
		</div>';
		

	}
}
$frontHtml .= '
</div> <!-- END TIMELINE -->


<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js?ver=3.4.2"></script>
<script type="text/javascript" src="' . $this->url . 'js/frontend/jquery.easing.1.3.js?ver=3.4.2"></script>
<script type="text/javascript" src="' . $this->url . 'js/frontend/jquery.timeline.js?ver=3.4.2"></script>
<script type="text/javascript" src="' . $this->url . 'js/frontend/jquery.mousewheel.min.js?ver=3.4.2"></script>
<script type="text/javascript" src="' . $this->url . 'js/frontend/jquery.mCustomScrollbar.min.js?ver=3.4.2"></script>
<script type="text/javascript">
(function($){
$(window).load(function() {
	$(".scrollable-content").mCustomScrollbar();
	
	$("#tl'.$id.'").timeline({
		itemMargin : '. $settings['item-margin'].',
		scrollSpeed : '.$settings['scroll-speed'].',
		easing : "'.$settings['easing'].'",
		openTriggerClass : '.$read_more.',
		swipeOn : '.$swipeOn.',
		startItem : "'. $start_item . '",
		yearsOn : '.(($settings['hide-years'] || $settings['cat-type'] == 'categories') ? 'false' :  'true').',
		hideTimeline : '.($settings['hide-line'] ? 'true' : 'false').',
		hideControles : '.($settings['hide-nav'] ? 'true' : 'false').
		$cats.'
	});
	
});	
})(jQuery);
</script>';