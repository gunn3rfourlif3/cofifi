<?php
/**
 * COFiFi site provisioning.
 *
 * Runs inside a live WordPress with WooCommerce active:
 *
 *     wp eval-file setup/provision.php
 *
 * Everything here is IDEMPOTENT — run it as many times as you like. It creates
 * what is missing and leaves what already exists alone. It never deletes.
 *
 * Options (pass after the file name):
 *   --skip-products   Do not seed the three sample products.
 *   --skip-menus      Do not create or assign menus.
 *
 * @package Cofifi
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "This script must be run through WP-CLI: wp eval-file setup/provision.php\n";
	exit( 1 );
}

$argv          = isset( $args ) && is_array( $args ) ? $args : array();
$skip_products = in_array( '--skip-products', $argv, true );
$skip_menus    = in_array( '--skip-menus', $argv, true );

$has_woo = class_exists( 'WooCommerce' );

WP_CLI::line( '' );
WP_CLI::line( '  COFiFi provisioning' );
WP_CLI::line( '  ------------------' );

/* -------------------------------------------------------------------------
 * 1. Site options
 * ---------------------------------------------------------------------- */

update_option( 'blogname', 'COFiFi' );
update_option( 'blogdescription', 'Afro Coffee & Treats Co.' );
update_option( 'timezone_string', 'Africa/Johannesburg' );
update_option( 'date_format', 'j F Y' );
update_option( 'start_of_week', 1 );

// Pretty permalinks. Flushed at the end.
if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
	update_option( 'permalink_structure', '/%postname%/' );
	WP_CLI::log( '  permalinks     set to /%postname%/' );
}

// Discourage indexing on anything that is not a production hostname.
$host  = wp_parse_url( home_url(), PHP_URL_HOST );
$local = in_array( $host, array( 'localhost', '127.0.0.1' ), true )
	|| ( is_string( $host ) && ( str_ends_with( $host, '.local' ) || str_ends_with( $host, '.test' ) ) );

update_option( 'blog_public', $local ? 0 : 1 );
if ( $local ) {
	WP_CLI::log( '  indexing       discouraged (local host detected)' );
}

/* -------------------------------------------------------------------------
 * 2. Theme
 * ---------------------------------------------------------------------- */

if ( 'cofifi' !== get_option( 'stylesheet' ) ) {
	if ( wp_get_theme( 'cofifi' )->exists() ) {
		switch_theme( 'cofifi' );
		WP_CLI::log( '  theme          activated cofifi' );
	} else {
		WP_CLI::warning( 'The cofifi theme was not found in wp-content/themes. Skipping theme activation.' );
	}
}

/*
 * Placeholder values. Every one of these is a stand-in for a fact nobody has
 * confirmed — they stay in square brackets on purpose so they are impossible to
 * miss on the page. Replace them in Appearance → Customize → COFiFi details.
 */
$mods = array(
	'cofifi_free_delivery'  => '[R950]',
	'cofifi_address_line_1' => '[Street address]',
	'cofifi_address_line_2' => '[City, postal code]',
	'cofifi_email'          => 'hello@cofifi.com',
	'cofifi_legal_name'     => 'CoFiFi Roastery',
	'cofifi_utility_1'      => 'Roasted by women, the traditional way',
	'cofifi_utility_2'      => 'Cannabis range — strictly 18+',
);

foreach ( $mods as $key => $value ) {
	if ( false === get_theme_mod( $key, false ) ) {
		set_theme_mod( $key, $value );
	}
}
WP_CLI::log( '  placeholders   seeded (Customize → COFiFi details)' );

/* -------------------------------------------------------------------------
 * 3. WooCommerce settings
 * ---------------------------------------------------------------------- */

