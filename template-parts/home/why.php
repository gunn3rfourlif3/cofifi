<?php
/**
 * Why Cofifi — four numbered columns.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

$values = array(
	array( __( 'Single origin', 'cofifi' ), __( '100% Ethiopian beans, bought in small lots so a bag tastes like the harvest it came from.', 'cofifi' ) ),
	array( __( 'Roasted by women', 'cofifi' ), __( 'Pan-roasted the traditional way by the women who have done it their whole lives, and paid for it properly.', 'cofifi' ) ),
	array( __( 'Tested, not claimed', 'cofifi' ), __( 'Every CBD and THC batch goes to an independent lab. The certificate is published against the batch number.', 'cofifi' ) ),
	array( __( 'Made to share', 'cofifi' ), __( 'Bags sized for a household, not a single cup — because the ceremony was never meant for one.', 'cofifi' ) ),
);
?>

<section class="section section--tight">
	<div class="wrap">
		<div class="grid grid-4">
			<?php foreach ( $values as $i => $value ) : ?>
				<div class="value<?php echo 0 === $i ? ' value--lead' : ''; ?>">
					<span class="value__n"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h4><?php echo esc_html( $value[0] ); ?></h4>
					<p class="small"><?php echo esc_html( $value[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
