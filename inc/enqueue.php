<?php
/**
 * Asset loading — every stylesheet and script the theme puts on a page.
 *
 * "Enqueueing" is WordPress's way of saying "add this file to the page, but let
 * everything else have an opinion first". It handles dependency order, prevents
 * the same file loading twice when two plugins want it, and lets a child theme
 * or a caching plugin swap or remove a file. Writing a <link> tag by hand in
 * header.php gives up all of that, which is why the theme never does.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'acreage_enqueue_assets' );
/**
 * Front-end assets.
 */
function acreage_enqueue_assets() {

	/*
	 * NO WEBFONT, ON PURPOSE.
	 *
	 * The approved comp pairs Georgia for headings with Helvetica for body — both
	 * already on every machine. A webfont was briefly added here while matching a
	 * different reference; it cost an extra origin, a render-blocking request and
	 * a flash of fallback text, and it made the site look like something other
	 * than the design that was signed off. Fonts the client already approved and
	 * the browser already has are the cheaper and more faithful answer.
	 */

	// Base: tokens, typography, elements, header and footer.
	wp_enqueue_style(
		'acreage',
		get_theme_file_uri( 'assets/css/theme.css' ),
		array(),
		ACREAGE_VERSION
	);

	/*
	 * PHASE 3: home.css still carries the header, footer and the legacy homepage
	 * in one file, so it has to load everywhere. When the homepage moves to the
	 * Elementor kit, the header/footer rules merge into theme.css and this file
	 * disappears entirely.
	 */
	wp_enqueue_style(
		'acreage-layout',
		get_theme_file_uri( 'assets/css/home.css' ),
		array( 'acreage' ),
		ACREAGE_VERSION
	);

	/*
	 * The child theme's own style.css.
	 *
	 * This is the line that makes child themes work. get_stylesheet_uri() returns
	 * the CHILD's style.css when one is active and the parent's when it is not,
	 * and it is loaded last so its rules win. Without this, a customer's child
	 * theme silently has no effect — the single most common way a theme claims
	 * child support it does not actually have.
	 */
	if ( is_child_theme() ) {
		wp_enqueue_style(
			'acreage-child',
			get_stylesheet_uri(),
			array( 'acreage', 'acreage-layout' ),
			wp_get_theme()->get( 'Version' )
		);
	}

	// Right-to-left rules, added automatically when the locale is RTL.
	wp_style_add_data( 'acreage', 'rtl', 'replace' );

	wp_enqueue_script(
		'acreage',
		get_theme_file_uri( 'assets/js/theme.js' ),
		array(),
		ACREAGE_VERSION,
		true
	);

	// Threaded replies are only needed where comments are actually open.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}

add_action( 'enqueue_block_editor_assets', 'acreage_enqueue_editor_assets' );
/**
 * Editor assets.
 *
 * Kept separate from the front end deliberately: anything queued here loads in
 * wp-admin, where a heavy stylesheet slows the editor for the person who has to
 * use it all day.
 */
function acreage_enqueue_editor_assets() {
	if ( file_exists( get_theme_file_path( 'assets/css/editor.css' ) ) ) {
		wp_enqueue_style(
			'acreage-editor',
			get_theme_file_uri( 'assets/css/editor.css' ),
			array(),
			ACREAGE_VERSION
		);
	}
}

