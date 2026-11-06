<?php
/** Disposable carousel fixture and real-API assertions. */
defined( 'GWT_TEST_RUN' ) && defined( 'ABSPATH' ) || exit;
wp_set_current_user( 1 );
$widget = new GWT_Banner_Carousel();
$settings = $widget->update( array( 'title' => '<script>bad()</script>DILG', 'count' => 100, 'interval_ms' => 1, 'autoplay' => array( 'bad' ) ), array() );
if ( 12 !== $settings['count'] || 50 !== $settings['interval_ms'] || ! $settings['autoplay'] || str_contains( $settings['title'], '<' ) ) { throw new RuntimeException( 'Widget sanitization failed' ); }
require_once ABSPATH . 'wp-admin/includes/image.php';
$upload = wp_upload_bits( 'carousel-test.png', null, file_get_contents( get_template_directory() . '/screenshot.png' ) );
if ( $upload['error'] ) { throw new RuntimeException( $upload['error'] ); }
$image = wp_insert_attachment( array( 'post_title' => 'Test banner', 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $upload['file'] );
wp_update_attachment_metadata( $image, wp_generate_attachment_metadata( $image, $upload['file'] ) );
foreach ( array( 'Responsive public services', 'Stronger local governance', 'Safer communities' ) as $i => $title ) {
	$post = wp_insert_post( array( 'post_type' => 'slider-image', 'post_title' => $title, 'post_excerpt' => 'Working with local governments and communities.', 'menu_order' => $i, 'post_status' => 'publish' ) );
	set_post_thumbnail( $post, $image );
	update_post_meta( $post, '_slider_link', '/?page_id=2' );
}
$protected = wp_insert_post( array( 'post_type' => 'slider-image', 'post_title' => 'PRIVATE_CAROUSEL_TITLE', 'post_password' => 'private', 'post_status' => 'publish' ) );
set_post_thumbnail( $protected, $image );
ob_start();
$widget->widget( array( 'before_widget' => '', 'after_widget' => '' ), array( 'title' => 'DILG test', 'count' => 12 ) );
$html = ob_get_clean();
if ( str_contains( $html, 'PRIVATE_CAROUSEL_TITLE' ) || 3 !== substr_count( $html, 'class="dilg-carousel-slide"' ) ) { throw new RuntimeException( 'Slide filtering failed' ); }
update_option( 'widget_gwt_banner_carousel', array( 2 => array( 'title' => 'DILG highlights', 'count' => 6, 'interval' => 4, 'autoplay' => true ), '_multiwidget' => 1 ) );
$sidebars = wp_get_sidebars_widgets();
$sidebars['header-carousel'] = array( 'gwt_banner_carousel-2' );
wp_set_sidebars_widgets( $sidebars );
file_put_contents( get_template_directory() . '/.test-artifacts/carousel-php.log', 'PASS: setting sanitization, bounds, malformed autoplay, published-image filtering, protected-slide exclusion. Runtime: WordPress ' . get_bloginfo( 'version' ) . ' / PHP ' . PHP_VERSION );
