<?php

/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div class="page-wrapper">
 *
 * @package uncode
 */

global $is_redirect, $redirect_page;

if ($redirect_page) {
	$post_id = $redirect_page;
} else {
	if (isset(get_queried_object()->ID) && !is_home()) {
		$post_id = get_queried_object()->ID;
	} else {
		$post_id = null;
	}
}

if (wp_is_mobile()) $html_class = 'touch';
else $html_class = 'no-touch';

if (!is_admin() && is_user_logged_in()) $html_class .= ' admin-mode';

?><!DOCTYPE html>
<html class="<?php echo esc_attr($html_class); ?>" <?php language_attributes(); ?> xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta property="og:url" content="/wp-content/uploads/2016/07/Ildsjel-FacebookShare-01-01-1.jpg" />

<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>">
<?php if (wp_is_mobile()): ?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
<?php else: ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php endif; ?>

<link rel="icon" href="/wp-content/themes/uncode-child/img/favicon-2.ico" type="image/x-icon"/>
<link rel="shortcut icon" href="/wp-content/themes/uncode-child/img/favicon-2.ico" type="image/x-icon"/>

<script src="https://use.typekit.net/put4pio.js"></script>
<script>try{Typekit.load({ async: true });}catch(e){}</script>

<script src="https://code.jquery.com/jquery-3.0.0.min.js"></script> 
<script src="https://cdn.jsdelivr.net/jquery.typeit/4.2.3/typeit.min.js"></script>

<script type="text/javascript">
$(function() {
	$('#example2').typeIt({
    	strings: [ " ildsjel" , " passion", " art", " music" ],
    	speed: 300,
    	breakLines: false,
    	autoStart: true,
    	cursor: false,
    	loop: true
	});
	console.log("bingo");
});
</script>

<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>



</head>
<?php
	global $LOGO, $metabox_data, $onepage, $fontsizes, $is_redirect, $menutype;

	if ($post_id !== null) {
		$metabox_data = get_post_custom($post_id);
		$metabox_data['post_id'] = $post_id;
	} else $metabox_data = array();

	$onepage = false;
	$background_div = $background_style = $background_color_css = '';

	if (isset($metabox_data['_uncode_page_scroll'][0]) && $metabox_data['_uncode_page_scroll'][0] == 'on') {
		$onepage = true;
	}

	$boxed = ot_get_option( '_uncode_boxed');
	$fontsizes = ot_get_option( '_uncode_heading_font_sizes');
	$background = ot_get_option( '_uncode_body_background');

	if (isset($metabox_data['_uncode_specific_body_background'])) {
		$specific_background = unserialize($metabox_data['_uncode_specific_body_background'][0]);
		if ($specific_background['background-color'] != '' || $specific_background['background-image'] != '') {
			$background = $specific_background;
		}
	}

	$back_class = '';
	if (!empty($background) && ($background['background-color'] != '' || $background['background-image'] != '')) {
		if ($background['background-color'] !== '') $background_color_css = ' style-'. $background['background-color'] . '-bg';
		$back_result_array = uncode_get_back_html($background, '', '', '', 'div');

		if ((strpos($back_result_array['mime'], 'image') !== false)) {
			$background_style .= (strpos($back_result_array['back_url'], 'background-image') !== false) ? $back_result_array['back_url'] : 'background-image: url(' . $back_result_array['back_url'] . ');';
			if ($background['background-repeat'] !== '') $background_style .= 'background-repeat: '. $background['background-repeat'] . ';';
			if ($background['background-position'] !== '') $background_style .= 'background-position: '. $background['background-position'] . ';';
			if ($background['background-size'] !== '') $background_style .= 'background-size: '. ($background['background-attachment'] === 'fixed' ? 'cover' : $background['background-size']) . ';';
			if ($background['background-attachment'] !== '') $background_style .= 'background-attachment: '. $background['background-attachment'] . ';';
		} else $background_div = $back_result_array['back_html'];
		if ($background_style !== '') $background_style = ' style="'.$background_style.'"';
		if (isset($back_result_array['async_class']) && $back_result_array['async_class'] !== '') {
			$back_class = $back_result_array['async_class'];
			$background_style .= $back_result_array['async_data'];
		}
	}

	$boxed_width = ($boxed === 'on') ? ' limit-width' : '';

?>
<body <?php body_class($background_color_css); ?>>
	<?php echo do_shortcode( shortcode_unautop( $background_div ) ); ?>
	<?php do_action( 'before' ); ?>

	<div class="box-wrapper<?php echo esc_html($back_class); ?>"<?php echo wp_kses_post($background_style); ?>>
		<div class="box-container<?php echo esc_attr($boxed_width); ?>">
		<script type="text/javascript">UNCODE.initBox();</script>
		<?php
			if ($is_redirect !== true) {
				if ($menutype === 'vmenu-offcanvas' || $menutype == 'menu-overlay') {
					$mainmenu = new unmenu('offcanvas_head', $menutype);
					echo do_shortcode( shortcode_unautop( $mainmenu->html ) );
				}
				$mainmenu = new unmenu($menutype, $menutype);
				echo do_shortcode( shortcode_unautop( $mainmenu->html ) );
			}
			?>
			<script type="text/javascript">UNCODE.fixMenuHeight();</script>
			<div class="main-wrapper">
				<div class="main-container">
					<div class="page-wrapper<?php if ($onepage) echo ' main-onepage'; ?>">
						<div class="sections-container">