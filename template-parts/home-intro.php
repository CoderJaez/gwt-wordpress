<?php
/** Full-width homepage Carousel replaces the former introduction. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
?>
<h1 class="screen-reader-text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
<?php
ob_start();
if ( is_active_sidebar( 'header-carousel' ) ) {
	dynamic_sidebar( 'header-carousel' );
} else {
	the_widget( 'GWT_Banner_Carousel', array(), array( 'before_widget' => '<div class="homepage-carousel-widget">', 'after_widget' => '</div>' ) );
}
$carousel_output = trim( ob_get_clean() );
if ( $carousel_output ) {
	echo '<div class="homepage-carousel-area">' . $carousel_output . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered WordPress widget markup.
}
?>
<div class="site-container"><?php
gwt_wp_widget_group( array( 'banner-section-1', 'banner-section-2', 'ear-content-1', 'ear-content-2' ) );
?></div>
