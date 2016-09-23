<?php   
/*
Plugin Name: Content timeline
Plugin URI: http://codecanyon.net/item/content-timeline-responsive-wordpress-plugin/3027163
Description: Content timeline is a responsive, jQuery powered, wordpress plugin. It's ideal for showcasing your work. It has independent content, that can be imported from posts, pages or even whole categories. With intuitive user interface you can create multiple timelines categorized by date, or some other custom criteria. It is flexible, customizable and easy to use.
Author: Shindiri Studio
Version: 4.2
Author URI: http://www.shindiristudio.com/
*/

if (!class_exists("contentTimelineAdmin")) {
	ini_set('memory_limit','100M');
	require_once dirname( __FILE__ ) . '/content_timeline_class.php';
	global $my_ctimeline_object;	
	$ctimeline = new ContentTimelineAdmin (__FILE__);
	$my_ctimeline_object=$ctimeline;
}