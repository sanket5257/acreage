<?php
/**
 * Self-contained GitHub Releases updater for a classic WordPress theme.
 *
 * Watches the "latest" release of a GitHub repo. When the release tag
 * (v1.0.1 / 1.0.1) is newer than the Version: line in style.css, WordPress shows
 * the normal "Update available" notice under Appearance > Themes.
 *
 * No external plugin (Git Updater etc.) required.
 */

defined( 'ABSPATH' ) || exit;

class Acreage_Updater {

	/** @var string Theme directory name, read from the install itself — never hardcoded. */
	private $slug;

	/** @var WP_Theme */
	private $theme;

	/** @var string owner/repo on github.com */
	private $repo;

	/** @var string Optional token for private repos (define ACREAGE_GITHUB_TOKEN in wp-config.php). */
	private $token;

	/** @var string Transient key for the cached release payload. */
	private $cache_key;

	/** @var int Seconds to cache the GitHub response. */
	private $cache_ttl = 6 * HOUR_IN_SECONDS;

	public function __construct( $repo ) {
		$this->repo  = trim( $repo, '/ ' );
		$this->slug  = get_template();
		$this->theme = wp_get_theme( $this->slug );

		$this->token     = defined( 'ACREAGE_GITHUB_TOKEN' ) ? ACREAGE_GITHUB_TOKEN : '';
		$this->cache_key = 'acreage_gh_release_' . md5( $this->repo );

		add_filter( 'site_transient_update_themes', array( $this, 'inject_update' ) );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_dir' ), 10, 4 );
		add_action( 'upgrader_process_complete', array( $this, 'flush_cache' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'handle_manual_check' ) );
	}

	public function slug() {
		return $this->slug;
	}

	public function installed_version() {
		return $this->theme->get( 'Version' );
	}

	/* ---------------------------------------------------------------- API */

