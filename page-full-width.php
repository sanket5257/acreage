<?php
/**
 * Template Name: Full width (Elementor)
 *
 * Elementor Free owns the whole canvas on this template — no container, no
 * title, nothing fighting the builder.
 *
 * But a page can carry this template *without* being built in Elementor: the
 * demo importer used to assign it, and a client can pick it from the dropdown.
 * In that case raw the_content() renders edge to edge with no styling at all,
 * which reads as a broken theme. So when Elementor is not driving this page we
 * fall back to the standard container.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$acreage_elementor_driven = false;

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->documents ) ) {
		$acreage_document         = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
		$acreage_elementor_driven = $acreage_document && $acreage_document->is_built_with_elementor();
	}

	if ( $acreage_elementor_driven ) {
		the_content();
	} else {
		?>
		<div class="acreage-wrap acreage-page">
			<article <?php post_class( 'acreage-article' ); ?>>
				<div class="acreage-prose"><?php the_content(); ?></div>
			</article>
		</div>
		<?php
	}

endwhile;

get_footer();
