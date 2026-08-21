<?php
/**
 * Boot the Theme Options screen.
 *
 * A thin loader, matching how the demo importer is started: the class file is
 * required at load time so a child theme can replace it wholesale through
 * get_theme_file_path(), but nothing is instantiated on a front-end request.
 * The screen is pure wp-admin, and a visitor should never pay for its hooks.
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
