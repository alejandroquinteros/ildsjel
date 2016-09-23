<?php

$json = @file_get_contents( 'http://www.shindiristudio.com/test/themes.json' );

$output = '';

if ( ! empty( $json ) ) {
	$data = json_decode( $json );

	$txtClass = ( ( ! empty( $theme->name ) && isset( $theme->name ) ) ? 'txtFull' : 'txtEmpty');

	$output .= '<h1 style="margin: 35px 0;">Recommended wordpress templates that go perfectly with Content Timeline plugin</h1>';

	$output .= '<div class="recomm-wrap timeline-grid">';
	foreach ( $data->themes as $theme ) {
		$output .= '
		<div class="recomm-plugin timeline-grid-item ' . esc_attr( $theme->class ) . ' ' . $txtClass . '">
			<div class="animation">
				<a class="image"
				   href="' . esc_URL( $theme->purchase ) . '"
				   target="_blank">
					<img
						src="' . esc_URL( $theme->image ) . '"
						alt="All Around">
				</a>
			</div>';
		if ( $theme->name != '' ) {
			$output .= '<a class="link-plugin"
			   href="' . esc_URL( $theme->purchase ) . '"
			   target="_blank">' . esc_attr( $theme->name ) . ' - ' . esc_attr( $theme->description ) . '</a>';
		}

		$output .= '</div>';
	}

	$output .= '</div>';
	$output .= '
	<script type="text/javascript">

		$(document).ready(function () {
			$(".timeline-grid").isotope({
				itemSelector: ".timeline-grid-item",
				masonry: {
					columnWidth: 100
				}
			});
		})
	</script>';

} else {
	$output .= '<p class="recomm-error">Houston we have a problem! <br/> Plaese try latter or visit Our portfolio on <a
		href="http://themeforest.net/user/ShindiriStudio/portfolio?ref=ShindiriStudio"> Envato </a>.</p><p>You can also
	contact Our support on <a href="support.shindiristudio.com"> support.shindiristudio.com </a></p>';
}

echo $output;