<?php
/** Run inside a disposable WordPress installation; never exposed as an endpoint. */
defined( 'GWT_TEST_RUN' ) && defined( 'ABSPATH' ) || exit;

function gwt_test( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	echo 'PASS: ' . $message . "\n";
}

gwt_test( current_theme_supports( 'title-tag' ), 'WordPress manages document titles' );
gwt_test( current_theme_supports( 'editor-styles' ), 'Block editor styles enabled' );
gwt_test( use_block_editor_for_post_type( 'post' ), 'Block editor remains available' );
gwt_test( isset( $GLOBALS['wp_registered_sidebars']['left-sidebar'], $GLOBALS['wp_registered_sidebars']['footer-4'] ), 'Legacy widget locations retained' );

foreach ( array( 'javascript:alert(1)', 'data:text/html,<script>alert(1)</script>', '//evil.example', '/\\evil.example', array( 'url' ), "https://example.org/\nattack" ) as $bad_url ) {
	gwt_test( '' === gwt_wp_sanitize_banner_url( $bad_url ), 'Unsafe banner URL rejected: ' . wp_json_encode( $bad_url ) );
}
gwt_test( '/services/' === gwt_wp_sanitize_banner_url( '/services/' ), 'Site-relative banner URL accepted' );
gwt_test( 'https://example.org/path?a=1&b=2' === gwt_wp_sanitize_banner_url( 'https://example.org/path?a=1&b=2' ), 'HTTPS banner URL accepted' );

$widget = new govph_widget_transparency();
$saved = $widget->update( array( 'url' => 'javascript:alert(1)' ), array() );
gwt_test( '' === $saved['url'], 'Widget rejects executable URL schemes' );
ob_start();
$widget->widget( array( 'before_widget' => '<aside>', 'after_widget' => '</aside>' ), array( 'url' => 'https://example.org/" onmouseover="alert(1)' ) );
$output = ob_get_clean();
gwt_test( ! str_contains( $output, ' onmouseover="' ), 'Stored widget values cannot break out of attributes' );
new govph_widget_foi();

$banner_id = wp_insert_post( array( 'post_type' => 'slider-image', 'post_status' => 'publish', 'post_title' => 'Test banner' ) );
update_post_meta( $banner_id, '_slider_link', '/original/' );
wp_set_current_user( 1 );
$_POST = array( 'slider_link' => '/changed/' );
slider_link_save_meta_box_data( $banner_id );
gwt_test( '/original/' === get_post_meta( $banner_id, '_slider_link', true ), 'Missing nonce prevents writes' );
$_POST['slider_link_meta_box_nonce'] = wp_create_nonce( 'gwt_save_banner_link' );
slider_link_save_meta_box_data( $banner_id );
gwt_test( '/changed/' === get_post_meta( $banner_id, '_slider_link', true ), 'Authorized nonce-protected write succeeds' );
$_POST['slider_link'] = 'javascript:alert(1)';
slider_link_save_meta_box_data( $banner_id );
gwt_test( '' === get_post_meta( $banner_id, '_slider_link', true ), 'Executable URLs removed on save' );
$_POST['slider_link'] = '/unauthorized/';
wp_set_current_user( 0 );
slider_link_save_meta_box_data( $banner_id );
gwt_test( '' === get_post_meta( $banner_id, '_slider_link', true ), 'Anonymous user cannot write banner metadata' );
wp_set_current_user( 1 );
$_POST['slider_link_meta_box_nonce'] = array( 'bad' );
slider_link_save_meta_box_data( $banner_id );
gwt_test( '' === get_post_meta( $banner_id, '_slider_link', true ), 'Malformed nonce rejected without a type error' );
$_POST = array();
wp_delete_post( $banner_id, true );

$query = $GLOBALS['wp_query'];
gwt_test( '' === efs_get_slider(), 'No banners produces empty output without warnings' );
gwt_test( $query === $GLOBALS['wp_query'], 'Banner rendering preserves the main query' );

require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
$customizer = new WP_Customize_Manager();
gwt_wp_customize_register( $customizer );
gwt_test( 'edit_theme_options' === $customizer->get_setting( 'gwt_intro_title' )->capability, 'Customizer uses theme-editing capability' );
gwt_test( ! str_contains( $customizer->get_setting( 'gwt_intro_title' )->sanitize( '<script>alert(1)</script>Headline' ), '<' ), 'Headline setting strips markup' );
gwt_test( '' === $customizer->get_setting( 'gwt_services_url' )->sanitize( 'javascript:alert(1)' ), 'Customizer URL setting rejects executable schemes' );

wp_set_current_user( 0 );
$response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/posts' ) );
gwt_test( 200 === $response->get_status(), 'Public content REST reads remain available' );
$response = rest_do_request( new WP_REST_Request( 'POST', '/wp/v2/posts' ) );
gwt_test( 401 === $response->get_status(), 'Anonymous REST writes remain forbidden' );

echo 'Runtime: WordPress ' . get_bloginfo( 'version' ) . ', PHP ' . PHP_VERSION . "\n";
