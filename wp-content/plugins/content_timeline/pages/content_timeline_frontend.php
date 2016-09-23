<?php 
require($this->path . '/pages/default_settings.php');


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
$my_cats_timeline=array();
$my_cats_timeline=wp_my_timeline_get_timeline_cats($timeline->settings);
?>
<?php /*
<pre><?php print_r($my_cats_timeline);?></pre>
*/ ?>
<?php
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
}

?>
<?php /*
<h2>Settings</h2>
<pre><?php print_r($settings);?></pre>
*/ ?>
<?php

require($this->path . '/pages/front_html.php');
require($this->path . '/pages/front_script.php');
?>