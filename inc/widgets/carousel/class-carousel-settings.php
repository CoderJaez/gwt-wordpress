<?php
/** Validated carousel configuration shared by saving, preview, and rendering. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

class GWT_Carousel_Settings {
	/** Declarative controls keep validation and the editor in sync. */
	public static function schema() {
		return array(
			'title' => array( 'Content', __( 'Accessible name', 'gwt_wp' ), 'text', __( 'Featured announcements', 'gwt_wp' ) ),
			'source' => array( 'Content', __( 'Slide source', 'gwt_wp' ), 'select', 'banners', array( 'manual' => __( 'Manual slides', 'gwt_wp' ), 'banners' => __( 'Existing Banner images', 'gwt_wp' ), 'dynamic' => __( 'WordPress content', 'gwt_wp' ) ) ),
			'post_type' => array( 'Content', __( 'Dynamic post type', 'gwt_wp' ), 'post_type', 'post' ),
			'category' => array( 'Content', __( 'Category ID (0 = all; supported post types only)', 'gwt_wp' ), 'number', 0, 0, 2147483647 ),
			'tag' => array( 'Content', __( 'Tag ID (0 = all; supported post types only)', 'gwt_wp' ), 'number', 0, 0, 2147483647 ),
			'count' => array( 'Content', __( 'Dynamic/banner item limit', 'gwt_wp' ), 'number', 6, 1, 50 ),
			'order_by' => array( 'Content', __( 'Sort by', 'gwt_wp' ), 'select', 'menu_order', array( 'menu_order' => __( 'Order', 'gwt_wp' ), 'date' => __( 'Publication date', 'gwt_wp' ), 'title' => __( 'Title', 'gwt_wp' ) ) ),
			'order' => array( 'Content', __( 'Sort direction', 'gwt_wp' ), 'select', 'ASC', array( 'ASC' => __( 'Ascending', 'gwt_wp' ), 'DESC' => __( 'Descending', 'gwt_wp' ) ) ),
			'layout' => array( 'Layout', __( 'Layout', 'gwt_wp' ), 'select', 'full', array( 'full' => __( 'Full width', 'gwt_wp' ), 'boxed' => __( 'Boxed / container', 'gwt_wp' ), 'content' => __( 'Content width', 'gwt_wp' ) ) ),
			'container_width' => array( 'Layout', __( 'Boxed width (px)', 'gwt_wp' ), 'number', 1200, 320, 1920 ),
			'height' => array( 'Layout', __( 'Height', 'gwt_wp' ), 'select', 'medium', array( 'auto' => __( 'Auto (image ratio)', 'gwt_wp' ), 'small' => __( 'Small — 320px', 'gwt_wp' ), 'medium' => __( 'Medium — 480px', 'gwt_wp' ), 'large' => __( 'Large — 600px', 'gwt_wp' ), 'custom' => __( 'Custom', 'gwt_wp' ) ) ),
			'custom_height' => array( 'Layout', __( 'Custom height (px)', 'gwt_wp' ), 'number', 480, 200, 1000 ),
			'previous' => array( 'Navigation', __( 'Previous arrow', 'gwt_wp' ), 'checkbox', true ),
			'next' => array( 'Navigation', __( 'Next arrow', 'gwt_wp' ), 'checkbox', true ),
			'dots' => array( 'Navigation', __( 'Pagination dots', 'gwt_wp' ), 'checkbox', true ),
			'counter' => array( 'Navigation', __( 'Slide counter', 'gwt_wp' ), 'checkbox', true ),
			'autoplay' => array( 'Playback', __( 'Autoplay', 'gwt_wp' ), 'checkbox', true ),
			'interval_ms' => array( 'Playback', __( 'Autoplay delay (ms)', 'gwt_wp' ), 'number', 5000, 1000, 60000 ),
			'pause_hover' => array( 'Playback', __( 'Pause on hover', 'gwt_wp' ), 'checkbox', true ),
			'pause_hidden' => array( 'Playback', __( 'Pause in inactive browser tabs', 'gwt_wp' ), 'checkbox', true ),
			'stop_interaction' => array( 'Playback', __( 'Stop after user interaction', 'gwt_wp' ), 'checkbox', true ),
			'loop' => array( 'Playback', __( 'Loop slides', 'gwt_wp' ), 'checkbox', true ),
			'transition' => array( 'Playback', __( 'Animation', 'gwt_wp' ), 'select', 'slide', array( 'slide' => __( 'Slide horizontally', 'gwt_wp' ), 'fade' => __( 'Fade', 'gwt_wp' ), 'slide-up' => __( 'Slide vertically', 'gwt_wp' ), 'none' => __( 'None', 'gwt_wp' ) ) ),
			'speed' => array( 'Playback', __( 'Transition speed (ms)', 'gwt_wp' ), 'number', 600, 0, 2000 ),
			'overlay' => array( 'Overlay', __( 'Show text overlay', 'gwt_wp' ), 'checkbox', true ),
			'position' => array( 'Overlay', __( 'Content position', 'gwt_wp' ), 'select', 'bottom-left', array( 'left-center' => __( 'Left center', 'gwt_wp' ), 'center' => __( 'Center', 'gwt_wp' ), 'right-center' => __( 'Right center', 'gwt_wp' ), 'bottom-left' => __( 'Bottom left', 'gwt_wp' ), 'bottom-center' => __( 'Bottom center', 'gwt_wp' ), 'bottom-right' => __( 'Bottom right', 'gwt_wp' ) ) ),
			'align' => array( 'Overlay', __( 'Text alignment', 'gwt_wp' ), 'select', 'left', array( 'left' => __( 'Left', 'gwt_wp' ), 'center' => __( 'Center', 'gwt_wp' ), 'right' => __( 'Right', 'gwt_wp' ) ) ),
			'content_width' => array( 'Overlay', __( 'Maximum text width (px)', 'gwt_wp' ), 'number', 650, 200, 1000 ),
			'overlay_color' => array( 'Overlay', __( 'Overlay color (hex; blank uses theme)', 'gwt_wp' ), 'color', '' ),
			'opacity' => array( 'Overlay', __( 'Overlay opacity (%)', 'gwt_wp' ), 'number', 75, 0, 100 ),
			'text_color' => array( 'Overlay', __( 'Text color (hex)', 'gwt_wp' ), 'color', '#ffffff' ),
			'hide_description' => array( 'Overlay', __( 'Hide descriptions on mobile', 'gwt_wp' ), 'checkbox', false ),
		);
	}

	/** Normalize untrusted widget, shortcode, and preview input. */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$output = array();
		foreach ( self::schema() as $key => $field ) {
			$value = $input[ $key ] ?? $field[3];
			switch ( $field[2] ) {
				case 'checkbox':
					$output[ $key ] = in_array( $value, array( true, 1, '1', 'true' ), true );
					break;
				case 'number':
					$output[ $key ] = is_scalar( $value ) && is_numeric( $value ) ? max( $field[4], min( $field[5], (int) $value ) ) : $field[3];
					break;
				case 'select':
					$output[ $key ] = is_string( $value ) && isset( $field[4][ $value ] ) ? $value : $field[3];
					break;
				case 'post_type':
					$output[ $key ] = is_string( $value ) && in_array( $value, get_post_types( array( 'public' => true ) ), true ) && 'attachment' !== $value ? $value : 'post';
					break;
				case 'color':
					$output[ $key ] = is_string( $value ) ? ( sanitize_hex_color( $value ) ?: $field[3] ) : $field[3];
					break;
				default:
					$output[ $key ] = sanitize_text_field( $value );
			}
		}
		// The former 50 ms configuration is superseded by the readable hero default.
		if ( isset( $input['interval_ms'] ) && is_scalar( $input['interval_ms'] ) && (int) $input['interval_ms'] < 1000 ) {
			$output['interval_ms'] = 5000;
		}
		$output['slides'] = array();
		// Normalize older gallery configurations to a single-image slideshow.
		$output['desktop'] = 1;
		$output['tablet'] = 1;
		$output['mobile'] = 1;
		$output['reference'] = is_string( $input['reference'] ?? null ) && wp_is_uuid( $input['reference'] ) ? $input['reference'] : '';
		foreach ( array_slice( is_array( $input['slides'] ?? null ) ? $input['slides'] : array(), 0, 30 ) as $slide ) {
			if ( is_array( $slide ) ) {
				$output['slides'][] = self::slide( $slide );
			}
		}
		return $output;
	}

	/** Keep incomplete slides editable, but render only enabled, complete slides. */
	public static function slide( $input ) {
		$slide = array();
		foreach ( array( 'title', 'subtitle', 'button_text', 'secondary_text', 'category', 'badge', 'alt' ) as $key ) {
			$slide[ $key ] = sanitize_text_field( $input[ $key ] ?? '' );
		}
		$slide['description'] = sanitize_textarea_field( $input['description'] ?? '' );
		foreach ( array( 'image', 'mobile_image' ) as $key ) {
			$id = is_scalar( $input[ $key ] ?? null ) ? absint( $input[ $key ] ) : 0;
			$slide[ $key ] = $id && wp_attachment_is_image( $id ) ? $id : 0;
		}
		foreach ( array( 'url', 'secondary_url' ) as $key ) {
			$slide[ $key ] = gwt_wp_sanitize_banner_url( $input[ $key ] ?? '' );
		}
		foreach ( array( 'enabled', 'new_tab', 'secondary_new_tab' ) as $key ) {
			$slide[ $key ] = in_array( $input[ $key ] ?? ( 'enabled' === $key ), array( true, 1, '1', 'true' ), true );
		}
		$positions = array( 'center', 'top', 'bottom', 'left', 'right', 'custom' );
		$slide['image_position'] = in_array( $input['image_position'] ?? '', $positions, true ) ? $input['image_position'] : 'center';
		foreach ( array( 'focal_x', 'focal_y' ) as $key ) {
			$slide[ $key ] = is_scalar( $input[ $key ] ?? null ) && is_numeric( $input[ $key ] ) ? max( 0, min( 100, (float) $input[ $key ] ) ) : 50;
		}
		$date = is_string( $input['date'] ?? null ) ? $input['date'] : '';
		$parsed = preg_match( '/^\d{4}-\d{2}-\d{2}$/D', $date ) ? DateTimeImmutable::createFromFormat( '!Y-m-d', $date ) : false;
		$slide['date'] = $parsed && $parsed->format( 'Y-m-d' ) === $date ? $date : '';
		return $slide;
	}
}
