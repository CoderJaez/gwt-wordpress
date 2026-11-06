<?php
/** Shared HTML renderer for widgets, shortcodes, and the editor preview. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

/** Return a complete carousel, or nothing when no complete enabled slides exist. */
function gwt_carousel_render( $input, $preview = false ) {
	$settings = GWT_Carousel_Settings::sanitize( $input );
	$slides = gwt_carousel_slides( $settings );
	if ( ! $slides ) {
		return $preview ? '<p class="gov-carousel-empty">' . esc_html__( 'No carousel slides have been added yet.', 'gwt_wp' ) . '</p>' : '';
	}
	gwt_carousel_enqueue_assets();
	$id = wp_unique_id( 'gov-carousel-' );
	$presets = array( 'small' => 320, 'medium' => 480, 'large' => 600 );
	$height = $presets[ $settings['height'] ] ?? $settings['custom_height'];
	$first_image = wp_get_attachment_image_src( $slides[0]['image'], 'gwt-carousel' );
	$styles = array(
		'--carousel-height:' . $height . 'px',
		'--carousel-container-width:' . $settings['container_width'] . 'px',
		'--carousel-content-width:' . $settings['content_width'] . 'px',
		'--carousel-overlay-opacity:' . ( $settings['opacity'] / 100 ),
		'--carousel-text-color:' . $settings['text_color'],
		'--carousel-image-ratio:' . ( $first_image ? $first_image[1] . '/' . $first_image[2] : '16/7' ),
	);
	if ( $settings['overlay_color'] ) {
		$styles[] = '--carousel-overlay-color:' . $settings['overlay_color'];
	}
	$config = array_intersect_key( $settings, array_flip( array( 'desktop', 'tablet', 'mobile', 'autoplay', 'interval_ms', 'pause_hover', 'pause_hidden', 'stop_interaction', 'loop', 'transition', 'speed' ) ) );
	$config['preview'] = $preview;
	$config['playLabel'] = __( 'Play slideshow', 'gwt_wp' );
	$config['pauseLabel'] = __( 'Pause slideshow', 'gwt_wp' );
	$config['rangeLabel'] = __( 'Slides %1$d–%2$d of %3$d', 'gwt_wp' );
	$config['pageLabel'] = __( 'Show slides starting at %d', 'gwt_wp' );
	$classes = 'gov-carousel gov-carousel--' . $settings['layout'] . ' gov-carousel--height-' . $settings['height'];
	$classes .= $settings['hide_description'] ? ' gov-carousel--hide-description' : '';
	ob_start();
	?>
	<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( implode( ';', $styles ) ); ?>" aria-roledescription="<?php esc_attr_e( 'carousel', 'gwt_wp' ); ?>" aria-label="<?php echo esc_attr( $settings['title'] ?: __( 'Featured announcements', 'gwt_wp' ) ); ?>" data-gov-carousel="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
		<div class="gov-carousel__viewport">
			<div id="<?php echo esc_attr( $id . '-slides' ); ?>" class="gov-carousel__slides">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php
				$positions = array( 'center' => '50% 50%', 'top' => '50% 0%', 'bottom' => '50% 100%', 'left' => '0% 50%', 'right' => '100% 50%' );
				$focal = $positions[ $slide['image_position'] ] ?? $slide['focal_x'] . '% ' . $slide['focal_y'] . '%';
				$alt = $slide['alt'] ?: get_post_meta( $slide['image'], '_wp_attachment_image_alt', true );
				$alt = is_string( $alt ) ? $alt : '';
				$largest_count = max( $settings['desktop'], $settings['tablet'], $settings['mobile'] );
				$metadata = wp_get_attachment_metadata( $slide['image'] );
				$size = $largest_count > 1 || empty( $metadata['sizes']['gwt-carousel'] ) ? 'large' : 'gwt-carousel';
				$sizes = '(max-width: 760px) ' . (int) ceil( 100 / $settings['mobile'] ) . 'vw, (max-width: 1000px) ' . (int) ceil( 100 / $settings['tablet'] ) . 'vw, ' . (int) ceil( 100 / $settings['desktop'] ) . 'vw';
				$attributes = array( 'class' => 'gov-carousel__image', 'alt' => $alt, 'sizes' => $sizes, 'loading' => 0 === $index ? 'eager' : 'lazy', 'decoding' => 'async' );
				if ( 0 === $index && ! $preview ) {
					$attributes['fetchpriority'] = 'high';
				}
				?>
				<article class="gov-carousel__slide" style="<?php echo esc_attr( '--carousel-focal:' . $focal ); ?>" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'gwt_wp' ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d: %3$s', 'gwt_wp' ), $index + 1, count( $slides ), $slide['title'] ) ); ?>" <?php echo $index ? 'hidden' : ''; ?>>
					<picture class="gov-carousel__picture">
					<?php if ( $slide['mobile_image'] ) : $mobile = wp_get_attachment_image_src( $slide['mobile_image'], 'large' ); ?>
						<?php if ( $mobile ) : ?><source media="(max-width: 760px)" srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $slide['mobile_image'], 'large' ) ?: $mobile[0] ); ?>" sizes="<?php echo esc_attr( (int) ceil( 100 / $settings['mobile'] ) . 'vw' ); ?>" width="<?php echo esc_attr( $mobile[1] ); ?>" height="<?php echo esc_attr( $mobile[2] ); ?>"><?php endif; ?>
					<?php endif; ?>
					<?php echo wp_get_attachment_image( $slide['image'], $size, false, $attributes ); ?>
					</picture>
					<?php if ( $settings['overlay'] ) : ?>
					<div class="gov-carousel__overlay" aria-hidden="true"></div>
					<div class="gov-carousel__content gov-carousel__content--<?php echo esc_attr( $settings['position'] ); ?> gov-carousel__content--align-<?php echo esc_attr( $settings['align'] ); ?>">
						<div class="gov-carousel__text">
							<?php if ( $slide['badge'] ) : ?><span class="gov-carousel__badge"><?php echo esc_html( $slide['badge'] ); ?></span><?php endif; ?>
							<?php if ( $slide['category'] || $slide['date'] ) : ?><p class="gov-carousel__meta"><?php echo esc_html( $slide['category'] ); ?> <?php if ( $slide['date'] ) : ?><time datetime="<?php echo esc_attr( $slide['date'] ); ?>"><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( $slide['date'] . ' 12:00:00 UTC' ), new DateTimeZone( 'UTC' ) ) ); ?></time><?php endif; ?></p><?php endif; ?>
							<h2 class="gov-carousel__title"><?php echo esc_html( $slide['title'] ); ?></h2>
							<?php if ( $slide['subtitle'] ) : ?><p class="gov-carousel__subtitle"><?php echo esc_html( $slide['subtitle'] ); ?></p><?php endif; ?>
							<?php if ( $slide['description'] ) : ?><p class="gov-carousel__description"><?php echo esc_html( $slide['description'] ); ?></p><?php endif; ?>
							<div class="gov-carousel__actions">
							<?php foreach ( array( array( 'url', 'button_text', 'new_tab', 'primary' ), array( 'secondary_url', 'secondary_text', 'secondary_new_tab', 'secondary' ) ) as $button ) : ?>
								<?php if ( $slide[ $button[0] ] && $slide[ $button[1] ] ) : ?><a class="gov-carousel__button gov-carousel__button--<?php echo esc_attr( $button[3] ); ?>" href="<?php echo esc_url( $slide[ $button[0] ] ); ?>" <?php echo $slide[ $button[2] ] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $slide[ $button[1] ] ); ?><?php if ( $slide[ $button[2] ] ) : ?><span class="screen-reader-text"><?php esc_html_e( ' (opens in a new tab)', 'gwt_wp' ); ?></span><?php endif; ?></a><?php endif; ?>
							<?php endforeach; ?>
							</div>
						</div>
					</div>
					<?php elseif ( $slide['url'] ) : ?>
					<a class="gov-carousel__image-link" href="<?php echo esc_url( $slide['url'] ); ?>" aria-label="<?php echo esc_attr( $slide['title'] ); ?>" <?php echo $slide['new_tab'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>><span class="screen-reader-text"><?php echo esc_html( $slide['title'] ); ?><?php if ( $slide['new_tab'] ) { esc_html_e( ' (opens in a new tab)', 'gwt_wp' ); } ?></span></a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
			</div>
			<?php if ( $settings['previous'] ) : ?><button class="gov-carousel__arrow gov-carousel__arrow--previous" type="button" data-previous aria-label="<?php esc_attr_e( 'Previous slide', 'gwt_wp' ); ?>" aria-controls="<?php echo esc_attr( $id . '-slides' ); ?>" hidden><span aria-hidden="true">‹</span></button><?php endif; ?>
			<?php if ( $settings['next'] ) : ?><button class="gov-carousel__arrow gov-carousel__arrow--next" type="button" data-next aria-label="<?php esc_attr_e( 'Next slide', 'gwt_wp' ); ?>" aria-controls="<?php echo esc_attr( $id . '-slides' ); ?>" hidden><span aria-hidden="true">›</span></button><?php endif; ?>
		</div>
		<div class="gov-carousel__navigation" data-navigation hidden>
			<?php if ( $settings['dots'] ) : ?><div class="gov-carousel__pagination" data-pagination role="group" aria-label="<?php esc_attr_e( 'Choose slides', 'gwt_wp' ); ?>"></div><?php endif; ?>
			<?php if ( $settings['counter'] ) : ?><span class="gov-carousel__counter" data-counter aria-hidden="true"></span><?php endif; ?>
			<?php if ( $settings['autoplay'] ) : ?><button class="gov-carousel__rotation" type="button" data-rotation><?php esc_html_e( 'Pause slideshow', 'gwt_wp' ); ?></button><?php endif; ?>
		</div>
		<p class="screen-reader-text" data-status aria-live="polite" aria-atomic="true"></p>
	</section>
	<?php
	return ob_get_clean();
}
