<?php
/** Native comments respect site discussion settings. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
if ( post_password_required() ) { return; }
?>
<section id="comments" class="comments-area">
<?php if ( have_comments() ) : ?>
<h2><?php esc_html_e( 'Comments', 'gwt_wp' ); ?></h2>
<ol class="comment-list"><?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true, 'avatar_size' => 48 ) ); ?></ol>
<?php the_comments_pagination(); ?>
<?php endif; ?>
<?php if ( ! comments_open() && get_comments_number() ) : ?><p><?php esc_html_e( 'Comments are closed.', 'gwt_wp' ); ?></p><?php endif; ?>
<?php comment_form(); ?>
</section>
