<?php
/** Theme bootstrap. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/function-initialize.php';
require_once get_template_directory() . '/inc/function-enqueue_scripts.php';
require_once get_template_directory() . '/inc/function-widget.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/govph-widget.php';
require_once get_template_directory() . '/inc/vendors/envato-flex-slider/envato-flex-slider.php';
require_once get_template_directory() . '/inc/carousel-widget.php';

/** Leave authentication and REST permissions to WordPress. */
function gwt_wp_security_headers( $headers ) {
	$headers['X-Content-Type-Options'] = $headers['X-Content-Type-Options'] ?? 'nosniff';
	$headers['X-Frame-Options'] = $headers['X-Frame-Options'] ?? 'SAMEORIGIN';
	$headers['Referrer-Policy'] = $headers['Referrer-Policy'] ?? 'strict-origin-when-cross-origin';
	return $headers;
}
add_filter( 'wp_headers', 'gwt_wp_security_headers' );

/** Render occupied legacy widget areas without changing saved assignments. */
function gwt_wp_widget_group( $ids, $class = 'widget-grid' ) {
	$active = array_filter( $ids, 'is_active_sidebar' );
	if ( ! $active ) {
		return;
	}
	echo '<div class="' . esc_attr( $class ) . '">';
	foreach ( $active as $id ) {
		echo '<div class="widget-area">';
		dynamic_sidebar( $id );
		echo '</div>';
	}
	echo '</div>';
}

/** Read existing identity without rewriting the legacy options record. */
function gwt_wp_identity( $key, $fallback = '' ) {
	$options = get_option( 'govph_options', array() );
	$value = is_array( $options ) ? ( $options[ $key ] ?? '' ) : '';
	return is_string( $value ) && '' !== trim( $value ) ? $value : $fallback;
}
