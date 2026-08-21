<?php
/**
 * Third-party contact form support.
 *
 * WHY THE THEME CARES ABOUT FORM PLUGINS AT ALL
 *
 * Acreage ships its own enquiry form so a fresh install can be contacted before
 * anything else is installed. That is the right default, but it is a poor
 * ceiling: an agency already running Contact Form 7 or WPForms has its
 * notifications, entry log, spam filtering and CRM hooks wired into that plugin,
 * and will not give them up to match a theme.
 *
 * So the theme does the two things a theme is actually responsible for, and
 * nothing else:
 *
 *   1. STYLE.   A form plugin's default markup lands in the middle of a page
 *               designed around 11px uppercase labels and square 1px inputs, and
 *               looks pasted on. forms.css restyles it to match. That file loads
 *               only when one of these plugins is present, so a site with none
 *               pays nothing for the feature.
 *
 *   2. CONTEXT. A visitor on a farm page is asking about THAT farm. The form
 *               plugin has no idea which one, so the enquiry arrives as
 *               "someone asked something" and the agent has to guess. The
 *               bridge below hands the farm's name, ID and URL to the form.
 *
 * The choice of WHICH form renders is not made here — it is the Form dropdown on
 * the Enquiry Form widget in Acreage Core. This file supports whatever that
 * choice produces.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

/**
 * Form plugins the theme has styling for.
 *
 * Keyed by slug, valued by a check that is true when the plugin is running. Each
 * check tests something the plugin must have in order to render a form at all —
 * a post type it registers, or a shortcode it adds — rather than a class name or
 * a version constant, which are internals a plugin may rename without warning.
 *
 * @return string[] Slugs of the form plugins currently active.
 */
function acreage_active_form_plugins() {
	$candidates = array(
		'cf7'        => static function () {
			return post_type_exists( 'wpcf7_contact_form' );
		},
		'wpforms'    => static function () {
			return shortcode_exists( 'wpforms' );
		},
		'gravity'    => static function () {
			return shortcode_exists( 'gravityform' ) || shortcode_exists( 'gravityforms' );
		},
		'fluent'     => static function () {
			return shortcode_exists( 'fluentform' );
		},
		'forminator' => static function () {
			return shortcode_exists( 'forminator_form' );
		},
	);

	$active = array();

	foreach ( $candidates as $slug => $test ) {
		if ( $test() ) {
			$active[] = $slug;
		}
	}

	/**
	 * Filter the form plugins the theme styles.
	 *
	 * Add a slug here and the stylesheet loads; add matching rules from a child
	 * theme to style a plugin Acreage does not know about.
	 *
	 * @param string[] $active Slugs.
	 */
	return apply_filters( 'acreage_active_form_plugins', $active );
}

add_action( 'wp_enqueue_scripts', 'acreage_enqueue_form_styles', 20 );
/**
 * Load the third-party form styling, and only when it is needed.
 *
 * Priority 20 so it lands after the theme's own stylesheets and wins on equal
 * specificity without any rule here having to reach for !important.
 */
function acreage_enqueue_form_styles() {
	if ( ! acreage_active_form_plugins() ) {
		return;
	}

	wp_enqueue_style(
		'acreage-forms',
		get_theme_file_uri( 'assets/css/forms.css' ),
		array( 'acreage' ),
		ACREAGE_VERSION
	);
}

/**
 * The farm a form is being submitted from, as name / ID / URL.
 *
 * Empty strings off a farm page, so a general contact form is not littered with
 * blank "Farm:" lines in every notification email.
 *
 * @return array{name:string,id:string,url:string}
 */
function acreage_form_context() {
	$empty = array( 'name' => '', 'id' => '', 'url' => '' );

	if ( ! post_type_exists( 'listing' ) || ! is_singular( 'listing' ) ) {
		return $empty;
	}

	$id = get_queried_object_id();

	if ( ! $id ) {
		return $empty;
	}

	return array(
		'name' => get_the_title( $id ),
		'id'   => (string) $id,
		'url'  => get_permalink( $id ),
	);
}

add_filter( 'wpcf7_form_hidden_fields', 'acreage_cf7_farm_fields' );
/**
 * Hand the farm to Contact Form 7.
 *
 * CF7 turns every hidden field it renders into a mail tag of the same name, so
 * adding them here is all that is needed to write [acreage-farm] into the
 * subject line or the message body of the form's Mail tab.
 *
 * Documented in readme.txt, because a feature nobody knows the tag name for is
 * a feature nobody uses.
 *
 * @param array $fields Existing hidden fields.
 * @return array
 */
function acreage_cf7_farm_fields( $fields ) {
	$farm = acreage_form_context();

	if ( '' === $farm['name'] ) {
		return $fields;
	}

	$fields['acreage-farm']     = $farm['name'];
	$fields['acreage-farm-id']  = $farm['id'];
	$fields['acreage-farm-url'] = $farm['url'];

	return $fields;
}

add_shortcode( 'acreage_farm_name', 'acreage_shortcode_farm_name' );
/**
 * The current farm's name.
 *
 * For form plugins that have no hidden-field filter but do expand shortcodes in
 * a default field value — which is most of them. Returns nothing off a farm
 * page rather than a stray label.
 *
 * @return string
 */
function acreage_shortcode_farm_name() {
	$farm = acreage_form_context();

	return esc_html( $farm['name'] );
}

add_shortcode( 'acreage_farm_url', 'acreage_shortcode_farm_url' );
/**
 * The current farm's permalink, for the same reason.
 *
 * @return string
 */
function acreage_shortcode_farm_url() {
	$farm = acreage_form_context();

	return esc_url( $farm['url'] );
}
