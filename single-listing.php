<?php
/**
 * One farm.
 *
 * If a page is assigned to the "Single farm page" slot, that layout renders here
 * and its Farm Details widgets read whichever farm the visitor opened. Otherwise
 * the theme prints the farm itself, so a listing is never dependent on a builder.
 *
 * THE LAYOUT IS ASKED ABOUT FIRST, AND THAT ORDER MATTERS
 *
 * This template used to print its own location-and-title masthead before asking
 * whether a designed layout existed. When one did — which is the case on every
 * site that has run the demo importer — the Farm Details widget then rendered
 * its own hero with the same location and the same title over the photograph,
 * so the page opened with a bare stack of:
 *
 *     Otjozondjupa, Namibia
 *     Otjiwarongo Cattle Post
 *     Home / Cattle farms / Namibia / Otjiwarongo Cattle Post
 *     ← Back to results
 *
 * sitting above the real hero, which said all of it again. Two <h1> elements on
 * one page as well, which search engines and screen readers both read as a page
 * that cannot make up its mind what it is about.
 *
 * archive-listing.php had this right all along: ask the layout first, and print
 * the fallback only if there is nothing designed. This now matches it.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	/*
	 * The designed layout renders the whole farm — breadcrumb, hero, sections,
	 * gallery and enquiry form — so when it runs, nothing here may print.
	 */
	if ( acreage_do_location( 'single' ) ) {
		continue;
	}

	$acreage_id = get_the_ID();
	?>
	<div class="acreage-sec acreage-pad">
		<div class="acreage-sec__head">
			<div>
				<?php
				$acreage_province = acreage_home_first_term( $acreage_id, 'province' );
				$acreage_region   = acreage_home_first_term( $acreage_id, 'region' );
				$acreage_place    = trim( implode( ', ', array_filter( array( $acreage_region, $acreage_province ) ) ), ', ' );
				?>
				<?php if ( $acreage_place ) : ?>
					<span class="acreage-eyebrow"><?php echo esc_html( $acreage_place ); ?></span>
				<?php endif; ?>
				<h1 class="acreage-h2"><?php the_title(); ?></h1>
			</div>
		</div>
	</div>

	<div class="acreage-sec acreage-pad">
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="acreage-article__media"><?php the_post_thumbnail( 'full' ); ?></figure>
		<?php endif; ?>

		<div class="acreage-prose"><?php the_content(); ?></div>

		<?php
		// The three extra sections, printed only when they hold something.
		$acreage_sections = array(
			'acreage_improvements' => __( 'Improvements', 'acreage' ),
			'acreage_wildlife'     => __( 'Wildlife and vegetation', 'acreage' ),
			'acreage_land_claims'  => __( 'Land claims', 'acreage' ),
		);

		foreach ( $acreage_sections as $acreage_key => $acreage_label ) :
			$acreage_value = get_post_meta( $acreage_id, $acreage_key, true );

			if ( '' === trim( wp_strip_all_tags( (string) $acreage_value ) ) ) {
				continue;
			}
			?>
			<h2 class="acreage-h2"><?php echo esc_html( $acreage_label ); ?></h2>
			<div class="acreage-prose"><?php echo wp_kses_post( wpautop( $acreage_value ) ); ?></div>
		<?php endforeach; ?>

		<?php
		$acreage_price = (float) get_post_meta( $acreage_id, 'acreage_price', true );
		?>
		<p class="acreage-c__price">
			<?php echo esc_html( $acreage_price > 0 ? 'R' . number_format_i18n( $acreage_price ) : __( 'Price on application', 'acreage' ) ); ?>
		</p>
		<?php if ( $acreage_price > 0 ) : ?>
			<p class="acreage-c__vat"><?php esc_html_e( 'Excludes VAT if applicable', 'acreage' ); ?></p>
		<?php endif; ?>
	</div>
	<?php

endwhile;

get_footer();
