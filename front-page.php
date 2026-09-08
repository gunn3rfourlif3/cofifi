<?php
/**
 * Homepage.
 *
 * Section order is the designed funnel — hero, the two category doors, the
 * range, the story, the CBD spotlight, why Cofifi, subscription, newsletter.
 * Changing the order changes the argument the page makes, so don't reorder
 * without a reason.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main">
	<?php
	get_template_part( 'template-parts/home/hero' );
	get_template_part( 'template-parts/home/doors' );
	get_template_part( 'template-parts/home/range' );
	get_template_part( 'template-parts/home/story' );
	get_template_part( 'template-parts/home/cbd' );
	get_template_part( 'template-parts/home/why' );
	get_template_part( 'template-parts/home/subscribe' );
	get_template_part( 'template-parts/home/newsletter' );
	?>
</main>

<?php
get_footer();
