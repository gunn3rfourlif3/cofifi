<?php
/**
 * Single page.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main section">
	<div class="wrap">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<header class="stack gap-12" style="padding-bottom:36px;margin-bottom:40px;border-bottom:1px solid var(--line)">
					<h1 class="t-section"><?php the_title(); ?></h1>
				</header>
				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages( array( 'before' => '<div class="page-links">', 'after' => '</div>' ) );
					?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
