<?php
/**
 * Cofifi site provisioning.
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
WP_CLI::line( '  Cofifi provisioning' );
WP_CLI::line( '  ------------------' );

/* -------------------------------------------------------------------------
 * 1. Site options
 * ---------------------------------------------------------------------- */

update_option( 'blogname', 'Cofifi' );
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
 * miss on the page. Replace them in Appearance → Customize → Cofifi details.
 */
$mods = array(
	'cofifi_free_delivery'  => '[R950]',
	'cofifi_address_line_1' => '[Street address]',
	'cofifi_address_line_2' => '[City, postal code]',
	'cofifi_email'          => '[hello@cofifi.co]',
	'cofifi_utility_1'      => 'Roasted by women, the traditional way',
	'cofifi_utility_2'      => 'Contains less than 0.3% THC',
);

foreach ( $mods as $key => $value ) {
	if ( false === get_theme_mod( $key, false ) ) {
		set_theme_mod( $key, $value );
	}
}
WP_CLI::log( '  placeholders   seeded (Customize → Cofifi details)' );

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

	WP_CLI::log( '  woocommerce    ZAR, ZA, /shop/<slug> product URLs' );

	/*
	 * Product categories. The theme gates the CBD disclaimer on these slugs —
	 * cbd, cbd-oil and cbd-plus. Renaming them silently drops a legal notice.
	 */
	$cats = array(
		'coffee'   => 'Coffee',
		'cbd'      => 'CBD',
		'cbd-oil'  => 'CBD Oil',
		'cbd-plus' => 'Coffee CBD+',
	);

	foreach ( $cats as $slug => $name ) {
		if ( ! term_exists( $slug, 'product_cat' ) ) {
			wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		}
	}
	WP_CLI::log( '  categories     coffee, cbd, cbd-oil, cbd-plus' );
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

	$menus = array(
		'primary' => array(
			'name'  => 'Primary',
			'items' => array(
				array( 'Coffee', $shop_url ),
				array( 'CBD Oil', $shop_url ),
				array( 'Bundles', $shop_url ),
				array( 'Our Story', get_permalink( $page_ids['our-story'] ) ),
				array( 'Wholesale', get_permalink( $page_ids['wholesale'] ) ),
			),
		),
		'footer-shop' => array(
			'name'  => 'Footer — Shop',
			'items' => array(
				array( 'Cofifi Coffee', $shop_url ),
				array( 'Coffee CBD+', $shop_url ),
				array( 'CBD Oil — Focus', $shop_url ),
				array( 'Bundles', $shop_url ),
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
 * PRICES AND BATCH NUMBERS BELOW ARE SAMPLE VALUES. They exist so the shop is
 * walkable end to end on a fresh install. Replace them before launch.
 * ---------------------------------------------------------------------- */

if ( $has_woo && ! $skip_products ) {

	/**
	 * Attach a theme asset to a product as its featured image, once.
	 *
	 * @param int    $product_id Product ID.
	 * @param string $file       File name inside the theme's assets/img.
	 */
	$attach_image = function ( $product_id, $file ) {
		if ( has_post_thumbnail( $product_id ) ) {
			return;
		}

		$source = get_template_directory() . '/assets/img/' . $file;

		if ( ! file_exists( $source ) ) {
			WP_CLI::warning( 'Image not found: ' . $file );
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = wp_tempnam( $file );

		if ( ! $tmp || ! copy( $source, $tmp ) ) {
			WP_CLI::warning( 'Could not stage image: ' . $file );
			return;
		}

		$attachment_id = media_handle_sideload(
			array(
				'name'     => $file,
				'tmp_name' => $tmp,
			),
			$product_id
		);

		if ( is_wp_error( $attachment_id ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $tmp );
			WP_CLI::warning( 'Could not attach image ' . $file . ': ' . $attachment_id->get_error_message() );
			return;
		}

		set_post_thumbnail( $product_id, $attachment_id );
	};

	$seed = array(
		array(
			'sku'        => 'COF-COFFEE-250',
			'name'       => 'Cofifi Coffee',
			'price'      => '265.00',
			'image'      => 'bag-double.jpg',
			'cats'       => array( 'coffee' ),
			'featured'   => false,
			'short'      => 'Creamy, smooth and low in acidity. The everyday bag — 100% Ethiopian, pan-roasted the traditional way.',
			'long'       => "100% Ethiopian beans, roasted by women in the traditional way and packed in small batches.\n\nCreamy, smooth and low in acidity — the bag we hand people who say they do not like black coffee.",
			'weight'     => '0.25',
			'attributes' => array(
				'Origin' => '100% Ethiopian',
				'Roast'  => 'Traditional pan-roast, medium',
				'Weight' => '250 g',
			),
		),
		array(
			'sku'        => 'COF-CBD-250',
			'name'       => 'Cofifi Coffee CBD+',
			'price'      => '395.00',
			'image'      => 'bag-single.jpg',
			'cats'       => array( 'coffee', 'cbd', 'cbd-plus' ),
			'featured'   => true,
			'short'      => 'The same Ethiopian roast with 150 mg of broad-spectrum CBD. Under 0.3% THC, third-party tested.',
			'long'       => "Our everyday Ethiopian roast with 150 mg of broad-spectrum CBD folded into the bag.\n\nCreamy, smooth and low in acidity — the cup tastes the same.",
			'weight'     => '0.25',
			'attributes' => array(
				'Origin' => '100% Ethiopian',
				'Roast'  => 'Traditional pan-roast, medium',
				'Weight' => '250 g',
				'CBD'    => '150 mg broad spectrum',
				'THC'    => 'Less than 0.3%',
				'Batch'  => '[batch number]',
			),
		),
		array(
			'sku'        => 'COF-OIL-30',
			'name'       => 'Cofifi CBD Oil — Focus',
			'price'      => '620.00',
			'image'      => 'oil-white.jpg',
			'cats'       => array( 'cbd', 'cbd-oil' ),
			'featured'   => false,
			'short'      => 'Broad-spectrum extract, 150 mg in 30 ml, with a graduated dropper. Contains less than 0.3% THC.',
			'long'       => "Broad-spectrum extract in a 30 ml amber bottle, with a graduated dropper so a dose is a measurement rather than a squeeze.\n\nEvery batch is tested by an independent laboratory and the certificate is published against the batch number on the label.",
			'weight'     => '0.05',
			'attributes' => array(
				'CBD'   => '150 mg broad spectrum',
				'THC'   => 'Less than 0.3%',
				'Size'  => '30 ml / 1 fl oz',
				'Batch' => '[batch number]',
			),
		),
	);

	$created = 0;

	foreach ( $seed as $item ) {
		if ( wc_get_product_id_by_sku( $item['sku'] ) ) {
			continue;
		}

		$product = new WC_Product_Simple();
		$product->set_name( $item['name'] );
		$product->set_sku( $item['sku'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_regular_price( $item['price'] );
		$product->set_short_description( $item['short'] );
		$product->set_description( $item['long'] );
		$product->set_weight( $item['weight'] );
		$product->set_featured( $item['featured'] );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( 40 );

		$term_ids = array();
		foreach ( $item['cats'] as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term ) {
				$term_ids[] = $term->term_id;
			}
		}
		$product->set_category_ids( $term_ids );

		$attributes = array();
		$position   = 0;

		foreach ( $item['attributes'] as $label => $value ) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_name( $label );
			$attribute->set_options( array( $value ) );
			$attribute->set_position( $position++ );
			$attribute->set_visible( true );
			$attribute->set_variation( false );
			$attributes[] = $attribute;
		}
		$product->set_attributes( $attributes );

		$product_id = $product->save();

		// Marker so these can be found and cleaned up later.
		update_post_meta( $product_id, '_cofifi_seeded', '1' );

		$attach_image( $product_id, $item['image'] );
		++$created;
	}

	WP_CLI::log( '  products       ' . $created . ' created, ' . ( count( $seed ) - $created ) . ' already present' );
}

/* -------------------------------------------------------------------------
 * 7. Finish
 * ---------------------------------------------------------------------- */

flush_rewrite_rules( false );

WP_CLI::line( '' );
WP_CLI::success( 'Provisioning complete — ' . home_url( '/' ) );
WP_CLI::line( '' );
WP_CLI::warning( 'Sample data in place. Before launch, replace:' );
WP_CLI::line( '    · prices R265 / R395 / R620 — invented, not real' );
WP_CLI::line( '    · every [bracketed] value in Customize → Cofifi details' );
WP_CLI::line( '    · [batch number] on both CBD products, and link the real COA' );
WP_CLI::line( '' );
WP_CLI::line( '  Keep the cbd, cbd-oil and cbd-plus categories — the compliance' );
WP_CLI::line( '  notice is gated on them. Have someone read the CBD copy.' );
WP_CLI::line( '' );
