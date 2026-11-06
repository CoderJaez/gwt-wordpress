<?php
/** Carousel component bootstrap; the legacy include path remains stable. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/widgets/carousel/class-carousel-settings.php';
require_once __DIR__ . '/widgets/carousel/carousel-content.php';
require_once __DIR__ . '/widgets/carousel/carousel-render.php';
require_once __DIR__ . '/widgets/carousel/class-carousel-admin.php';
require_once __DIR__ . '/widgets/carousel/class-carousel-widget.php';

/** Register the reusable widget while preserving existing sidebar assignments. */
function gwt_wp_register_carousel() {
	register_widget( 'GWT_Banner_Carousel' );
	register_sidebar( array(
		'name' => __( 'Carousel', 'gwt_wp' ),
		'id' => 'header-carousel',
		'description' => __( 'Homepage carousel below navigation. Configure Carousel widgets here; existing Banner images appear when empty.', 'gwt_wp' ),
		'before_widget' => '<div id="%1$s" class="homepage-carousel-widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	) );
}
add_action( 'widgets_init', 'gwt_wp_register_carousel' );

/** New uploads receive a suitably sized hero variant; older uploads use large. */
function gwt_carousel_image_sizes() {
	add_image_size( 'gwt-carousel', 1920, 0, false );
}
add_action( 'after_setup_theme', 'gwt_carousel_image_sizes' );

/** Asset registration is separate from use so widgets and shortcodes share it. */
function gwt_carousel_register_assets() {
	$version = wp_get_theme( get_template() )->get( 'Version' );
	wp_register_style( 'gwt-carousel', get_template_directory_uri() . '/css/carousel.css', array(), $version );
	wp_register_script( 'gwt-carousel', get_template_directory_uri() . '/js/carousel.js', array(), $version, array( 'strategy' => 'defer', 'in_footer' => true ) );
}
add_action( 'init', 'gwt_carousel_register_assets' );

function gwt_carousel_enqueue_assets() {
	wp_enqueue_style( 'gwt-carousel' );
	wp_enqueue_script( 'gwt-carousel' );
}

/** Preload styles on predictable widget/shortcode surfaces to avoid a late repaint. */
function gwt_carousel_maybe_enqueue() {
	$post = get_post();
	$needed = ( is_front_page() && is_home() ) || is_active_sidebar( 'header-carousel' ) || is_active_widget( false, false, 'gwt_banner_carousel', true );
	if ( $post && has_shortcode( $post->post_content, 'gwt_carousel' ) ) {
		$needed = true;
	}
	if ( ! $needed && is_active_widget( false, false, 'block', true ) ) {
		foreach ( (array) get_option( 'widget_block', array() ) as $block ) {
			if ( is_array( $block ) && is_string( $block['content'] ?? null ) && str_contains( $block['content'], 'gwt_banner_carousel' ) ) {
				$needed = true;
				break;
			}
		}
	}
	if ( $needed ) {
		gwt_carousel_enqueue_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'gwt_carousel_maybe_enqueue', 20 );

/** Handle a widget rendered from a template that could not be predicted in the head. */
function gwt_carousel_late_styles() {
	if ( wp_style_is( 'gwt-carousel', 'enqueued' ) && ! wp_style_is( 'gwt-carousel', 'done' ) ) {
		wp_print_styles( 'gwt-carousel' );
	}
}
add_action( 'wp_footer', 'gwt_carousel_late_styles', 5 );

/** Reuse a saved widget on any page through a core Shortcode block. */
function gwt_carousel_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'id' => 0, 'ref' => '' ), $attributes, 'gwt_carousel' );
	$id = is_scalar( $attributes['id'] ) ? absint( $attributes['id'] ) : 0;
	$instances = get_option( 'widget_gwt_banner_carousel', array() );
	if ( is_array( $instances ) && is_string( $attributes['ref'] ) && wp_is_uuid( $attributes['ref'] ) ) {
		foreach ( $instances as $instance ) {
			if ( is_array( $instance ) && ( $instance['reference'] ?? '' ) === $attributes['ref'] ) {
				return gwt_carousel_render( $instance );
			}
		}
		return '';
	}
	return $id && is_array( $instances ) && isset( $instances[ $id ] ) ? gwt_carousel_render( $instances[ $id ] ) : '';
}
add_shortcode( 'gwt_carousel', 'gwt_carousel_shortcode' );

/** Enqueue the native Media Library only on widget/customizer screens. */
function gwt_carousel_admin_assets( $hook = '' ) {
	if ( ! in_array( $hook, array( 'widgets.php', 'customize.php' ), true ) && ! doing_action( 'customize_controls_enqueue_scripts' ) ) {
		return;
	}
	wp_enqueue_media();
	$version = wp_get_theme( get_template() )->get( 'Version' );
	wp_enqueue_style( 'gwt-carousel-admin', get_template_directory_uri() . '/css/carousel-admin.css', array(), $version );
	wp_enqueue_script( 'gwt-carousel-admin', get_template_directory_uri() . '/js/carousel-admin.js', array( 'media-views', 'wp-api-fetch', 'wp-data' ), $version, true );
	wp_localize_script( 'gwt-carousel-admin', 'gwtCarouselEditor', array(
		'url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'gwt_carousel_preview' ),
		'chooseImage' => __( 'Choose carousel image', 'gwt_wp' ),
		'useImage' => __( 'Use image', 'gwt_wp' ),
		'newSlide' => __( 'New slide', 'gwt_wp' ),
		'limit' => __( 'A carousel supports up to 30 manual slides.', 'gwt_wp' ),
		'loading' => __( 'Updating preview…', 'gwt_wp' ),
		'updated' => __( 'Preview updated. Save the widget to publish changes.', 'gwt_wp' ),
		'failed' => __( 'Preview could not be loaded. Save your changes and reload the editor.', 'gwt_wp' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'gwt_carousel_admin_assets' );
add_action( 'customize_controls_enqueue_scripts', 'gwt_carousel_admin_assets' );
add_action( 'wp_ajax_gwt_carousel_preview', array( 'GWT_Carousel_Admin', 'preview' ) );
