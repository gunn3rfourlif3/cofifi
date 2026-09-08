<?php
/**
 * Single post — journal article.
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
				<header class="stack gap-16" style="max-width:72ch;padding-bottom:36px;margin-bottom:40px;border-bottom:1px solid var(--line)">
					<p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
					<h1 class="t-section"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure style="margin:0 0 44px;border:1px solid var(--line)">
						<?php the_post_thumbnail( 'cofifi-band', array( 'style' => 'width:100%;height:auto' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages( array( 'before' => '<div class="page-links">', 'after' => '</div>' ) );
					?>
				</div>
			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
