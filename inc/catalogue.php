<?php
/**
 * The catalogue — categories and seed products, in one place.
 *
 * setup/provision.php seeds a new install from this; inc/bootstrap.php brings an
 * install that is already running up to date from the same data. Keep it here so
 * the two can never drift.
 *
 * EVERY FIGURE HERE IS TAKEN OFF THE PACKAGING. Names, weights and cannabinoid
 * strengths are what is printed on the bags:
 *
 *   COFFEE            100% Ethiopian · creamy, smooth, low acidity
 *                     Roasted by women, the traditional way
 *   CBD + COFFEE      THC 150 mg or THC 750 mg · 250 gram
 *                     Handroasted in SA · 100% Ethiopian · creamy, smooth, no acidity
 *                     Manufactured according to SAHPRA & MCC standards
 *   RASTA ROAST       Mada Kush · THC + Coffee 500 mg · 150 g · 18+
 *                     Hand roasted and infused with love by black families
 *
 * PRICES ARE SAMPLE VALUES and so is [batch number]. Replace both before launch.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product categories.
 *
 * The slugs are load-bearing. `cbd`, `cbd-oil` and `cbd-plus` gate the CBD
 * notice; `thc` gates the stronger THC notice. Rename one and a legal notice
 * disappears from a product page without any other symptom.
 *
 * @return array slug => name.
 */
function cofifi_product_categories() {
	return array(
		'coffee'      => __( 'Coffee', 'cofifi' ),
		'cbd'         => __( 'CBD', 'cofifi' ),
		'cbd-oil'     => __( 'CBD Oil', 'cofifi' ),
		'cbd-plus'    => __( 'Coffee CBD+', 'cofifi' ),
		'thc'         => __( 'THC', 'cofifi' ),
		'rasta-roast' => __( 'Rasta Roast', 'cofifi' ),
	);
}

/**
 * The seed catalogue.
 *
 * `renames` lists earlier SKUs that became this one — the seeder moves the
 * existing product across rather than leaving a duplicate behind.
 *
 * @return array[]
 */
