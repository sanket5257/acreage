<?php
/**
 * Farms for Sale — the listings archive.
 *
 * If a page has been assigned to the "Farms for Sale page" slot under
 * Appearance > Elementor Layout, that layout is rendered here and the widgets
 * inside it read the current, filtered query. Otherwise the theme falls back to
 * its own card grid, so the archive works with no page builder at all.
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( acreage_do_location( 'archive' ) ) {
	get_footer();
	return;
}
?>

<div class="acreage-sec acreage-pad">
	<div class="acreage-sec__head">
		<div>
			<h1 class="acreage-h2">
				<?php
				/*
				 * search.php hands a farm keyword search to this template, so
				 * that a search lands on the page with the filters on it rather
				 * than on the generic list. The heading has to say which of the
				 * two it is: "Farms for Sale" over eleven results for "kudu"
				 * reads as the whole archive having quietly lost fifty farms.
				 */
				if ( is_search() ) {
					printf(
						/* translators: %s: the keyword searched for. */
						esc_html__( 'Results for “%s”', 'acreage' ),
						esc_html( get_search_query() )
					);
				} else {
					post_type_archive_title();
				}
				?>
			</h1>
			<p class="acreage-sub">
				<?php
				global $wp_query;
				printf(
					/* translators: %s: number of farms matching the current filters. */
					esc_html( _n( '%s farm', '%s farms', (int) $wp_query->found_posts, 'acreage' ) ),
					esc_html( number_format_i18n( $wp_query->found_posts ) )
				);
				?>
			</p>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="acreage-cards">
			<?php
			while ( have_posts() ) :
				the_post();
				$acreage_id       = get_the_ID();
				$acreage_price    = (float) get_post_meta( $acreage_id, 'acreage_price', true );
				$acreage_hectares = (float) get_post_meta( $acreage_id, 'acreage_hectares', true );
				$acreage_status   = acreage_home_first_term( $acreage_id, 'status' );
				$acreage_province = acreage_home_first_term( $acreage_id, 'province' );
				?>
				<article class="acreage-c">
					<a class="acreage-c__media" href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
						<?php if ( $acreage_status ) : ?>
							<span class="acreage-c__badge"><?php echo esc_html( $acreage_status ); ?></span>
						<?php endif; ?>
					</a>
					<div class="acreage-c__body">
						<?php if ( $acreage_province ) : ?>
							<div class="acreage-c__meta"><span><?php echo esc_html( $acreage_province ); ?></span></div>
						<?php endif; ?>
						<h2 class="acreage-c__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div>
							<div class="acreage-c__price">
								<?php echo esc_html( $acreage_price > 0 ? 'R' . number_format_i18n( $acreage_price ) : __( 'Price on application', 'acreage' ) ); ?>
							</div>
							<?php if ( $acreage_price > 0 ) : ?>
								<div class="acreage-c__vat"><?php esc_html_e( 'Excludes VAT if applicable', 'acreage' ); ?></div>
							<?php endif; ?>
						</div>
						<div class="acreage-c__foot">
							<span class="acreage-c__ha">
								<?php
								echo $acreage_hectares > 0
									/* translators: %s: hectares. */
									? esc_html( sprintf( __( '%s ha', 'acreage' ), number_format_i18n( $acreage_hectares ) ) )
									: '';
								?>
							</span>
							<span class="acreage-c__links">
								<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'View listing', 'acreage' ); ?></a>
							</span>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination( array( 'class' => 'acreage-pagination', 'mid_size' => 2 ) ); ?>

	<?php else : ?>
		<p><?php esc_html_e( 'No farms match that combination. Try widening one of the filters.', 'acreage' ); ?></p>
		<p><a class="acreage-btn-o" href="<?php echo esc_url( acreage_farms_url() ); ?>"><?php esc_html_e( 'Clear the filters', 'acreage' ); ?></a></p>
	<?php endif; ?>
</div>

<?php
get_footer();
