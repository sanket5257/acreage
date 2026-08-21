<?php
/**
 * Elementor-editable header, footer and templates — on Elementor FREE.
 *
 * Elementor's Theme Builder is a Pro feature, so Free can never edit a header,
 * footer, archive or single template. It can edit pages perfectly well, though.
 *
 * So: the client builds a normal page in Elementor, and the theme renders that
 * page's content where the header (or footer, or archive wrapper) belongs. The
 * editing experience is Elementor; the placement is ours. No Pro licence, no
 * recurring cost, and nothing for a buyer of this template to pay for.
 *
 * If no page is chosen, or Elementor is not installed, the theme falls back to
 * its own coded header and footer. The site is never dependent on the builder.
 */

defined( 'ABSPATH' ) || exit;

class Acreage_Elementor_Layout {

	/** Option holding the chosen page ID per slot. */
	const OPTION = 'acreage_elementor_layout';

	/** @var int[] Posts already rendered this request, so nothing loops. */
	private static $rendering = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_post_acreage_save_layout', array( $this, 'save' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_elementor' ), 5 );
		add_action( 'after_switch_theme', array( $this, 'claim_typography' ) );
		add_action( 'admin_post_acreage_claim_typography', array( $this, 'handle_claim' ) );
		add_action( 'admin_post_acreage_create_slot_page', array( $this, 'create_slot_page' ) );
		add_action( 'pre_get_posts', array( $this, 'hide_slot_pages' ) );
		add_filter( 'wp_robots', array( $this, 'noindex_slot_pages' ) );
		add_filter( 'page_row_actions', array( $this, 'restore_elementor_link' ), 12, 2 );
		add_filter( 'post_row_actions', array( $this, 'restore_elementor_link' ), 12, 2 );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_link' ), 200 );
		add_action( 'admin_post_acreage_enable_pages', array( $this, 'handle_enable_pages' ) );
		add_action( 'admin_post_acreage_build_home', array( $this, 'build_home' ) );
		add_action( 'admin_post_acreage_build_everything', array( $this, 'build_everything' ) );
	}

	/* ------------------------------------------------------------- helpers */

	/** The slots a page can be assigned to. */
	public static function slots() {
		return array(
			'header'         => array(
				__( 'Header', 'acreage' ),
				__( 'Replaces the theme header on every page. Build it as a normal page in Elementor — logo, menu, phone number, whatever you want.', 'acreage' ),
				__( 'Design a header in Elementor', 'acreage' ),
			),
			'footer'         => array(
				__( 'Footer', 'acreage' ),
				__( 'Replaces the theme footer on every page.', 'acreage' ),
				__( 'Design a footer in Elementor', 'acreage' ),
			),
			'before_content' => array(
				__( 'Above every page', 'acreage' ),
				__( 'Optional strip that appears under the header site-wide — an announcement bar, for example.', 'acreage' ),
				__( 'Design the strip above every page', 'acreage' ),
			),
			'archive'        => array(
				__( 'Farms for Sale page', 'acreage' ),
				__( 'The layout used for the farm listings archive — filters and results. Build it with the Farm Filters and Farm Grid widgets.', 'acreage' ),
				__( 'Design the Farms for Sale page', 'acreage' ),
			),
			'single'         => array(
				__( 'Single farm page', 'acreage' ),
				__( 'The layout used for one farm. Build it with the Farm Details widgets — gallery, sections, facts, enquiry form.', 'acreage' ),
				__( 'Design the single farm page', 'acreage' ),
			),
			'after_content'  => array(
				__( 'Below every page', 'acreage' ),
				__( 'Optional strip above the footer site-wide — a call-to-action band, for example.', 'acreage' ),
				__( 'Design the strip below every page', 'acreage' ),
			),
		);
	}

	public static function settings() {
		$saved = get_option( self::OPTION, array() );

		return is_array( $saved ) ? $saved : array();
	}

	/** Page ID assigned to a slot, or 0. */
	public static function page_for( $slot ) {
		$settings = self::settings();

		return isset( $settings[ $slot ] ) ? (int) $settings[ $slot ] : 0;
	}

	public static function elementor_active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
	}

	/**
	 * Is this page actually built with Elementor, and does it contain anything?
	 *
	 * Elementor marks a page as "built with Elementor" the moment the editor is
	 * opened, before a single widget is placed. Treating that as usable would
	 * replace the theme header with an empty one and leave the site headerless
	 * until somebody finished designing. So an empty layout does not count.
	 */
	public static function built_with_elementor( $post_id ) {
		if ( ! $post_id || ! self::elementor_active() ) {
			return false;
		}

		if ( ! isset( \Elementor\Plugin::$instance->documents ) ) {
			return false;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id );

		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return false;
		}

		return self::has_content( $post_id );
	}

	/** Does the Elementor document hold at least one element? */
	public static function has_content( $post_id ) {
		$data = get_post_meta( $post_id, '_elementor_data', true );

		if ( is_string( $data ) ) {
			$data = json_decode( $data, true );
		}

		return is_array( $data ) && ! empty( $data );
	}

	/** Does this slot have a usable Elementor page behind it? */
	public static function has( $slot ) {
		$post_id = self::page_for( $slot );

		return $post_id && self::built_with_elementor( $post_id );
	}

	/**
	 * Print the Elementor content assigned to a slot.
	 *
	 * @return bool True if something was printed.
	 */
	public static function render( $slot ) {
		$post_id = self::page_for( $slot );

		if ( ! $post_id || ! self::built_with_elementor( $post_id ) ) {
			return false;
		}

		// A header page that somehow points at itself would recurse forever.
		if ( in_array( $post_id, self::$rendering, true ) ) {
			return false;
		}

		self::$rendering[] = $post_id;

		// phpcs:ignore WordPress.Security.EscapeOutput -- Elementor returns rendered, escaped markup.
		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $post_id, true );

		array_pop( self::$rendering );

		return true;
	}

	/**
	 * Elementor only loads its front-end stylesheets on pages it is driving.
	 * A header built in Elementor appears on every page, so its styles have to
	 * load on every page too — otherwise the header is unstyled everywhere
	 * except the header page itself.
	 */
	public function enqueue_elementor() {
		if ( ! self::elementor_active() ) {
			return;
		}

		$needed = false;

		foreach ( array_keys( self::slots() ) as $slot ) {
			if ( self::has( $slot ) ) {
				$needed = true;
				break;
			}
		}

		if ( ! $needed ) {
			return;
		}

		\Elementor\Plugin::$instance->frontend->enqueue_styles();

		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			foreach ( array_keys( self::slots() ) as $slot ) {
				$post_id = self::page_for( $slot );
				if ( $post_id && self::built_with_elementor( $post_id ) ) {
					$css = new \Elementor\Core\Files\CSS\Post( $post_id );
					$css->enqueue();
				}
			}
		}
	}


	/* ------------------------------------------------- Elementor defaults */

	/**
	 * Stop Elementor imposing its own fonts and colours on the whole site.
	 *
	 * Elementor puts an "elementor-kit-N" class on the body of EVERY page, then
	 * styles headings and body text through it — at a higher specificity than a
	 * theme's own rules, and on pages Elementor had nothing to do with. Installing
	 * the plugin therefore changes how the site looks, which is a surprise nobody
	 * asked for.
	 *
	 * Elementor ships two switches for exactly this. We set them once, on theme
	 * activation, and only when they have never been set — a deliberate choice by
	 * the site owner is left alone.
	 */
	public function claim_typography( $force = false ) {
		foreach ( array( 'elementor_disable_typography_schemes', 'elementor_disable_color_schemes' ) as $option ) {
			if ( $force || false === get_option( $option, false ) ) {
				update_option( $option, 'yes' );
			}
		}
	}

	public function handle_claim() {
		check_admin_referer( 'acreage_claim_typography' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$this->claim_typography( true );

		wp_safe_redirect( admin_url( 'themes.php?page=acreage-elementor-layout&typography=1' ) );
		exit;
	}

	/** Is Elementor currently overriding the theme's fonts or colours? */
	public static function elementor_is_overriding() {
		if ( ! self::elementor_active() ) {
			return false;
		}

		return 'yes' !== get_option( 'elementor_disable_typography_schemes' )
			|| 'yes' !== get_option( 'elementor_disable_color_schemes' );
	}

	/* --------------------------------------------------- creating a layout */

	/**
	 * Create a page for a slot and open it straight in Elementor.
	 *
	 * Without this the flow is: leave this screen, add a page, name it, build it,
	 * come back, find it in the dropdown, save. Six steps to answer "let me design
	 * my header", which is five too many.
	 */
	public function create_slot_page() {
		check_admin_referer( 'acreage_create_slot_page' );

		if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'publish_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$slot  = isset( $_POST['slot'] ) ? sanitize_key( $_POST['slot'] ) : '';
		$slots = self::slots();

		if ( ! isset( $slots[ $slot ] ) ) {
			wp_die( esc_html__( 'Unknown layout slot.', 'acreage' ) );
		}

		$post_id = wp_insert_post( array(
			'post_type'   => 'page',
			/* translators: %s: the slot name, e.g. Header. */
			'post_title'  => sprintf( __( 'Site %s', 'acreage' ), $slots[ $slot ][0] ),
			'post_status' => 'publish',
			'post_name'   => 'acreage-layout-' . $slot,
		), true );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			wp_die( esc_html__( 'The page could not be created.', 'acreage' ) );
		}

		// Mark it as a layout part, and as an Elementor document, so the editor
		// opens on the builder canvas rather than the classic editor.
		update_post_meta( $post_id, '_acreage_layout_slot', $slot );
		update_post_meta( $post_id, '_wp_page_template', 'page-full-width.php' );
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );

		$settings          = self::settings();
		$settings[ $slot ] = $post_id;
		update_option( self::OPTION, $settings );

		if ( self::elementor_active() ) {
			wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=elementor' ) );
			exit;
		}

		wp_safe_redirect( get_edit_post_link( $post_id, 'raw' ) );
		exit;
	}

	/** Layout pages are parts of the site, not destinations. Keep them out of listings. */
	public function hide_slot_pages( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_search() && ! $query->is_archive() ) {
			return;
		}

		$ids = array_filter( array_map( 'absint', array_values( self::settings() ) ) );

		if ( $ids ) {
			$query->set( 'post__not_in', array_merge( (array) $query->get( 'post__not_in' ), $ids ) );
		}
	}

	/** And out of search engines, since they are fragments of other pages. */
	public function noindex_slot_pages( $robots ) {
		if ( ! is_page() ) {
			return $robots;
		}

		$ids = array_filter( array_map( 'absint', array_values( self::settings() ) ) );

		if ( in_array( get_queried_object_id(), $ids, true ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}

		return $robots;
	}

	/**
	 * Put "Edit with Elementor" back on pages that have never been opened in it.
	 *
	 * Elementor 4 only shows that link on posts already built with Elementor:
	 *
	 *     is_editable_with_elementor() {
	 *         return is_editable_by_current_user() && is_built_with_elementor();
	 *     }
	 *
	 * Which leaves no way to start from the Pages list — the link appears only
	 * after you have done the thing the link is for. The button is still there
	 * inside the editor screen, but people look for it in the list first, and
	 * reasonably conclude the feature is missing.
	 *
	 * The URL is Elementor's own; we are only restoring a way to reach it.
	 */
	public function restore_elementor_link( $actions, $post ) {
		if ( ! self::elementor_active() || isset( $actions['edit_with_elementor'] ) ) {
			return $actions;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		if ( ! post_type_supports( $post->post_type, 'elementor' ) ) {
			return $actions;
		}

		// Anything Elementor itself treats as off-limits stays off-limits.
		$document = isset( \Elementor\Plugin::$instance->documents )
			? \Elementor\Plugin::$instance->documents->get( $post->ID )
			: null;

		if ( ! $document || ! $document->is_editable_by_current_user() ) {
			return $actions;
		}

		$actions['edit_with_elementor'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=elementor' ) ),
			esc_html__( 'Edit with Elementor', 'acreage' )
		);

		return $actions;
	}

	/**
	 * The same link in the toolbar, where people actually look for it.
	 *
	 * Viewing a page on the front end, the toolbar offers "Edit Page" and nothing
	 * else until Elementor has built that page. This adds the Elementor route
	 * beside it, so the entry point is wherever you happen to be standing.
	 */
	public function admin_bar_link( $bar ) {
		if ( is_admin() || ! self::elementor_active() || ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$document = isset( \Elementor\Plugin::$instance->documents )
			? \Elementor\Plugin::$instance->documents->get( $post_id )
			: null;

		if ( ! $document || ! $document->is_editable_by_current_user() ) {
			return;
		}

		// Already built with Elementor? Then Elementor shows its own entry.
		if ( $document->is_built_with_elementor() ) {
			return;
		}

		$bar->add_node( array(
			'id'    => 'acreage_edit_with_elementor',
			'title' => __( 'Edit with Elementor', 'acreage' ),
			'href'  => admin_url( 'post.php?post=' . $post_id . '&action=elementor' ),
		) );
	}


	/**
	 * Elementor can be configured to ignore Pages entirely, under
	 * Elementor > Settings > General > Post Types. When that happens every entry
	 * point disappears at once and nothing explains why, so say so plainly.
	 */
	public static function pages_enabled_in_elementor() {
		if ( ! self::elementor_active() ) {
			return true;
		}

		return post_type_supports( 'page', 'elementor' );
	}

	public function handle_enable_pages() {
		check_admin_referer( 'acreage_enable_pages' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$types = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
		$types = is_array( $types ) ? $types : array( 'page', 'post' );

		if ( ! in_array( 'page', $types, true ) ) {
			$types[] = 'page';
		}

		update_option( 'elementor_cpt_support', $types );

		wp_safe_redirect( admin_url( 'themes.php?page=acreage-elementor-layout&pages=1' ) );
		exit;
	}

	/* --------------------------------------------- the Elementor homepage */

	/**
	 * Build the homepage as an Elementor template and open it in the editor.
	 *
	 * The PHP homepage cannot be edited in Elementor, because its design is a
	 * template rather than page content. This creates the same design out of
	 * Elementor sections and the listings plugin's widgets, so every part of it
	 * can be moved, restyled or removed — without Elementor Pro.
	 */
	public function build_home() {
		check_admin_referer( 'acreage_build_home' );

		if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'publish_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		if ( ! self::elementor_active() ) {
			wp_die( esc_html__( 'Elementor is not active, so there is nothing to build with.', 'acreage' ) );
		}

		require_once get_template_directory() . '/inc/class-acreage-elementor-template.php';

		$builder = new Acreage_Elementor_Template();
		$data    = $builder->build();

		$existing = get_page_by_path( 'home-elementor' );

		$post_id = wp_insert_post( array(
			'ID'          => $existing ? $existing->ID : 0,
			'post_type'   => 'page',
			'post_title'  => __( 'Home (Elementor)', 'acreage' ),
			'post_name'   => 'home-elementor',
			'post_status' => 'publish',
		), true );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			wp_die( esc_html__( 'The page could not be created.', 'acreage' ) );
		}

		update_post_meta( $post_id, '_wp_page_template', 'page-full-width.php' );
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );

		// Build the page's CSS now so the first view is not unstyled.
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$css = new \Elementor\Core\Files\CSS\Post( $post_id );
			$css->update();
		}

		wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=elementor' ) );
		exit;
	}

	/* ------------------------------------------------ build every page */

	/**
	 * What each generated page is called, and which builder makes it.
	 *
	 * Slot pages are assigned to a layout slot; the rest are ordinary pages the
	 * client can link to from a menu.
	 */
	public static function buildable() {
		return array(
			'header'  => array( __( 'Site Header', 'acreage' ), 'build_header', 'header' ),
			'footer'  => array( __( 'Site Footer', 'acreage' ), 'build_footer', 'footer' ),
			'home'    => array( __( 'Home (Elementor)', 'acreage' ), 'build', '' ),
			'archive' => array( __( 'Farms for Sale layout', 'acreage' ), 'build_archive', 'archive' ),
			'single'  => array( __( 'Single farm layout', 'acreage' ), 'build_single', 'single' ),
			'about'   => array( __( 'About (Elementor)', 'acreage' ), 'build_about', '' ),
			'contact' => array( __( 'Contact (Elementor)', 'acreage' ), 'build_contact', '' ),
		);
	}

	public function build_everything() {
		check_admin_referer( 'acreage_build_everything' );

		if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'publish_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		if ( ! self::elementor_active() ) {
			wp_die( esc_html__( 'Elementor is not active, so there is nothing to build with.', 'acreage' ) );
		}

		require_once get_template_directory() . '/inc/class-acreage-elementor-template.php';

		$settings = self::settings();
		$made     = 0;

		foreach ( self::buildable() as $key => $config ) {
			list( $title, $builder, $slot ) = $config;

			// A fresh builder per page keeps element ids from colliding.
			$template = new Acreage_Elementor_Template();

			if ( ! method_exists( $template, $builder ) ) {
				continue;
			}

			$slug     = 'acreage-el-' . $key;
			$existing = get_page_by_path( $slug );

			$post_id = wp_insert_post( array(
				'ID'          => $existing ? $existing->ID : 0,
				'post_type'   => 'page',
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_status' => 'publish',
			), true );

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}

			update_post_meta( $post_id, '_wp_page_template', 'page-full-width.php' );
			update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
			update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
			update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $template->$builder() ) ) );

			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css = new \Elementor\Core\Files\CSS\Post( $post_id );
				$css->update();
			}

			if ( $slot ) {
				$settings[ $slot ] = $post_id;
			}

			$made++;
		}

		update_option( self::OPTION, $settings );

		wp_safe_redirect( admin_url( 'themes.php?page=acreage-elementor-layout&built=' . $made ) );
		exit;
	}
	/* ------------------------------------------------------------------ UI */

	public function add_page() {
		add_theme_page(
			__( 'Elementor Layout', 'acreage' ),
			__( 'Elementor Layout', 'acreage' ),
			'edit_theme_options',
			'acreage-elementor-layout',
			array( $this, 'render_page' )
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$settings = self::settings();
		$pages    = get_pages( array( 'sort_column' => 'menu_order,post_title', 'number' => 200 ) );
		$saved    = isset( $_GET['saved'] ) ? true : false;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Elementor Layout', 'acreage' ); ?></h1>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'acreage' ); ?></p></div>
			<?php endif; ?>

			<?php if ( isset( $_GET['built'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					printf(
						/* translators: %d: number of pages generated. */
						esc_html__( 'Built %d pages in Elementor and assigned the layouts.', 'acreage' ),
						absint( $_GET['built'] )
					);
					?>
				</p></div>
			<?php endif; ?>

			<?php if ( isset( $_GET['typography'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Elementor will now leave the theme’s fonts and colours alone.', 'acreage' ); ?></p></div>
			<?php endif; ?>

			<?php if ( ! self::pages_enabled_in_elementor() ) : ?>
				<div class="notice notice-error">
					<p><strong><?php esc_html_e( 'Elementor is switched off for Pages.', 'acreage' ); ?></strong></p>
					<p style="max-width:60em"><?php esc_html_e( 'While that is the case, no “Edit with Elementor” option appears anywhere — not in the Pages list, not in the toolbar, not on the editor screen. It is set under Elementor > Settings > General > Post Types.', 'acreage' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="acreage_enable_pages">
						<?php wp_nonce_field( 'acreage_enable_pages' ); ?>
						<?php submit_button( __( 'Switch Elementor on for Pages', 'acreage' ), 'primary', 'submit', false ); ?>
					</form>
				</div>
			<?php endif; ?>

			<?php if ( self::elementor_is_overriding() ) : ?>
				<div class="notice notice-warning">
					<p><strong><?php esc_html_e( 'Elementor is overriding the theme’s fonts and colours.', 'acreage' ); ?></strong></p>
					<p style="max-width:60em"><?php esc_html_e( 'Elementor adds its own styling to every page on the site, including pages it did not build, which is why the site can look different after installing it. This switches that off and hands the design back to the theme. You can still set fonts and colours per widget inside Elementor.', 'acreage' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="acreage_claim_typography">
						<?php wp_nonce_field( 'acreage_claim_typography' ); ?>
						<?php submit_button( __( 'Use the theme’s fonts and colours', 'acreage' ), 'primary', 'submit', false ); ?>
					</form>
				</div>
			<?php endif; ?>

			<?php if ( ! self::elementor_active() ) : ?>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'Elementor is not active. The theme is using its own header and footer, which is fine — install Elementor (the free version) if you want to design them yourself.', 'acreage' ); ?></p>
				</div>
			<?php endif; ?>

			<p style="max-width:60em">
				<?php esc_html_e( 'Build a normal page in Elementor, then choose it here. The theme will render it as your header or footer on every page.', 'acreage' ); ?>
			</p>
			<p style="max-width:60em">
				<strong><?php esc_html_e( 'Why it works this way:', 'acreage' ); ?></strong>
				<?php esc_html_e( 'Elementor’s Theme Builder — the feature that edits headers and footers directly — is only in Elementor Pro. This gives you the same result with the free version, and costs nothing.', 'acreage' ); ?>
			</p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="acreage_save_layout">
				<?php wp_nonce_field( 'acreage_save_layout' ); ?>

				<table class="form-table" role="presentation">
					<?php foreach ( self::slots() as $slot => $slot_info ) : ?>
						<?php
						$label = $slot_info[0];
						$hint  = $slot_info[1];
						$current = isset( $settings[ $slot ] ) ? (int) $settings[ $slot ] : 0;
						?>
						<tr>
							<th scope="row"><label for="acreage-slot-<?php echo esc_attr( $slot ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td>
								<select name="acreage_layout[<?php echo esc_attr( $slot ); ?>]" id="acreage-slot-<?php echo esc_attr( $slot ); ?>">
									<option value="0"><?php esc_html_e( '— use the theme’s own —', 'acreage' ); ?></option>
									<?php foreach ( $pages as $page ) : ?>
										<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $current, $page->ID ); ?>>
											<?php
											echo esc_html( $page->post_title );
											echo self::built_with_elementor( $page->ID ) ? '' : esc_html__( '  (not built in Elementor yet)', 'acreage' );
											?>
										</option>
									<?php endforeach; ?>
								</select>

								<p class="description" style="max-width:44em"><?php echo esc_html( $hint ); ?></p>

								<?php if ( ! $current ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px">
									<input type="hidden" name="action" value="acreage_create_slot_page">
									<input type="hidden" name="slot" value="<?php echo esc_attr( $slot ); ?>">
									<?php wp_nonce_field( 'acreage_create_slot_page' ); ?>
									<button type="submit" class="button button-primary">
										<?php echo esc_html( isset( $slot_info[2] ) ? $slot_info[2] : $label ); ?>
									</button>
								</form>
							<?php endif; ?>

							<?php if ( $current ) : ?>
									<p>
										<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $current . '&action=elementor' ) ); ?>" class="button">
											<?php esc_html_e( 'Edit with Elementor', 'acreage' ); ?>
										</a>
										<?php if ( ! self::built_with_elementor( $current ) ) : ?>
											<span class="description" style="color:#9C6423">
												<?php esc_html_e( 'Nothing has been placed on this page yet, so the theme’s own version is still showing. Open it in Elementor and add your first section.', 'acreage' ); ?>
											</span>
										<?php endif; ?>
									</p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>

				<?php submit_button( __( 'Save layout', 'acreage' ) ); ?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Build every page in Elementor', 'acreage' ); ?></h2>
			<p style="max-width:60em">
				<?php esc_html_e( 'Generates the header, footer, homepage, the Farms for Sale layout, the single farm layout, About and Contact — all as Elementor pages made from the farm widgets, and assigns the ones that are layouts. Nothing needs Elementor Pro.', 'acreage' ); ?>
			</p>
			<p style="max-width:60em">
				<strong><?php esc_html_e( 'Run it as many times as you like:', 'acreage' ); ?></strong>
				<?php esc_html_e( 'it rebuilds the same seven pages rather than making duplicates — which also means it discards edits you made to them.', 'acreage' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				onsubmit="return confirm('<?php echo esc_js( __( 'Rebuild all seven Elementor pages? Any changes you made to them will be replaced.', 'acreage' ) ); ?>');">
				<input type="hidden" name="action" value="acreage_build_everything">
				<?php wp_nonce_field( 'acreage_build_everything' ); ?>
				<?php submit_button( __( 'Build every page in Elementor', 'acreage' ), 'primary', 'submit', false ); ?>
			</form>
			<hr>

			<h2><?php esc_html_e( 'The homepage', 'acreage' ); ?></h2>
			<p style="max-width:60em">
				<?php esc_html_e( 'The homepage that ships with the theme is a PHP template, which is why it opens in Elementor as an empty canvas — its design is not page content. This builds the same homepage out of Elementor sections and the farm widgets, so you can rearrange it.', 'acreage' ); ?>
			</p>
			<p style="max-width:60em">
				<?php esc_html_e( 'It creates a page called “Home (Elementor)”. Look at it first, then set it as your front page under Settings > Reading if you prefer it. Nothing is overwritten either way.', 'acreage' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="acreage_build_home">
				<?php wp_nonce_field( 'acreage_build_home' ); ?>
				<?php submit_button( __( 'Build the homepage in Elementor', 'acreage' ), 'secondary', 'submit', false, self::elementor_active() ? array() : array( 'disabled' => 'disabled' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function save() {
		check_admin_referer( 'acreage_save_layout' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$submitted = isset( $_POST['acreage_layout'] ) ? (array) wp_unslash( $_POST['acreage_layout'] ) : array();
		$clean     = array();

		foreach ( array_keys( self::slots() ) as $slot ) {
			$clean[ $slot ] = isset( $submitted[ $slot ] ) ? absint( $submitted[ $slot ] ) : 0;
		}

		update_option( self::OPTION, $clean );

		wp_safe_redirect( admin_url( 'themes.php?page=acreage-elementor-layout&saved=1' ) );
		exit;
	}
}
