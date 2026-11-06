<?php
/** Government and agency footer. @package gwt_wp */
defined( 'ABSPATH' ) || exit;
?>
<footer id="site-footer" class="site-footer">
	<div class="site-container">
		<?php gwt_wp_widget_group( array( 'footer-1', 'footer-2', 'footer-3', 'footer-4' ) ); ?>
		<div class="footer-grid">
			<div><img class="government-seal" src="<?php echo esc_url( get_template_directory_uri() . '/images/govph-seal-mono-footer.png' ); ?>" width="100" height="100" alt="<?php esc_attr_e( 'Republic of the Philippines seal', 'gwt_wp' ); ?>"><h2><?php esc_html_e( 'Republic of the Philippines', 'gwt_wp' ); ?></h2><p><?php esc_html_e( 'All content is in the public domain unless otherwise stated.', 'gwt_wp' ); ?></p></div>
			<div><h2><?php esc_html_e( 'About GOVPH', 'gwt_wp' ); ?></h2><p><?php esc_html_e( 'Learn more about the Philippine government, its structure, how government works and the people behind it.', 'gwt_wp' ); ?></p><a href="https://www.gov.ph/">GOV.PH</a><br><a href="https://www.officialgazette.gov.ph/"><?php esc_html_e( 'Official Gazette', 'gwt_wp' ); ?></a></div>
			<div><h2><?php esc_html_e( 'Government links', 'gwt_wp' ); ?></h2><ul><li><a href="https://president.gov.ph/"><?php esc_html_e( 'Office of the President', 'gwt_wp' ); ?></a></li><li><a href="https://www.senate.gov.ph/"><?php esc_html_e( 'Senate of the Philippines', 'gwt_wp' ); ?></a></li><li><a href="https://www.congress.gov.ph/"><?php esc_html_e( 'House of Representatives', 'gwt_wp' ); ?></a></li><li><a href="https://sc.judiciary.gov.ph/"><?php esc_html_e( 'Supreme Court', 'gwt_wp' ); ?></a></li></ul></div>
		</div>
		<div class="footer-bottom"><span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span><?php the_privacy_policy_link(); ?><a href="#main-content"><?php esc_html_e( 'Back to content ↑', 'gwt_wp' ); ?></a></div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
