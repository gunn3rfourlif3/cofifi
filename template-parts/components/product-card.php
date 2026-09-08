<?php
/**
 * Product card.
 *
 * @package Cofifi
 *
 * @var array $args Expects 'product' => WC_Product.
 */

defined( 'ABSPATH' ) || exit;

$product = isset( $args['product'] ) ? $args['product'] : null;

if ( ! $product instanceof WC_Product ) {
	return;
}

$is_cbd = has_term( array( 'cbd', 'cbd-oil', 'cbd-plus' ), 'product_cat', $product->get_id() )
	|| has_term( array( 'cbd' ), 'product_tag', $product->get_id() );

$image_id = $product->get_image_id();
$excerpt  = $product->get_short_description();
$terms    = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
?>

<article <?php wc_product_class( 'prod' . ( $is_cbd ? ' prod--cbd' : '' ), $product ); ?>>
	<a class="prod__media" href="<?php the_permalink(); ?>">
		<?php
		if ( $image_id ) {
			echo wp_get_attachment_image( $image_id, 'cofifi-card', false, array(
				'alt'     => $product->get_name(),
				'loading' => 'lazy',
			) );
		} else {
			woocommerce_placeholder_img( 'cofifi-card' );
		}

		if ( $product->is_featured() ) {
			printf( '<span class="badge prod__badge">%s</span>', esc_html__( 'Best seller', 'cofifi' ) );
		} elseif ( $product->is_on_sale() ) {
			printf( '<span class="badge prod__badge">%s</span>', esc_html__( 'On offer', 'cofifi' ) );
		}
		?>
	</a>

	<div class="prod__body">
		<?php if ( ! empty( $terms ) ) : ?>
			<p class="prod__meta"><?php echo esc_html( implode( ' · ', array_slice( $terms, 0, 3 ) ) ); ?></p>
		<?php endif; ?>

		<h3 class="t-card">
			<a href="<?php the_permalink(); ?>" style="color:inherit"><?php echo esc_html( $product->get_name() ); ?></a>
		</h3>

		<?php if ( $excerpt ) : ?>
			<p class="small"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 22 ) ); ?></p>
		<?php endif; ?>

		<div class="prod__foot">
			<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			<?php
			woocommerce_template_loop_add_to_cart( array(
				'class' => 'pill' . ( $is_cbd ? ' is-active--teal' : '' ),
			) );
			?>
		</div>
	</div>
</article>
