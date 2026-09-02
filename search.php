<?php
/**
 * Search results.
 *
 * A KEYWORD SEARCH FOR FARMS IS STILL THE FARMS PAGE
 *
 * The header search submits to the farms archive, so a search for "kudu" is a
 * request for a filtered version of the page the visitor already knows: the
 * same cards, the same sort control, the same filter panel down the side. Left
 * to WordPress it would instead fall through to index.php — the generic list
 * template — and the visitor would arrive at a stripped page with no count, no
 * sort and no way to narrow what they got. Keyword-first, refine-afterwards is
 * the whole point of searching from the header, and that needs the filters.
 *
 * So a search that has already been scoped to farms is handed to the farms
 * archive template, which renders the assigned Elementor layout exactly as it
 * does at /game-farms/ and whose widgets read the keyword back out of the query
 * string. Anything else — a site-wide search across pages and posts — carries
 * on to index.php as before.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

if ( post_type_exists( 'listing' ) && is_post_type_archive( 'listing' ) ) {
	get_template_part( 'archive-listing' );
	return;
}

get_template_part( 'index' );
