<?php
/**
 * CBD spotlight.
 *
 * Copy here describes the product — carrier, spectrum, dose, testing. It must
 * never describe an effect or imply a health benefit. See the README.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="section section--cbd">
	<div class="wrap">
		<div class="grid" style="grid-template-columns:5fr 7fr;align-items:center;gap:clamp(24px,3vw,40px)">
			<div class="stack gap-22">
				<p class="eyebrow"><?php esc_html_e( 'CBD Oil — Focus', 'cofifi' ); ?></p>
				<h2 class="t-section">
					<?php esc_html_e( '150 mg.', 'cofifi' ); ?><br>
					<?php esc_html_e( 'One dropper.', 'cofifi' ); ?><br>
					<?php esc_html_e( 'No guesswork.', 'cofifi' ); ?>
				</h2>
				<p class="body-muted" style="max-width:46ch;color:#8ca39d">
					<?php esc_html_e( 'Broad-spectrum extract in a 30 ml amber bottle, with a graduated dropper so a dose is a measurement rather than a squeeze. Every batch is tested by an independent lab and the certificate is published with the batch number on the label.', 'cofifi' ); ?>
				</p>
				<div class="row gap-12" style="flex-wrap:wrap">
					<span class="tag tag--teal"><?php esc_html_e( 'Less than 0.3% THC', 'cofifi' ); ?></span>
					<span class="tag tag--teal"><?php esc_html_e( '30 ml / 1 fl oz', 'cofifi' ); ?></span>
					<span class="tag tag--teal"><?php esc_html_e( '3rd-party tested', 'cofifi' ); ?></span>
				</div>
				<div class="row gap-16" style="flex-wrap:wrap;padding-top:8px">
					<a class="btn btn--teal" href="<?php echo esc_url( cofifi_shop_url() ); ?>">
						<?php esc_html_e( 'Shop CBD oil', 'cofifi' ); ?>
						<?php cofifi_icon( 'arrow', 18 ); ?>
					</a>
					<a class="label" style="color:var(--teal-muted)" href="<?php echo esc_url( home_url( '/lab-certificates/' ) ); ?>">
						<?php esc_html_e( 'View batch certificate', 'cofifi' ); ?>
					</a>
				</div>
			</div>

			<div class="grid grid-2">
				<figure style="margin:0;overflow:hidden;border:1px solid var(--line-cbd);aspect-ratio:1/1.15">
					<img src="<?php echo esc_url( cofifi_img( 'oil-dropper.jpg' ) ); ?>"
					     alt="<?php esc_attr_e( 'COFiFi CBD oil dropper', 'cofifi' ); ?>"
					     style="width:100%;height:100%;object-fit:cover" width="620" height="620" loading="lazy">
				</figure>
				<figure style="margin:0;overflow:hidden;border:1px solid var(--line-cbd);aspect-ratio:1/1.15">
					<img src="<?php echo esc_url( cofifi_img( 'oil-box.jpg' ) ); ?>"
					     alt="<?php esc_attr_e( 'COFiFi CBD oil bottle and carton', 'cofifi' ); ?>"
					     style="width:100%;height:100%;object-fit:cover" width="700" height="701" loading="lazy">
				</figure>
			</div>
		</div>
	</div>
</section>
