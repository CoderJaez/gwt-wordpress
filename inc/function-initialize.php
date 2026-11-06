<?php
/** Classic theme with modern editor support. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

function gwt_wp_setup() {
	load_theme_textdomain( 'gwt_wp', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 96, 'width' => 96, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_editor_style( 'css/editor.css' );
	$GLOBALS['content_width'] = 800;
}
add_action( 'after_setup_theme', 'gwt_wp_setup' );
