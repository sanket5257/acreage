<?php
/**
 * Template hooks — the theme's extension points.
 *
 * WHAT A HOOK IS, PLAINLY
 *
 * A hook is a named moment. The theme calls do_action( 'acreage_after_header' )
 * at that moment; anyone — a child theme, a plugin, the customer's snippet — can
 * ask WordPress to run their function whenever that name fires:
 *
 *     add_action( 'acreage_after_header', 'my_announcement_bar' );
 *
 * WHY IT MATTERS COMMERCIALLY
 *
 * Without hooks, the only way for a customer to insert something under the
 * header is to copy header.php into their child theme and edit it. Their copy
 * then never receives another update, and every fix you ship afterwards silently
 * skips them. Hooks let them add without forking. Every hook you publish is a
 * support ticket you do not receive.
 *
 * Published hooks are a promise: renaming one after release breaks customer
 * sites. Add freely, rename never.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fire a theme hook.
 *
 * A thin wrapper so every hook is spelled consistently and shows up in one grep.
 *
 * @param string $name Hook name without the acreage_ prefix.
 * @param mixed  ...$args Optional arguments passed to listeners.
 */
function acreage_hook( $name, ...$args ) {
	/**
	 * Dynamic theme hook.
	 *
	 * Fires at a named point in the templates. See inc/template-hooks.php for
	 * the full list of names.
	 */
	do_action( 'acreage_' . $name, ...$args );
}

/*
 * The published list. Documented here rather than scattered through templates,
 * so the documentation can be generated from one file.
 *
 *   acreage_before_header   Immediately inside <body>, before anything is drawn.
 *   acreage_after_header    Between the header and the main content.
 *   acreage_before_content  Inside <main>, before the loop.
 *   acreage_after_content   Inside <main>, after the loop.
 *   acreage_before_footer   Between the main content and the footer.
 *   acreage_after_footer    After the footer, before wp_footer().
 *   acreage_post_meta       Inside a post header, after the date. Args: post ID.
 *   acreage_after_post      After a single post's content. Args: post ID.
 */
