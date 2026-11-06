<?php
/** Accessible site masthead. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Skip to content', 'gwt_wp' ); ?></a>
<div class="government-bar"><div class="site-container"><a href="https://www.gov.ph/">GOVPH</a><span><?php esc_html_e( 'Republic of the Philippines', 'gwt_wp' ); ?></span><?php if ( has_nav_menu( 'topbar_right' ) ) { wp_nav_menu( array( 'theme_location' => 'topbar_right', 'container' => 'nav', 'container_aria_label' => __( 'Government links', 'gwt_wp' ), 'depth' => 1, 'fallback_cb' => false ) ); } ?></div></div>
<header class="site-header">
	<div class="site-container masthead">
		<?php if ( is_active_sidebar( 'header-widget' ) ) : ?>
		<div class="header-widget-area">
			<?php dynamic_sidebar( 'header-widget' ); ?>
		</div>
		<?php else : ?>
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : the_custom_logo(); elseif ( gwt_wp_identity( 'govph_logo' ) ) : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-logo-link"><img class="custom-logo" src="<?php echo esc_url( gwt_wp_identity( 'govph_logo' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="80" height="80"></a>
			<?php else : ?><a class="brand-monogram" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'DILG home', 'gwt_wp' ); ?>">DILG</a><?php endif; ?>
			<div><a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_theme_mod( 'gwt_agency_name', gwt_wp_identity( 'govph_agency_name', get_bloginfo( 'name' ) ) ) ); ?></a><p class="site-description"><?php echo esc_html( get_theme_mod( 'gwt_agency_tagline', gwt_wp_identity( 'govph_agency_tagline', get_bloginfo( 'description' ) ) ) ); ?></p></div>
		</div>
		<?php endif; ?>
		<?php get_search_form(); ?>
	</div>
	<div class="nav-shell"><div class="site-container">
		<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" hidden><?php esc_html_e( 'Menu', 'gwt_wp' ); ?><span aria-hidden="true">☰</span></button>
		<nav id="primary-navigation" aria-label="<?php esc_attr_e( 'Main navigation', 'gwt_wp' ); ?>" data-submenu-label="<?php esc_attr_e( 'Toggle submenu for', 'gwt_wp' ); ?>">
		<?php if ( has_nav_menu( 'topbar_left' ) ) { wp_nav_menu( array( 'theme_location' => 'topbar_left', 'container' => false, 'menu_class' => 'primary-menu', 'fallback_cb' => false ) ); } else { ?>
		<ul class="primary-menu"><li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'gwt_wp' ); ?></a></li><?php wp_list_pages( array( 'title_li' => '', 'depth' => 2 ) ); ?></ul>
		<?php } ?>
		</nav>
	</div></div>
	<?php if ( has_nav_menu( 'aux_nav' ) ) : ?><div class="site-container auxiliary-nav"><?php wp_nav_menu( array( 'theme_location' => 'aux_nav', 'container' => 'nav', 'container_aria_label' => __( 'Quick links', 'gwt_wp' ), 'depth' => 1, 'fallback_cb' => false ) ); ?></div><?php endif; ?>
</header>
