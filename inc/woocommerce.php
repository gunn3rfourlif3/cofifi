<?php
/**
 * WooCommerce integration.
 *
 * Overrides are kept deliberately small: templates are only copied into the
 * theme when they actually need to change. Everything else is done with hooks.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare support and take over the gallery.
 */
function cofifi_woocommerce_setup() {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 720,
		'single_image_width'    => 1000,
		'product_grid'          => array(
			'default_columns' => 3,
			'min_columns'     => 1,
			'max_columns'     => 4,
		),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'cofifi_woocommerce_setup' );

/**
 * Three products per row on archives.
 *
 * @return int
 */
function cofifi_loop_columns() {
	return 3;
}
add_filter( 'loop_shop_columns', 'cofifi_loop_columns' );

/**
 * Wrap Woo pages in the theme's own container.
 */
function cofifi_woo_wrapper_start() {
	echo '<main id="main" class="site-main section"><div class="wrap">';
}
function cofifi_woo_wrapper_end() {
	echo '</div></main>';
}
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', 'cofifi_woo_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'cofifi_woo_wrapper_end', 10 );

/**
 * No sidebar on shop pages — the design has none.
 */
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Breadcrumb separator to match the design.
 *
 * @param array $args Breadcrumb args.
 * @return array
 */
function cofifi_breadcrumb_args( $args ) {
	$args['delimiter']   = '<span class="woocommerce-breadcrumb__sep" aria-hidden="true">/</span>';
	$args['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'cofifi' ) . '">';
	$args['wrap_after']  = '</nav>';
	$args['before']      = '';
	$args['after']       = '';
	return $args;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'cofifi_breadcrumb_args' );

/**
 * The compliance line under the add-to-cart form on CBD products.
 *
 * A product counts as CBD if it is in the `cbd` product category or carries the
 * `cbd` tag. Keep that taxonomy in place — the disclaimer is a legal
 * requirement, not decoration.
 */
function cofifi_product_compliance_note() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$is_cbd = has_term( array( 'cbd', 'cbd-oil', 'cbd-plus' ), 'product_cat', $product->get_id() )
		|| has_term( array( 'cbd' ), 'product_tag', $product->get_id() );

	if ( ! $is_cbd ) {
		return;
	}

	printf(
		'<p class="legal pdp__compliance">%s</p>',
		esc_html( cofifi_compliance_line() )
	);
}
add_action( 'woocommerce_after_add_to_cart_form', 'cofifi_product_compliance_note', 20 );

/**
 * Cart count for the header, refreshed by Woo's own fragments.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function cofifi_cart_fragment( $fragments ) {
	ob_start();
	cofifi_cart_link();
	$fragments['a.cart-link'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'cofifi_cart_fragment' );

/**
 * Output the header cart link.
 */
function cofifi_cart_link() {
	$count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<a class="cart-link" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ); ?>">
		<span class="screen-reader-text"><?php esc_html_e( 'View cart', 'cofifi' ); ?></span>
		<?php cofifi_icon( 'bag', 18 ); ?>
		<span class="cart-link__count"><?php echo esc_html( $count ); ?></span>
	</a>
	<?php
}

/**
 * Move the price under the title on single products (the design puts it in a
 * row with the weight and CBD strength).
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/**
 * Sale flash uses the theme's badge.
 *
 * @return string
 */
function cofifi_sale_flash() {
	return '<span class="badge prod__badge">' . esc_html__( 'Best seller', 'cofifi' ) . '</span>';
}
add_filter( 'woocommerce_sale_flash', 'cofifi_sale_flash' );

/**
 * Add-to-cart wording.
 *
 * @return string
 */
function cofifi_add_to_cart_text() {
	return __( 'Add to bag', 'cofifi' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'cofifi_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'cofifi_add_to_cart_text' );

/**
 * Loop add-to-cart links carry the theme's pill classes.
 *
 * WooCommerce builds this link itself, so the classes are added here rather
 * than by overriding the template. The single-product button is styled by CSS
 * on `.single_add_to_cart_button` — Woo hardcodes its classes.
 *
 * @param string     $html    Link markup.
 * @param WC_Product $product Product.
 * @param array      $args    Link args.
 * @return string
 */
function cofifi_loop_add_to_cart_classes( $html, $product, $args ) {
	unset( $product );

	if ( empty( $args['class'] ) || false === strpos( $args['class'], 'pill' ) ) {
		$html = str_replace( 'class="', 'class="pill ', $html );
	}

	return $html;
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'cofifi_loop_add_to_cart_classes', 10, 3 );
