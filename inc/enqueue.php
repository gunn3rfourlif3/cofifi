<?php
/**
 * Styles and scripts.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end assets.
 */
function cofifi_enqueue_assets() {
	$css = COFIFI_DIR . '/assets/css/theme.css';
	$js  = COFIFI_DIR . '/assets/js/theme.js';

	// Cormorant Garamond — display face. Amulya is self-hosted in theme.css.
	wp_enqueue_style(
		'cofifi-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'cofifi',
		COFIFI_URI . '/assets/css/theme.css',
		array( 'cofifi-fonts' ),
		file_exists( $css ) ? filemtime( $css ) : COFIFI_VERSION
	);

	wp_enqueue_script(
		'cofifi',
		COFIFI_URI . '/assets/js/theme.js',
		array(),
		file_exists( $js ) ? filemtime( $js ) : COFIFI_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'cofifi_enqueue_assets' );

/**
 * Preconnect to the font hosts so the display face lands sooner.
 *
 * @param array  $urls           URLs to print.
 * @param string $relation_type  Relation type.
 * @return array
 */
function cofifi_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'cofifi_resource_hints', 10, 2 );

/**
 * Preload the self-hosted body font — it is used above the fold everywhere.
 */
function cofifi_preload_body_font() {
	$font = COFIFI_URI . '/assets/fonts/Amulya-Variable.woff2';
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( $font )
	);
}
add_action( 'wp_head', 'cofifi_preload_body_font', 1 );

/**
 * Drop the WooCommerce block styles we do not use — the theme styles these.
 */
function cofifi_dequeue_woo_block_css() {
	wp_dequeue_style( 'wc-blocks-style' );
}
add_action( 'wp_enqueue_scripts', 'cofifi_dequeue_woo_block_css', 100 );
