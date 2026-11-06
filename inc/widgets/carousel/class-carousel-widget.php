<?php
/** Native widget adapter; rendering and validation are reusable outside widgets. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

class GWT_Banner_Carousel extends WP_Widget {
	/** Preserve the existing ID so installed carousel instances retain their data. */
	public function __construct() {
		parent::__construct(
			'gwt_banner_carousel',
			__( 'Carousel', 'gwt_wp' ),
			array(
				'description' => __( 'Configurable announcements, programs, and services with manual or dynamic slides.', 'gwt_wp' ),
				'customize_selective_refresh' => true,
				'show_instance_in_rest' => true,
			),
			array( 'width' => 500 )
		);
	}

	/** WordPress enforces widget-save capabilities/nonces before calling this method. */
	public function update( $new_instance, $old_instance ) {
		return GWT_Carousel_Settings::sanitize( $new_instance );
	}

	/** Render editor controls in both classic and block-based widget screens. */
	public function form( $instance ) {
		GWT_Carousel_Admin::form( $this, $instance );
	}

	/** Do not emit an empty wrapper to public visitors. */
	public function widget( $args, $instance ) {
		$html = gwt_carousel_render( $instance );
		if ( $html ) {
			echo $args['before_widget'] . $html . $args['after_widget'];
		} elseif ( current_user_can( 'edit_theme_options' ) ) {
			echo $args['before_widget'] . '<div class="gov-carousel-empty"><p>' . esc_html__( 'No carousel slides have been added yet.', 'gwt_wp' ) . '</p><a href="' . esc_url( admin_url( 'widgets.php' ) ) . '">' . esc_html__( 'Add Slide', 'gwt_wp' ) . '</a></div>' . $args['after_widget'];
		}
	}
}
