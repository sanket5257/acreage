<?php
/**
 * The design system — one palette, one place to change it.
 *
 * THE PROBLEM THIS SOLVES
 *
 * A theme normally hardcodes its colours in CSS. The customer who wants a blue
 * site instead of a green one has to write CSS, which is exactly the thing they
 * bought a page builder to avoid. Meanwhile Elementor has a perfectly good
 * colour manager built in — Site Settings > Global Colors — that they already
 * know how to use. Most themes ignore it, so the site ends up with two palettes
 * that disagree: the theme's header is green, the Elementor sections are blue.
 *
 * HOW ACREAGE HANDLES IT
 *
 *   Elementor's Global Colors are the source of truth.
 *   The theme reads them and republishes them as its own CSS variables.
 *
 * So the customer edits colours in one familiar place, and the change reaches
 * the parts of the site Elementor does not draw — the coded header, the blog,
 * comments, the footer.
 *
 * Three states, all handled:
 *
 *   Elementor absent      -> the static :root block in theme.css is used as-is.
 *   Elementor, untouched  -> the kit is seeded with Acreage's palette on
 *                            activation, so the builder shows our colours.
 *   Elementor, customised -> the customer's values win and flow into the theme.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

/**
 * The canonical palette.
 *
 * This array is the single definition of Acreage's colours. theme.css carries
 * the same values as static defaults so the theme still looks right with no
 * builder installed; if you change one, change both. Everything else in the
 * theme refers to the CSS variable and never to a hex code.
 *
 * 'slot' is where the colour lives in Elementor:
 *   primary / secondary / text / accent are Elementor's four SYSTEM colours,
 *   which appear at the top of every colour picker. Anything else becomes a
 *   custom global, listed underneath.
 *
 * @return array[]
 */
function acreage_palette() {
	return array(
		'moss'       => array(
			'slot'  => 'primary',
			'title' => __( 'Primary', 'acreage' ),
			'hex'   => '#354027',
			'var'   => '--acreage-moss',
		),
		'moss-light' => array(
			'slot'  => 'secondary',
			'title' => __( 'Secondary', 'acreage' ),
			'hex'   => '#5E6557',
			'var'   => '--acreage-moss-light',
		),
		'ink'        => array(
			'slot'  => 'text',
			'title' => __( 'Text', 'acreage' ),
			'hex'   => '#1F2A21',
			'var'   => '--acreage-ink',
		),
		'sienna'     => array(
			'slot'  => 'accent',
			'title' => __( 'Accent', 'acreage' ),
			'hex'   => '#8F5A1E',
			'var'   => '--acreage-sienna',
		),
		'paper'      => array(
			'slot'  => 'custom',
			'title' => __( 'Paper', 'acreage' ),
			'hex'   => '#F5F0E4',
			'var'   => '--acreage-paper',
		),
		'panel'      => array(
			'slot'  => 'custom',
			'title' => __( 'Panel', 'acreage' ),
			'hex'   => '#EFE9DA',
			'var'   => '--acreage-panel',
		),
		'stone'      => array(
			'slot'  => 'custom',
			'title' => __( 'Stone', 'acreage' ),
			'hex'   => '#665F52',
			'var'   => '--acreage-stone',
		),
		'rule'       => array(
			'slot'  => 'custom',
			'title' => __( 'Rule', 'acreage' ),
			'hex'   => '#DED5C0',
			'var'   => '--acreage-rule',
		),
		'ink-soft'   => array(
			'slot'  => 'custom',
			'title' => __( 'Ink soft', 'acreage' ),
			'hex'   => '#3C4A3F',
			'var'   => '--acreage-ink-soft',
		),
	);
}

/**
 * The canonical type pairing.
 *
 * Mapped onto Elementor's system typography so the customer changes fonts in
 * the builder rather than in CSS.
 *
 * @return array[]
 */
