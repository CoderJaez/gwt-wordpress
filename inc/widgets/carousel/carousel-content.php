<?php
/** Resolve manual, legacy banner, and public WordPress content to one slide model. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

function gwt_carousel_slides( $settings ) {
	if ( 'manual' === $settings['source'] ) {
		return array_values( array_filter( $settings['slides'], static function ( $slide ) {
			return $slide['enabled'] && $slide['image'] && '' !== trim( $slide['title'] );
		} ) );
	}
	$type = 'banners' === $settings['source'] ? 'slider-image' : $settings['post_type'];
	$query = array(
		'post_type' => $type,
		'post_status' => 'publish',
		'has_password' => false,
		'posts_per_page' => $settings['count'],
		'meta_key' => '_thumbnail_id',
		'orderby' => array( $settings['order_by'] => $settings['order'] ) + array( 'date' => 'DESC', 'ID' => 'DESC' ),
		'ignore_sticky_posts' => true,
		'no_found_rows' => true,
	);
	foreach ( array( 'category' => 'category', 'tag' => 'post_tag' ) as $key => $taxonomy ) {
		if ( $settings[ $key ] && is_object_in_taxonomy( $type, $taxonomy ) ) {
			$query['tax_query'][] = array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $settings[ $key ] );
		}
	}
	$slides = array();
	foreach ( get_posts( $query ) as $post ) {
		if ( ! has_post_thumbnail( $post ) || '' === trim( $post->post_title ) ) {
			continue;
		}
		$terms = get_the_category( $post->ID );
		$slide = GWT_Carousel_Settings::slide( array(
			'image' => get_post_thumbnail_id( $post ),
			'title' => get_the_title( $post ),
			'description' => 'banners' === $settings['source'] ? $post->post_excerpt : wp_trim_words( get_the_excerpt( $post ), 35 ),
			'button_text' => __( 'Learn more', 'gwt_wp' ),
			'url' => 'banners' === $settings['source'] ? slider_link_get_meta_box_data( $post->ID ) : get_permalink( $post ),
			'date' => 'banners' === $settings['source'] ? '' : get_the_date( 'Y-m-d', $post ),
			'category' => ! empty( $terms ) ? $terms[0]->name : '',
		) );
		if ( $slide['image'] ) {
			$slides[] = $slide;
		}
	}
	return $slides;
}