if ( $has_woo ) {
	// Product URLs as /shop/<slug>, matching the reference site.
	$permalinks                 = (array) get_option( 'woocommerce_permalinks', array() );
	$permalinks['product_base'] = '/shop';
	$permalinks['category_base']= 'product-category';
	$permalinks['tag_base']     = 'product-tag';
	$permalinks['attribute_base'] = '';
	update_option( 'woocommerce_permalinks', $permalinks );

	update_option( 'woocommerce_currency', 'ZAR' );
	update_option( 'woocommerce_currency_pos', 'left' );
	update_option( 'woocommerce_price_thousand_sep', ' ' );
	update_option( 'woocommerce_price_decimal_sep', '.' );
	update_option( 'woocommerce_price_num_decimals', 2 );
	update_option( 'woocommerce_default_country', 'ZA' );
	update_option( 'woocommerce_weight_unit', 'kg' );
	update_option( 'woocommerce_dimension_unit', 'cm' );
	update_option( 'woocommerce_manage_stock', 'yes' );
	update_option( 'woocommerce_enable_reviews', 'yes' );

	// Don't hijack the admin with the setup wizard on first login.
	update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true ) );
	delete_transient( '_wc_activation_redirect' );

	/*
	 * Recent WooCommerce ships with "coming soon" mode ON. Left alone it hides
	 * the entire store behind a launch page — the site looks broken and the
	 * reason is nowhere near the symptom. Turn it off; a local dev install is
	 * already noindexed by the check above.
	 */
	update_option( 'woocommerce_coming_soon', 'no' );
	update_option( 'woocommerce_store_pages_only', 'no' );
	WP_CLI::log( '  coming soon    off (store is live)' );

	WP_CLI::log( '  woocommerce    ZAR, ZA, /shop/<slug> product URLs' );

	// Categories come from inc/catalogue.php — the slugs gate the CBD and THC
	// notices, so the theme and the provisioner must agree on them.
	cofifi_seed_categories();
	WP_CLI::log( '  categories     ' . implode( ', ', array_keys( cofifi_product_categories() ) ) );
} else {
	WP_CLI::warning( 'WooCommerce is not active — skipping shop settings, categories and products.' );
}

/* -------------------------------------------------------------------------
 * 4. Pages
 * ---------------------------------------------------------------------- */

$pages = array(
	'home'             => 'Home',
	'our-story'        => 'Our story',
	'the-ceremony'     => 'The ceremony',
	'lab-certificates' => 'Lab certificates',
	'brewing'          => 'Brewing',
	'delivery'         => 'Delivery',
	'returns'          => 'Returns',
	'contact'          => 'Contact',
	'wholesale'        => 'Wholesale',
	'gallery'          => 'Gallery',
	'journal'          => 'Journal',
);

$page_ids = array();

foreach ( $pages as $slug => $title ) {
	$existing = get_page_by_path( $slug );

	if ( $existing ) {
		$page_ids[ $slug ] = $existing->ID;
		continue;
	}

	$page_ids[ $slug ] = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => '',
	) );
}

if ( ! empty( $page_ids['gallery'] ) ) {
	update_post_meta( $page_ids['gallery'], '_wp_page_template', 'page-gallery.php' );
}

WP_CLI::log( '  pages          ' . count( $pages ) . ' checked/created' );

// Static front page so the Journal can list posts. front-page.php still wins
// for the front page, whichever way this is set.
if ( ! empty( $page_ids['home'] ) && ! empty( $page_ids['journal'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_ids['home'] );
	update_option( 'page_for_posts', $page_ids['journal'] );
}

/* -------------------------------------------------------------------------
 * 5. Menus
 * ---------------------------------------------------------------------- */

if ( ! $skip_menus ) {
	$shop_url = $has_woo ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

	// Category archives, so a footer link lands somewhere real. Falls back to
	// the shop if a term is missing.
	$cat_url = function ( $slug ) use ( $shop_url ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) {
			return $shop_url;
		}
		$url = get_term_link( $term );
		return is_wp_error( $url ) ? $shop_url : $url;
	};

	$menus = array(
		'primary' => array(
			'name'  => 'Primary',
			'items' => array(
				array( 'Coffee', $shop_url ),
				array( 'CBD Oil', $cat_url( 'cbd-oil' ) ),
				array( 'Rasta Roast', $cat_url( 'rasta-roast' ) ),
				array( 'Our Story', get_permalink( $page_ids['our-story'] ) ),
				array( 'Gallery', get_permalink( $page_ids['gallery'] ) ),
				array( 'Wholesale', get_permalink( $page_ids['wholesale'] ) ),
			),
		),
		'footer-shop' => array(
			'name'  => 'Footer — Shop',
			'items' => array(
				array( 'COFiFi Coffee', $cat_url( 'coffee' ) ),
				array( 'CBD + Coffee', $cat_url( 'cbd-plus' ) ),
				array( 'Rasta Roast', $cat_url( 'rasta-roast' ) ),
				array( 'CBD Oil — Focus', $cat_url( 'cbd-oil' ) ),
			),
		),
		'footer-learn' => array(
			'name'  => 'Footer — Learn',
			'items' => array(
				array( 'Our story', get_permalink( $page_ids['our-story'] ) ),
				array( 'The ceremony', get_permalink( $page_ids['the-ceremony'] ) ),
				array( 'Lab certificates', get_permalink( $page_ids['lab-certificates'] ) ),
				array( 'Brewing', get_permalink( $page_ids['brewing'] ) ),
			),
		),
		'footer-service' => array(
			'name'  => 'Footer — Service',
			'items' => array(
				array( 'Delivery', get_permalink( $page_ids['delivery'] ) ),
				array( 'Returns', get_permalink( $page_ids['returns'] ) ),
				array( 'Manage subscription', $has_woo ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) ),
				array( 'Contact', get_permalink( $page_ids['contact'] ) ),
			),
		),
		'footer-follow' => array(
			'name'  => 'Footer — Follow',
			'items' => array(
				array( 'Instagram', '#' ),
				array( 'TikTok', '#' ),
				array( 'Wholesale', get_permalink( $page_ids['wholesale'] ) ),
			),
		),
	);

	$locations = get_theme_mod( 'nav_menu_locations', array() );

	foreach ( $menus as $location => $menu ) {
		$object = wp_get_nav_menu_object( $menu['name'] );

		if ( ! $object ) {
			$menu_id = wp_create_nav_menu( $menu['name'] );

			if ( is_wp_error( $menu_id ) ) {
				WP_CLI::warning( 'Could not create menu: ' . $menu['name'] );
				continue;
			}

			foreach ( $menu['items'] as $item ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'  => $item[0],
					'menu-item-url'    => $item[1],
					'menu-item-status' => 'publish',
				) );
			}
		} else {
			$menu_id = $object->term_id;
		}

		$locations[ $location ] = $menu_id;
	}

	set_theme_mod( 'nav_menu_locations', $locations );
	WP_CLI::log( '  menus          primary + 4 footer menus assigned' );
}

