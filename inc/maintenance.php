<?php
/**
 * Maintenance mode — live, but closed.
 *
 * The front end returns 503 with a holding page. Signed-in staff and anyone
 * holding the preview link see the real shop. wp-admin, wp-login, AJAX, REST,
 * cron and WooCommerce's payment callbacks are never touched: `template_redirect`
 * only fires on a front-end page load, which is exactly the surface we want shut.
 *
 * Switch it on in Customize → COFiFi details, or force it either way from
 * wp-config.php with `define( 'COFIFI_MAINTENANCE', true );` — a constant beats
 * the setting, so a broken database cannot leave the shop open.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

const COFIFI_PREVIEW_COOKIE = 'cofifi_preview';
const COFIFI_WAITLIST       = 'cofifi_waitlist';

/**
 * Is the shop closed?
 *
 * @return bool
 */
function cofifi_maintenance_on() {
	if ( defined( 'COFIFI_MAINTENANCE' ) ) {
		return (bool) COFIFI_MAINTENANCE;
	}

	/**
	 * Filter whether maintenance mode is active.
	 *
	 * @param bool $on Active.
	 */
	return (bool) apply_filters( 'cofifi_maintenance_on', get_theme_mod( 'cofifi_maintenance', false ) );
}

/**
 * The preview key, generated once and kept in the theme mods.
 *
 * @return string
 */
function cofifi_preview_key() {
	$key = get_theme_mod( 'cofifi_maintenance_key' );

	if ( ! $key ) {
		$key = wp_generate_password( 20, false );
		set_theme_mod( 'cofifi_maintenance_key', $key );
	}

	return $key;
}

/**
 * The shareable preview link.
 *
 * @return string
 */
function cofifi_preview_url() {
	return add_query_arg( 'cofifi-preview', cofifi_preview_key(), home_url( '/' ) );
}

/**
 * May this visitor see the real site?
 *
 * @return bool
 */
