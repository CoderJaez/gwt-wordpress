<?php
/** Existing widget identifiers retained for migration. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

class govph_widget_pst extends WP_Widget {
	public function __construct() {
		parent::__construct( 'govph_widget_pst', __( 'Philippine time', 'gwt_wp' ) );
	}
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		echo '<h3 class="widget-title">' . esc_html__( 'Philippine time', 'gwt_wp' ) . '</h3><span data-ph-time>' . esc_html( wp_date( 'M j, Y, g:i a', null, new DateTimeZone( 'Asia/Manila' ) ) ) . '</span>';
		echo $args['after_widget'];
	}
}

class govph_widget_transparency extends WP_Widget {
	protected $image = 'transparency-seal-160x160.png';
	protected $label = 'Transparency Seal';
	protected $default_url = '';
	public function __construct() {
		parent::__construct( 'govph_widget_transparency', __( 'Transparency Seal', 'gwt_wp' ) );
	}
	public function widget( $args, $instance ) {
		$url = isset( $instance['url'] ) && is_string( $instance['url'] ) ? esc_url( $instance['url'], array( 'https', 'http' ) ) : $this->default_url;
		echo $args['before_widget'];
		if ( $url ) {
			echo '<a href="' . esc_url( $url ) . '"><img src="' . esc_url( get_template_directory_uri() . '/images/' . $this->image ) . '" width="160" height="160" loading="lazy" alt="' . esc_attr( $this->label ) . '"></a>';
		}
		echo $args['after_widget'];
	}
	public function update( $new_instance, $old_instance ) {
		return array( 'url' => isset( $new_instance['url'] ) && is_string( $new_instance['url'] ) ? esc_url_raw( $new_instance['url'], array( 'http', 'https' ) ) : '' );
	}
	public function form( $instance ) {
		$url = isset( $instance['url'] ) && is_string( $instance['url'] ) ? $instance['url'] : $this->default_url;
		echo '<p><label for="' . esc_attr( $this->get_field_id( 'url' ) ) . '">' . esc_html__( 'Destination URL', 'gwt_wp' ) . '</label>';
		echo '<input class="widefat" type="url" id="' . esc_attr( $this->get_field_id( 'url' ) ) . '" name="' . esc_attr( $this->get_field_name( 'url' ) ) . '" value="' . esc_attr( $url ) . '"></p>';
	}
}

class govph_widget_foi extends govph_widget_transparency {
	protected $image = 'foi-logo-160x160.png';
	protected $label = 'Freedom of Information';
	protected $default_url = 'https://www.foi.gov.ph/';
	public function __construct() {
		WP_Widget::__construct( 'govph_widget_foi', __( 'Freedom of Information', 'gwt_wp' ) );
	}
}
function gwt_wp_register_government_widgets() {
	register_widget( 'govph_widget_pst' );
	register_widget( 'govph_widget_transparency' );
	register_widget( 'govph_widget_foi' );
}
add_action( 'widgets_init', 'gwt_wp_register_government_widgets' );