function acreage_typography() {
	return array(
		'primary' => array(
			'title'  => __( 'Primary', 'acreage' ),
			'family' => 'Georgia',
			'weight' => '400',
			'var'    => '--acreage-serif',
			'stack'  => 'Georgia,"Times New Roman",serif',
		),
		'text'    => array(
			'title'  => __( 'Text', 'acreage' ),
			'family' => 'Helvetica Neue',
			'weight' => '400',
			'var'    => '--acreage-sans',
			'stack'  => '"Helvetica Neue",Helvetica,"Segoe UI",Arial,sans-serif',
		),
	);
}

/* ------------------------------------------------------------- reading the kit */

/**
 * The active Elementor kit's saved settings, or an empty array.
 *
 * Elementor stores kit settings in post meta. Reading the meta directly rather
 * than booting a document object works even when Elementor is deactivated
 * mid-request, and get_post_meta() is already served from WordPress's object
 * cache, so this is cheap enough for every front end request.
 *
 * DELIBERATELY NOT STATICALLY CACHED. An earlier version held the result in a
 * static, which went stale the moment anything wrote to the kit in the same
 * request — and the seeding routine below then read "no colours saved" for a
 * kit that had just been populated, and overwrote the customer's palette with
 * the defaults. Caching a value you also write to is how you lose data.
 *
 * @return array
 */
function acreage_kit_settings() {
	$kit_id = (int) get_option( 'elementor_active_kit' );

	if ( ! $kit_id ) {
		return array();
	}

	$saved = get_post_meta( $kit_id, '_elementor_page_settings', true );

	return is_array( $saved ) ? $saved : array();
}

/**
 * Build the :root override block from whatever the kit currently holds.
 *
 * Only variables that actually differ from the static default are emitted, so a
 * site that has not touched the palette ships zero extra bytes.
 *
 * @return string CSS, or an empty string when there is nothing to override.
 */
function acreage_kit_css() {
	$settings = acreage_kit_settings();

	if ( ! $settings ) {
		return '';
	}

	$by_slot  = array();
	$by_title = array();

	foreach ( array( 'system_colors', 'custom_colors' ) as $group ) {
		foreach ( (array) ( isset( $settings[ $group ] ) ? $settings[ $group ] : array() ) as $row ) {
			if ( empty( $row['color'] ) ) {
				continue;
			}
			if ( ! empty( $row['_id'] ) ) {
				$by_slot[ $row['_id'] ] = $row['color'];
			}
			if ( ! empty( $row['title'] ) ) {
				$by_title[ strtolower( $row['title'] ) ] = $row['color'];
			}
		}
	}

	$rules = array();

	foreach ( acreage_palette() as $colour ) {
		// System colours are found by their fixed slot id; customs by title.
		$value = 'custom' === $colour['slot']
			? ( isset( $by_title[ strtolower( $colour['title'] ) ] ) ? $by_title[ strtolower( $colour['title'] ) ] : '' )
			: ( isset( $by_slot[ $colour['slot'] ] ) ? $by_slot[ $colour['slot'] ] : '' );

		if ( ! $value || strtoupper( $value ) === strtoupper( $colour['hex'] ) ) {
			continue;
		}

		$rules[] = $colour['var'] . ':' . $value . ';';
	}

	foreach ( acreage_typography() as $id => $type ) {
		$family = '';

		foreach ( (array) ( isset( $settings['system_typography'] ) ? $settings['system_typography'] : array() ) as $row ) {
			if ( isset( $row['_id'] ) && $id === $row['_id'] && ! empty( $row['typography_font_family'] ) ) {
				$family = $row['typography_font_family'];
			}
		}

		if ( ! $family || $family === $type['family'] ) {
			continue;
		}

		// Keep the original stack behind the chosen face as a fallback.
		$rules[] = $type['var'] . ':"' . str_replace( '"', '', $family ) . '",' . $type['stack'] . ';';
	}

	if ( ! $rules ) {
		return '';
	}

	return ':root{' . implode( '', $rules ) . '}';
}

