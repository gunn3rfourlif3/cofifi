<?php
/**
 * The standing order band.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="section" style="padding-top:0">
	<div class="wrap">
		<div class="split split--fade-right" style="border:1px solid var(--line)">
			<div class="split__copy" style="background:var(--surface)">
				<p class="eyebrow"><?php esc_html_e( 'The standing order', 'cofifi' ); ?></p>
				<h2 class="t-sub">
					<?php esc_html_e( 'Never run out', 'cofifi' ); ?><br>
					<?php esc_html_e( 'mid-ceremony', 'cofifi' ); ?>
				</h2>
				<p class="body-muted" style="max-width:48ch">
					<?php esc_html_e( 'Pick your bag and your rhythm — every two, four or six weeks. Skip, swap or pause from the link in every dispatch email. No account maze, no phone call.', 'cofifi' ); ?>
				</p>
				<div class="row gap-22" style="flex-wrap:wrap;padding-top:8px">
					<a class="btn btn--bone" href="<?php echo esc_url( cofifi_shop_url() ); ?>">
						<?php esc_html_e( 'Build your order', 'cofifi' ); ?>
						<?php cofifi_icon( 'arrow', 18 ); ?>
					</a>
					<span class="label" style="color:var(--dim)"><?php esc_html_e( 'Save 15% · delivery included', 'cofifi' ); ?></span>
				</div>
			</div>
			<div class="split__media">
				<img src="<?php echo esc_url( cofifi_img( 'sharing.jpg' ) ); ?>"
				     alt="<?php esc_attr_e( 'Two cups of coffee shared', 'cofifi' ); ?>"
				     width="800" height="600" loading="lazy">
			</div>
		</div>
	</div>
</section>
