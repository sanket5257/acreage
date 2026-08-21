<?php defined( 'ABSPATH' ) || exit; ?>
<article <?php post_class( 'acreage-card' ); ?>>
	<a class="acreage-card__link" href="<?php the_permalink(); ?>">
		<div class="acreage-card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'acreage-card', array( 'loading' => 'lazy' ) ); ?>
			<?php else : ?>
				<span class="acreage-card__placeholder" aria-hidden="true"></span>
			<?php endif; ?>
		</div>
		<div class="acreage-card__body">
			<h2 class="acreage-card__title"><?php the_title(); ?></h2>
			<p class="acreage-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
			<span class="acreage-card__more"><?php esc_html_e( 'View details', 'acreage' ); ?></span>
		</div>
	</a>
</article>
