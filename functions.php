<?php
/**
 * Cofifi theme bootstrap.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

define( 'COFIFI_VERSION', '0.1.0' );
define( 'COFIFI_DIR', get_template_directory() );
define( 'COFIFI_URI', get_template_directory_uri() );

require_once COFIFI_DIR . '/inc/setup.php';
require_once COFIFI_DIR . '/inc/enqueue.php';
require_once COFIFI_DIR . '/inc/template-tags.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once COFIFI_DIR . '/inc/woocommerce.php';
}