function cofifi_maintenance_may_pass() {
	if ( ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() ) {
		return true;
	}

	// Anyone who can write a post can see what they are writing it for.
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return true;
	}

	if ( isset( $_COOKIE[ COFIFI_PREVIEW_COOKIE ] ) ) {
		$given = sanitize_text_field( wp_unslash( $_COOKIE[ COFIFI_PREVIEW_COOKIE ] ) );
		if ( hash_equals( wp_hash( cofifi_preview_key() ), $given ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Trade `?cofifi-preview=<key>` for a cookie, then drop the key from the URL so
 * it does not end up in a screenshot, a referrer header or the browser history.
 *
 * Deliberately not `?preview=` — WordPress owns that one for post previews.
 */
function cofifi_maintenance_claim_preview() {
	if ( empty( $_GET['cofifi-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$given = sanitize_text_field( wp_unslash( $_GET['cofifi-preview'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( hash_equals( cofifi_preview_key(), $given ) ) {
		setcookie(
			COFIFI_PREVIEW_COOKIE,
			wp_hash( cofifi_preview_key() ),
			array(
				'expires'  => time() + WEEK_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	wp_safe_redirect( remove_query_arg( 'cofifi-preview' ) );
	exit;
}
add_action( 'template_redirect', 'cofifi_maintenance_claim_preview', 0 );

/**
 * Shut the front end.
 */
function cofifi_maintenance_gate() {
	if ( ! cofifi_maintenance_on() || cofifi_maintenance_may_pass() ) {
		return;
	}

	$notice = cofifi_maintenance_handle_signup();

	nocache_headers();
	status_header( 503 );
	header( 'Retry-After: 3600' );
	header( 'X-Robots-Tag: noindex, nofollow', true );

	// $notice is read by the template.
	require COFIFI_DIR . '/template-parts/maintenance.php';
	exit;
}
add_action( 'template_redirect', 'cofifi_maintenance_gate', 1 );

/**
 * Keep the admin bar honest about it.
 */
function cofifi_maintenance_admin_bar( $bar ) {
	if ( ! cofifi_maintenance_on() || ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$bar->add_node( array(
		'id'    => 'cofifi-maintenance',
		'title' => __( '● Shop closed to the public', 'cofifi' ),
		'href'  => admin_url( 'customize.php' ),
		'meta'  => array( 'title' => __( 'Maintenance mode is on. Only staff and the preview link get through.', 'cofifi' ) ),
	) );
}
add_action( 'admin_bar_menu', 'cofifi_maintenance_admin_bar', 100 );

/* -------------------------------------------------------------------------
 * The waiting list
 * ---------------------------------------------------------------------- */

/**
 * Take a signup off the holding page.
 *
 * Stored locally unless something has filtered `cofifi_newsletter_action` to a
 * list provider, in which case the form posts straight there and this never
 * runs. The option is NOT autoloaded — it is read on two screens, never on a
 * page view.
 *
 * @return array{type:string,text:string}|null
 */
function cofifi_maintenance_handle_signup() {
	if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : '' ) ) {
		return null;
	}

	if ( ! isset( $_POST['cofifi_signup'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cofifi_signup'] ) ), 'cofifi_signup' ) ) {
		return null;
	}

	// Honeypot. A person never fills this in; a bot fills in everything.
	if ( ! empty( $_POST['cofifi_website'] ) ) {
		return array( 'type' => 'ok', 'text' => __( 'Thank you — we will be in touch.', 'cofifi' ) );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		return array( 'type' => 'error', 'text' => __( 'That address does not look right. Try again?', 'cofifi' ) );
	}

	$list = get_option( COFIFI_WAITLIST, array() );

	if ( ! is_array( $list ) ) {
		$list = array();
	}

	$key = strtolower( $email );

	if ( ! isset( $list[ $key ] ) ) {
		if ( count( $list ) >= 20000 ) {
			return array( 'type' => 'error', 'text' => __( 'The list is full. Please email us instead.', 'cofifi' ) );
		}

		$list[ $key ] = gmdate( 'c' );
		update_option( COFIFI_WAITLIST, $list, false );

		/**
		 * Fires when someone joins the waiting list.
		 *
		 * @param string $email The address.
		 */
		do_action( 'cofifi_waitlist_signup', $email );
	}

	return array( 'type' => 'ok', 'text' => __( 'You are on the list. We will write once, when the shop opens.', 'cofifi' ) );
}

/**
 * Somewhere to read the list, and a way to get it out again.
 */
function cofifi_waitlist_menu() {
	add_management_page(
		__( 'COFiFi waiting list', 'cofifi' ),
		__( 'COFiFi waiting list', 'cofifi' ),
		'manage_options',
		'cofifi-waitlist',
		'cofifi_waitlist_screen'
	);
}
add_action( 'admin_menu', 'cofifi_waitlist_menu' );

/**
 * The waiting-list screen.
 */
function cofifi_waitlist_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$list = get_option( COFIFI_WAITLIST, array() );
	$list = is_array( $list ) ? $list : array();

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'COFiFi waiting list', 'cofifi' ) . '</h1>';

	printf(
		'<p>%s</p>',
		esc_html( sprintf(
			/* translators: %s: number of addresses. */
			_n( '%s address collected from the holding page.', '%s addresses collected from the holding page.', count( $list ), 'cofifi' ),
			number_format_i18n( count( $list ) )
		) )
	);

	if ( $list ) {
		printf(
			'<p><a class="button button-primary" href="%s">%s</a></p>',
			esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cofifi_waitlist_csv' ), 'cofifi_waitlist_csv' ) ),
			esc_html__( 'Download CSV', 'cofifi' )
		);

		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Email', 'cofifi' ) . '</th><th>' . esc_html__( 'Joined', 'cofifi' ) . '</th></tr></thead><tbody>';
		foreach ( array_reverse( $list, true ) as $email => $when ) {
			printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( $email ), esc_html( $when ) );
		}
		echo '</tbody></table>';
	}

	echo '</div>';
}

/**
 * CSV of the waiting list.
 */
function cofifi_waitlist_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'cofifi' ), 403 );
	}

	check_admin_referer( 'cofifi_waitlist_csv' );

	$list = get_option( COFIFI_WAITLIST, array() );
	$list = is_array( $list ) ? $list : array();

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=cofifi-waiting-list-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'email', 'joined_utc' ) );

	foreach ( $list as $email => $when ) {
		fputcsv( $out, array( $email, $when ) );
	}

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_cofifi_waitlist_csv', 'cofifi_waitlist_csv' );
