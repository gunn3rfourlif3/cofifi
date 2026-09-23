<?php
/**
 * Theme supports, menus and image sizes.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports.
 */
function cofifi_setup() {
	load_theme_textdomain( 'cofifi', COFIFI_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 400,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );

	register_nav_menus( array(
		'primary'        => __( 'Primary menu', 'cofifi' ),
		'footer-shop'    => __( 'Footer — Shop', 'cofifi' ),
		'footer-learn'   => __( 'Footer — Learn', 'cofifi' ),
		'footer-service' => __( 'Footer — Service', 'cofifi' ),
		'footer-follow'  => __( 'Footer — Follow', 'cofifi' ),
	) );

	// Product imagery is square on cards and 4:5 on the product page.
	add_image_size( 'cofifi-card', 720, 720, true );
	add_image_size( 'cofifi-pdp', 1000, 1250, true );
	add_image_size( 'cofifi-band', 1400, 900, true );
}
add_action( 'after_setup_theme', 'cofifi_setup' );

/**
 * Content width for embeds.
 */
function cofifi_content_width() {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'cofifi_content_width', 0 );

/**
 * Sidebar for the footer newsletter area (optional).
 */
function cofifi_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Footer note', 'cofifi' ),
		'id'            => 'footer-note',
		'description'   => __( 'Small area under the footer columns.', 'cofifi' ),
		'before_widget' => '<div class="footer-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h5>',
		'after_title'   => '</h5>',
	) );
}
add_action( 'widgets_init', 'cofifi_widgets_init' );

/**
 * Theme options that hold the bracketed placeholders until real values exist.
 *
 * Every one of these is a placeholder by design — see the README. Set them in
 * Appearance → Customize, or filter them.
 *
 * @param string $key Option key.
 * @return string
 */
function cofifi_option( $key ) {
	$defaults = array(
		'free_delivery'  => '[R950]',
		'address_line_1' => '[Street address]',
		'address_line_2' => '[City, postal code]',
		'email'          => '[hello@cofifi.co]',
		'utility_1'      => __( 'Roasted by women, the traditional way', 'cofifi' ),
		'utility_2'      => __( 'Cannabis range — strictly 18+', 'cofifi' ),
	);

	$value = get_theme_mod( 'cofifi_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );

	/**
	 * Filter a Cofifi theme option.
	 *
	 * @param string $value Option value.
	 * @param string $key   Option key.
	 */
	return apply_filters( 'cofifi_option', $value, $key );
}

/**
 * Expose the placeholders in the Customizer so they can be replaced without code.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function cofifi_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'cofifi_brand', array(
		'title'       => __( 'Cofifi details', 'cofifi' ),
		'priority'    => 30,
		'description' => __( 'Values shown in square brackets are placeholders and should be replaced before launch.', 'cofifi' ),
	) );

	$fields = array(
		'free_delivery'  => __( 'Free delivery threshold', 'cofifi' ),
		'address_line_1' => __( 'Address line 1', 'cofifi' ),
		'address_line_2' => __( 'Address line 2', 'cofifi' ),
		'email'          => __( 'Contact email', 'cofifi' ),
		'utility_1'      => __( 'Utility bar — message 1', 'cofifi' ),
		'utility_2'      => __( 'Utility bar — message 2', 'cofifi' ),
	);

	foreach ( $fields as $key => $label ) {
		$wp_customize->add_setting( 'cofifi_' . $key, array(
			'default'           => cofifi_option( $key ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( 'cofifi_' . $key, array(
			'label'   => $label,
			'section' => 'cofifi_brand',
			'type'    => 'text',
		) );
	}
}
add_action( 'customize_register', 'cofifi_customize_register' );
