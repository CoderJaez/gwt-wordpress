<?php
/** Core read-only search endpoint. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
$gwt_search_id = wp_unique_id( 'site-search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $gwt_search_id ); ?>"><?php esc_html_e( 'Search the website', 'gwt_wp' ); ?></label>
	<input id="<?php echo esc_attr( $gwt_search_id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>" placeholder="<?php esc_attr_e( 'Search the website…', 'gwt_wp' ); ?>">
	<button type="submit"><?php esc_html_e( 'Search', 'gwt_wp' ); ?></button>
</form>
