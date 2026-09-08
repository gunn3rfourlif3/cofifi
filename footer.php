<?php
/**
 * Footer.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;
?>

<footer class="site-footer">
	<div class="wrap">
		<div class="footer-grid">
			<div class="footer-brand">
				<img src="<?php echo esc_url( cofifi_img( 'logo-white.png' ) ); ?>"
				     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				     width="296" height="241" loading="lazy">
				<p class="small" style="color:var(--dim)">
					<?php echo esc_html( cofifi_option( 'address_line_1' ) ); ?><br>
					<?php echo esc_html( cofifi_option( 'address_line_2' ) ); ?><br>
					<?php echo esc_html( cofifi_option( 'email' ) ); ?>
				</p>
			</div>

			<div class="footer-col">
				<h5><?php esc_html_e( 'Shop', 'cofifi' ); ?></h5>
				<?php
				cofifi_menu( 'footer-shop', array(
					__( 'Cofifi Coffee', 'cofifi' )      => cofifi_shop_url(),
					__( 'Coffee CBD+', 'cofifi' )        => cofifi_shop_url(),
					__( 'CBD Oil — Focus', 'cofifi' )    => cofifi_shop_url(),
					__( 'Bundles', 'cofifi' )            => cofifi_shop_url(),
				) );
				?>
			</div>

			<div class="footer-col">
				<h5><?php esc_html_e( 'Learn', 'cofifi' ); ?></h5>
				<?php
				cofifi_menu( 'footer-learn', array(
					__( 'Our story', 'cofifi' )       => home_url( '/our-story/' ),
					__( 'The ceremony', 'cofifi' )    => home_url( '/the-ceremony/' ),
					__( 'Lab certificates', 'cofifi' ) => home_url( '/lab-certificates/' ),
					__( 'Brewing', 'cofifi' )         => home_url( '/brewing/' ),
				) );
				?>
			</div>

			<div class="footer-col">
				<h5><?php esc_html_e( 'Service', 'cofifi' ); ?></h5>
				<?php
				cofifi_menu( 'footer-service', array(
					__( 'Delivery', 'cofifi' )            => home_url( '/delivery/' ),
					__( 'Returns', 'cofifi' )             => home_url( '/returns/' ),
					__( 'Manage subscription', 'cofifi' ) => home_url( '/my-account/' ),
					__( 'Contact', 'cofifi' )             => home_url( '/contact/' ),
				) );
				?>
			</div>

			<div class="footer-col">
				<h5><?php esc_html_e( 'Follow', 'cofifi' ); ?></h5>
				<?php
				cofifi_menu( 'footer-follow', array(
					__( 'Instagram', 'cofifi' ) => '#',
					__( 'TikTok', 'cofifi' )    => '#',
					__( 'Wholesale', 'cofifi' ) => home_url( '/wholesale/' ),
				) );
				?>
			</div>
		</div>

		<?php if ( is_active_sidebar( 'footer-note' ) ) : ?>
			<div class="footer-note"><?php dynamic_sidebar( 'footer-note' ); ?></div>
		<?php endif; ?>

		<div class="footer-legal">
			<p class="legal"><?php echo esc_html( cofifi_compliance_line() ); ?></p>
			<span class="legal" style="white-space:nowrap">
				<?php
				/* translators: %1$s: year, %2$s: site name. */
				printf( esc_html__( '© %1$s %2$s · Terms · Privacy', 'cofifi' ), esc_html( gmdate( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) );
				?>
			</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
