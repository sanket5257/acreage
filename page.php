<?php defined( 'ABSPATH' ) || exit; get_header(); ?>

<div class="acreage-wrap acreage-page acreage-page--narrow">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'acreage-article' ); ?>>
			<?php if ( ! is_front_page() ) : ?>
				<header class="acreage-page__head">
					<h1 class="acreage-page__title"><?php the_title(); ?></h1>
				</header>
			<?php endif; ?>
			<div class="acreage-prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
