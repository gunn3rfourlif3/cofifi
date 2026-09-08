<?php
/**
 * Newsletter strip.
 *
 * The form posts nowhere yet — wire it to your list provider (or a WP form
 * plugin) and replace the action. It is marked up so that is a one-line change.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filter the newsletter form action URL.
 *
 * @param string $action Form action.
 */
$action = apply_filters( 'cofifi_newsletter_action', '' );
?>

<section class="section section--tight" style="padding-top:0">
	<div class="wrap">
		<div class="row-between gap-40" style="align-items:flex-end;flex-wrap:wrap;padding-top:clamp(36px,4vw,52px);border-top:1px solid var(--line)">
			<div class="stack gap-12" style="max-width:520px">
				<h2 class="t-sub"><?php esc_html_e( 'Join the ceremony', 'cofifi' ); ?></h2>
				<p class="small"><?php esc_html_e( 'New batches, brewing notes and the odd Ethiopian recipe. Twice a month, never more.', 'cofifi' ); ?></p>
			</div>

			<form class="subscribe-form" method="post" action="<?php echo esc_url( $action ); ?>">
				<label class="screen-reader-text" for="cofifi-email"><?php esc_html_e( 'Email address', 'cofifi' ); ?></label>
				<input type="email" id="cofifi-email" name="email" required
				       placeholder="<?php esc_attr_e( 'your@email.com', 'cofifi' ); ?>"
				       autocomplete="email">
				<button class="btn btn--teal" type="submit"><?php esc_html_e( 'Join', 'cofifi' ); ?></button>
			</form>
		</div>
	</div>
</section>
