<?php
/**
 * Theme updates.
 *
 * WHY A THEME NEEDS THIS AT ALL
 *
 * WordPress only knows how to update themes it can find in the wordpress.org
 * directory. A theme sold from your own site is invisible to it, so unless the
 * theme tells WordPress where its updates live, customers never get one — they
 * download a zip and overwrite by FTP, or more realistically they never update
 * and then report a bug you fixed a year ago.
 *
 * PHASE M5 — WHAT MUST CHANGE BEFORE SALE
 *
 * The current updater reads a public GitHub repository. That works, but it
 * enforces no licence: anybody who learns the repo name gets the paid theme and
 * its updates for free. Before launch this is repointed at a licensed endpoint
 * that checks a key before answering. The class structure below survives that
 * change; only the request URL and the addition of the key do not.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'inc/class-acreage-updater.php' );

/**
 * Access the updater instance.
 *
 * A static holder rather than a global variable: same convenience, but nothing
 * else can overwrite it by accident, and it does not appear in $GLOBALS where a
 * plugin might collide with the name.
 *
 * @param Acreage_Updater|null $set Internal — sets the instance.
 * @return Acreage_Updater|null
 */
function acreage_updater( $set = null ) {
	static $instance = null;

	if ( $set instanceof Acreage_Updater ) {
		$instance = $set;
	}

	return $instance;
}

add_action( 'after_setup_theme', 'acreage_boot_updater', 1 );
/**
 * Start the updater where it is actually useful.
 *
 * Not on front-end requests: a visitor's page load should never wait on an
 * outbound HTTP call to a release server.
 */
function acreage_boot_updater() {
	if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		acreage_updater( new Acreage_Updater( ACREAGE_UPDATE_REPO ) );
	}
}

add_action( 'admin_notices', 'acreage_update_notice' );
/**
 * Report update status on Appearance > Themes, and offer a manual re-check.
 *
 * Deliberately confined to that one screen. A notice that follows an
 * administrator around every page of wp-admin is the sort of thing that gets a
 * theme uninstalled.
 */
function acreage_update_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'themes' !== $screen->id || ! current_user_can( 'update_themes' ) ) {
		return;
	}

	$updater = acreage_updater();
	$url     = wp_nonce_url( admin_url( 'themes.php?acreage-check-updates=1' ), 'acreage-check' );

	$lines = array(
		sprintf(
			/* translators: 1: version number, 2: theme folder name. */
			__( 'Installed: <strong>v%1$s</strong> in folder <code>%2$s</code>', 'acreage' ),
			ACREAGE_VERSION ? esc_html( ACREAGE_VERSION ) : esc_html__( 'unknown', 'acreage' ),
			esc_html( ACREAGE_SLUG )
		),
	);

	$class = 'notice-info';

	if ( $updater ) {
		$release = $updater->get_release();

		if ( isset( $release['error'] ) ) {
			$class   = 'notice-warning';
			$lines[] = sprintf(
				/* translators: 1: release source, 2: reason the lookup failed. */
				__( 'Update source <code>%1$s</code>: <strong>%2$s</strong>', 'acreage' ),
				esc_html( ACREAGE_UPDATE_REPO ),
				esc_html( $release['error'] )
			);
		} else {
			$newer = ACREAGE_VERSION && version_compare( $release['version'], ACREAGE_VERSION, '>' );

			$lines[] = sprintf(
				/* translators: 1: release source, 2: latest version, 3: status wording. */
				__( 'Update source <code>%1$s</code>: latest release <strong>v%2$s</strong> — %3$s', 'acreage' ),
				esc_html( ACREAGE_UPDATE_REPO ),
				esc_html( $release['version'] ),
				$newer ? esc_html__( 'update available', 'acreage' ) : esc_html__( 'up to date', 'acreage' )
			);

			if ( ! $release['asset'] ) {
				$class   = 'notice-warning';
				$lines[] = esc_html__( 'That release has no attached .zip — falling back to the source archive.', 'acreage' );
			}
		}
	}

	printf(
		'<div class="notice %1$s"><p><strong>%2$s</strong></p><p>%3$s</p><p><a class="button" href="%4$s">%5$s</a></p></div>',
		esc_attr( $class ),
		esc_html__( 'Acreage updates', 'acreage' ),
		wp_kses( implode( '<br>', $lines ), array( 'strong' => array(), 'code' => array(), 'br' => array() ) ),
		esc_url( $url ),
		esc_html__( 'Check for updates now', 'acreage' )
	);
}
