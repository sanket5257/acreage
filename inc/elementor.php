<?php
/**
 * Elementor integration.
 *
 * THREE TIERS, ONE DECISION POINT
 *
 * Every template that can be replaced by a builder asks acreage_do_location()
 * first, and draws its own markup only if the answer is no:
 *
 *   Tier 1  Elementor Pro Theme Builder
 *           The customer paid for Theme Builder, so if they have built a header
 *           there, it wins. A theme that ignores Pro templates and renders its
 *           own header anyway is the single most common Elementor-theme bug.
 *
 *   Tier 2  Elementor Free, via an assigned page
 *           Theme Builder is Pro-only, so Free customers cannot place a template
 *           on a header. Acreage lets them build an ordinary page in Elementor
 *           and assign it to a slot; the theme renders that page's content in
 *           the header's place. Same editing experience, no licence.
 *
 *   Tier 3  The theme's own PHP
 *           No builder, a deactivated plugin, or a slot left empty. The site is
 *           never broken by a missing plugin.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'inc/class-acreage-elementor-layout.php' );

new Acreage_Elementor_Layout();

/**
 * Is Elementor active at all?
 *
 * @return bool
 */
function acreage_elementor_active() {
	return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
}

/**
 * Is Elementor Pro active?
 *
 * @return bool
 */
function acreage_elementor_pro_active() {
	return defined( 'ELEMENTOR_PRO_VERSION' );
}

/**
 * The locations this theme can hand over to a builder.
 *
 * Keys are Elementor's own location names, so a Pro template saved for
 * "Header" lands in the right place without the customer configuring anything.
 *
 * @return array<string,array>
 */
function acreage_locations() {
	return array(
		'header'  => array(
			'label'      => __( 'Header', 'acreage' ),
			'multiple'   => false,
			'edit_in_content' => false,
		),
		'footer'  => array(
			'label'      => __( 'Footer', 'acreage' ),
			'multiple'   => false,
			'edit_in_content' => false,
		),
		'single'  => array(
			'label'      => __( 'Single', 'acreage' ),
			'multiple'   => true,
			'edit_in_content' => true,
		),
		'archive' => array(
			'label'      => __( 'Archive', 'acreage' ),
			'multiple'   => true,
			'edit_in_content' => true,
		),
	);
}

add_action( 'elementor/theme/register_locations', 'acreage_register_locations' );
/**
 * Tell Elementor Pro which locations this theme supports.
 *
 * Without this call, Theme Builder shows the customer a "your theme does not
 * support Theme Builder" notice and silently refuses to apply their templates.
 * The hook only ever fires when Pro is active, so no guard is needed.
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Location registrar.
 */
function acreage_register_locations( $manager ) {
	foreach ( acreage_locations() as $name => $args ) {
		$manager->register_location( $name, $args );
	}
}

/**
 * Render a location, whichever tier is available.
 *
 * @param string $location One of acreage_locations().
 * @return bool True when something was printed and the caller should print nothing.
 */
function acreage_do_location( $location ) {

	/**
	 * Short-circuit a location entirely.
	 *
	 * Return true to suppress every tier, e.g. a child theme that renders the
	 * header itself and wants no builder involvement.
	 *
	 * @param bool   $handled  Whether the location is already dealt with.
	 * @param string $location Location name.
	 */
	if ( apply_filters( 'acreage_pre_do_location', false, $location ) ) {
		return true;
	}

	// Tier 1 — Elementor Pro Theme Builder. The function is defined by Pro only.
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location ) ) {
		return true;
	}

	// Tier 2 — a page assigned to the slot, rendered through Elementor Free.
	if ( Acreage_Elementor_Layout::render( $location ) ) {
		return true;
	}

	// Tier 3 — the caller draws its own fallback.
	return false;
}

/*
 * Demo import — admin only.
 *
 * PHASE 3b: replaced by an Elementor kit exported from Elementor > Tools >
 * Import / Export Kit. A kit carries pages, templates, global colours, fonts and
 * menus in a format Elementor maintains, which is 630 lines of importer we then
 * delete rather than support.
 */
require_once get_theme_file_path( 'inc/class-acreage-demo-import.php' );

add_action( 'after_setup_theme', 'acreage_boot_demo_import', 2 );
/**
 * Load the demo importer only in wp-admin — a visitor never needs it.
 */
function acreage_boot_demo_import() {
	if ( is_admin() ) {
		new Acreage_Demo_Import();
	}
}
