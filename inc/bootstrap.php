<?php
/**
 * One-shot content bootstrap.
 *
 * setup/provision.php is the canonical provisioner and needs WP-CLI. This is
 * the small subset that has to happen on installs that are already running —
 * it fires once, records the version it reached in an autoloaded option, and
 * then costs a single array lookup per request.
 *
 * Bump COFIFI_BOOTSTRAP to make it run again.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

define( 'COFIFI_BOOTSTRAP', 6 );

/**
 * Run any bootstrap steps this install has not reached yet.
 */
function cofifi_bootstrap() {
	if ( (int) get_option( 'cofifi_bootstrap', 0 ) >= COFIFI_BOOTSTRAP ) {
		return;
	}

	// Claim the run before doing the work, so a burst of requests can't all
	// start it at once.
	update_option( 'cofifi_bootstrap', COFIFI_BOOTSTRAP, true );

	cofifi_bootstrap_brand();
	cofifi_bootstrap_gallery_page();
	cofifi_bootstrap_catalogue();
	cofifi_bootstrap_retire_thc_claim();
	cofifi_bootstrap_footer_shop_menu();
}

/**
 * Rebuild the footer Shop menu from the catalogue.
 *
 * It was seeded with a "Coffee CBD+" that no longer exists and a "Bundles"
 * that never did. Only items still pointing at whatever they were seeded with
 * are replaced: if every label in the menu has been edited by hand, the whole
 * menu is left alone.
 */
function cofifi_bootstrap_footer_shop_menu() {
	$locations = get_nav_menu_locations();

	if ( empty( $locations['footer-shop'] ) || ! function_exists( 'wc_get_page_permalink' ) ) {
		return;
	}

	$menu_id = (int) $locations['footer-shop'];
	$items   = wp_get_nav_menu_items( $menu_id );
	$seeded  = array( 'COFiFi Coffee', 'Cofifi Coffee', 'Coffee CBD+', 'CBD Oil — Focus', 'Bundles' );

	if ( $items ) {
		foreach ( $items as $item ) {
			if ( ! in_array( $item->title, $seeded, true ) ) {
				return; // Someone has curated this. Leave it.
			}
		}
		foreach ( $items as $item ) {
			wp_delete_post( $item->ID, true );
		}
	}

	$shop = wc_get_page_permalink( 'shop' );

	$wanted = array(
		array( __( 'COFiFi Coffee', 'cofifi' ), 'coffee' ),
		array( __( 'CBD + Coffee', 'cofifi' ), 'cbd-plus' ),
		array( __( 'Rasta Roast', 'cofifi' ), 'rasta-roast' ),
		array( __( 'CBD Oil — Focus', 'cofifi' ), 'cbd-oil' ),
	);

	foreach ( $wanted as $row ) {
		list( $label, $slug ) = $row;

		$term = get_term_by( 'slug', $slug, 'product_cat' );
		$url  = $shop;

		if ( $term && ! is_wp_error( $term ) ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$url = $link;
			}
		}

		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title' => $label,
			'menu-item-url'    => $url,
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		) );
	}
}

/**
 * Bring the brand details on a running install up to date.
 *
 * The site title and the contact address were seeded before anyone had the
 * domain. Only the exact seeded values are replaced — anything the shop has
 * since written for itself is left alone.
 *
 * Product names are handled by the catalogue seeder, which runs after this.
 */
function cofifi_bootstrap_brand() {
	if ( 'Cofifi' === get_option( 'blogname' ) ) {
		update_option( 'blogname', 'COFiFi' );
	}

	$stale_email = array( '[hello@cofifi.co]', '[hello@cofifi.com]', 'hello@cofifi.co' );

	if ( in_array( get_theme_mod( 'cofifi_email' ), $stale_email, true ) ) {
		set_theme_mod( 'cofifi_email', 'hello@cofifi.com' );
	}

	if ( ! get_theme_mod( 'cofifi_legal_name' ) ) {
		set_theme_mod( 'cofifi_legal_name', 'CoFiFi Roastery' );
	}
}