/* -------------------------------------------------------------------------
 * 6. Sample products
 *
 * The catalogue itself lives in inc/catalogue.php so the theme's own bootstrap
 * and this provisioner can never drift. Names, weights and cannabinoid
 * strengths there are taken off the packaging.
 *
 * PRICES AND BATCH NUMBERS ARE SAMPLE VALUES. They exist so the shop is
 * walkable end to end on a fresh install. Replace them before launch.
 * ---------------------------------------------------------------------- */

if ( $has_woo && ! $skip_products ) {
	$counts = cofifi_seed_products();

	WP_CLI::log( sprintf(
		'  products       %d created, %d updated, %d left alone',
		$counts['created'],
		$counts['updated'],
		$counts['skipped']
	) );
}

/* -------------------------------------------------------------------------
 * 7. .htaccess
 *
 * WordPress writes this itself in the browser, but not under WP-CLI:
 * got_mod_rewrite() checks $is_apache, which is false when there is no
 * SERVER_SOFTWARE, so `wp rewrite flush` just warns and moves on. Without the
 * file, pretty permalinks 404 on everything except the homepage — the site
 * looks installed and is not. So write it here.
 * ---------------------------------------------------------------------- */

$htaccess = untrailingslashit( ABSPATH ) . '/.htaccess';
$base     = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
$base     = $base ? trailingslashit( $base ) : '/';

$rules = array(
	'<IfModule mod_rewrite.c>',
	'RewriteEngine On',
	'RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]',
	'RewriteBase ' . $base,
	'RewriteRule ^index\.php$ - [L]',
	'RewriteCond %{REQUEST_FILENAME} !-f',
	'RewriteCond %{REQUEST_FILENAME} !-d',
	'RewriteRule . ' . $base . 'index.php [L]',
	'</IfModule>',
);

require_once ABSPATH . 'wp-admin/includes/misc.php';

if ( ! file_exists( $htaccess ) ) {
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_touch
	@touch( $htaccess );
}

$written = false;

if ( file_exists( $htaccess ) ) {
	$written = insert_with_markers( $htaccess, 'WordPress', $rules );
}

if ( ! $written ) {
	// insert_with_markers leans on is_writable(), which misreports on Windows.
	$block = "# BEGIN WordPress\n" . implode( "\n", $rules ) . "\n# END WordPress\n";
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$written = (bool) @file_put_contents( $htaccess, $block );
}

if ( $written ) {
	WP_CLI::log( '  .htaccess      written, RewriteBase ' . $base );
} else {
	WP_CLI::warning( 'Could not write .htaccess at ' . $htaccess . ' — pretty permalinks will 404 until it exists.' );
}

/* -------------------------------------------------------------------------
 * 8. Finish
 * ---------------------------------------------------------------------- */

flush_rewrite_rules( false );

WP_CLI::line( '' );
WP_CLI::success( 'Provisioning complete — ' . home_url( '/' ) );
WP_CLI::line( '' );
WP_CLI::warning( 'Sample data in place. Before launch, replace:' );
WP_CLI::line( '    · prices R265 / R395 / R620 — invented, not real' );
WP_CLI::line( '    · every [bracketed] value in Customize → COFiFi details' );
WP_CLI::line( '    · [batch number] on both CBD products, and link the real COA' );
WP_CLI::line( '' );
WP_CLI::line( '  Keep the cbd, cbd-oil and cbd-plus categories — the compliance' );
WP_CLI::line( '  notice is gated on them. Have someone read the CBD copy.' );
WP_CLI::line( '' );