function cofifi_catalogue() {
	$catalogue = array(

		/* ---------------------------------------------------------------
		 * Cofifi — the house line
		 * ------------------------------------------------------------ */
		array(
			'sku'        => 'COF-COFFEE-250',
			'name'       => __( 'Cofifi Coffee', 'cofifi' ),
			'price'      => '265.00',
			'image'      => 'prod-coffee-sq.webp',
			'cats'       => array( 'coffee' ),
			'featured'   => false,
			'weight'     => '0.25',
			'short'      => __( 'Creamy, smooth and low in acidity. The everyday bag — 100% Ethiopian, pan-roasted the traditional way. No cannabinoids.', 'cofifi' ),
			'long'       => __( "100% Ethiopian beans, roasted by women in the traditional way and packed in small batches.\n\nCreamy, smooth and low in acidity — the bag we hand people who say they do not like black coffee.\n\nThis is the plain roast. It contains no CBD and no THC.", 'cofifi' ),
			'attributes' => array(
				'Origin' => __( '100% Ethiopian', 'cofifi' ),
				'Roast'  => __( 'Traditional pan-roast, medium', 'cofifi' ),
				'Weight' => __( '250 g', 'cofifi' ),
			),
		),

		array(
			'sku'        => 'COF-CBD-THC150-250',
			'renames'    => array( 'COF-CBD-250' ),
			'name'       => __( 'Cofifi CBD + Coffee — THC 150 mg', 'cofifi' ),
			'price'      => '395.00',
			'image'      => 'prod-cbd150-sq.webp',
			'cats'       => array( 'coffee', 'cbd', 'cbd-plus', 'thc' ),
			'featured'   => true,
			'weight'     => '0.25',
			'short'      => __( 'Our Ethiopian roast with CBD and 150 mg of THC in a 250 g bag. Handroasted in South Africa. Strictly 18+.', 'cofifi' ),
			'long'       => __( "Handroasted in SA from 100% Ethiopian beans — creamy, smooth, no acidity — infused with CBD and 150 mg of THC per 250 gram bag.\n\nManufactured according to SAHPRA and MCC standards. This is the milder of the two strengths; the 750 mg bag is the same coffee, five times the THC.\n\nContains THC. Not for sale to anyone under 18. Do not drive or operate machinery after drinking it.", 'cofifi' ),
			'attributes' => array(
				'Origin'   => __( '100% Ethiopian', 'cofifi' ),
				'Roast'    => __( 'Handroasted in South Africa', 'cofifi' ),
				'Weight'   => __( '250 g', 'cofifi' ),
				'THC'      => __( '150 mg per bag', 'cofifi' ),
				'Standard' => __( 'SAHPRA &amp; MCC', 'cofifi' ),
				'Batch'    => __( '[batch number]', 'cofifi' ),
			),
		),

		array(
			'sku'        => 'COF-CBD-THC750-250',
			'name'       => __( 'Cofifi CBD + Coffee — THC 750 mg', 'cofifi' ),
			'price'      => '545.00',
			'image'      => 'prod-cbd750-sq.webp',
			'cats'       => array( 'coffee', 'cbd', 'cbd-plus', 'thc' ),
			'featured'   => false,
			'weight'     => '0.25',
			'short'      => __( 'The same 250 g bag at 750 mg of THC. The strong one. Strictly 18+ — start with a small serving.', 'cofifi' ),
			'long'       => __( "Handroasted in SA from 100% Ethiopian beans — creamy, smooth, no acidity — infused with CBD and 750 mg of THC per 250 gram bag.\n\nManufactured according to SAHPRA and MCC standards. This is the strongest coffee we pack.\n\nContains THC. Not for sale to anyone under 18. Edible cannabinoids can take up to two hours to be felt, so measure a small serving and wait before making another. Do not drive or operate machinery after drinking it.", 'cofifi' ),
			'attributes' => array(
				'Origin'   => __( '100% Ethiopian', 'cofifi' ),
				'Roast'    => __( 'Handroasted in South Africa', 'cofifi' ),
				'Weight'   => __( '250 g', 'cofifi' ),
				'THC'      => __( '750 mg per bag', 'cofifi' ),
				'Standard' => __( 'SAHPRA &amp; MCC', 'cofifi' ),
				'Batch'    => __( '[batch number]', 'cofifi' ),
			),
		),

		/* ---------------------------------------------------------------
		 * Rasta Roast — the Mada Kush sub-brand
		 * ------------------------------------------------------------ */
		array(
			'sku'        => 'RR-MK-THC500-150',
			'name'       => __( 'Rasta Roast Mada Kush Coffee — THC 500 mg', 'cofifi' ),
			'price'      => '420.00',
			'image'      => 'prod-rasta-sq.webp',
			'cats'       => array( 'coffee', 'thc', 'rasta-roast' ),
			'featured'   => true,
			'weight'     => '0.15',
			'short'      => __( 'Local coffee, hand roasted and infused with 500 mg of THC in a 150 g bag. Lab tested. Strictly 18+.', 'cofifi' ),
			'long'       => __( "Rasta Roast is the Mada Kush line: our delicious, creamy local coffee, hand roasted and infused with love by black families.\n\n500 mg of THC per 150 g bag. Lab tested, premium quality.\n\nContains THC. Not for sale to anyone under 18. Edible cannabinoids can take up to two hours to be felt, so measure a small serving and wait before making another. Do not drive or operate machinery after drinking it.", 'cofifi' ),
			'attributes' => array(
				'Origin' => __( 'Local, hand roasted', 'cofifi' ),
				'Weight' => __( '150 g', 'cofifi' ),
				'THC'    => __( '500 mg per bag', 'cofifi' ),
				'Age'    => __( '18+', 'cofifi' ),
				'Batch'  => __( '[batch number]', 'cofifi' ),
			),
		),

		/* ---------------------------------------------------------------
		 * The Apothecary
		 * ------------------------------------------------------------ */
		array(
			'sku'        => 'COF-OIL-30',
			'name'       => __( 'Cofifi CBD Oil — Focus', 'cofifi' ),
			'price'      => '620.00',
			'image'      => 'oil-white.jpg',
			'cats'       => array( 'cbd', 'cbd-oil' ),
			'featured'   => false,
			'weight'     => '0.05',
			'short'      => __( 'Broad-spectrum extract, 150 mg in 30 ml, with a graduated dropper. Contains less than 0.3% THC.', 'cofifi' ),
			'long'       => __( "Broad-spectrum extract in a 30 ml amber bottle, with a graduated dropper so a dose is a measurement rather than a squeeze.\n\nEvery batch is tested by an independent laboratory and the certificate is published against the batch number on the label.", 'cofifi' ),
			'attributes' => array(
				'CBD'   => __( '150 mg broad spectrum', 'cofifi' ),
				'THC'   => __( 'Less than 0.3%', 'cofifi' ),
				'Size'  => __( '30 ml / 1 fl oz', 'cofifi' ),
				'Batch' => __( '[batch number]', 'cofifi' ),
			),
		),
	);

	/**
	 * Filter the seed catalogue.
	 *
	 * @param array[] $catalogue Seed products.
	 */
	return apply_filters( 'cofifi_catalogue', $catalogue );
}

