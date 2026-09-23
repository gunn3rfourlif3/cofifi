<?php
/**
 * The two category doors — Roastery and Apothecary.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

$doors = array(
	array(
		'class' => '',
		'img'   => 'door-coffee.webp',
		'alt'   => __( 'COFiFi coffee, 250 g bags', 'cofifi' ),
		'kicker'=> __( '01 — The Roastery', 'cofifi' ),
		'title' => __( 'COFiFi Coffee', 'cofifi' ),
		'copy'  => __( '100% Ethiopian beans, pan-roasted the traditional way by the women of our roastery. Creamy, smooth, low in acidity — with or without CBD.', 'cofifi' ),
		'cta'   => __( 'Shop coffee', 'cofifi' ),
		'link'  => 'link-arrow',
	),
	array(
		'class' => ' door--cbd',
		'img'   => 'oil-desk.jpg',
		'alt'   => __( 'COFiFi CBD oil on a desk beside a cup of coffee', 'cofifi' ),
		'kicker'=> __( '02 — The Apothecary', 'cofifi' ),
		'title' => __( 'COFiFi CBD Oil', 'cofifi' ),
		'copy'  => __( 'Focus, 150 mg in 30 ml. Third-party tested, under 0.3% THC, and made to sit alongside your morning cup rather than replace it.', 'cofifi' ),
		'cta'   => __( 'Shop CBD oil', 'cofifi' ),
		'link'  => 'link-arrow link-arrow--teal',
	),
);
?>

<section class="section">
	<div class="wrap">
		<div class="grid grid-2">
			<?php foreach ( $doors as $door ) : ?>
				<article class="door<?php echo esc_attr( $door['class'] ); ?>">
					<div class="door__media">
						<img src="<?php echo esc_url( cofifi_img( $door['img'] ) ); ?>"
						     alt="<?php echo esc_attr( $door['alt'] ); ?>"
						     width="700" height="480" loading="lazy">
					</div>
					<div class="door__body">
						<p class="eyebrow"><?php echo esc_html( $door['kicker'] ); ?></p>
						<h2 class="t-sub"><?php echo esc_html( $door['title'] ); ?></h2>
						<p class="body-muted"><?php echo esc_html( $door['copy'] ); ?></p>
						<a class="<?php echo esc_attr( $door['link'] ); ?>" href="<?php echo esc_url( cofifi_shop_url() ); ?>">
							<?php echo esc_html( $door['cta'] ); ?>
							<?php cofifi_icon( 'arrow', 16 ); ?>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
