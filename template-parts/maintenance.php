<?php
/**
 * The holding page.
 *
 * A whole document, not a template part in the usual sense — it is printed in
 * place of the site, so it must not pull in the header, the nav or the cart.
 * It borrows the theme's stylesheet and nothing else.
 *
 * @var array|null $notice Result of a signup attempt, from the gate.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

$css    = COFIFI_DIR . '/assets/css/theme.css';
$line   = cofifi_option( 'maintenance_line' );
$email  = cofifi_option( 'email' );
$action = apply_filters( 'cofifi_newsletter_action', '' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( sprintf( /* translators: %s: site name. */ __( '%s — opening soon', 'cofifi' ), get_bloginfo( 'name' ) ) ); ?></title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" href="<?php echo esc_url( COFIFI_URI . '/assets/fonts/Amulya-Variable.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&display=swap">
	<link rel="stylesheet" href="<?php echo esc_url( COFIFI_URI . '/assets/css/theme.css?v=' . ( file_exists( $css ) ? filemtime( $css ) : COFIFI_VERSION ) ); ?>">
	<?php wp_site_icon(); ?>
</head>

<body class="maint-body">

<main class="maint">
	<div class="glow glow--amber maint__glow" aria-hidden="true"></div>
	<div class="grain" aria-hidden="true"></div>

	<div class="maint__inner">
		<img class="maint__logo"
		     src="<?php echo esc_url( cofifi_img( 'logo-white.png' ) ); ?>"
		     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
		     width="296" height="241">

		<p class="eyebrow eyebrow--rule"><?php esc_html_e( 'Afro Coffee &amp; Treats Co. · Est. 2022', 'cofifi' ); ?></p>

		<h1 class="t-hero">
			<?php esc_html_e( 'Something is', 'cofifi' ); ?><br>
			<em><?php esc_html_e( 'roasting.', 'cofifi' ); ?></em>
		</h1>

		<p class="lede maint__lede">
			<?php esc_html_e( '100% Ethiopian beans, pan-roasted the traditional way by the women of our roastery. The shop is not open yet.', 'cofifi' ); ?>
			<?php if ( $line ) : ?>
				<br><?php echo esc_html( $line ); ?>
			<?php endif; ?>
		</p>

		<?php if ( ! empty( $notice ) ) : ?>
			<p class="maint__notice maint__notice--<?php echo esc_attr( $notice['type'] ); ?>" role="status">
				<?php echo esc_html( $notice['text'] ); ?>
			</p>
		<?php else : ?>
			<form class="subscribe-form maint__form" method="post" action="<?php echo esc_url( $action ); ?>">
				<label class="screen-reader-text" for="cofifi-email"><?php esc_html_e( 'Email address', 'cofifi' ); ?></label>
				<input type="email" id="cofifi-email" name="email" required
				       placeholder="<?php esc_attr_e( 'your@email.com', 'cofifi' ); ?>"
				       autocomplete="email">
				<button class="btn btn--teal" type="submit"><?php esc_html_e( 'Tell me when', 'cofifi' ); ?></button>

				<?php wp_nonce_field( 'cofifi_signup', 'cofifi_signup' ); ?>
				<p class="maint__pot" aria-hidden="true">
					<label for="cofifi-website"><?php esc_html_e( 'Leave this empty', 'cofifi' ); ?></label>
					<input type="text" id="cofifi-website" name="cofifi_website" tabindex="-1" autocomplete="off">
				</p>
			</form>

			<p class="small maint__small"><?php esc_html_e( 'One email, when the shop opens. Nothing else.', 'cofifi' ); ?></p>
		<?php endif; ?>

		<?php if ( $email ) : ?>
			<p class="legal maint__foot">
				<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<span aria-hidden="true"> · </span>
				<?php echo esc_html( sprintf( '© %1$s %2$s', gmdate( 'Y' ), cofifi_option( 'legal_name' ) ) ); ?>
			</p>
		<?php endif; ?>
	</div>
</main>

</body>
</html>
