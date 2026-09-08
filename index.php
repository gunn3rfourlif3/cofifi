<?php
/**
 * Fallback template — also the blog / journal index.
 *
 * @package Cofifi
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main section">
	<div class="wrap">
		<header class="section-head">
			<div class="section-head__title">
				<p class="eyebrow"><?php esc_html_e( 'Journal', 'cofifi' ); ?></p>
				<h1 class="t-section">
					<?php
					if ( is_home() && ! is_front_page() ) {
						echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) );
					} elseif ( is_archive() ) {
						the_archive_title();
					} elseif ( is_search() ) {
						/* translators: %s: search term. */
						printf( esc_html__( 'Results for “%s”', 'cofifi' ), esc_html( get_search_query() ) );
					} else {
						esc_html_e( 'Latest', 'cofifi' );
					}
					?>
				</h1>
			</div>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="grid grid-3">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'prod' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="prod__media" href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'cofifi-card', array( 'loading' => 'lazy' ) ); ?>
							</a>
						<?php endif; ?>
						<div class="prod__body">
							<p class="prod__meta"><?php echo esc_html( get_the_date() ); ?></p>
							<h2 class="t-card">
								<a href="<?php the_permalink(); ?>" style="color:inherit"><?php the_title(); ?></a>
							</h2>
							<p class="small"><?php echo esc_html( get_the_excerpt() ); ?></p>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<div style="padding-top:48px">
				<?php
				the_posts_pagination( array(
					'mid_size'  => 1,
					'prev_text' => esc_html__( 'Previous', 'cofifi' ),
					'next_text' => esc_html__( 'Next', 'cofifi' ),
				) );
				?>
			</div>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'Nothing here yet.', 'cofifi' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
