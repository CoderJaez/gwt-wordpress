<?php
/** Search and archive card. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?><a class="card-image" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large', array( 'alt' => '' ) ); ?></a><?php endif; ?>
<div class="card-body">
<p class="entry-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ?: __( 'Untitled', 'gwt_wp' ) ); ?></a></h2>
<div class="entry-summary"><?php the_excerpt(); ?></div>
<a class="read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'gwt_wp' ); ?><span class="screen-reader-text">: <?php echo esc_html( get_the_title() ); ?></span> <span aria-hidden="true">↗</span></a>
</div>
</article>
