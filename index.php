<?php
/** Shared listing template. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" tabindex="-1">
<?php if ( is_front_page() ) { get_template_part( 'template-parts/home', 'intro' ); } ?>
<div class="site-container section">
<?php gwt_wp_widget_group( array( 'panel-top-1', 'panel-top-2', 'panel-top-3', 'panel-top-4' ) ); ?>
<header class="section-heading">
	<?php if ( is_search() ) : ?>
		<p class="eyebrow"><?php esc_html_e( 'Search', 'gwt_wp' ); ?></p><h1><?php printf( esc_html__( 'Results for “%s”', 'gwt_wp' ), esc_html( get_search_query( false ) ) ); ?></h1>
	<?php elseif ( is_archive() ) : ?>
		<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1><?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
	<?php else : ?>
		<p class="eyebrow"><?php esc_html_e( 'Stay informed', 'gwt_wp' ); ?></p><?php echo is_front_page() ? '<h2>' : '<h1>'; ?><?php esc_html_e( 'Latest news & updates', 'gwt_wp' ); ?><?php echo is_front_page() ? '</h2>' : '</h1>'; ?>
	<?php endif; ?>
</header>
<div class="content-layout<?php echo is_active_sidebar( 'left-sidebar' ) || is_active_sidebar( 'right-sidebar' ) ? ' has-sidebar' : ''; ?>">
	<div class="content-column">
	<?php if ( have_posts() ) : ?>
		<div class="post-grid"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content' ); endwhile; ?></div>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : get_template_part( 'template-parts/content', 'none' ); endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>
<?php gwt_wp_widget_group( array( 'panel-bottom-1', 'panel-bottom-2', 'panel-bottom-3', 'panel-bottom-4' ) ); ?>
</div>
</main>
<?php get_footer(); ?>
