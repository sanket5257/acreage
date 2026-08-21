<?php
/**
 * One farm.
 *
 * If a page is assigned to the "Single farm page" slot, that layout renders here
 * and its Farm Details widgets read whichever farm the visitor opened. Otherwise
 * the theme prints the farm itself, so a listing is never dependent on a builder.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

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
	<?php

	// The designed layout, if there is one.
	if ( ! acreage_do_location( 'single' ) ) :
		?>
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
	endif;

endwhile;

get_footer();
