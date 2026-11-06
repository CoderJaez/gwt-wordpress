<?php
/** Read-only checks for the saved editor fixture; write only the test report. */
defined( 'GWT_TEST_RUN' ) && defined( 'ABSPATH' ) || exit;
$checks = array();
$assert = static function ( $value, $label ) use ( &$checks ) {
	if ( ! $value ) { throw new RuntimeException( $label ); }
	$checks[] = $label;
};
$settings = GWT_Carousel_Settings::sanitize( array( 'reference' => 'invalid', 'source' => array(), 'title' => array(), 'slides' => array( array( 'date' => "2026-01-01\0", 'url' => 'javascript:alert(1)', 'title' => '<img src=x onerror=alert(1)>Title' ) ) ) );
$assert( '' === $settings['reference'] && '' === $settings['slides'][0]['date'] && '' === $settings['slides'][0]['url'] && 'Title' === $settings['slides'][0]['title'], 'Malformed settings and malicious slide fields rejected' );
$assert( '' === gwt_carousel_render( array( 'source' => 'manual', 'slides' => array() ) ), 'Empty public carousel has no wrapper' );
$reference = '';
foreach ( get_option( 'widget_gwt_banner_carousel', array() ) as $instance ) {
	if ( is_array( $instance ) && ! empty( $instance['reference'] ) ) { $reference = $instance['reference']; break; }
}
$assert( wp_is_uuid( $reference ), 'Editor saved a persistent UUID reference' );
$html = do_shortcode( '[gwt_carousel ref="' . $reference . '"]' );
$assert( str_contains( $html, 'gov-carousel__slide' ), 'Reference shortcode renders the saved carousel' );
$assert( '' === do_shortcode( '[gwt_carousel ref="00000000-0000-4000-8000-000000000000"]' ), 'Unknown reference renders nothing' );
$before = $GLOBALS['post'] ?? null;
gwt_carousel_slides( GWT_Carousel_Settings::sanitize( array( 'source' => 'dynamic', 'post_type' => 'page' ) ) );
$assert( $before === ( $GLOBALS['post'] ?? null ), 'Dynamic page query preserves global post' );
$assert( count( gwt_carousel_slides( GWT_Carousel_Settings::sanitize( array( 'source' => 'banners' ) ) ) ) > 0, 'Legacy Banner images remain available' );
$widget = new GWT_Banner_Carousel();
wp_set_current_user( 0 );
ob_start();
$widget->widget( array( 'before_widget' => '<div>', 'after_widget' => '</div>' ), array( 'source' => 'manual' ) );
$assert( '' === ob_get_clean(), 'Anonymous empty widget has no wrapper' );
wp_set_current_user( 1 );
ob_start();
$widget->widget( array( 'before_widget' => '<div>', 'after_widget' => '</div>' ), array( 'source' => 'manual' ) );
$assert( str_contains( ob_get_clean(), 'Add Slide' ), 'Administrator receives empty-state setup action' );
file_put_contents( get_template_directory() . '/.test-artifacts/component-final-' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.log', implode( "\n", $checks ) . "\nWordPress " . get_bloginfo( 'version' ) . ' / PHP ' . PHP_VERSION );
