<?php
/**
 * 404.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main section">
	<div class="wrap stack gap-22" style="max-width:56ch">
		<p class="eyebrow eyebrow--rule"><?php esc_html_e( 'Error 404', 'cofifi' ); ?></p>
		<h1 class="t-section"><?php esc_html_e( 'That page has gone cold', 'cofifi' ); ?></h1>
		<p class="lede"><?php esc_html_e( 'The page you were after is not here. The coffee still is.', 'cofifi' ); ?></p>
		<div class="row gap-16" style="flex-wrap:wrap;padding-top:8px">
			<a class="btn btn--bone" href="<?php echo esc_url( cofifi_shop_url() ); ?>">
				<?php esc_html_e( 'Shop the range', 'cofifi' ); ?>
				<?php cofifi_icon( 'arrow', 18 ); ?>
			</a>
			<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back home', 'cofifi' ); ?>
			</a>
		</div>
	</div>
</main>

<?php
get_footer();
