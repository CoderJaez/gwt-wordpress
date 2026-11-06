<?php
/** Local assets without legacy frameworks or duplicate jQuery. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

function gwt_wp_scripts() {
	$version = wp_get_theme( get_template() )->get( 'Version' );
	wp_enqueue_style( 'gwt_wp-style', get_template_directory_uri() . '/theme.css', array(), $version );
	wp_enqueue_style( 'gwt_wp-user-style', get_stylesheet_uri(), array( 'gwt_wp-style' ), wp_get_theme()->get( 'Version' ) );
	wp_enqueue_script( 'gwt_wp-theme', get_template_directory_uri() . '/js/theme.js', array(), $version, array( 'strategy' => 'defer', 'in_footer' => true ) );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'gwt_wp_scripts' );
