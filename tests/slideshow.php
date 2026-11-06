<?php
/** Read-only fixture rendered with real WordPress APIs. Disposable site only. */
defined( 'GWT_TEST_RUN' ) && defined( 'ABSPATH' ) || exit;
$settings = GWT_Carousel_Settings::sanitize( array( 'desktop' => 4, 'tablet' => 3, 'mobile' => 2, 'transition' => 'invalid' ) );
if ( 1 !== $settings['desktop'] || 1 !== $settings['tablet'] || 1 !== $settings['mobile'] || 'slide' !== $settings['transition'] ) {
	throw new RuntimeException( 'Slideshow defaults and legacy gallery normalization failed.' );
}
$instances = get_option( 'widget_gwt_banner_carousel' );
$input = $instances[2];
$input['autoplay'] = true;
$input['interval_ms'] = 1000;
$input['pause_hover'] = false;
$output = array();
foreach ( array( 'slide', 'fade', 'slide-up', 'none' ) as $effect ) {
	$input['transition'] = $effect;
	$output[ $effect ] = gwt_carousel_render( $input );
	if ( GWT_Carousel_Settings::sanitize( $input )['transition'] !== $effect ) { throw new RuntimeException( 'Invalid animation option.' ); }
}
file_put_contents( get_template_directory() . '/.test-artifacts/slideshow-fixture.json', wp_json_encode( $output ) );
file_put_contents( get_template_directory() . '/.test-artifacts/slideshow-php.log', 'PASS: one-slide normalization, default slide effect, four validated animation options, real widget rendering. PHP ' . PHP_VERSION );
