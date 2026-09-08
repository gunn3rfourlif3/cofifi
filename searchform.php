<?php
/**
 * Search form.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="subscribe-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="s"><?php esc_html_e( 'Search', 'cofifi' ); ?></label>
	<input type="search" id="s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
	       placeholder="<?php esc_attr_e( 'Search', 'cofifi' ); ?>"
	       style="flex:1 1 auto;min-height:56px;padding:16px 20px;background:transparent;border:1px solid #3a3a3a;border-right:0;color:var(--bone);font-family:var(--font-body);font-size:14px">
	<button class="btn btn--bone" type="submit"><?php esc_html_e( 'Go', 'cofifi' ); ?></button>
</form>
