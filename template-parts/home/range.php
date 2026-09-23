<?php
/**
 * The range — three product cards.
 *
 * Uses real WooCommerce products when they exist (featured first, then most
 * recent). Falls back to the designed cards so the homepage is never empty on
 * a fresh install. The fallback prices are SAMPLE VALUES — replace them with
 * real products before launch.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

$products = array();

if ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products( array(
		'status'   => 'publish',
		'limit'    => 3,
		'orderby'  => 'menu_order',
		'order'    => 'ASC',
		'featured' => true,
	) );

	if ( count( $products ) < 3 ) {
		$products = wc_get_products( array(
			'status'  => 'publish',
			'limit'   => 3,
			'orderby' => 'date',
			'order'   => 'DESC',
		) );
	}
}
?>

<section class="section" style="padding-top:0">
	<div class="wrap">
		<header class="section-head">
			<div class="section-head__title">
				<p class="eyebrow"><?php esc_html_e( 'The range', 'cofifi' ); ?></p>
				<h2 class="t-section"><?php esc_html_e( 'Three ways to start', 'cofifi' ); ?></h2>
			</div>
			<a class="link-arrow link-arrow--muted" href="<?php echo esc_url( cofifi_shop_url() ); ?>">
				<?php esc_html_e( 'Shop everything', 'cofifi' ); ?>
				<?php cofifi_icon( 'arrow', 16 ); ?>
			</a>
		</header>

		<div class="grid grid-3">
			<?php
			if ( ! empty( $products ) ) {
				foreach ( $products as $product ) {
					get_template_part( 'template-parts/components/product-card', null, array( 'product' => $product ) );
				}
			} else {
				$fallback = array(
					array(
						'img'   => 'prod-coffee-sq.webp',
						'pos'   => '50% 50%',
						'meta'  => __( '100% Ethiopian · 250 g', 'cofifi' ),
						'title' => __( 'COFiFi Coffee', 'cofifi' ),
						'copy'  => __( 'Creamy, smooth and low in acidity. The everyday bag — traditional pan-roast, ground or whole bean.', 'cofifi' ),
						'price' => 'R265',
						'cbd'   => false,
						'badge' => '',
					),
					array(
						'img'   => 'prod-cbd150-sq.webp',
						'pos'   => '50% 50%',
						'meta'  => __( '100% Ethiopian · THC 150 mg · 250 g', 'cofifi' ),
						'title' => __( 'CBD + Coffee — THC 150 mg', 'cofifi' ),
						'copy'  => __( 'The same bag, handroasted in SA with CBD and 150 mg of THC. Strictly 18+.', 'cofifi' ),
						'price' => 'R395',
						'cbd'   => true,
						'badge' => __( 'Best seller', 'cofifi' ),
					),
					array(
						'img'   => 'oil-white.jpg',
						'pos'   => '50% 46%',
						'meta'  => __( 'Focus · 150 mg · 30 ml', 'cofifi' ),
						'title' => __( 'CBD Oil — Focus', 'cofifi' ),
						'copy'  => __( 'A measured daily dropper. Contains less than 0.3% THC; certificate of analysis on every batch.', 'cofifi' ),
						'price' => 'R620',
						'cbd'   => false,
						'badge' => '',
					),
				);

				foreach ( $fallback as $card ) :
					?>
					<article class="prod<?php echo $card['cbd'] ? ' prod--cbd' : ''; ?>">
						<div class="prod__media">
							<img src="<?php echo esc_url( cofifi_img( $card['img'] ) ); ?>"
							     alt="<?php echo esc_attr( $card['title'] ); ?>"
							     style="object-position:<?php echo esc_attr( $card['pos'] ); ?>"
							     width="700" height="700" loading="lazy">
							<?php if ( $card['badge'] ) : ?>
								<span class="badge prod__badge"><?php echo esc_html( $card['badge'] ); ?></span>
							<?php endif; ?>
						</div>
						<div class="prod__body">
							<p class="prod__meta"><?php echo esc_html( $card['meta'] ); ?></p>
							<h3 class="t-card"><?php echo esc_html( $card['title'] ); ?></h3>
							<p class="small"><?php echo esc_html( $card['copy'] ); ?></p>
							<div class="prod__foot">
								<span class="price"><?php echo esc_html( $card['price'] ); ?></span>
								<span class="pill"><?php esc_html_e( 'Add', 'cofifi' ); ?></span>
							</div>
						</div>
					</article>
					<?php
				endforeach;
			}
			?>
		</div>
	</div>
</section>
