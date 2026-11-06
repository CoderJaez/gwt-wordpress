<?php
/** Legacy banner data, with guarded writes. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

function efs_register() {
	register_post_type( 'slider-image', array(
		'labels' => array( 'name' => __( 'Banner images', 'gwt_wp' ), 'singular_name' => __( 'Banner image', 'gwt_wp' ) ),
		'public' => false, 'show_ui' => true, 'show_in_rest' => true,
		'publicly_queryable' => false, 'exclude_from_search' => true,
		'rewrite' => false, 'supports' => array( 'title', 'thumbnail', 'excerpt', 'page-attributes' ),
	) );
}
add_action( 'init', 'efs_register' );

function slider_link_add_meta_boxes() {
	add_meta_box( 'slider_link_meta_box', __( 'Banner link', 'gwt_wp' ), 'slider_link_build_meta_box', 'slider-image', 'normal', 'low' );
}
add_action( 'add_meta_boxes_slider-image', 'slider_link_add_meta_boxes' );

function slider_link_build_meta_box( $post ) {
	wp_nonce_field( 'gwt_save_banner_link', 'slider_link_meta_box_nonce' );
	echo '<label for="gwt-banner-link">' . esc_html__( 'Destination URL (https://… or /page/)', 'gwt_wp' ) . '</label>';
	echo '<input class="widefat" id="gwt-banner-link" type="text" name="slider_link" value="' . esc_attr( get_post_meta( $post->ID, '_slider_link', true ) ) . '">';
}

/** Permit HTTP(S) and site-relative paths; reject executable and protocol-relative URLs. */
function gwt_wp_sanitize_banner_url( $value ) {
	if ( ! is_string( $value ) ) { return ''; }
	$value = trim( $value );
	if ( '' === $value || str_contains( $value, '\\' ) || preg_match( '/[\x00-\x20\x7f]/', $value ) ) { return ''; }
	if ( str_starts_with( $value, '/' ) && ! str_starts_with( $value, '//' ) ) {
		return esc_url_raw( $value, array( 'http', 'https' ) );
	}
	$scheme = strtolower( (string) wp_parse_url( $value, PHP_URL_SCHEME ) );
	return in_array( $scheme, array( 'https', 'http' ), true ) ? esc_url_raw( $value, array( 'http', 'https' ) ) : '';
}

function slider_link_save_meta_box_data( $post_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) { return; }
	if ( 'slider-image' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	if ( ! isset( $_POST['slider_link_meta_box_nonce'] ) || ! is_string( $_POST['slider_link_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['slider_link_meta_box_nonce'] ) ), 'gwt_save_banner_link' ) ) { return; }
	if ( isset( $_POST['slider_link'] ) && is_string( $_POST['slider_link'] ) ) {
		update_post_meta( $post_id, '_slider_link', gwt_wp_sanitize_banner_url( wp_unslash( $_POST['slider_link'] ) ) );
	}
}
add_action( 'save_post_slider-image', 'slider_link_save_meta_box_data' );

function slider_link_get_meta_box_data( $post_id ) {
	$value = gwt_wp_sanitize_banner_url( get_post_meta( $post_id, '_slider_link', true ) );
	return str_starts_with( $value, '/' ) ? home_url( $value ) : $value;
}

