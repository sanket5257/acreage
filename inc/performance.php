<?php
/**
 * Performance — mostly a list of things this theme refuses to load.
 *
 * WHAT IS DELIBERATELY NOT HERE
 *
 * Removing wp_generator, rsd_link and the shortlink tags, and filtering
 * upload_mimes, all used to live in this theme. Theme Check flags every one as
 * plugin territory and it is right: they change how the whole SITE behaves, not
 * how it looks, so they would vanish the day a customer switches theme — which
 * is the definition of behaviour that belonged in a plugin. The documentation
 * recommends a security plugin for them instead.
 *
 * Every item here is measured in requests and kilobytes a visitor does not pay
 * for. None of it changes how the site looks.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_default_scripts', 'acreage_drop_jquery_migrate' );
/**
 * Drop jQuery Migrate on the front end.
 *
 * Migrate exists to prop up jQuery code written before 3.0. Nothing in this
 * theme uses jQuery at all, so it is pure weight. Admin is left alone, because
 * plugins there genuinely may need it.
 *
 * @param WP_Scripts $scripts Script registry.
 */
function acreage_drop_jquery_migrate( $scripts ) {
	if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}

	$scripts->registered['jquery']->deps = array_diff(
		$scripts->registered['jquery']->deps,
		array( 'jquery-migrate' )
	);
}

add_filter( 'wp_resource_hints', 'acreage_resource_hints', 10, 2 );
/**
 * Drop the s.w.org DNS prefetch that core adds for emoji.
 *
 * @param array  $hints Hint URLs.
 * @param string $relation Hint type.
 * @return array
 */
function acreage_resource_hints( $hints, $relation ) {
	if ( 'dns-prefetch' !== $relation ) {
		return $hints;
	}

	return array_filter(
		$hints,
		static function ( $hint ) {
			$url = is_array( $hint ) && isset( $hint['href'] ) ? $hint['href'] : $hint;

			return ! is_string( $url ) || false === strpos( $url, 's.w.org' );
		}
	);
}
