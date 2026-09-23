<?php
/**
 * Template Name: Gallery
 *
 * Every photograph in assets/img/gallery/, in manifest order, with a lightbox.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

$items = cofifi_gallery_items();

get_header();
?>

<main id="main" class="site-main">

	<section class="section section--tight gal-head">
		<div class="glow glow--amber gal-head__glow" aria-hidden="true"></div>
		<div class="grain" aria-hidden="true"></div>
		<div class="wrap stack gap-22">
			<p class="eyebrow eyebrow--rule"><?php esc_html_e( 'Afro Coffee &amp; Treats Co.', 'cofifi' ); ?></p>
			<h1 class="t-hero"><?php the_title(); ?></h1>
			<?php if ( trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
				<div class="lede" style="max-width:56ch"><?php the_content(); ?></div>
			<?php else : ?>
				<p class="lede" style="max-width:56ch">
					<?php esc_html_e( 'The range as it actually looks — bags, labels, grind and pour, photographed on the bench rather than rendered.', 'cofifi' ); ?>
				</p>
			<?php endif; ?>
			<p class="label" style="color:var(--dim)">
				<?php
				printf(
					/* translators: %s: number of photographs. */
					esc_html( _n( '%s photograph', '%s photographs', count( $items ), 'cofifi' ) ),
					esc_html( number_format_i18n( count( $items ) ) )
				);
				?>
			</p>
		</div>
	</section>

	<section class="section" style="padding-top:0">
		<div class="wrap">
			<div class="gal-grid">
				<?php
				foreach ( $items as $i => $item ) {
					cofifi_gallery_tile( $item, $i, '', $i < 3 );
				}
				?>
			</div>
		</div>
	</section>

	<?php cofifi_lightbox(); ?>
</main>

<?php
get_footer();