add_action( 'wp_enqueue_scripts', 'acreage_enqueue_kit_css', 20 );
/**
 * Attach the kit overrides to the theme stylesheet.
 *
 * Priority 20 so it runs after inc/enqueue.php has registered the handle.
 * Inlined rather than written to a file because it is a handful of bytes and a
 * separate request would cost more than it saves.
 */
function acreage_enqueue_kit_css() {
	$css = acreage_kit_css();

	if ( $css ) {
		wp_add_inline_style( 'acreage', $css );
	}
}

/* ------------------------------------------------------------- seeding the kit */

add_action( 'after_switch_theme', 'acreage_seed_elementor_globals' );
/**
 * Put Acreage's palette into Elementor's Global Colors on activation.
 *
 * Non-destructive by design. If the kit already holds colours — because the
 * customer has been in there, or a demo kit was imported — this does nothing at
 * all. A theme that overwrites a customer's palette on every update is a theme
 * that gets a refund request.
 */
function acreage_seed_elementor_globals() {
	if ( ! acreage_elementor_active() ) {
		return;
	}

	$kit_id = (int) get_option( 'elementor_active_kit' );

	if ( ! $kit_id ) {
		return;
	}

	$settings = acreage_kit_settings();

	// Already populated — leave it alone.
	if ( ! empty( $settings['system_colors'] ) || ! empty( $settings['custom_colors'] ) ) {
		return;
	}

	$system = array();
	$custom = array();

	foreach ( acreage_palette() as $key => $colour ) {
		$row = array(
			'_id'   => 'custom' === $colour['slot'] ? 'acreage' . substr( md5( $key ), 0, 4 ) : $colour['slot'],
			'title' => $colour['title'],
			'color' => $colour['hex'],
		);

		if ( 'custom' === $colour['slot'] ) {
			$custom[] = $row;
		} else {
			$system[] = $row;
		}
	}

	$typography = array();

	foreach ( acreage_typography() as $id => $type ) {
		$typography[] = array(
			'_id'                      => $id,
			'title'                    => $type['title'],
			'typography_typography'    => 'custom',
			'typography_font_family'   => $type['family'],
			'typography_font_weight'   => $type['weight'],
		);
	}

	$settings['system_colors']     = $system;
	$settings['custom_colors']     = $custom;
	$settings['system_typography'] = $typography;

	acreage_save_kit_settings( $kit_id, $settings );
}

/**
 * Write kit settings back, preferring Elementor's own API.
 *
 * update_settings() keeps Elementor's caches and generated CSS in step. Writing
 * post meta directly works too but leaves a stale CSS file behind, so it is only
 * the fallback for when the document API is unavailable.
 *
 * @param int   $kit_id   Kit post ID.
 * @param array $settings Full settings array.
 */
function acreage_save_kit_settings( $kit_id, $settings ) {
	$saved = false;

	if ( isset( \Elementor\Plugin::$instance->documents ) ) {
		$kit = \Elementor\Plugin::$instance->documents->get( $kit_id );

		if ( $kit && method_exists( $kit, 'update_settings' ) ) {
			/*
			 * update_settings() runs Elementor's own capability check and THROWS
			 * "Access denied" when there is no current user. That happens whenever
			 * the theme is activated outside a browser session — WP-CLI, a
			 * migration script, a staging sync. Uncaught, it is a fatal error on
			 * theme activation, which is about the worst moment to have one.
			 *
			 * So the API is attempted, and any failure quietly falls through to
			 * writing the meta ourselves. Seeding a palette is a convenience; it
			 * must never be able to take the site down.
			 */
			try {
				$kit->update_settings( $settings );
				$saved = true;
			} catch ( \Throwable $e ) {
				$saved = false;
			}
		}
	}

	if ( ! $saved ) {
		update_post_meta( $kit_id, '_elementor_page_settings', $settings );
	}

	if ( isset( \Elementor\Plugin::$instance->files_manager ) ) {
		try {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		} catch ( \Throwable $e ) {
			// A stale CSS cache is a cosmetic problem; a fatal is not.
			return;
		}
	}
}
