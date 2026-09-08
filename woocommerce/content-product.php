<?php
/**
 * Product card in archives and loops.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php get_template_part( 'template-parts/components/product-card', null, array( 'product' => $product ) ); ?>
</li>