/**
 * Retire the sitewide "less than 0.3% THC" line.
 *
 * It was seeded into the Customizer as a theme mod, which beats the default in
 * inc/setup.php. It is false of the infused coffees — they carry 150 mg, 500 mg
 * and 750 mg — so it cannot stand above every page. Only the exact old string
 * is replaced; anything the shop has since written itself is left alone.
 */
function cofifi_bootstrap_retire_thc_claim() {
	$stale = array(
		'Contains less than 0.3% THC',
		'Contains less than 0.3%% THC',
	);

	if ( in_array( get_theme_mod( 'cofifi_utility_2' ), $stale, true ) ) {
		set_theme_mod( 'cofifi_utility_2', __( 'Cannabis range — strictly 18+', 'cofifi' ) );
	}
}
add_action( 'init', 'cofifi_bootstrap', 20 );

/**
 * The Gallery page, on the Gallery template, linked from the primary menu.
 *
 * @return int|false Page ID, or false if it could not be made.
 */
function cofifi_bootstrap_gallery_page() {
	$page = get_page_by_path( 'gallery' );

	if ( ! $page ) {
		$page_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => __( 'Gallery', 'cofifi' ),
			'post_name'    => 'gallery',
			'post_content' => '',
		) );

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return false;
		}
	} else {
		$page_id = $page->ID;
	}

	if ( 'page-gallery.php' !== get_page_template_slug( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-gallery.php' );
	}

	cofifi_bootstrap_menu_item( 'primary', __( 'Gallery', 'cofifi' ), $page_id );

	return $page_id;
}

/**
 * Append a page to a menu location, if it is not already on it.
 *
 * @param string $location Theme menu location.
 * @param string $label    Menu label.
 * @param int    $page_id  Page to link to.
 */
function cofifi_bootstrap_menu_item( $location, $label, $page_id ) {
	$locations = get_nav_menu_locations();

	if ( empty( $locations[ $location ] ) ) {
		return;
	}

	$menu_id = (int) $locations[ $location ];
	$items   = wp_get_nav_menu_items( $menu_id );

	if ( $items ) {
		foreach ( $items as $item ) {
			if ( 'post_type' === $item->type && (int) $item->object_id === (int) $page_id ) {
				return;
			}
		}
	}

	wp_update_nav_menu_item( $menu_id, 0, array(
		'menu-item-title'     => $label,
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
	) );
}

/**
 * Bring the catalogue up to date.
 *
 * Categories first, then the products in inc/catalogue.php — created if
 * missing, refreshed if they carry the `_cofifi_seeded` marker, and left alone
 * otherwise. Nothing is ever deleted, and an old SKU listed under `renames` is
 * carried across rather than duplicated.
 */
function cofifi_bootstrap_catalogue() {
	if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return;
	}

	cofifi_seed_categories();
	cofifi_seed_products();
}

/**
 * Copy a file out of the theme's assets/img into the media library.
 *
 * Re-uses the attachment if the same file has been sideloaded before, so this
 * does not pile up duplicates each time it is bumped.
 *
 * @param string $file      File name inside assets/img.
 * @param int    $parent_id Post to attach to.
 * @return int|false Attachment ID.
 */
function cofifi_sideload_theme_image( $file, $parent_id = 0 ) {
	$existing = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'   => '_cofifi_source',
				'value' => $file,
			),
		),
	) );

	if ( $existing ) {
		return (int) $existing[0];
	}

	$source = COFIFI_DIR . '/assets/img/' . $file;

	if ( ! file_exists( $source ) ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( $file );

	if ( ! $tmp || ! copy( $source, $tmp ) ) {
		return false;
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		$parent_id
	);

	if ( is_wp_error( $attachment_id ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		@unlink( $tmp );
		return false;
	}

	update_post_meta( $attachment_id, '_cofifi_source', $file );

	return (int) $attachment_id;
}
