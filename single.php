<?php
/**
 * A single blog post.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="acreage-wrap acreage-page <?php echo acreage_has_sidebar() ? 'acreage-page--has-sidebar' : 'acreage-page--narrow'; ?>">

	<div class="acreage-page__main">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'acreage-article' ); ?>>

				<header class="acreage-page__head">
					<h1 class="acreage-page__title"><?php the_title(); ?></h1>
					<?php acreage_posted_on(); ?>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="acreage-article__media">
						<?php the_post_thumbnail( 'acreage-hero' ); ?>
						<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
							<figcaption><?php echo esc_html( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<div class="acreage-prose"><?php the_content(); ?></div>

				<?php
				// Splits a long post across several URLs when the editor used <!--nextpage-->.
				wp_link_pages(
					array(
						'before' => '<nav class="acreage-pagelinks" aria-label="' . esc_attr__( 'Page', 'acreage' ) . '">',
						'after'  => '</nav>',
					)
				);
				?>

				<?php acreage_entry_footer(); ?>
			</article>

			<?php acreage_hook( 'after_post', get_the_ID() ); ?>

			<?php
			the_post_navigation(
				array(
					'prev_text'          => '<span class="acreage-meta">' . esc_html__( 'Previous', 'acreage' ) . '</span> %title',
					'next_text'          => '<span class="acreage-meta">' . esc_html__( 'Next', 'acreage' ) . '</span> %title',
					'screen_reader_text' => esc_html__( 'Post navigation', 'acreage' ),
				)
			);
			?>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
			<?php
		endwhile;
		?>

	</div>

	<?php get_sidebar(); ?>

</div>

<?php
get_footer();
