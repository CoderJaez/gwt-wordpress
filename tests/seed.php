<?php
/** Disposable visual-test content. */
defined( 'GWT_TEST_RUN' ) && defined( 'ABSPATH' ) || exit;
wp_set_current_user( 1 );
update_option( 'blogname', 'Department of the Interior and Local Government' );
update_option( 'blogdescription', 'Matino, Mahusay at Maaasahan' );
update_option( 'posts_per_page', 6 );
update_option( 'default_comment_status', 'open' );
set_theme_mod( 'gwt_services_url', home_url( '/?p=2' ) );
set_theme_mod( 'gwt_contact_url', home_url( '/?p=2' ) );
set_theme_mod( 'gwt_transparency_url', home_url( '/?p=2' ) );
$menu = wp_create_nav_menu( 'Primary' );
foreach ( array( 'Home', 'About us', 'Programs & services', 'Resources', 'Contact' ) as $label ) {
	$item = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $label, 'menu-item-url' => home_url( 'Home' === $label ? '/' : '/?page_id=2' ), 'menu-item-status' => 'publish' ) );
	if ( 'About us' === $label ) {
		wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Mandate and vision', 'menu-item-url' => home_url( '/?p=2' ), 'menu-item-parent-id' => $item, 'menu-item-status' => 'publish' ) );
	}
}
set_theme_mod( 'nav_menu_locations', array( 'topbar_left' => $menu ) );
foreach ( array( 'Building stronger, more resilient communities', 'Supporting excellence in local governance', 'Bringing public services closer to every Filipino', 'Working together for safer communities', 'Accountability starts with accessible information', 'A shared commitment to good governance', 'Local partnerships for meaningful progress' ) as $title ) {
	wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => '<!-- wp:paragraph --><p>Updates on our work with local governments and communities. Find information on programs, public services, and opportunities to participate in local governance.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Working with our communities</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Accessible information helps people take part in decisions that shape their communities.</p><!-- /wp:paragraph -->', 'comment_status' => 'open' ) );
}
$protected = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Protected test document', 'post_password' => 'test-password', 'post_content' => 'PRIVATE_TEST_CONTENT_DO_NOT_EXPOSE' ) );
echo 'Protected post ID: ' . $protected . "\n";
