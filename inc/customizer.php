<?php
/** Settings protected by core Customizer capability and nonce checks. @package gwt_wp */
defined( 'ABSPATH' ) || exit;

function gwt_wp_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'gwt_home', array( 'title' => __( 'DILG homepage', 'gwt_wp' ), 'priority' => 35 ) );
	$fields = array(
		'gwt_agency_name' => array( __( 'Agency name', 'gwt_wp' ), gwt_wp_identity( 'govph_agency_name', get_bloginfo( 'name' ) ), 'sanitize_text_field', 'text' ),
		'gwt_agency_tagline' => array( __( 'Agency tagline', 'gwt_wp' ), gwt_wp_identity( 'govph_agency_tagline', get_bloginfo( 'description' ) ), 'sanitize_text_field', 'text' ),
		'gwt_intro_title' => array( __( 'Headline', 'gwt_wp' ), __( 'Serving communities. Strengthening local governance.', 'gwt_wp' ), 'sanitize_text_field', 'text' ),
		'gwt_intro_text' => array( __( 'Introduction', 'gwt_wp' ), __( 'Working together for peaceful, safe, and resilient communities, with accountable and responsive local government.', 'gwt_wp' ), 'sanitize_textarea_field', 'textarea' ),
		'gwt_services_url' => array( __( 'Services page URL', 'gwt_wp' ), '', 'esc_url_raw', 'url' ),
		'gwt_transparency_url' => array( __( 'Transparency page URL', 'gwt_wp' ), '', 'esc_url_raw', 'url' ),
		'gwt_contact_url' => array( __( 'Contact page URL', 'gwt_wp' ), '', 'esc_url_raw', 'url' ),
	);
	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array( 'default' => $field[1], 'sanitize_callback' => $field[2], 'capability' => 'edit_theme_options' ) );
		$wp_customize->add_control( $id, array( 'label' => $field[0], 'type' => $field[3], 'section' => 'gwt_home' ) );
	}
}
add_action( 'customize_register', 'gwt_wp_customize_register' );
