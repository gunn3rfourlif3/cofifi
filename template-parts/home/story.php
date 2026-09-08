<?php
/**
 * Rooted in ritual — the story band.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="split split--fade-left" style="border-block:1px solid var(--line-soft)">
	<div class="split__media">
		<img src="<?php echo esc_url( cofifi_img( 'roasting.jpg' ) ); ?>"
		     alt="<?php esc_attr_e( 'Coffee roasting drum', 'cofifi' ); ?>"
		     width="760" height="506" loading="lazy">
	</div>

	<div class="split__copy">
		<p class="eyebrow"><?php esc_html_e( 'Our story', 'cofifi' ); ?></p>
		<h2 class="t-section">
			<?php esc_html_e( 'Roasted with fire.', 'cofifi' ); ?><br>
			<?php esc_html_e( 'Rooted in ritual.', 'cofifi' ); ?>
		</h2>
		<p class="body-muted" style="max-width:52ch">
			<?php esc_html_e( 'In Ethiopia, coffee is not a drink you grab. It is roasted in front of you, ground by hand and poured three times — a ceremony that has gathered people for centuries.', 'cofifi' ); ?>
		</p>
		<p class="body-muted" style="max-width:52ch">
			<?php esc_html_e( 'Cofifi was founded in 2022 to carry that ritual forward: beans roasted by women in the traditional way, packed in small batches, and sent to people who would rather sit down with a cup than run out the door with one.', 'cofifi' ); ?>
		</p>
		<div class="row gap-28" style="flex-wrap:wrap;padding-top:12px">
			<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/our-story/' ) ); ?>">
				<?php esc_html_e( 'Read our story', 'cofifi' ); ?>
				<?php cofifi_icon( 'arrow', 16 ); ?>
			</a>
			<span class="label" style="color:var(--dim)"><?php esc_html_e( 'Est. 2022', 'cofifi' ); ?></span>
		</div>
	</div>
</section>
