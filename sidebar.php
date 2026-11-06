<?php
/** Retain both existing sidebars. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
if ( is_active_sidebar( 'left-sidebar' ) || is_active_sidebar( 'right-sidebar' ) ) : ?>
<aside class="site-sidebar" aria-label="<?php esc_attr_e( 'Related information', 'gwt_wp' ); ?>">
<?php gwt_wp_widget_group( array( 'left-sidebar', 'right-sidebar' ), 'sidebar-widgets' ); ?>
</aside>
<?php endif; ?>
