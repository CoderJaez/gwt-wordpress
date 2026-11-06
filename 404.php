<?php
/** Missing content. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-container section empty-state" tabindex="-1">
<p class="eyebrow">404</p><h1><?php esc_html_e( 'This page could not be found.', 'gwt_wp' ); ?></h1>
<p><?php esc_html_e( 'Try a search, or return to the homepage to find the information you need.', 'gwt_wp' ); ?></p>
<?php get_search_form(); ?>
<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return to home', 'gwt_wp' ); ?></a>
</main>
<?php get_footer(); ?>
