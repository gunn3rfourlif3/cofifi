<?php
/**
 * Template helpers: inline icons and small output helpers.
 *
 * Icons are inline SVG on a 24px grid, stroke-based, currentColor — so they
 * scale and recolour with the text they sit beside. No icon fonts, no emoji.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return an inline SVG icon.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @param string $class Extra class names.
 * @return string
 */
function cofifi_get_icon( $name, $size = 20, $class = '' ) {
	$paths = array(
		'arrow'    => '<path d="M2 12h18M15.5 6.6L21 12l-5.5 5.4" fill="none" stroke="currentColor" stroke-width="1.4"/>',
		'search'   => '<circle cx="10.5" cy="10.5" r="7" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M15.8 15.8L21 21" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
		'bag'      => '<path d="M3.4 7.4h17.2L19.4 21.4H4.6z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M8.2 10V5.8a3.8 3.8 0 0 1 7.6 0V10" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
		'check'    => '<path d="M4 12.6l5 5L20 5.6" fill="none" stroke="currentColor" stroke-width="1.6"/>',
		'chevron'  => '<path d="M5 9l7 7.4L19 9" fill="none" stroke="currentColor" stroke-width="1.4"/>',
		'minus'    => '<path d="M4 12h16" stroke="currentColor" stroke-width="1.4"/>',
		'plus'     => '<path d="M4 12h16M12 4v16" stroke="currentColor" stroke-width="1.4"/>',
		'close'    => '<path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="1.4"/>',
		'menu'     => '<path d="M3 7h18M3 12h18M3 17h18" stroke="currentColor" stroke-width="1.4"/>',
		'clock'    => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M12 7v5.2l3.4 2.4" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
		'external' => '<path d="M13 4h7v7" fill="none" stroke="currentColor" stroke-width="1.4"/><path d="M20 4l-9 9" stroke="currentColor" stroke-width="1.4"/><path d="M18 14v6H4V6h6" fill="none" stroke="currentColor" stroke-width="1.4"/>',
		// Trust marks.
		'origin'   => '<path d="M12 21C6.5 17 4 13 4 9a8 8 0 0 1 16 0c0 4-2.5 8-8 12z" fill="none" stroke="currentColor" stroke-width="1.2"/><path d="M12 21V8M12 12L8.5 9M12 14.5l3.5-3" fill="none" stroke="currentColor" stroke-width="1.2"/>',
		'women'    => '<path d="M4.5 20c0-5 2.5-8 4.5-8.5-3-1.5-4-5-1.5-7.5S15 2.5 16 6c.8 2.8-1 4.8-3 5.5 2.6.9 4 3.5 4 8.5" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>',
		'tested'   => '<path d="M12 2.6l8 3v5.8c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V5.6z" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><path d="M8.4 11.8l2.6 2.6 4.8-5" fill="none" stroke="currentColor" stroke-width="1.4"/>',
		'thc'      => '<path d="M12 2.8S5.5 10 5.5 14.3a6.5 6.5 0 0 0 13 0C18.5 10 12 2.8 12 2.8z" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/><path d="M9 14.6a3 3 0 0 0 3 3" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="icon icon--%1$s %2$s" width="%3$d" height="%3$d" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">%4$s</svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		(int) $size,
		$paths[ $name ]
	);
}

/**
 * Echo an inline SVG icon.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @param string $class Extra class names.
 */
function cofifi_icon( $name, $size = 20, $class = '' ) {
	echo cofifi_get_icon( $name, $size, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted static markup.
}

/**
 * URL for a theme image.
 *
 * @param string $file File name inside assets/img.
 * @return string
 */
function cofifi_img( $file ) {
	return COFIFI_URI . '/assets/img/' . ltrim( $file, '/' );
}

/**
 * The compliance line that must appear wherever CBD products are shown.
 *
 * @return string
 */
function cofifi_compliance_line() {
	return __( 'CBD products are sold as food supplements and are not intended to diagnose, treat, cure or prevent any disease. Contains less than 0.3% THC. Not for use by anyone under 18, pregnant or breastfeeding.', 'cofifi' );
}

/**
 * Render a menu, falling back to a plain list of links so the theme still
 * looks right on a fresh install with no menus assigned.
 *
 * @param string $location Menu location.
 * @param array  $fallback Associative array of label => url.
 */
function cofifi_menu( $location, $fallback = array() ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array(
			'theme_location' => $location,
			'container'      => false,
			'depth'          => 1,
			'fallback_cb'    => false,
		) );
		return;
	}

	if ( empty( $fallback ) ) {
		return;
	}

	echo '<ul>';
	foreach ( $fallback as $label => $url ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}

/**
 * Shop URL, or home if WooCommerce is not active yet.
 *
 * @return string
 */
function cofifi_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'shop' );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/' );
}
