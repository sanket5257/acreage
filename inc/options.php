<?php
/**
 * Boot the Theme Options screen, and put its settings on the page.
 *
 * The class file is required unconditionally so its static helpers — get(),
 * ui_css() and google_fonts_url() — are available on the front end. Only the
 * screen itself is instantiated, and only in wp-admin: a visitor should never
 * pay for hooks that exist to draw a settings form.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'inc/class-acreage-theme-options.php' );

add_action( 'after_setup_theme', 'acreage_boot_theme_options', 2 );
/**
 * Load the options screen in wp-admin only.
 */
function acreage_boot_theme_options() {
	if ( is_admin() ) {
		new Acreage_Theme_Options();
	}
}

add_action( 'wp_enqueue_scripts', 'acreage_add_ui_css', 30 );
/**
 * Attach the appearance settings to the stylesheet.
 *
 * INLINE, NOT A SEPARATE FILE
 *
 * These are a few hundred bytes that differ per site, so a file would cost a
 * second request to save nothing — and a cached file is the classic reason a
 * customer changes a colour and sees no difference for an hour.
 *
 * PRIORITY 30 IS THE POINT
 *
 * forms.css goes on at 20 and design-system.php inlines the Elementor kit's
 * palette at 20 as well. Landing after both is what lets a colour set on this
 * screen actually win, and what keeps the button radius consistent between the
 * theme's own buttons and a contact form plugin's on the same page.
 */
function acreage_add_ui_css() {
	$css = Acreage_Theme_Options::ui_css();

	if ( '' === $css ) {
		return;
	}

	/*
	 * Hung off the base handle rather than one of its own: wp_add_inline_style
	 * needs a stylesheet that is actually enqueued, and 'acreage' always is.
	 */
	wp_add_inline_style( 'acreage', $css );
}

add_action( 'wp_enqueue_scripts', 'acreage_enqueue_google_fonts', 5 );
/**
 * Request the chosen webfonts, and only when one has actually been chosen.
 *
 * The theme ships with two faces that are already on every machine, so this
 * fires for nobody until a customer picks something from the Typography tab.
 * That keeps the default install free of a render-blocking third-party request,
 * which was a deliberate decision in the original design and is worth keeping
 * for everyone who never opens that tab.
 */
function acreage_enqueue_google_fonts() {
	$url = Acreage_Theme_Options::google_fonts_url();

	if ( '' === $url ) {
		return;
	}

	wp_enqueue_style( 'acreage-fonts', $url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google serves its own versioning.
}

add_action( 'admin_enqueue_scripts', 'acreage_enqueue_editor_fonts' );
/**
 * The same faces inside the block editor, so the writing view matches the site.
 */
function acreage_enqueue_editor_fonts() {
	$url = Acreage_Theme_Options::google_fonts_url();

	if ( '' === $url || ! is_admin() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
		wp_enqueue_style( 'acreage-fonts', $url, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}
}
