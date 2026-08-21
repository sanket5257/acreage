<?php
/**
 * Acreage — bootstrap.
 *
 * This file is a table of contents, nothing more. It defines where the theme is
 * on disk, then loads one file per responsibility from inc/. When something
 * breaks you should be able to guess which file to open from its name; that is
 * the whole point of keeping this file short.
 *
 * HARD RULE: no register_post_type(), no register_taxonomy(), no field
 * definitions in this theme. Listing data belongs to the Acreage Core plugin so
 * a customer's properties survive switching theme.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

/*
 * Identity.
 *
 * get_template() is whatever folder WordPress actually put us in — a customer
 * may rename it, and a child theme changes get_stylesheet() but not this. Every
 * path below is therefore derived rather than hardcoded.
 */
define( 'ACREAGE_SLUG', get_template() );
define( 'ACREAGE_VERSION', wp_get_theme( ACREAGE_SLUG )->get( 'Version' ) );
define( 'ACREAGE_DIR', get_template_directory() );
define( 'ACREAGE_URI', get_template_directory_uri() );

/**
 * Where release updates are fetched from.
 *
 * PHASE M5: this still points at a public GitHub repo, which enforces no
 * licence. It is replaced by the licensed update endpoint before the theme is
 * sold. Defined here so a child theme or a site can override it first.
 */
if ( ! defined( 'ACREAGE_UPDATE_REPO' ) ) {
	define( 'ACREAGE_UPDATE_REPO', 'sanket5257/acreage' );
}

/**
 * Load a file from inc/.
 *
 * Wrapped in a helper so a child theme can replace any single module by
 * declaring its own copy — get_theme_file_path() checks the child folder first.
 *
 * @param string $name File name without extension.
 */
function acreage_load( $name ) {
	require_once get_theme_file_path( 'inc/' . $name . '.php' );
}

acreage_load( 'setup' );             // Theme supports, menus, image sizes.
acreage_load( 'enqueue' );           // Every stylesheet and script.
acreage_load( 'template-hooks' );    // The do_action() points templates fire.
acreage_load( 'template-functions' );// Small helpers templates call.
acreage_load( 'performance' );       // Things we deliberately do not load.
acreage_load( 'design-system' );  // Palette + type, synced with Elementor globals.
acreage_load( 'elementor' );         // Free slot system + Pro Theme Builder.
acreage_load( 'updates' );           // Update checks and the admin notice.

/*
 * Legacy — scheduled for removal in Phase 3.
 *
 * home-data.php and page-home.php are the original single-client homepage: PHP
 * that holds copy. A product cannot ship that, because the buyer cannot edit it.
 * Both are replaced by the Elementor demo kit and deleted then. They stay for
 * now only so the site keeps rendering while the kit is built.
 */
acreage_load( 'home-data' );