/**
 * Create the product categories if they are missing.
 *
 * @return int Number of terms created.
 */
function cofifi_seed_categories() {
	$created = 0;

	foreach ( cofifi_product_categories() as $slug => $name ) {
		if ( term_exists( $slug, 'product_cat' ) ) {
			continue;
		}
		wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		++$created;
	}

	return $created;
}

/**
 * Seed or update the catalogue.
 *
 * Creates what is missing. Updates a product only when it carries the
 * `_cofifi_seeded` marker, so a real catalogue edited by hand is never
 * overwritten. Never deletes.
 *
 * @param bool $update_existing Refresh seeded products that already exist.
 * @return array counts: created, updated, skipped.
 */
function cofifi_seed_products( $update_existing = true ) {
	$counts = array( 'created' => 0, 'updated' => 0, 'skipped' => 0 );

	if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return $counts;
	}

	foreach ( cofifi_catalogue() as $item ) {
		$product_id = wc_get_product_id_by_sku( $item['sku'] );
		$is_new     = false;

		// An earlier SKU that became this one — carry the product across so the
		// post, its URL and any orders against it survive the rename.
		if ( ! $product_id && ! empty( $item['renames'] ) ) {
			foreach ( $item['renames'] as $old_sku ) {
				$found = wc_get_product_id_by_sku( $old_sku );
				if ( $found && get_post_meta( $found, '_cofifi_seeded', true ) ) {
					$product_id = $found;
					break;
				}
			}
		}

		if ( $product_id ) {
			if ( ! $update_existing || ! get_post_meta( $product_id, '_cofifi_seeded', true ) ) {
				++$counts['skipped'];
				continue;
			}
			$product = wc_get_product( $product_id );
		} else {
			$product = new WC_Product_Simple();
			$is_new  = true;
		}

		if ( ! $product ) {
			++$counts['skipped'];
			continue;
		}

		$product->set_name( $item['name'] );
		$product->set_sku( $item['sku'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_short_description( $item['short'] );
		$product->set_description( $item['long'] );
		$product->set_weight( $item['weight'] );
		$product->set_featured( $item['featured'] );

		// Price and stock are the shop's business once it is live — only set
		// them when the product is new.
		if ( $is_new ) {
			$product->set_regular_price( $item['price'] );
			$product->set_manage_stock( true );
			$product->set_stock_quantity( 40 );
		}

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

		update_post_meta( $product_id, '_cofifi_seeded', '1' );

		$attachment_id = cofifi_sideload_theme_image( $item['image'], $product_id );
		if ( $attachment_id ) {
			set_post_thumbnail( $product_id, $attachment_id );
		}

		++$counts[ $is_new ? 'created' : 'updated' ];
	}

	return $counts;
}