	/**
	 * Fetch the latest release from GitHub (cached).
	 *
	 * Always returns an array: either release data, or array( 'error' => 'why' ).
	 *
	 * @param bool $force Bypass the transient.
	 * @return array
	 */
	public function get_release( $force = false ) {
		if ( ! $force ) {
			$cached = get_site_transient( $this->cache_key );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$args = array(
			'timeout' => 15,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'acreage-theme-updater',
			),
		);
		if ( $this->token ) {
			$args['headers']['Authorization'] = 'Bearer ' . $this->token;
		}

		$response = wp_remote_get( "https://api.github.com/repos/{$this->repo}/releases/latest", $args );

		if ( is_wp_error( $response ) ) {
			return $this->cache_error( $response->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( 404 === $code ) {
			return $this->cache_error( __( 'no published release yet (or the repo is private and no token is set)', 'acreage' ) );
		}
		if ( 403 === $code ) {
			return $this->cache_error( __( 'GitHub API rate limit reached — try again shortly', 'acreage' ) );
		}
		if ( 200 !== $code ) {
			/* translators: %d: HTTP status code returned by the GitHub API. */
			return $this->cache_error( sprintf( __( 'GitHub returned HTTP %d', 'acreage' ), $code ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['tag_name'] ) ) {
			return $this->cache_error( __( 'the latest release has no tag name', 'acreage' ) );
		}

		$release = array(
			'version' => ltrim( $body['tag_name'], 'vV' ),
			'package' => $this->pick_package( $body ),
			'url'     => isset( $body['html_url'] ) ? $body['html_url'] : "https://github.com/{$this->repo}",
			'asset'   => $this->has_zip_asset( $body ),
		);

		if ( empty( $release['package'] ) ) {
			return $this->cache_error( __( 'the release has no downloadable package', 'acreage' ) );
		}

		set_site_transient( $this->cache_key, $release, $this->cache_ttl );

		return $release;
	}

	/** Cache a failure briefly so a bad repo name doesn't hammer the API. */
	private function cache_error( $message ) {
		$payload = array( 'error' => $message );
		set_site_transient( $this->cache_key, $payload, 15 * MINUTE_IN_SECONDS );
		return $payload;
	}

	/** Prefer an uploaded .zip asset; fall back to the auto-generated source zipball. */
	private function pick_package( $body ) {
		if ( ! empty( $body['assets'] ) && is_array( $body['assets'] ) ) {
			foreach ( $body['assets'] as $asset ) {
				if ( ! empty( $asset['browser_download_url'] ) && '.zip' === strtolower( substr( $asset['name'], -4 ) ) ) {
					return $asset['browser_download_url'];
				}
			}
		}
		return isset( $body['zipball_url'] ) ? $body['zipball_url'] : '';
	}

	private function has_zip_asset( $body ) {
		if ( empty( $body['assets'] ) || ! is_array( $body['assets'] ) ) {
			return false;
		}
		foreach ( $body['assets'] as $asset ) {
			if ( ! empty( $asset['name'] ) && '.zip' === strtolower( substr( $asset['name'], -4 ) ) ) {
				return true;
			}
		}
		return false;
	}

	/* ------------------------------------------------------------- Filters */

	/** Add our theme to WordPress's update queue when GitHub has something newer. */
	public function inject_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$release = $this->get_release();
		if ( isset( $release['error'] ) ) {
			return $transient;
		}

		$installed = $this->installed_version();

		if ( $installed && version_compare( $release['version'], $installed, '>' ) ) {
			$transient->response[ $this->slug ] = array(
				'theme'       => $this->slug,
				'new_version' => $release['version'],
				'url'         => $release['url'],
				'package'     => $release['package'],
			);
			unset( $transient->no_update[ $this->slug ] );
		} else {
			$transient->no_update[ $this->slug ] = array(
				'theme'       => $this->slug,
				'new_version' => $installed,
				'url'         => $release['url'],
				'package'     => '',
			);
		}

		return $transient;
	}

	/**
	 * GitHub zipballs extract to "owner-repo-a1b2c3/". WordPress requires the folder
	 * to equal the installed theme's folder or the update lands as a *new* theme.
	 */
	public function fix_source_dir( $source, $remote_source, $upgrader, $extra = array() ) {
		global $wp_filesystem;

		if ( empty( $extra['theme'] ) || $extra['theme'] !== $this->slug ) {
			return $source;
		}
		if ( ! $wp_filesystem || basename( $source ) === $this->slug ) {
			return $source;
		}

		// Defensive: if the theme sits one level down (a zip built from an older
		// layout), descend into it so we never nest acreage/acreage/.
		if (
			! $wp_filesystem->exists( trailingslashit( $source ) . 'style.css' )
			&& $wp_filesystem->exists( trailingslashit( $source ) . 'acreage/style.css' )
		) {
			$source = trailingslashit( $source ) . 'acreage';
		}

		// Nothing installable here — let WordPress raise its own error.
		if ( ! $wp_filesystem->exists( trailingslashit( $source ) . 'style.css' ) ) {
			return $source;
		}

		$corrected = trailingslashit( $remote_source ) . $this->slug;

		if ( $wp_filesystem->move( $source, $corrected, true ) ) {
			return trailingslashit( $corrected );
		}

		return new WP_Error(
			'acreage_rename_failed',
			/* translators: %s: theme folder name. */
			sprintf( __( 'Could not rename the downloaded theme folder to "%s".', 'acreage' ), $this->slug )
		);
	}

	/* ------------------------------------------------------------ Cache mgmt */

	public function flush_cache( $upgrader, $options ) {
		if ( isset( $options['type'] ) && 'theme' === $options['type'] ) {
			delete_site_transient( $this->cache_key );
		}
	}

	/** Appearance > Themes link: /wp-admin/themes.php?acreage-check-updates=1 */
	public function handle_manual_check() {
		if ( empty( $_GET['acreage-check-updates'] ) || ! current_user_can( 'update_themes' ) ) {
			return;
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'acreage-check' ) ) {
			return;
		}

		delete_site_transient( $this->cache_key );
		$this->get_release( true );
		delete_site_transient( 'update_themes' );

		wp_safe_redirect( admin_url( 'themes.php?acreage-checked=1' ) );
		exit;
	}
}
