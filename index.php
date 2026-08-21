<?php
/**
 * The fallback template.
 *
 * WordPress picks a template by walking a fixed list from most specific to least
 * — for a category archive it tries category-slug.php, category-id.php,
 * category.php, archive.php, then this. index.php is the last stop and the only
 * file a theme is actually required to have, which is why it must handle any
 * kind of list of posts, not just the blog.
 *
 * archive.php and search.php defer to this file rather than duplicating it.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="acreage-wrap acreage-page <?php echo acreage_has_sidebar() ? 'acreage-page--has-sidebar' : ''; ?>">

	<div class="acreage-page__main">

		<?php if ( have_posts() ) : ?>

			<header class="acreage-page__head">
				<h1 class="acreage-page__title">
					<?php
					if ( is_home() && ! is_front_page() ) {
						single_post_title();
					} elseif ( is_search() ) {
						printf(
							/* translators: %s: search term. */
							esc_html__( 'Results for “%s”', 'acreage' ),
							esc_html( get_search_query() )
						);
					} else {
						the_archive_title();
					}
					?>
				</h1>

				<?php if ( ! is_search() && ! is_home() ) : ?>
					<?php the_archive_description( '<div class="acreage-page__intro">', '</div>' ); ?>
				<?php endif; ?>
			</header>

			<div class="acreage-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'class'              => 'acreage-pagination',
					'mid_size'           => 2,
					'screen_reader_text' => esc_html__( 'Posts navigation', 'acreage' ),
				)
			);
			?>

		<?php else : ?>

			<?php get_template_part( 'template-parts/content', 'none' ); ?>

		<?php endif; ?>

	</div>

	<?php get_sidebar(); ?>

</div>

<?php
get_footer();
