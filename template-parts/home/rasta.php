<?php
/**
 * Rasta Roast — the Mada Kush sub-brand band.
 *
 * Copy describes the product and the restriction. No effect is described, and
 * no health claim is made. See the README.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

$link = cofifi_shop_url();

if ( function_exists( 'get_term_link' ) ) {
	$term = get_term_by( 'slug', 'rasta-roast', 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$url = get_term_link( $term );
		if ( ! is_wp_error( $url ) ) {
			$link = $url;
		}
	}
}
?>

<section class="split split--fade-left rasta" style="border-block:1px solid var(--line-soft)">
	<div class="split__media">
		<img src="<?php echo esc_url( cofifi_img( 'rasta-band.webp' ) ); ?>"
		     alt="<?php esc_attr_e( 'Rasta Roast Mada Kush coffee, 150 g', 'cofifi' ); ?>"
		     width="800" height="533" loading="lazy">
	</div>

	<div class="split__copy">
		<p class="eyebrow"><?php esc_html_e( 'Rasta Roast · Mada Kush', 'cofifi' ); ?></p>
		<h2 class="t-section">
			<?php esc_html_e( 'Local beans.', 'cofifi' ); ?><br>
			<?php esc_html_e( 'Hand roasted.', 'cofifi' ); ?><br>
			<?php esc_html_e( 'Infused.', 'cofifi' ); ?>
		</h2>
		<p class="body-muted" style="max-width:52ch">
			<?php esc_html_e( 'Our second line, and the only one that is not Ethiopian: delicious, creamy local coffee, hand roasted and infused with love by black families. 500 mg of THC in a 150 g bag, lab tested.', 'cofifi' ); ?>
		</p>
		<div class="row gap-12" style="flex-wrap:wrap">
			<span class="tag tag--age"><?php esc_html_e( 'Strictly 18+', 'cofifi' ); ?></span>
			<span class="tag"><?php esc_html_e( 'THC 500 mg · 150 g', 'cofifi' ); ?></span>
			<span class="tag"><?php esc_html_e( 'Lab tested', 'cofifi' ); ?></span>
		</div>
		<div class="row gap-28" style="flex-wrap:wrap;padding-top:12px">
			<a class="btn btn--outline" href="<?php echo esc_url( $link ); ?>">
				<?php esc_html_e( 'Shop Rasta Roast', 'cofifi' ); ?>
				<?php cofifi_icon( 'arrow', 16 ); ?>
			</a>
		</div>
		<p class="legal" style="max-width:56ch;padding-top:4px">
			<?php echo esc_html( cofifi_compliance_line( 'thc' ) ); ?>
		</p>
	</div>
</section>
