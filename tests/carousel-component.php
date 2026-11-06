<?php
/** Run only in a disposable WordPress Playground installation. */
defined( 'GWT_TEST_RUN' ) && defined( 'ABSPATH' ) || exit;
require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$checks = array();
$check = static function ( $condition, $label ) use ( &$checks ) {
	if ( ! $condition ) { throw new RuntimeException( $label ); }
	$checks[] = $label;
};
$settings = GWT_Carousel_Settings::sanitize( array( 'desktop' => 99, 'mobile' => -1, 'interval_ms' => 50, 'overlay_color' => 'red;position:fixed', 'autoplay' => array( 1 ), 'source' => 'bad' ) );
$check( 4 === $settings['desktop'] && 1 === $settings['mobile'] && 5000 === $settings['interval_ms'] && '' === $settings['overlay_color'] && ! $settings['autoplay'] && 'banners' === $settings['source'], 'Settings bounds, defaults, and hostile types' );
$slide = GWT_Carousel_Settings::slide( array( 'date' => "2026-01-01\0", 'title' => '<script>alert(1)</script>Title', 'url' => 'javascript:alert(1)', 'secondary_url' => '//evil.example', 'focal_x' => 200 ) );
$check( '' === $slide['date'] && '' === $slide['url'] && '' === $slide['secondary_url'] && ! str_contains( $slide['title'], '<' ) && 100 === $slide['focal_x'], 'Slide sanitization rejects script URLs, invalid dates, and CSS injection' );
$check( '' === gwt_carousel_render( array( 'source' => 'manual' ) ), 'Empty public output' );
$images = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => 1 ) );
if ( $images ) {
	$image = $images[0]->ID;
} else {
	$upload = wp_upload_bits( 'carousel-component-test.png', null, file_get_contents( get_template_directory() . '/screenshot.png' ) );
	if ( $upload['error'] ) { throw new RuntimeException( $upload['error'] ); }
	$image = wp_insert_attachment( array( 'post_title' => 'Carousel fixture artwork', 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $upload['file'] );
	wp_update_attachment_metadata( $image, wp_generate_attachment_metadata( $image, $upload['file'] ) );
}
$slides = array();
foreach ( array( 'Strengthening local governance', 'Public services for everyone', 'Safer, resilient communities', 'Working together' ) as $i => $title ) {
	$slides[] = array( 'image' => $image, 'mobile_image' => $image, 'title' => $title, 'subtitle' => 'Empowering local government units', 'description' => 'Discover programs and initiatives supporting responsive and accountable local governance.', 'button_text' => 'Learn more', 'url' => '/?page_id=2', 'secondary_text' => 'Our services', 'secondary_url' => '/?page_id=2', 'new_tab' => true, 'category' => 'Programs', 'date' => '2026-09-12', 'badge' => 'DILG', 'alt' => 'Test artwork' );
}
$manual = GWT_Carousel_Settings::sanitize( array( 'source' => 'manual', 'slides' => $slides ) );
$invalid = $manual;
$invalid['slides'][] = GWT_Carousel_Settings::slide( array( 'title' => 'Missing image' ) );
$invalid['slides'][] = GWT_Carousel_Settings::slide( array( 'image' => $image, 'title' => 'Disabled', 'enabled' => false ) );
$check( 4 === count( gwt_carousel_slides( $invalid ) ), 'Disabled and incomplete slides excluded' );
$html = gwt_carousel_render( $manual );
$check( 4 === substr_count( $html, 'class="gov-carousel__slide"' ) && str_contains( $html, 'srcset=' ) && str_contains( $html, '<source media=' ) && str_contains( $html, 'noopener noreferrer' ) && str_contains( $html, 'fetchpriority="high"' ), 'Responsive media, safe links, all slide fields rendered' );
$check( $html !== gwt_carousel_render( $manual ), 'Unique instance IDs' );
$category = wp_create_category( 'Carousel test category ' . wp_generate_uuid4() );
foreach ( array( '2024-01-01', '2025-01-01' ) as $i => $date ) {
	$id = wp_insert_post( array( 'post_title' => 'Carousel ordering ' . $i, 'post_status' => 'publish', 'post_date' => $date . ' 12:00:00', 'post_category' => array( $category ) ) );
	set_post_thumbnail( $id, $image );
}
$id = wp_insert_post( array( 'post_title' => 'Protected carousel content', 'post_status' => 'publish', 'post_password' => 'secret', 'post_category' => array( $category ) ) );
set_post_thumbnail( $id, $image );
$dynamic = gwt_carousel_slides( GWT_Carousel_Settings::sanitize( array( 'source' => 'dynamic', 'category' => $category, 'order_by' => 'date', 'order' => 'ASC' ) ) );
$check( 2 === count( $dynamic ) && 'Carousel ordering 0' === $dynamic[0]['title'], 'Dynamic category filter, ascending dates, protected content excluded' );
$multiple = $manual;
$multiple['desktop'] = 3;
$multiple['tablet'] = 2;
$multiple['mobile'] = 2;
$multiple['autoplay'] = false;
$multiple['loop'] = false;
$multiple['transition'] = 'slide';
update_option( 'widget_gwt_banner_carousel', array( 2 => $manual, 3 => $multiple, '_multiwidget' => 1 ) );
$sidebars = wp_get_sidebars_widgets();
$sidebars['header-carousel'] = array( 'gwt_banner_carousel-2' );
wp_set_sidebars_widgets( $sidebars );
$page_id = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'Carousel component test', 'post_status' => 'publish', 'post_content' => '[gwt_carousel id="2"][gwt_carousel id="3"]' ) );
$check( str_contains( do_shortcode( '[gwt_carousel id="2"]' ), 'gov-carousel' ) && '' === do_shortcode( '[gwt_carousel id="999999"]' ), 'Saved widget shortcode and invalid ID' );
wp_set_current_user( 1 );
wp_set_password( 'Carousel-Test-Only-2026!', 1 );
file_put_contents( get_template_directory() . '/.test-artifacts/component-fixture.json', wp_json_encode( array( 'page' => $page_id, 'image' => $image, 'username' => get_userdata( 1 )->user_login ) ) );
file_put_contents( get_template_directory() . '/.test-artifacts/component-php.log', implode( "\n", $checks ) . "\nWordPress " . get_bloginfo( 'version' ) . ' / PHP ' . PHP_VERSION );
