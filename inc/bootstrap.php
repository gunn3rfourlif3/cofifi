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

define( 'COFIFI_BOOTSTRAP', 2 );

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

	cofifi_bootstrap_gallery_page();
	cofifi_bootstrap_product_images();
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
 * Point the seeded products at the new photography.
 *
 * Only touches products carrying the `_cofifi_seeded` marker, so a real
 * catalogue is never overwritten. The CBD oil keeps its own shot — the new
 * shoot is coffee only.
 */
function cofifi_bootstrap_product_images() {
	if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return;
	}

	$map = array(
		'COF-COFFEE-250' => 'prod-coffee-sq.webp',
		'COF-CBD-250'    => 'prod-cbd-sq.webp',
	);

	foreach ( $map as $sku => $file ) {
		$product_id = wc_get_product_id_by_sku( $sku );

		if ( ! $product_id || ! get_post_meta( $product_id, '_cofifi_seeded', true ) ) {
			continue;
		}

		$attachment_id = cofifi_sideload_theme_image( $file, $product_id );

		if ( $attachment_id ) {
			set_post_thumbnail( $product_id, $attachment_id );
		}
	}
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
