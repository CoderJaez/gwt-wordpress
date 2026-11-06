<?php
/** Media Library, repeatable slides, and a sandboxed server-rendered preview. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

class GWT_Carousel_Admin {
	/** Output a consistent, escaped field. Names also work in the Legacy Widget block. */
	private static function field( $name, $id, $label, $type, $value, $options = array(), $min = null, $max = null ) {
		echo '<div class="gwt-carousel-field">';
		if ( 'checkbox' === $type ) {
			echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="0">';
			echo '<label for="' . esc_attr( $id ) . '"><input id="' . esc_attr( $id ) . '" type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . checked( (bool) $value, true, false ) . '> ' . esc_html( $label ) . '</label>';
		} else {
			echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
			if ( 'select' === $type ) {
				echo '<select class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
				foreach ( $options as $key => $text ) {
					echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $text ) . '</option>';
				}
				echo '</select>';
			} elseif ( 'textarea' === $type ) {
				echo '<textarea class="widefat" rows="3" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $value ) . '</textarea>';
			} else {
				echo '<input class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $value ) . '"';
				if ( null !== $min ) {
					echo ' min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '"';
				}
				echo '>';
			}
		}
		echo '</div>';
	}

	/** Render a reorderable manual slide; incomplete slides remain visible to editors. */
	public static function slide( $widget, $index, $slide ) {
		$prefix = $widget->get_field_name( 'slides' ) . '[' . $index . ']';
		$id = $widget->get_field_id( 'slide-' . $index );
		echo '<details class="gwt-carousel-slide-editor" open><summary><span data-slide-heading>' . esc_html( $slide['title'] ?: __( 'New slide', 'gwt_wp' ) ) . '</span></summary>';
		self::field( $prefix . '[enabled]', $id . '-enabled', __( 'Enabled', 'gwt_wp' ), 'checkbox', $slide['enabled'] );
		foreach ( array( 'image' => __( 'Slide image (required)', 'gwt_wp' ), 'mobile_image' => __( 'Mobile image (optional)', 'gwt_wp' ) ) as $key => $label ) {
			echo '<div class="gwt-carousel-media"><p>' . esc_html( $label ) . '</p>';
			$url = $slide[ $key ] ? wp_get_attachment_image_url( $slide[ $key ], 'medium' ) : '';
			if ( $url ) {
				echo '<img src="' . esc_url( $url ) . '" alt="" class="gwt-carousel-media-preview">';
			} else {
				echo '<img alt="" class="gwt-carousel-media-preview" hidden>';
			}
			echo '<input type="hidden" name="' . esc_attr( $prefix . '[' . $key . ']' ) . '" value="' . esc_attr( $slide[ $key ] ) . '" data-media-id>';
			echo '<button class="button" type="button" data-choose-image>' . esc_html__( 'Choose image', 'gwt_wp' ) . '</button> <button class="button-link" type="button" data-remove-image>' . esc_html__( 'Remove image', 'gwt_wp' ) . '</button></div>';
		}
		$fields = array(
			'title' => array( __( 'Slide title (required)', 'gwt_wp' ), 'text' ),
			'alt' => array( __( 'Alt text (blank uses Media Library alt text)', 'gwt_wp' ), 'text' ),
			'subtitle' => array( __( 'Subtitle', 'gwt_wp' ), 'text' ),
			'description' => array( __( 'Short description', 'gwt_wp' ), 'textarea' ),
			'button_text' => array( __( 'Primary button text', 'gwt_wp' ), 'text' ),
			'url' => array( __( 'Primary URL (HTTP(S) or /path/)', 'gwt_wp' ), 'text' ),
			'new_tab' => array( __( 'Primary link opens in a new tab', 'gwt_wp' ), 'checkbox' ),
			'secondary_text' => array( __( 'Secondary button text', 'gwt_wp' ), 'text' ),
			'secondary_url' => array( __( 'Secondary URL', 'gwt_wp' ), 'text' ),
			'secondary_new_tab' => array( __( 'Secondary link opens in a new tab', 'gwt_wp' ), 'checkbox' ),
			'category' => array( __( 'Category / tag label', 'gwt_wp' ), 'text' ),
			'date' => array( __( 'Date', 'gwt_wp' ), 'date' ),
			'badge' => array( __( 'Badge', 'gwt_wp' ), 'text' ),
		);
		foreach ( $fields as $key => $field ) {
			self::field( $prefix . '[' . $key . ']', $id . '-' . $key, $field[0], $field[1], $slide[ $key ] );
		}
		self::field( $prefix . '[image_position]', $id . '-position', __( 'Image position', 'gwt_wp' ), 'select', $slide['image_position'], array( 'center' => __( 'Center', 'gwt_wp' ), 'top' => __( 'Top', 'gwt_wp' ), 'bottom' => __( 'Bottom', 'gwt_wp' ), 'left' => __( 'Left', 'gwt_wp' ), 'right' => __( 'Right', 'gwt_wp' ), 'custom' => __( 'Custom focal point', 'gwt_wp' ) ) );
		self::field( $prefix . '[focal_x]', $id . '-x', __( 'Focal point X (%)', 'gwt_wp' ), 'number', $slide['focal_x'], array(), 0, 100 );
		self::field( $prefix . '[focal_y]', $id . '-y', __( 'Focal point Y (%)', 'gwt_wp' ), 'number', $slide['focal_y'], array(), 0, 100 );
		echo '<div class="gwt-carousel-slide-actions">';
		foreach ( array( 'up' => __( 'Move up', 'gwt_wp' ), 'down' => __( 'Move down', 'gwt_wp' ), 'duplicate' => __( 'Duplicate slide', 'gwt_wp' ), 'remove' => __( 'Remove slide', 'gwt_wp' ) ) as $action => $label ) {
			echo '<button type="button" class="button" data-slide-action="' . esc_attr( $action ) . '">' . esc_html( $label ) . '</button> ';
		}
		echo '</div></details>';
	}

	/** Render grouped settings and the repeatable manual editor. */
	public static function form( $widget, $instance ) {
		$settings = GWT_Carousel_Settings::sanitize( $instance ?: array( 'source' => 'manual' ) );
		$reference = $settings['reference'] ?: wp_generate_uuid4();
		echo '<div class="gwt-carousel-editor" data-field-prefix="' . esc_attr( $widget->get_field_name( '' ) ) . '">';
		echo '<input type="hidden" name="' . esc_attr( $widget->get_field_name( 'reference' ) ) . '" value="' . esc_attr( $reference ) . '">';
		echo '<p>' . esc_html__( 'Create manual slides, reuse Banner images, or choose dynamic public content. Save the widget to publish your changes.', 'gwt_wp' ) . '</p>';
		$group = '';
		$group_labels = array( 'Content' => __( 'Content', 'gwt_wp' ), 'Layout' => __( 'Layout', 'gwt_wp' ), 'Navigation' => __( 'Navigation', 'gwt_wp' ), 'Playback' => __( 'Playback', 'gwt_wp' ), 'Overlay' => __( 'Overlay', 'gwt_wp' ) );
		foreach ( GWT_Carousel_Settings::schema() as $key => $definition ) {
			if ( $group !== $definition[0] ) {
				if ( $group ) {
					echo '</div></details>';
				}
				$group = $definition[0];
				echo '<details class="gwt-carousel-settings" ' . ( 'Content' === $group ? 'open' : '' ) . '><summary>' . esc_html( $group_labels[ $group ] ) . '</summary><div class="gwt-carousel-settings-grid">';
			}
			$type = $definition[2];
			$options = 'select' === $type ? $definition[4] : array();
			if ( 'post_type' === $type ) {
				$type = 'select';
				foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) {
					if ( 'attachment' !== $post_type->name ) {
						$options[ $post_type->name ] = $post_type->labels->singular_name;
					}
				}
			}
			self::field( $widget->get_field_name( $key ), $widget->get_field_id( $key ), $definition[1], 'color' === $type ? 'text' : $type, $settings[ $key ], $options, 'number' === $type ? $definition[4] : null, 'number' === $type ? $definition[5] : null );
		}
		echo '</div></details><div class="gwt-carousel-manual"><h4>' . esc_html__( 'Manual slides', 'gwt_wp' ) . '</h4><p data-empty-state>' . esc_html__( 'No carousel slides have been added yet.', 'gwt_wp' ) . '</p><div data-slide-list>';
		foreach ( $settings['slides'] as $index => $slide ) {
			self::slide( $widget, $index, $slide );
		}
		echo '</div><template data-slide-template>';
		self::slide( $widget, '__INDEX__', GWT_Carousel_Settings::slide( array() ) );
		echo '</template><button type="button" class="button" data-add-slide>' . esc_html__( 'Add Slide', 'gwt_wp' ) . '</button><p>' . esc_html__( 'Up to 30 manual slides. Disabled or incomplete slides are saved but not displayed.', 'gwt_wp' ) . '</p></div>';
		echo '<div class="gwt-carousel-preview-controls"><button type="button" class="button" data-preview-carousel>' . esc_html__( 'Update preview', 'gwt_wp' ) . '</button> <label><input type="checkbox" data-live-preview> ' . esc_html__( 'Live preview', 'gwt_wp' ) . '</label></div><p role="status" data-preview-status></p>';
		echo '<iframe class="gwt-carousel-preview" title="' . esc_attr__( 'Carousel preview', 'gwt_wp' ) . '" sandbox="allow-scripts" hidden></iframe>';
		echo '<p>' . esc_html__( 'Save this widget, then add it to a page using a Shortcode block:', 'gwt_wp' ) . ' <code>[gwt_carousel ref="' . esc_html( $reference ) . '"]</code></p></div>';
	}

	/** Preview requests use the same sanitizer and renderer as saved public output. */
	public static function preview() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You cannot edit theme widgets.', 'gwt_wp' ) ), 403 );
		}
		check_ajax_referer( 'gwt_carousel_preview', 'nonce' );
		$data = isset( $_POST['settings'] ) && is_string( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : null;
		if ( ! is_array( $data ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid carousel settings.', 'gwt_wp' ) ), 400 );
		}
		$html = gwt_carousel_render( $data, true );
		$base = get_template_directory_uri();
		$version = wp_get_theme( get_template() )->get( 'Version' );
		$document = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="' . esc_url( add_query_arg( 'ver', $version, $base . '/theme.css' ) ) . '"><link rel="stylesheet" href="' . esc_url( get_stylesheet_uri() ) . '"><link rel="stylesheet" href="' . esc_url( add_query_arg( 'ver', $version, $base . '/css/carousel.css' ) ) . '"><style>body{margin:0}*{box-sizing:border-box}</style></head><body>' . $html . '<script src="' . esc_url( add_query_arg( 'ver', $version, $base . '/js/carousel.js' ) ) . '"></script></body></html>';
		wp_send_json_success( array( 'html' => $document ) );
	}
}
