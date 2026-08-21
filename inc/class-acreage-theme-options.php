<?php
/**
 * Appearance > Theme Options — the one screen for everything the theme reads.
 *
 * THE PROBLEM THIS FIXES
 *
 * The theme has always read around thirty editable values through
 * acreage_option(): the phone number in the header, the email and disclaimer in
 * the footer, the homepage headings, the statistics band. Every one of them was
 * readable and none of them was editable. A customer who wanted their own
 * telephone number in the header had to either edit a PHP file — which a theme
 * update then overwrites — or ask the developer. That is not a product.
 *
 * WHAT IS DELIBERATELY NOT HERE
 *
 * Colours and fonts. They already have a home: the palette and the type pairing
 * are mapped onto Elementor's system colours and system typography by
 * design-system.php, so the customer changes them under Site Settings and every
 * CSS variable in the theme follows. A second set of colour pickers here would
 * create two sources of truth that disagree the first time somebody uses the
 * wrong one. The last tab links there instead of duplicating it.
 *
 * HONESTY ABOUT THE HOMEPAGE FIELDS
 *
 * Elementor bakes content into a page when it is built, so editing "Hero
 * heading" here does NOT rewrite a homepage the demo importer has already
 * built — that page is edited in Elementor, as it should be. These fields seed
 * the build, and they drive the theme's own fallback homepage when Elementor is
 * not running. The tab says so, because a settings field that silently does
 * nothing is worse than no field at all.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

class Acreage_Theme_Options {

	/** @var string The screen's slug. */
	const PAGE = 'acreage-options';

	/** @var string Settings group name. */
	const GROUP = 'acreage_options_group';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/* ----------------------------------------------------------- the schema */

	/**
	 * Every field on the screen, grouped into tabs.
	 *
	 * One array, so adding a field is one edit rather than four — the renderer
	 * and the sanitiser both read from here.
	 *
	 * @return array[]
	 */
	public static function tabs() {
		return array(
			'site' => array(
				'label'  => __( 'Site details', 'acreage' ),
				'blurb'  => __( 'Used across the whole site — the header, the footer, and the pages the demo builds. Clear a field to fall back to the theme default.', 'acreage' ),
				'fields' => array(
					'phone' => array(
						'label' => __( 'Telephone', 'acreage' ),
						'type'  => 'text',
						'help'  => __( 'Shown in the header and the footer.', 'acreage' ),
					),
					'fax'   => array(
						'label' => __( 'Fax', 'acreage' ),
						'type'  => 'text',
						'help'  => __( 'Optional. Used on the contact page.', 'acreage' ),
					),
					'email' => array(
						'label' => __( 'Email', 'acreage' ),
						'type'  => 'email',
						'help'  => __( 'Shown in the footer. Where enquiries are SENT is set on the form itself.', 'acreage' ),
					),
					'legal' => array(
						'label' => __( 'Footer disclaimer', 'acreage' ),
						'type'  => 'textarea',
						'help'  => __( 'The short legal line at the foot of every page.', 'acreage' ),
					),
				),
			),

			'home' => array(
				'label'  => __( 'Homepage copy', 'acreage' ),
				'blurb'  => __( 'These seed the pages the demo importer builds, and they drive the homepage when Elementor is not running. A page that has ALREADY been built in Elementor is edited in Elementor — changing a field here will not rewrite it.', 'acreage' ),
				'fields' => array(
					'hero_title' => array( 'label' => __( 'Hero heading', 'acreage' ), 'type' => 'text' ),
					'hero_lede'  => array( 'label' => __( 'Hero paragraph', 'acreage' ), 'type' => 'textarea' ),
					'hero_place' => array( 'label' => __( 'Hero caption', 'acreage' ), 'type' => 'text' ),
					'hero_stat'  => array( 'label' => __( 'Hero figure', 'acreage' ), 'type' => 'text' ),
					'hero_alt'   => array(
						'label' => __( 'Hero image description', 'acreage' ),
						'type'  => 'text',
						'help'  => __( 'Read aloud by screen readers. Describe the photograph.', 'acreage' ),
					),
					'hero_image' => array( 'label' => __( 'Hero photograph', 'acreage' ), 'type' => 'image' ),

					'stat1_value' => array( 'label' => __( 'Statistic 1 — figure', 'acreage' ), 'type' => 'text' ),
					'stat1_label' => array( 'label' => __( 'Statistic 1 — wording', 'acreage' ), 'type' => 'text' ),
					'stat2_value' => array( 'label' => __( 'Statistic 2 — figure', 'acreage' ), 'type' => 'text' ),
					'stat2_label' => array( 'label' => __( 'Statistic 2 — wording', 'acreage' ), 'type' => 'text' ),
					'stat3_value' => array( 'label' => __( 'Statistic 3 — figure', 'acreage' ), 'type' => 'text' ),
					'stat3_label' => array( 'label' => __( 'Statistic 3 — wording', 'acreage' ), 'type' => 'text' ),
					'stat4_value' => array( 'label' => __( 'Statistic 4 — figure', 'acreage' ), 'type' => 'text' ),
					'stat4_label' => array( 'label' => __( 'Statistic 4 — wording', 'acreage' ), 'type' => 'text' ),

					'feat_title' => array( 'label' => __( 'Featured band — heading', 'acreage' ), 'type' => 'text' ),
					'feat_sub'   => array( 'label' => __( 'Featured band — sub-heading', 'acreage' ), 'type' => 'textarea' ),
					'prov_title' => array( 'label' => __( 'Provinces band — heading', 'acreage' ), 'type' => 'text' ),
					'prov_sub'   => array( 'label' => __( 'Provinces band — sub-heading', 'acreage' ), 'type' => 'textarea' ),

					'about_title' => array( 'label' => __( 'About band — heading', 'acreage' ), 'type' => 'text' ),
					'about_body'  => array( 'label' => __( 'About band — text', 'acreage' ), 'type' => 'textarea' ),

					'sell_title' => array( 'label' => __( 'Sell your farm — heading', 'acreage' ), 'type' => 'text' ),
					'sell_body'  => array( 'label' => __( 'Sell your farm — text', 'acreage' ), 'type' => 'textarea' ),
					'sell_image' => array( 'label' => __( 'Sell your farm — photograph', 'acreage' ), 'type' => 'image' ),
				),
			),

			'pages' => array(
				'label'  => __( 'Page headings', 'acreage' ),
				'blurb'  => __( 'The masthead on each of the built pages. Same rule as the homepage copy: these seed the build.', 'acreage' ),
				'fields' => array(
					'farms_title'    => array( 'label' => __( 'Farms for Sale — heading', 'acreage' ), 'type' => 'text' ),
					'farms_sub'      => array( 'label' => __( 'Farms for Sale — sub-heading', 'acreage' ), 'type' => 'textarea' ),
					'articles_title' => array( 'label' => __( 'Articles & News — heading', 'acreage' ), 'type' => 'text' ),
					'articles_sub'   => array( 'label' => __( 'Articles & News — sub-heading', 'acreage' ), 'type' => 'textarea' ),
					'contact_title'  => array( 'label' => __( 'Contact — heading', 'acreage' ), 'type' => 'text' ),
				),
			),
		);
	}

	/**
	 * The schema flattened to key => field.
	 *
	 * @return array[]
	 */
	private static function fields() {
		$flat = array();

		foreach ( self::tabs() as $tab ) {
			foreach ( $tab['fields'] as $key => $field ) {
				$flat[ $key ] = $field;
			}
		}

		return $flat;
	}

	/* ------------------------------------------------------------- plumbing */

	public function add_page() {
		add_theme_page(
			__( 'Acreage Theme Options', 'acreage' ),
			__( 'Theme Options', 'acreage' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render' )
		);
	}

	public function register() {
		register_setting(
			self::GROUP,
			ACREAGE_CONTENT_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Clean every submitted value according to its declared type.
	 *
	 * MERGES rather than replaces, for two reasons. The screen is tabbed, so any
	 * one submission carries only that tab's fields — a straight overwrite would
	 * wipe the other tabs every time somebody pressed Save. And the demo importer
	 * writes keys into this same option that no field here owns.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array
	 */
	public function sanitize( $input ) {
		$existing = get_option( ACREAGE_CONTENT_OPTION, array() );
		$existing = is_array( $existing ) ? $existing : array();

		if ( ! is_array( $input ) ) {
			return $existing;
		}

		foreach ( self::fields() as $key => $field ) {
			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}

			$value = wp_unslash( $input[ $key ] );

			switch ( $field['type'] ) {
				case 'email':
					$value = sanitize_email( $value );
					break;

				case 'textarea':
					$value = sanitize_textarea_field( $value );
					break;

				case 'image':
					$value = absint( $value );
					break;

				default:
					$value = sanitize_text_field( $value );
			}

			/*
			 * An emptied field is a request for the theme default, not a request
			 * to print nothing. acreage_option() already falls back when a key is
			 * missing, so removing the key is how "reset this one" is spelled.
			 */
			if ( '' === $value || 0 === $value ) {
				unset( $existing[ $key ] );
				continue;
			}

			$existing[ $key ] = $value;
		}

		return $existing;
	}

	/**
	 * The media picker needs wp.media, and only on this screen.
	 *
	 * @param string $hook Current admin page.
	 */
	public function assets( $hook ) {
		if ( 'appearance_page_' . self::PAGE !== $hook ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_script(
			'acreage-options',
			get_theme_file_uri( 'assets/js/theme-options.js' ),
			array( 'jquery' ),
			ACREAGE_VERSION,
			true
		);

		wp_localize_script( 'acreage-options', 'acreageOptions', array(
			'title'  => __( 'Choose photograph', 'acreage' ),
			'button' => __( 'Use this photograph', 'acreage' ),
		) );
	}

	/* --------------------------------------------------------------- render */

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$tabs = self::tabs();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switch.
		$current = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'site';

		if ( ! isset( $tabs[ $current ] ) && 'design' !== $current ) {
			$current = 'site';
		}

		$saved = get_option( ACREAGE_CONTENT_OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Theme Options', 'acreage' ); ?></h1>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $slug => $tab ) : ?>
					<a class="nav-tab <?php echo $current === $slug ? 'nav-tab-active' : ''; ?>"
						href="<?php echo esc_url( admin_url( 'themes.php?page=' . self::PAGE . '&tab=' . $slug ) ); ?>">
						<?php echo esc_html( $tab['label'] ); ?>
					</a>
				<?php endforeach; ?>
				<a class="nav-tab <?php echo 'design' === $current ? 'nav-tab-active' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'themes.php?page=' . self::PAGE . '&tab=design' ) ); ?>">
					<?php esc_html_e( 'Colours &amp; fonts', 'acreage' ); ?>
				</a>
			</h2>

			<?php if ( 'design' === $current ) : ?>
				<?php $this->render_design_tab(); ?>
			<?php else : ?>
				<p style="max-width:60em"><?php echo esc_html( $tabs[ $current ]['blurb'] ); ?></p>

				<form method="post" action="options.php">
					<?php settings_fields( self::GROUP ); ?>

					<table class="form-table" role="presentation">
						<tbody>
						<?php foreach ( $tabs[ $current ]['fields'] as $key => $field ) : ?>
							<?php $this->render_field( $key, $field, $saved ); ?>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php submit_button(); ?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * One row of the settings table.
	 *
	 * @param string $key   Option key.
	 * @param array  $field Schema entry.
	 * @param array  $saved Current values.
	 */
	private function render_field( $key, $field, $saved ) {
		$name  = ACREAGE_CONTENT_OPTION . '[' . $key . ']';
		$id    = 'acreage-' . $key;
		$value = isset( $saved[ $key ] ) ? $saved[ $key ] : '';
		?>
		<tr>
			<th scope="row">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			</th>
			<td>
				<?php if ( 'textarea' === $field['type'] ) : ?>
					<textarea class="large-text" rows="3" id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>

				<?php elseif ( 'image' === $field['type'] ) : ?>
					<div class="acreage-image-field">
						<input type="hidden" id="<?php echo esc_attr( $id ); ?>"
							name="<?php echo esc_attr( $name ); ?>"
							value="<?php echo esc_attr( (int) $value ); ?>">

						<p class="acreage-image-field__preview" style="margin:0 0 8px">
							<?php
							if ( $value ) {
								echo wp_get_attachment_image( (int) $value, 'medium', false, array( 'style' => 'max-width:260px;height:auto' ) );
							}
							?>
						</p>

						<button type="button" class="button acreage-image-field__pick">
							<?php esc_html_e( 'Choose photograph', 'acreage' ); ?>
						</button>
						<button type="button" class="button-link acreage-image-field__clear"
							<?php echo $value ? '' : 'style="display:none"'; ?>>
							<?php esc_html_e( 'Remove', 'acreage' ); ?>
						</button>
					</div>

				<?php else : ?>
					<input type="<?php echo 'email' === $field['type'] ? 'email' : 'text'; ?>"
						class="regular-text" id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						value="<?php echo esc_attr( $value ); ?>">
				<?php endif; ?>

				<?php if ( ! empty( $field['help'] ) ) : ?>
					<p class="description"><?php echo esc_html( $field['help'] ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * The Colours and fonts tab — a signpost, not a second control panel.
	 */
	private function render_design_tab() {
		$has_elementor = did_action( 'elementor/loaded' ) || class_exists( 'Elementor\Plugin' );
		?>
		<p style="max-width:60em">
			<?php esc_html_e( 'The palette and the two typefaces are mapped onto Elementor’s system colours and system typography, so you change them once and every heading, button, border and link in the theme follows — including the pages the demo built.', 'acreage' ); ?>
		</p>
		<p style="max-width:60em">
			<?php esc_html_e( 'They are deliberately not repeated on this screen. Two places to set the same colour is two places to disagree.', 'acreage' ); ?>
		</p>

		<?php if ( $has_elementor ) : ?>
			<p class="description">
				<?php esc_html_e( 'Edit any page with Elementor, then open the hamburger menu at the top left: Site Settings > Global Colors, and Site Settings > Global Fonts.', 'acreage' ); ?>
			</p>
		<?php else : ?>
			<div class="notice notice-warning inline"><p>
				<?php esc_html_e( 'Elementor is not active, so the theme is using its built-in palette and type. Install Elementor — the free version is enough — to change them without writing CSS.', 'acreage' ); ?>
			</p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'The palette', 'acreage' ); ?></h2>

		<table class="widefat striped" style="max-width:52em">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Role', 'acreage' ); ?></th>
					<th><?php esc_html_e( 'Default', 'acreage' ); ?></th>
					<th><?php esc_html_e( 'CSS variable', 'acreage' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( acreage_palette() as $colour ) : ?>
				<tr>
					<td><?php echo esc_html( $colour['title'] ); ?></td>
					<td>
						<span style="display:inline-block;width:14px;height:14px;vertical-align:-2px;border:1px solid rgba(0,0,0,.2);background:<?php echo esc_attr( $colour['hex'] ); ?>"></span>
						<code><?php echo esc_html( $colour['hex'] ); ?></code>
					</td>
					<td><code><?php echo esc_html( $colour['var'] ); ?></code></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<p class="description" style="max-width:52em">
			<?php esc_html_e( 'A child theme can override any of these variables on :root without touching a template.', 'acreage' ); ?>
		</p>
		<?php
	}
}
