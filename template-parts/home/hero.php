<?php
/**
 * Homepage hero — packshot led.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

$trust = array(
	array( 'origin', __( '100%', 'cofifi' ), __( 'Ethiopian', 'cofifi' ) ),
	array( 'women', __( 'Roasted', 'cofifi' ), __( 'by women', 'cofifi' ) ),
	array( 'tested', __( '3rd-party', 'cofifi' ), __( 'tested', 'cofifi' ) ),
	array( 'thc', __( 'Strictly', 'cofifi' ), __( '18 and over', 'cofifi' ) ),
);
?>

<section class="hero">
	<div class="glow glow--amber hero__glow-a" aria-hidden="true"></div>
	<div class="glow glow--teal hero__glow-b" aria-hidden="true"></div>
	<div class="grain" aria-hidden="true"></div>

	<div class="wrap hero__inner">
		<div class="hero__copy">
			<p class="eyebrow eyebrow--rule"><?php esc_html_e( 'Afro Coffee &amp; Treats Co. · Est. 2022', 'cofifi' ); ?></p>

			<h1 class="t-hero">
				<?php esc_html_e( 'Premium African coffee.', 'cofifi' ); ?><br>
				<?php esc_html_e( 'Roasted with fire.', 'cofifi' ); ?><br>
				<em><?php esc_html_e( 'Rooted in ritual.', 'cofifi' ); ?></em>
			</h1>

			<p class="lede">
				<?php esc_html_e( 'Fresh, artisanal coffee inspired by the ancient Ethiopian coffee ceremony — crafted to reconnect people, revive culture and create community.', 'cofifi' ); ?>
			</p>

			<div class="hero__cta">
				<a class="btn btn--bone" href="<?php echo esc_url( cofifi_shop_url() ); ?>">
					<?php esc_html_e( 'Shop the coffee', 'cofifi' ); ?>
					<?php cofifi_icon( 'arrow', 18 ); ?>
				</a>
				<a class="btn btn--ghost" href="<?php echo esc_url( cofifi_shop_url() ); ?>">
					<?php esc_html_e( 'Explore CBD oil', 'cofifi' ); ?>
				</a>
			</div>

			<ul class="trust" style="list-style:none;margin:0;padding:0">
				<?php foreach ( $trust as $item ) : ?>
					<li class="trust__item">
						<?php cofifi_icon( $item[0], 22 ); ?>
						<span><?php echo esc_html( $item[1] ); ?><br><?php echo esc_html( $item[2] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="packshot">
			<span class="packshot__ring packshot__ring--lg" aria-hidden="true"></span>
			<span class="packshot__ring packshot__ring--sm" aria-hidden="true"></span>

			<img class="packshot__img"
			     src="<?php echo esc_url( cofifi_img( 'pack-hero.webp' ) ); ?>"
			     alt="<?php esc_attr_e( 'COFiFi Coffee — 250 g, 100% Ethiopian', 'cofifi' ); ?>"
			     width="1000" height="1603" fetchpriority="high">

			<span class="packshot__floor" aria-hidden="true"></span>

			<img class="packshot__reflection"
			     src="<?php echo esc_url( cofifi_img( 'pack-hero.webp' ) ); ?>"
			     alt="" aria-hidden="true" width="1000" height="1603" loading="lazy">

			<p class="packshot__caption"><?php esc_html_e( '250 g · Roasted to order', 'cofifi' ); ?></p>
		</div>
	</div>
</section>
