<?php
/**
 * Template Name: Home — Africa Game Farms
 *
 * A faithful port of the approved homepage mockup: hero, overlapping search
 * panel, recently listed, browse by province, the two category panels, about
 * with its stat block, the sell band, and the footer.
 *
 * Everything that is data — farms, province counts, category counts — comes
 * from the acreage-listings plugin. Everything that is copy is editable under
 * Appearance > Homepage Content, so the client never needs a developer to
 * change a sentence.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$acreage_farms     = acreage_home_farms( 3 );
$acreage_provinces = acreage_home_provinces( 12 );
$acreage_live      = acreage_home_live_count();
?>

<section class="acreage-hero">
	<div class="acreage-hero__frame">
		<img
			src="<?php echo esc_url( acreage_option_image( 'hero_image', 'hero.jpg' ) ); ?>"
			alt="<?php echo esc_attr( acreage_option( 'hero_alt', __( 'Bushveld at dawn in the Waterberg, Limpopo', 'acreage' ) ) ); ?>"
			fetchpriority="high" decoding="async">
		<div class="acreage-hero__wash"></div>

		<div class="acreage-hero__inner">
			<div class="acreage-hero__meta">
				<span class="acreage-hero__count">
					<?php
					printf(
						/* translators: %s: number of live farms. */
						esc_html( acreage_option( 'hero_stat', __( '%s live farms · Nine provinces · International', 'acreage' ) ) ),
						esc_html( number_format_i18n( $acreage_live ) )
					);
					?>
				</span>
				<span class="acreage-hero__place"><?php echo esc_html( acreage_option( 'hero_place', __( 'Waterberg, Limpopo', 'acreage' ) ) ); ?></span>
			</div>

			<div>
				<h1 class="acreage-hero__title"><?php echo esc_html( acreage_option( 'hero_title', __( 'Land with game on it, and land that feeds cattle.', 'acreage' ) ) ); ?></h1>
				<p class="acreage-hero__lede"><?php echo esc_html( acreage_option( 'hero_lede', __( 'Every farm on this site is listed by the owner of the business, walked or flown before it goes up.', 'acreage' ) ) ); ?></p>
			</div>
		</div>
	</div>

	<div class="acreage-srch__wrap">
		<form class="acreage-srch" method="get" action="<?php echo esc_url( acreage_farms_url() ); ?>" role="search">
			<?php if ( post_type_exists( 'listing' ) ) : ?>
				<input type="hidden" name="post_type" value="listing">
			<?php endif; ?>

			<div class="acreage-srch__head">
				<h2 class="acreage-srch__title"><?php esc_html_e( 'Search all listings', 'acreage' ); ?></h2>
				<span class="acreage-srch__live">
					<?php
					printf(
						/* translators: %s: number of farms currently listed. */
						esc_html__( '%s farms listed', 'acreage' ),
						esc_html( number_format_i18n( $acreage_live ) )
					);
					?>
				</span>
			</div>

			<div class="acreage-srch__fields">
				<?php
				foreach ( acreage_home_search_axes() as $acreage_tax => $acreage_label ) :
					$acreage_terms = acreage_home_terms( $acreage_tax );

					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public read-only search.
					$acreage_current = isset( $_GET[ $acreage_tax ] ) ? sanitize_title( wp_unslash( $_GET[ $acreage_tax ] ) ) : '';
					?>
					<label class="acreage-srch__field">
						<?php echo esc_html( $acreage_label ); ?>
						<select name="<?php echo esc_attr( $acreage_tax ); ?>">
							<option value=""><?php echo esc_html( acreage_home_any_label( $acreage_tax ) ); ?></option>
							<?php foreach ( $acreage_terms as $acreage_term ) : ?>
								<option value="<?php echo esc_attr( $acreage_term->slug ); ?>" <?php selected( $acreage_current, $acreage_term->slug ); ?>>
									<?php echo esc_html( $acreage_term->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
				<?php endforeach; ?>
			</div>

			<div class="acreage-srch__actions">
				<button class="acreage-srch__go" type="submit"><?php esc_html_e( 'Search farms', 'acreage' ); ?></button>
				<a class="acreage-srch__clear" href="<?php echo esc_url( acreage_farms_url() ); ?>"><?php esc_html_e( 'Clear', 'acreage' ); ?></a>
			</div>
		</form>
	</div>

	<div class="acreage-hero__tail"></div>
</section>

<section id="farms" class="acreage-sec acreage-pad">
	<div class="acreage-sec__head">
		<div>
			<h2 class="acreage-h2"><?php echo esc_html( acreage_option( 'farms_title', __( 'Recently listed', 'acreage' ) ) ); ?></h2>
			<p class="acreage-sub">
				<?php
				printf(
					/* translators: %s: total number of live farms. */
					esc_html( acreage_option( 'farms_sub', __( 'Three of %s live farms. The full inventory sits on the farms for sale page.', 'acreage' ) ) ),
					esc_html( number_format_i18n( $acreage_live ) )
				);
				?>
			</p>
		</div>
		<a class="acreage-seeall" href="<?php echo esc_url( acreage_farms_url() ); ?>">
			<?php
			printf(
				/* translators: %s: total number of live farms. */
				esc_html__( 'See all %s farms', 'acreage' ),
				esc_html( number_format_i18n( $acreage_live ) )
			);
			?>
		</a>
	</div>

	<div class="acreage-cards">
		<?php foreach ( $acreage_farms as $acreage_farm ) : ?>
			<article class="acreage-c">
				<a class="acreage-c__media" href="<?php echo esc_url( $acreage_farm['url'] ); ?>">
					<?php if ( $acreage_farm['image'] ) : ?>
						<img src="<?php echo esc_url( $acreage_farm['image'] ); ?>" alt="" loading="lazy" decoding="async">
					<?php endif; ?>
					<?php if ( $acreage_farm['status'] ) : ?>
						<span class="acreage-c__badge <?php echo esc_attr( $acreage_farm['sold'] ? 'acreage-c__badge--sold' : '' ); ?>">
							<?php echo esc_html( $acreage_farm['status'] ); ?>
						</span>
					<?php endif; ?>
				</a>

				<div class="acreage-c__body">
					<?php if ( $acreage_farm['category'] || $acreage_farm['place'] ) : ?>
						<div class="acreage-c__meta">
							<?php if ( $acreage_farm['category'] ) : ?>
								<span><?php echo esc_html( $acreage_farm['category'] ); ?></span>
							<?php endif; ?>
							<?php if ( $acreage_farm['category'] && $acreage_farm['place'] ) : ?>
								<span aria-hidden="true">·</span>
							<?php endif; ?>
							<?php if ( $acreage_farm['place'] ) : ?>
								<span><?php echo esc_html( $acreage_farm['place'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<h3 class="acreage-c__title"><a href="<?php echo esc_url( $acreage_farm['url'] ); ?>"><?php echo esc_html( $acreage_farm['title'] ); ?></a></h3>

					<div>
						<div class="acreage-c__price"><?php echo esc_html( $acreage_farm['price'] ); ?></div>
						<?php if ( $acreage_farm['has_price'] ) : ?>
							<div class="acreage-c__vat"><?php esc_html_e( 'Excludes VAT if applicable', 'acreage' ); ?></div>
						<?php endif; ?>
					</div>

					<?php if ( $acreage_farm['excerpt'] ) : ?>
						<p class="acreage-c__excerpt"><?php echo esc_html( $acreage_farm['excerpt'] ); ?></p>
					<?php endif; ?>

					<div class="acreage-c__foot">
						<span class="acreage-c__ha"><?php echo esc_html( $acreage_farm['extent'] ); ?></span>
						<span class="acreage-c__links">
							<a href="<?php echo esc_url( $acreage_farm['url'] ); ?>"><?php esc_html_e( 'View listing', 'acreage' ); ?></a>
							<a href="<?php echo esc_url( $acreage_farm['url'] ); ?>#enquire"><?php esc_html_e( 'Enquire', 'acreage' ); ?></a>
						</span>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<div class="acreage-sec__foot">
		<a class="acreage-btn-o" href="<?php echo esc_url( acreage_farms_url() ); ?>"><?php esc_html_e( 'Browse all farms', 'acreage' ); ?></a>
	</div>
</section>

<section id="provinces" class="acreage-sec acreage-pad acreage-provinces" data-reveal>
	<h2 class="acreage-h2"><?php echo esc_html( acreage_option( 'prov_title', __( 'Browse by province', 'acreage' ) ) ); ?></h2>
	<p class="acreage-provinces__lede"><?php echo esc_html( acreage_option( 'prov_sub', __( 'All nine provinces, plus farms listed outside South Africa.', 'acreage' ) ) ); ?></p>

	<div class="acreage-tiles">
		<?php foreach ( $acreage_provinces as $acreage_province ) : ?>
			<a class="acreage-tile" href="<?php echo esc_url( $acreage_province['url'] ); ?>">
				<span class="acreage-tile__name"><?php echo esc_html( $acreage_province['name'] ); ?></span>
				<span class="acreage-tile__count">
					<?php
					printf(
						/* translators: %s: number of listings in this province. */
						esc_html( _n( '%s listing', '%s listings', $acreage_province['count'], 'acreage' ) ),
						esc_html( number_format_i18n( $acreage_province['count'] ) )
					);
					?>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<section class="acreage-cats">
	<?php foreach ( acreage_home_categories() as $acreage_cat ) : ?>
		<div id="<?php echo esc_attr( $acreage_cat['id'] ); ?>" class="acreage-cat acreage-cat--<?php echo esc_attr( $acreage_cat['id'] ); ?>" data-reveal>
			<div class="acreage-cat__media">
				<img src="<?php echo esc_url( $acreage_cat['image'] ); ?>" alt="<?php echo esc_attr( $acreage_cat['alt'] ); ?>" loading="lazy" decoding="async">
			</div>
			<div class="acreage-cat__body">
				<span class="acreage-cat__count">
					<?php
					printf(
						/* translators: %s: number of listings in this category. */
						esc_html( _n( '%s listing', '%s listings', $acreage_cat['count'], 'acreage' ) ),
						esc_html( number_format_i18n( $acreage_cat['count'] ) )
					);
					?>
				</span>
				<h2 class="acreage-cat__title"><?php echo esc_html( $acreage_cat['title'] ); ?></h2>
				<p class="acreage-cat__text"><?php echo esc_html( $acreage_cat['text'] ); ?></p>
				<a class="acreage-cat__link" href="<?php echo esc_url( $acreage_cat['url'] ); ?>"><?php echo esc_html( $acreage_cat['link'] ); ?></a>
			</div>
		</div>
	<?php endforeach; ?>
</section>

<section class="acreage-about">
	<div id="about" class="acreage-about__text" data-reveal>
		<span class="acreage-eyebrow"><?php esc_html_e( 'About', 'acreage' ); ?></span>
		<h2 class="acreage-about__title"><?php echo esc_html( acreage_option( 'about_title', __( 'One owner, one inventory, no middle layer.', 'acreage' ) ) ); ?></h2>
		<p class="acreage-about__body"><?php echo esc_html( acreage_option( 'about_body', __( 'Africa Game Farms lists game and cattle farms across South Africa and, occasionally, across the border. The owner loads every listing himself and photographs most of them from the air. Enquiries go straight to him, not to a call centre.', 'acreage' ) ) ); ?></p>
	</div>

	<div class="acreage-stats">
		<?php foreach ( acreage_home_stats( $acreage_live ) as $acreage_stat ) : ?>
			<div class="acreage-stat">
				<div class="acreage-stat__value"><?php echo esc_html( $acreage_stat['value'] ); ?></div>
				<div class="acreage-stat__label"><?php echo esc_html( $acreage_stat['label'] ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<section id="sell" class="acreage-sell">
	<div class="acreage-sell__media">
		<img src="<?php echo esc_url( acreage_option_image( 'sell_image', 'article-01.jpg' ) ); ?>" alt="" loading="lazy" decoding="async">
	</div>
	<div class="acreage-sell__body" data-reveal>
		<h2 class="acreage-sell__title"><?php echo esc_html( acreage_option( 'sell_title', __( 'Selling a farm?', 'acreage' ) ) ); ?></h2>
		<p class="acreage-sell__text"><?php echo esc_html( acreage_option( 'sell_body', __( 'Send the province, size, carrying capacity or game count, and asking price. Photographs help, but they are not needed to start the conversation.', 'acreage' ) ) ); ?></p>
		<div class="acreage-sell__actions">
			<a class="acreage-sell__primary" href="#footer"><?php esc_html_e( 'List your farm', 'acreage' ); ?></a>
			<a class="acreage-sell__ghost" href="#footer"><?php esc_html_e( 'Ask a question', 'acreage' ); ?></a>
		</div>
	</div>
</section>

<?php
/**
 * Anything the client types into the page still renders, below the design.
 *
 * the_content() is called unconditionally and buffered, rather than guarded by
 * get_the_content(). Two reasons, and the second one matters more:
 *
 *  1. get_the_content() returns the raw post body, which is empty on a page
 *     built with Elementor — its content lives in post meta. So the old guard
 *     was always false for exactly the pages that needed it.
 *  2. Elementor detects its content area by watching the_content() run. A
 *     template that never calls it fails with "the content area was not found
 *     on your page", and the page cannot be edited at all.
 *
 * Buffering keeps the wrapper off the page when there is genuinely nothing to
 * show, without skipping the call.
 */
while ( have_posts() ) :
	the_post();

	ob_start();
	the_content();
	$acreage_extra = trim( ob_get_clean() );

	if ( '' !== $acreage_extra ) :
		?>
		<div class="acreage-sec acreage-pad">
			<div class="acreage-prose"><?php echo wp_kses_post( $acreage_extra ); ?></div>
		</div>
		<?php
	endif;
endwhile;

get_footer();
