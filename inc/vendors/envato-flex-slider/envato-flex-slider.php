<?php
/** Static banners retain the ef_slider shortcode without a third-party carousel. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
require_once __DIR__ . '/slider-img-type.php';

function efs_get_slider() {
	$banners = get_posts( array( 'post_type' => 'slider-image', 'post_status' => 'publish', 'posts_per_page' => 6, 'orderby' => 'menu_order date', 'order' => 'DESC', 'no_found_rows' => true ) );
	$html = '';
	foreach ( $banners as $banner ) {
		if ( post_password_required( $banner ) ) { continue; }
		$image = get_the_post_thumbnail( $banner->ID, 'large' );
		if ( ! $image ) { continue; }
		$url = slider_link_get_meta_box_data( $banner->ID );
		$html .= '<figure>';
		$html .= $url ? '<a href="' . esc_url( $url ) . '" aria-label="' . esc_attr( get_the_title( $banner ) ?: __( 'View announcement', 'gwt_wp' ) ) . '">' . $image . '</a>' : $image;
		$html .= '<figcaption>' . esc_html( get_the_title( $banner ) ) . '</figcaption></figure>';
	}
	return $html ? '<div class="banner-gallery" aria-label="' . esc_attr__( 'Announcements', 'gwt_wp' ) . '">' . $html . '</div>' : '';
}
function efs_insert_slider( $atts = array(), $content = null ) {
	return efs_get_slider();
}
add_shortcode( 'ef_slider', 'efs_insert_slider' );
function efs_slider() {
	echo efs_get_slider();
}
