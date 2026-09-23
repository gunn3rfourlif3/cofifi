<?php
/**
 * The gallery strip — a mosaic of six frames leading to the full gallery.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

$picks = cofifi_gallery_pick( array( 'g01', 'g05', 'g07', 'g02', 'g06', 'g04' ) );

if ( ! $picks ) {
	return;
}
?>

<section class="section section--alt gal-strip">
	<div class="grain" aria-hidden="true"></div>

	<div class="wrap">
		<div class="section-head">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'The gallery', 'cofifi' ); ?></p>
				<h2 class="t-section"><?php esc_html_e( 'Every bag, photographed.', 'cofifi' ); ?></h2>
			</div>
			<a class="link-arrow" href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">
				<?php esc_html_e( 'View all photographs', 'cofifi' ); ?>
				<?php cofifi_icon( 'arrow', 16 ); ?>
			</a>
		</div>

		<div class="gal-mosaic">
			<?php
			foreach ( $picks as $i => $item ) {
				cofifi_gallery_tile( $item, $i, 0 === $i ? 'gal--feature' : '' );
			}
			?>
		</div>
	</div>

	<?php cofifi_lightbox(); ?>
</section>
