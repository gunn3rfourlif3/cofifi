<?php
/**
 * Header.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'cofifi' ); ?></a>

<div class="utility">
	<div class="wrap utility__inner">
		<span><?php echo esc_html( cofifi_option( 'utility_1' ) ); ?></span>
		<span class="utility__sep" aria-hidden="true">/</span>
		<span><?php echo esc_html( cofifi_option( 'utility_2' ) ); ?></span>
		<span class="utility__sep" aria-hidden="true">/</span>
		<span>
			<?php
			/* translators: %s: order value, e.g. R950. */
			printf( esc_html__( 'Free delivery over %s', 'cofifi' ), esc_html( cofifi_option( 'free_delivery' ) ) );
			?>
		</span>
	</div>
</div>

<header class="site-header">
	<div class="wrap site-header__inner">
		<div class="site-header__left">
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<img src="<?php echo esc_url( cofifi_img( 'logo-white.png' ) ); ?>"
				     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				     width="296" height="241" fetchpriority="high">
			</a>

			<nav class="nav" aria-label="<?php esc_attr_e( 'Primary', 'cofifi' ); ?>">
				<?php
				cofifi_menu( 'primary', array(
					__( 'Coffee', 'cofifi' )    => cofifi_shop_url(),
					__( 'CBD Oil', 'cofifi' )   => cofifi_shop_url(),
					__( 'Bundles', 'cofifi' )   => cofifi_shop_url(),
					__( 'Our Story', 'cofifi' ) => home_url( '/our-story/' ),
					__( 'Wholesale', 'cofifi' ) => home_url( '/wholesale/' ),
				) );
				?>
			</nav>
		</div>

		<div class="header-actions">
			<a class="label" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Account', 'cofifi' ); ?>
			</a>

			<a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'cofifi' ); ?>">
				<?php cofifi_icon( 'search', 18 ); ?>
			</a>

			<?php
			if ( function_exists( 'cofifi_cart_link' ) ) {
				cofifi_cart_link();
			}
			?>

			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-drawer">
				<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'cofifi' ); ?></span>
				<?php cofifi_icon( 'menu', 22 ); ?>
			</button>
		</div>
	</div>
</header>

<div class="nav-drawer" id="nav-drawer" hidden>
	<div class="nav-drawer__head">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo esc_url( cofifi_img( 'logo-white.png' ) ); ?>" alt="" width="296" height="241" style="height:52px;width:auto">
		</a>
		<button class="nav-toggle nav-toggle--close" type="button">
			<span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'cofifi' ); ?></span>
			<?php cofifi_icon( 'close', 22 ); ?>
		</button>
	</div>
	<nav aria-label="<?php esc_attr_e( 'Mobile', 'cofifi' ); ?>">
		<?php
		cofifi_menu( 'primary', array(
			__( 'Coffee', 'cofifi' )    => cofifi_shop_url(),
			__( 'CBD Oil', 'cofifi' )   => cofifi_shop_url(),
			__( 'Bundles', 'cofifi' )   => cofifi_shop_url(),
			__( 'Our Story', 'cofifi' ) => home_url( '/our-story/' ),
			__( 'Wholesale', 'cofifi' ) => home_url( '/wholesale/' ),
		) );
		?>
	</nav>
</div>
