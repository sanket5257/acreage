<?php
/**
 * Shown when a loop returns nothing.
 *
 * A dead end is a place people leave from. This one always offers a way onward:
 * a search box, and on an empty search the reason it was empty.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="acreage-none">
	<h1 class="acreage-page__title"><?php esc_html_e( 'Nothing found', 'acreage' ); ?></h1>

	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'No results matched that search. Try fewer words, or a different spelling.', 'acreage' ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( 'There is nothing here yet.', 'acreage' ); ?></p>
	<?php endif; ?>

	<?php get_search_form(); ?>
</section>
