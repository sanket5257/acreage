<?php
/**
 * Small helpers the templates call.
 *
 * Everything here is wrapped in function_exists() so a child theme can define
 * its own version first and win. That is the standard way to make a theme
 * function overridable without the customer editing the parent.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'acreage_posted_on' ) ) {
	/**
	 * The published date, marked up so search engines and screen readers agree
	 * on what it means.
	 */
	function acreage_posted_on() {
		printf(
			'<time class="acreage-meta" datetime="%s">%s</time>',
			esc_attr( get_the_date( DATE_W3C ) ),
			esc_html( get_the_date() )
		);

		acreage_hook( 'post_meta', get_the_ID() );
	}
}

if ( ! function_exists( 'acreage_entry_footer' ) ) {
	/**
	 * Categories and tags under a post, printed only when there are some.
	 */
	function acreage_entry_footer() {
		if ( 'post' !== get_post_type() ) {
			return;
		}

		$categories = get_the_category_list( ', ' );
		$tags       = get_the_tag_list( '', ', ' );

		if ( ! $categories && ! $tags ) {
			return;
		}

		echo '<footer class="acreage-entry__footer">';

		if ( $categories ) {
			printf(
				'<span class="acreage-entry__terms">%1$s %2$s</span>',
				esc_html__( 'Posted in', 'acreage' ),
				wp_kses_post( $categories )
			);
		}

		if ( $tags && ! is_wp_error( $tags ) ) {
			printf(
				'<span class="acreage-entry__terms">%1$s %2$s</span>',
				esc_html__( 'Tagged', 'acreage' ),
				wp_kses_post( $tags )
			);
		}

		echo '</footer>';
	}
}

if ( ! function_exists( 'acreage_has_sidebar' ) ) {
	/**
	 * Should this view show the blog sidebar?
	 *
	 * One decision in one place, so templates never disagree about it.
	 *
	 * @return bool
	 */
	function acreage_has_sidebar() {
		$show = is_active_sidebar( 'acreage-sidebar' ) && ! is_page() && ! is_singular( 'listing' );

		/**
		 * Filter whether the sidebar renders on this view.
		 *
		 * @param bool $show Whether to show it.
		 */
		return (bool) apply_filters( 'acreage_has_sidebar', $show );
	}
}
