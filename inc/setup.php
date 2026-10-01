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

/*
 * No widget areas, deliberately.
 *
 * There used to be a "Footer note" sidebar here. Nothing in the design used
 * it and it had no CSS, but WordPress auto-populates the first registered
 * sidebar on a fresh install — so every new site came up with Archives,
 * Categories and Recent Posts dumped unstyled into the footer, breaking the
 * layout.
 *
 * The footer is a designed, fixed composition: brand block, four menus, legal
 * line. If something new needs to go in it, add it to footer.php where it can
 * be laid out properly, rather than leaving an open slot for WordPress to
 * fill with defaults.
 */

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
		'email'          => 'hello@cofifi.com',
		/*
		 * The registered entity, as it reads on the Google Workspace billing
		 * record. Deliberately NOT the brand styling: "COFiFi" is how the
		 * brand is set, "CoFiFi Roastery" is who the money is owed to. Don't
		 * "fix" the capitals here — change it only against the registration.
		 */
		'legal_name'     => 'CoFiFi Roastery',
		'maintenance_line' => __( 'Opening [soon].', 'cofifi' ),
		'utility_1'      => __( 'Roasted by women, the traditional way', 'cofifi' ),
		'utility_2'      => __( 'Cannabis range — strictly 18+', 'cofifi' ),
	);

	$value = get_theme_mod( 'cofifi_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );

	/**
	 * Filter a COFiFi theme option.
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
		'title'       => __( 'COFiFi details', 'cofifi' ),
		'priority'    => 30,
		'description' => __( 'Values shown in square brackets are placeholders and should be replaced before launch.', 'cofifi' ),
	) );

	$fields = array(
		'free_delivery'  => __( 'Free delivery threshold', 'cofifi' ),
		'address_line_1' => __( 'Address line 1', 'cofifi' ),
		'address_line_2' => __( 'Address line 2', 'cofifi' ),
		'email'          => __( 'Contact email', 'cofifi' ),
		'maintenance_line' => __( 'Launch line (shown on the holding page)', 'cofifi' ),
		'legal_name'     => __( 'Registered name (footer copyright)', 'cofifi' ),
		'utility_1'      => __( 'Utility bar — message 1', 'cofifi' ),
		'utility_2'      => __( 'Utility bar — message 2', 'cofifi' ),
	);

	$wp_customize->add_setting( 'cofifi_maintenance', array(
		'default'           => false,
		'sanitize_callback' => 'wp_validate_boolean',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'cofifi_maintenance', array(
		'label'       => __( 'Close the shop (maintenance mode)', 'cofifi' ),
		'description' => sprintf(
			/* translators: %s: the preview URL. */
			__( 'Visitors get a holding page and a 503. You and anyone holding this link still see the shop: %s', 'cofifi' ),
			'<br><code style="word-break:break-all">' . esc_url( cofifi_preview_url() ) . '</code>'
		),
		'section'     => 'cofifi_brand',
		'type'        => 'checkbox',
	) );

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

/**
 * Fall back to the theme's icon files when no site icon is set.
 *
 * inc/bootstrap.php seeds a real site icon, and once it is set WordPress emits
 * everything below for us — so this prints nothing on a normal install. It
 * covers the gaps: a fresh database before bootstrap has run, or a shop that
 * clears the icon in Settings → General and leaves it empty.
 *
 * Deliberately not an unconditional set of <link> tags. Two competing icon
 * declarations in one <head> is how you end up with the old mark cached in a
 * tab for a week.
 */
function cofifi_site_icon_fallback() {
	if ( has_site_icon() ) {
		return;
	}

	printf(
		'<link rel="icon" href="%s" sizes="any">' . "\n",
		esc_url( cofifi_img( 'favicon.ico' ) )
	);
	printf(
		'<link rel="icon" href="%s" type="image/png" sizes="512x512">' . "\n",
		esc_url( cofifi_img( 'site-icon.png' ) )
	);
	printf(
		'<link rel="apple-touch-icon" href="%s">' . "\n",
		esc_url( cofifi_img( 'site-icon.png' ) )
	);
}
add_action( 'wp_head', 'cofifi_site_icon_fallback', 100 );
