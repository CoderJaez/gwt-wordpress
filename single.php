<?php
/** Singular content. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-container section" tabindex="-1">
<?php gwt_wp_widget_group( array( 'panel-top-1', 'panel-top-2', 'panel-top-3', 'panel-top-4' ) ); ?>
<div class="content-layout<?php echo is_active_sidebar( 'left-sidebar' ) || is_active_sidebar( 'right-sidebar' ) ? ' has-sidebar' : ''; ?>">
<div class="content-column">
<?php while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?>>
		<header class="entry-header"><p class="eyebrow"><?php echo esc_html( get_post_type() === 'page' ? __( 'Information', 'gwt_wp' ) : __( 'News & updates', 'gwt_wp' ) ); ?></p><h1><?php echo esc_html( get_the_title() ); ?></h1>
		<?php if ( 'post' === get_post_type() ) : ?><p class="entry-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> · <?php echo esc_html( get_the_author() ); ?></p><?php endif; ?>
		</header>
		<?php if ( ! post_password_required() && has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'class' => 'featured-image' ) ); } ?>
		<div class="entry-content">
		<?php
		if ( is_attachment() && wp_attachment_is_image() && ! post_password_required() ) {
			echo wp_get_attachment_image( get_the_ID(), 'large' );
		}
		the_content();
		wp_link_pages( array( 'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page sections', 'gwt_wp' ) . '">', 'after' => '</nav>' ) );
		?>
		</div>
		<?php if ( 'post' === get_post_type() && ! post_password_required() ) : ?><footer class="entry-footer"><?php the_tags( '<div class="tag-links">', ' ', '</div>' ); ?></footer><?php endif; ?>
	</article>
	<?php if ( 'post' === get_post_type() ) { the_post_navigation(); } ?>
	<?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>
<?php endwhile; ?>
</div>
<?php get_sidebar(); ?>
</div>
<?php gwt_wp_widget_group( array( 'panel-bottom-1', 'panel-bottom-2', 'panel-bottom-3', 'panel-bottom-4' ) ); ?>
</main>
<?php get_footer(); ?>
