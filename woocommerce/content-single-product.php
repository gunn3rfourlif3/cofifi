<?php
/**
 * Single product layout.
 *
 * The two-column shell and the "profile" block below it are the theme's. The
 * add-to-cart form is left to WooCommerce's own templates on purpose — the
 * designed grind/size pills are variation selectors, and faking them here would
 * break variable products and stock handling. Style Woo's selects instead, or
 * add a progressive enhancement that swaps them for pills.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

global $product;

$is_cbd = has_term( array( 'cbd', 'cbd-oil', 'cbd-plus' ), 'product_cat', $product->get_id() )
	|| has_term( array( 'cbd' ), 'product_tag', $product->get_id() );
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'pdp', $product ); ?>>

	<div class="pdp__gallery">
		<?php
		/**
		 * woocommerce_before_single_product_summary hook.
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		do_action( 'woocommerce_before_single_product_summary' );
		?>
	</div>

	<div class="summary entry-summary pdp__buy">
		<?php
		/**
		 * woocommerce_single_product_summary hook.
		 *
		 * @hooked woocommerce_template_single_title - 5
		 * @hooked woocommerce_template_single_rating - 10
		 * @hooked woocommerce_template_single_price - 10
		 * @hooked woocommerce_template_single_excerpt - 20
		 * @hooked woocommerce_template_single_add_to_cart - 30
		 * @hooked woocommerce_template_single_sharing - 50
		 */
		do_action( 'woocommerce_single_product_summary' );
		?>

		<ul class="assurances">
			<li>
				<?php cofifi_icon( 'check', 14 ); ?>
				<span><?php esc_html_e( 'Roasted Thursdays — order by Wednesday noon to make this week’s batch', 'cofifi' ); ?></span>
			</li>
			<li>
				<?php cofifi_icon( 'check', 14 ); ?>
				<span>
					<?php
					/* translators: %s: order value. */
					printf( esc_html__( 'Free delivery over %s · 2–4 working days', 'cofifi' ), esc_html( cofifi_option( 'free_delivery' ) ) );
					?>
				</span>
			</li>
		</ul>

		<div class="accordion">
			<?php
			$panels = array();

			if ( $is_cbd ) {
				$panels[] = array(
					__( 'Lab certificate', 'cofifi' ),
					__( 'Every batch is tested by an independent laboratory. Find this product’s batch number on the label and open the matching certificate of analysis.', 'cofifi' ),
				);
			}

			$panels[] = array(
				__( 'Storage &amp; freshness', 'cofifi' ),
				__( 'Keep the bag sealed, cool and out of direct light. Whole bean holds its character longest; ground coffee is best within two weeks of the roast date printed on the bag.', 'cofifi' ),
			);
			$panels[] = array(
				__( 'Delivery &amp; returns', 'cofifi' ),
				__( 'Dispatched within two working days of the roast. Unopened products can be returned within 30 days. Opened food and supplement products cannot be returned.', 'cofifi' ),
			);

			foreach ( $panels as $panel ) :
				?>
				<div class="accordion__item">
					<button class="accordion__btn" type="button">
						<span><?php echo esc_html( $panel[0] ); ?></span>
						<?php cofifi_icon( 'chevron', 14 ); ?>
					</button>
					<div class="accordion__panel">
						<p class="small"><?php echo esc_html( $panel[1] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<?php
/**
 * The profile block — attributes as a spec table.
 * Add product attributes in WooCommerce (Origin, Roast, Weight, CBD, THC,
 * Batch) and they appear here automatically.
 */
$attributes = $product->get_attributes();

if ( ! empty( $attributes ) ) :
	?>
	<section class="section section--alt" style="margin-top:var(--section-y)">
		<div class="wrap">
			<div class="grid" style="grid-template-columns:4fr 8fr;gap:clamp(24px,3vw,40px)">
				<div class="stack gap-16">
					<p class="eyebrow"><?php esc_html_e( 'The profile', 'cofifi' ); ?></p>
					<h2 class="t-sub"><?php esc_html_e( 'What’s in the bag', 'cofifi' ); ?></h2>
					<p class="small" style="max-width:36ch">
						<?php esc_html_e( 'Everything here is printed on the label too — origin, weight, CBD content and the batch it came from.', 'cofifi' ); ?>
					</p>
				</div>

				<div class="spec">
					<?php
					foreach ( $attributes as $attribute ) {
						if ( ! $attribute->get_visible() ) {
							continue;
						}

						$name  = wc_attribute_label( $attribute->get_name() );
						$value = $attribute->is_taxonomy()
							? implode( ', ', wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) ) )
							: implode( ', ', $attribute->get_options() );

						if ( '' === $value ) {
							continue;
						}
						?>
						<div class="spec__row">
							<span class="spec__k"><?php echo esc_html( $name ); ?></span>
							<span class="spec__v"><?php echo esc_html( $value ); ?></span>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</section>
	<?php
endif;

/**
 * woocommerce_after_single_product_summary hook.
 *
 * @hooked woocommerce_output_product_data_tabs - 10
 * @hooked woocommerce_upsell_display - 15
 * @hooked woocommerce_output_related_products - 20
 */
do_action( 'woocommerce_after_single_product_summary' );

do_action( 'woocommerce_after_single_product' );
