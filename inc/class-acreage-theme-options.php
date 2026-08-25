<?php
/**
 * Appearance > Theme Options — the theme's control panel.
 *
 * Colours, typography, buttons and layout, all editable here and all applying
 * across the whole site.
 *
 * HOW IT REACHES THE PAGE
 *
 * Every setting compiles to CSS custom properties on :root, plus a short list
 * of override rules for the things a variable cannot express on its own — button
 * padding, letter-spacing, the grid. Because the whole stylesheet is already
 * written against those variables (--acreage-moss, --acreage-serif and the rest)
 * setting one here changes every heading, button, border, link and card that
 * uses it, without a single rule in theme.css being rewritten.
 *
 * Only values that DIFFER from the design default are emitted, so a site that
 * has not been touched ships zero extra bytes and renders byte-identically to
 * one without this screen.
 *
 * ORDER MATTERS, AND THIS SCREEN WINS
 *
 * design-system.php mirrors the palette into Elementor's Global Colours and
 * inlines the kit's values at priority 20. This screen inlines at priority 30,
 * so where both have an opinion, Theme Options is the one that takes effect.
 * That is deliberate: this is the screen a customer will look for first, and a
 * control that visibly does nothing because something else quietly outranks it
 * is worse than no control. Elementor's Site Settings still drive Elementor's
 * own widgets, so both remain usable.
 *
 * WHAT IS STILL NOT HERE
 *
 * Page copy — the hero heading, the section wording. Pages are built in
 * Elementor and Elementor stores their content in the page, so a field here
 * could not change a page that already exists. It would sit there looking
 * editable and do nothing. That text is edited by opening the page.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

class Acreage_Theme_Options {

	/** @var string The screen's slug. */
	const PAGE = 'acreage-options';

	/** @var string Settings group name. */
	const GROUP = 'acreage_options_group';

	/**
	 * Every button the theme draws, in one place.
	 *
	 * Third-party form buttons are included because forms.css styles them to
	 * match, and a site where the enquiry form's button is rounded but the
	 * contact page's is square looks broken rather than customised.
	 */
	const BUTTONS = '.acreage-btn,.acreage-btn-o,.acreage-comments__form .submit,'
		. '.acreage-w-form__submit,.acreage-w-search__submit,.acreage-w-filters__submit,'
		. '.wpcf7 input[type="submit"],.wpcf7 button[type="submit"],'
		. '.wpforms-container button[type="submit"],.gform_wrapper input[type="submit"],'
		. '.fluentform button[type="submit"]';

	/** Input fields, which follow the button's corner radius. */
	const INPUTS = '.acreage-search input[type=search],.acreage-w-form__field input,'
		. '.acreage-w-form__field textarea,.wpcf7 input[type="text"],.wpcf7 input[type="email"],'
		. '.wpcf7 input[type="tel"],.wpcf7 textarea';

	/**
	 * Apply a pseudo-class to every selector in a comma-separated list.
	 *
	 * "a,b,c" . ":hover" attaches the pseudo-class to c alone and leaves a and b
	 * matching in every state — so a hover colour set on this screen appeared to
	 * work on one button out of eight. Each selector has to carry it.
	 *
	 * @param string $selectors Comma-separated selector list.
	 * @param string $pseudo    Pseudo-class, including the colon.
	 * @return string
	 */
	private static function each( $selectors, $pseudo ) {
		$out = array();

		foreach ( explode( ',', $selectors ) as $selector ) {
			$out[] = trim( $selector ) . $pseudo;
		}

		return implode( ',', $out );
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_init', array( $this, 'handle_reset' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/* ------------------------------------------------------------ typefaces */

	/**
	 * The typefaces on offer.
	 *
	 * 'stack' is what lands in the CSS variable. 'google' is the family name to
	 * request from Google Fonts, or '' for the faces already on every machine.
	 *
	 * WHY THE SYSTEM FACES COME FIRST
	 *
	 * A webfont costs an extra origin, a render-blocking request and a flash of
	 * fallback text. Georgia and Helvetica cost nothing and are what the design
	 * was approved in. The Google families are here because a customer buying a
	 * theme reasonably expects to change the typeface, but the default stays the
	 * one that loads instantly.
	 *
	 * @return array[]
	 */
	public static function fonts() {
		return array(
			'georgia'    => array( 'label' => 'Georgia (default heading)', 'stack' => 'Georgia,"Times New Roman",serif', 'google' => '' ),
			'helvetica'  => array( 'label' => 'Helvetica (default body)', 'stack' => '"Helvetica Neue",Helvetica,"Segoe UI",Arial,sans-serif', 'google' => '' ),
			'system'     => array( 'label' => 'System UI', 'stack' => '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif', 'google' => '' ),
			'times'      => array( 'label' => 'Times New Roman', 'stack' => '"Times New Roman",Times,serif', 'google' => '' ),
			'playfair'   => array( 'label' => 'Playfair Display', 'stack' => '"Playfair Display",Georgia,serif', 'google' => 'Playfair Display:wght@400;500;600;700' ),
			'lora'       => array( 'label' => 'Lora', 'stack' => 'Lora,Georgia,serif', 'google' => 'Lora:wght@400;500;600;700' ),
			'cormorant'  => array( 'label' => 'Cormorant Garamond', 'stack' => '"Cormorant Garamond",Georgia,serif', 'google' => 'Cormorant Garamond:wght@400;500;600;700' ),
			'baskerville' => array( 'label' => 'Libre Baskerville', 'stack' => '"Libre Baskerville",Georgia,serif', 'google' => 'Libre Baskerville:wght@400;700' ),
			'merriweather' => array( 'label' => 'Merriweather', 'stack' => 'Merriweather,Georgia,serif', 'google' => 'Merriweather:wght@400;700' ),
			'inter'      => array( 'label' => 'Inter', 'stack' => 'Inter,"Helvetica Neue",Arial,sans-serif', 'google' => 'Inter:wght@400;500;600;700' ),
			'dmsans'     => array( 'label' => 'DM Sans', 'stack' => '"DM Sans","Helvetica Neue",Arial,sans-serif', 'google' => 'DM Sans:wght@400;500;700' ),
			'worksans'   => array( 'label' => 'Work Sans', 'stack' => '"Work Sans","Helvetica Neue",Arial,sans-serif', 'google' => 'Work Sans:wght@400;500;600;700' ),
			'montserrat' => array( 'label' => 'Montserrat', 'stack' => 'Montserrat,"Helvetica Neue",Arial,sans-serif', 'google' => 'Montserrat:wght@400;500;600;700' ),
			'karla'      => array( 'label' => 'Karla', 'stack' => 'Karla,"Helvetica Neue",Arial,sans-serif', 'google' => 'Karla:wght@400;500;700' ),
			'sourcesans' => array( 'label' => 'Source Sans 3', 'stack' => '"Source Sans 3","Helvetica Neue",Arial,sans-serif', 'google' => 'Source Sans 3:wght@400;600;700' ),
			'spacegrotesk' => array( 'label' => 'Space Grotesk', 'stack' => '"Space Grotesk","Helvetica Neue",Arial,sans-serif', 'google' => 'Space Grotesk:wght@400;500;700' ),
		);
	}

	/** Font keys mapped to their labels, for a select control. */
	private static function font_choices() {
		$out = array();

		foreach ( self::fonts() as $key => $font ) {
			$out[ $key ] = $font['label'];
		}

		return $out;
	}

	/* ----------------------------------------------------------- the schema */

	/**
	 * Every control on the screen, grouped into tabs.
	 *
	 * One array, so adding a control is one edit: the renderer, the sanitiser,
	 * the defaults and the CSS generator all read from here. 'default' is
	 * load-bearing — the CSS generator emits a rule only where the saved value
	 * differs from it.
	 *
	 * @return array[]
	 */
	public static function tabs() {
		$tabs = array();

		/* --------------------------------------------------------- colours */

		$colours = array();

		foreach ( acreage_palette() as $key => $colour ) {
			$colours[ 'color_' . $key ] = array(
				'label'   => $colour['title'],
				'type'    => 'color',
				'default' => $colour['hex'],
				'var'     => $colour['var'],
			);
		}

		$tabs['colours'] = array(
			'label'  => __( 'Colours', 'acreage' ),
			'blurb'  => __( 'The whole stylesheet is written against these, so a change here reaches every heading, button, border, link and card on the site. Primary is the main brand colour; Accent is the hover and highlight colour.', 'acreage' ),
			'fields' => $colours,
		);

		/* ------------------------------------------------------ typography */

		$tabs['typography'] = array(
			'label'  => __( 'Typography', 'acreage' ),
			'blurb'  => __( 'Headings and body text are set separately. The two default faces are already on every machine and load instantly; the rest are served from Google Fonts and are only requested when you pick one.', 'acreage' ),
			'fields' => array(
				'type_heading' => array(
					'label'   => __( 'Heading typeface', 'acreage' ),
					'type'    => 'select',
					'default' => 'georgia',
					'choices' => self::font_choices(),
					'help'    => __( 'Every heading, card title and page masthead.', 'acreage' ),
				),
				'type_body'    => array(
					'label'   => __( 'Body typeface', 'acreage' ),
					'type'    => 'select',
					'default' => 'helvetica',
					'choices' => self::font_choices(),
					'help'    => __( 'Body copy, navigation, labels and buttons.', 'acreage' ),
				),
				'type_size'    => array(
					'label'   => __( 'Body text size', 'acreage' ),
					'type'    => 'number',
					'default' => '17',
					'min'     => 14,
					'max'     => 22,
					'suffix'  => 'px',
				),
				'type_line'    => array(
					'label'   => __( 'Line height', 'acreage' ),
					'type'    => 'select',
					'default' => '1.65',
					'choices' => array(
						'1.45' => __( 'Tight', 'acreage' ),
						'1.65' => __( 'Comfortable', 'acreage' ),
						'1.85' => __( 'Airy', 'acreage' ),
					),
				),
				'type_hsize'   => array(
					'label'   => __( 'Heading scale', 'acreage' ),
					'type'    => 'select',
					'default' => '100',
					'choices' => array(
						'85'  => __( 'Smaller', 'acreage' ),
						'100' => __( 'Standard', 'acreage' ),
						'115' => __( 'Larger', 'acreage' ),
						'130' => __( 'Statement', 'acreage' ),
					),
					'help'    => __( 'Scales every heading together, so the hierarchy holds.', 'acreage' ),
				),
			),
		);

		/* --------------------------------------------------------- buttons */

		$tabs['buttons'] = array(
			'label'  => __( 'Buttons', 'acreage' ),
			'blurb'  => __( 'Applies to every button on the site at once — page buttons, the enquiry form, the search and filter buttons, comment forms, and any contact form plugin the theme styles.', 'acreage' ),
			'fields' => array(
				'btn_style'    => array(
					'label'   => __( 'Style', 'acreage' ),
					'type'    => 'select',
					'default' => 'solid',
					'choices' => array(
						'solid'   => __( 'Solid fill', 'acreage' ),
						'outline' => __( 'Outline', 'acreage' ),
					),
				),
				'btn_bg'       => array(
					'label'   => __( 'Background', 'acreage' ),
					'type'    => 'color',
					'default' => '#354027',
					'help'    => __( 'With the Outline style this colours the border and the label instead.', 'acreage' ),
				),
				'btn_text'     => array(
					'label'   => __( 'Text colour', 'acreage' ),
					'type'    => 'color',
					'default' => '#F4F2EA',
				),
				'btn_hover'    => array(
					'label'   => __( 'Hover background', 'acreage' ),
					'type'    => 'color',
					'default' => '#9C6423',
				),
				'btn_radius'   => array(
					'label'   => __( 'Corner radius', 'acreage' ),
					'type'    => 'number',
					'default' => '0',
					'min'     => 0,
					'max'     => 999,
					'suffix'  => 'px',
					'help'    => __( 'Applied to input fields too, so a form never has rounded buttons above square boxes. 999 gives a pill.', 'acreage' ),
				),
				'btn_pad_y'    => array(
					'label'   => __( 'Vertical padding', 'acreage' ),
					'type'    => 'number',
					'default' => '13',
					'min'     => 6,
					'max'     => 28,
					'suffix'  => 'px',
				),
				'btn_pad_x'    => array(
					'label'   => __( 'Horizontal padding', 'acreage' ),
					'type'    => 'number',
					'default' => '28',
					'min'     => 10,
					'max'     => 64,
					'suffix'  => 'px',
				),
				'btn_transform' => array(
					'label'   => __( 'Wording', 'acreage' ),
					'type'    => 'select',
					'default' => 'uppercase',
					'choices' => array(
						'uppercase' => __( 'UPPERCASE', 'acreage' ),
						'none'      => __( 'As typed', 'acreage' ),
					),
				),
				'btn_tracking' => array(
					'label'   => __( 'Letter spacing', 'acreage' ),
					'type'    => 'select',
					'default' => '0.14',
					'choices' => array(
						'0'    => __( 'None', 'acreage' ),
						'0.06' => __( 'Slight', 'acreage' ),
						'0.14' => __( 'Standard', 'acreage' ),
						'0.2'  => __( 'Wide', 'acreage' ),
					),
				),
			),
		);


		/* ---------------------------------------------------------- layout */

		$tabs['layout'] = array(
			'label'  => __( 'Layout', 'acreage' ),
			'blurb'  => __( 'How wide the site runs, and how the farm grid is arranged.', 'acreage' ),
			'fields' => array(
				'ui_content_width' => array(
					'label'   => __( 'Content width', 'acreage' ),
					'type'    => 'number',
					'default' => '1440',
					'min'     => 960,
					'max'     => 1920,
					'suffix'  => 'px',
				),
				'ui_card_columns'  => array(
					'label'   => __( 'Farms per row', 'acreage' ),
					'type'    => 'select',
					'default' => '3',
					'choices' => array(
						'2' => __( 'Two', 'acreage' ),
						'3' => __( 'Three', 'acreage' ),
						'4' => __( 'Four', 'acreage' ),
					),
					'help'    => __( 'On a desktop screen. Narrower screens always collapse to fewer.', 'acreage' ),
				),
				'ui_card_ratio'    => array(
					'label'   => __( 'Farm photograph shape', 'acreage' ),
					'type'    => 'select',
					'default' => '3/2',
					'choices' => array(
						'3/2'  => __( 'Landscape 3:2', 'acreage' ),
						'4/3'  => __( 'Landscape 4:3', 'acreage' ),
						'16/9' => __( 'Wide 16:9', 'acreage' ),
						'1/1'  => __( 'Square', 'acreage' ),
					),
				),
				'ui_card_radius'   => array(
					'label'   => __( 'Photograph corners', 'acreage' ),
					'type'    => 'number',
					'default' => '0',
					'min'     => 0,
					'max'     => 40,
					'suffix'  => 'px',
				),
			),
		);

		/* ----------------------------------------------------------- site */

		$tabs['site'] = array(
			'label'  => __( 'Site details', 'acreage' ),
			'blurb'  => __( 'Printed by the theme’s own header and footer on every page, and read fresh each time, so these take effect immediately. Clear a field to fall back to the theme default.', 'acreage' ),
			'fields' => array(
				'phone' => array(
					'label'   => __( 'Telephone', 'acreage' ),
					'type'    => 'text',
					'default' => '',
					'help'    => __( 'Shown in the header and the footer.', 'acreage' ),
				),
				'fax'   => array(
					'label'   => __( 'Fax', 'acreage' ),
					'type'    => 'text',
					'default' => '',
				),
				'email' => array(
					'label'   => __( 'Email', 'acreage' ),
					'type'    => 'email',
					'default' => '',
					'help'    => __( 'Shown in the footer. Where enquiries are SENT is set on the form itself.', 'acreage' ),
				),
				'legal' => array(
					'label'   => __( 'Footer disclaimer', 'acreage' ),
					'type'    => 'textarea',
					'default' => '',
				),
			),
		);

		return $tabs;
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

	/**
	 * A saved value, or the schema default.
	 *
	 * @param string $key Field key.
	 * @return string
	 */
	public static function get( $key ) {
		$fields = self::fields();

		if ( ! isset( $fields[ $key ] ) ) {
			return '';
		}

		$saved = get_option( ACREAGE_CONTENT_OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();

		if ( isset( $saved[ $key ] ) && '' !== $saved[ $key ] ) {
			return (string) $saved[ $key ];
		}

		return (string) $fields[ $key ]['default'];
	}

	/** True when the value differs from the design default. */
	private static function changed( $key ) {
		$fields = self::fields();

		return isset( $fields[ $key ] ) && self::get( $key ) !== (string) $fields[ $key ]['default'];
	}

	/* -------------------------------------------------------------- the CSS */

	/**
	 * Build the override block for whatever differs from the design default.
	 *
	 * Returns '' when nothing has been changed, which is the common case.
	 *
	 * @return string
	 */
	public static function ui_css() {
		$root  = array();
		$rules = array();

		/* --------------------------------------------------------- colours */

		foreach ( acreage_palette() as $key => $colour ) {
			if ( self::changed( 'color_' . $key ) ) {
				$root[] = $colour['var'] . ':' . self::get( 'color_' . $key );
			}
		}

		/* ------------------------------------------------------ typography */

		$fonts = self::fonts();

		if ( self::changed( 'type_heading' ) ) {
			$key = self::get( 'type_heading' );
			if ( isset( $fonts[ $key ] ) ) {
				$root[] = '--acreage-serif:' . $fonts[ $key ]['stack'];
			}
		}

		if ( self::changed( 'type_body' ) ) {
			$key = self::get( 'type_body' );
			if ( isset( $fonts[ $key ] ) ) {
				$root[] = '--acreage-sans:' . $fonts[ $key ]['stack'];
			}
		}

		$body = array();

		if ( self::changed( 'type_size' ) ) {
			$body[] = 'font-size:' . (int) self::get( 'type_size' ) . 'px';
		}

		if ( self::changed( 'type_line' ) ) {
			$body[] = 'line-height:' . self::get( 'type_line' );
		}

		if ( $body ) {
			$rules[] = 'body{' . implode( ';', $body ) . ';}';
		}

		if ( self::changed( 'type_hsize' ) ) {
			/*
			 * One multiplier on the root font size would also scale body copy,
			 * which is set separately above and would then move twice. Scaling
			 * the headings themselves keeps the two controls independent.
			 */
			$scale = (int) self::get( 'type_hsize' ) / 100;

			$rules[] = 'h1{font-size:calc(clamp(1.9rem,4vw,2.85rem) * ' . $scale . ');}';
			$rules[] = 'h2{font-size:calc(1.7rem * ' . $scale . ');}';
			$rules[] = 'h3{font-size:calc(1.35rem * ' . $scale . ');}';
			$rules[] = '.acreage-card__title{font-size:calc(1.28rem * ' . $scale . ');}';
		}

		/* --------------------------------------------------------- buttons */

		/*
		 * Written as CSS variables, not as rules against a list of selectors.
		 *
		 * The list approach was wrong in a way that only showed up as the theme
		 * grew: it had to name every button class by hand, so the filter panel's
		 * button and the search button were simply missed and stayed a different
		 * size from everything else. Setting the contract on :root reaches every
		 * button that reads it — the plugin's included, which is where three of
		 * the mismatches were — and reaches ones added later for free.
		 */
		if ( self::changed( 'btn_radius' ) ) {
			$root[]  = '--acreage-btn-radius:' . (int) self::get( 'btn_radius' ) . 'px';

			// Input fields follow the buttons, so a form never ends up with
			// rounded buttons above square boxes.
			$rules[] = self::INPUTS . '{border-radius:' . (int) self::get( 'btn_radius' ) . 'px;}';
		}

		if ( self::changed( 'btn_pad_y' ) ) {
			$root[] = '--acreage-btn-pad-y:' . (int) self::get( 'btn_pad_y' ) . 'px';
		}

		if ( self::changed( 'btn_pad_x' ) ) {
			$root[] = '--acreage-btn-pad-x:' . (int) self::get( 'btn_pad_x' ) . 'px';
		}

		if ( self::changed( 'btn_transform' ) ) {
			$root[] = '--acreage-btn-transform:' . self::get( 'btn_transform' );
		}

		if ( self::changed( 'btn_tracking' ) ) {
			$tracking = (float) self::get( 'btn_tracking' );
			$root[]   = '--acreage-btn-tracking:' . ( $tracking ? $tracking . 'em' : 'normal' );
		}

		if ( 'outline' === self::get( 'btn_style' ) ) {
			/*
			 * The one case a variable cannot express alone: the fill has to go
			 * transparent while the border and label take the background colour,
			 * and the hover has to fill back in.
			 */
			$root[]  = '--acreage-btn-border:1px';
			$rules[] = self::each( self::BUTTONS, '' ) . '{background:transparent;color:'
				. self::get( 'btn_bg' ) . ';border-color:' . self::get( 'btn_bg' ) . ';}';
			$rules[] = self::each( self::BUTTONS, ':hover' ) . '{background:' . self::get( 'btn_bg' )
				. ';color:' . self::get( 'btn_text' ) . ';border-color:' . self::get( 'btn_bg' ) . ';}';
		} else {
			if ( self::changed( 'btn_bg' ) ) {
				$root[] = '--acreage-btn-bg:' . self::get( 'btn_bg' );
			}

			if ( self::changed( 'btn_text' ) ) {
				$root[] = '--acreage-btn-text:' . self::get( 'btn_text' );
			}

			if ( self::changed( 'btn_hover' ) ) {
				$root[] = '--acreage-btn-hover-bg:' . self::get( 'btn_hover' );
			}
		}


		/* ---------------------------------------------------------- layout */

		if ( self::changed( 'ui_content_width' ) ) {
			$width  = (int) self::get( 'ui_content_width' );
			$root[] = '--acreage-wrap:' . $width . 'px';
			$root[] = '--acreage-max:' . $width . 'px';
		}

		if ( self::changed( 'ui_card_columns' ) ) {
			/*
			 * Scoped to a desktop width. The stylesheet collapses the grid on
			 * narrow screens further down, and an unscoped override would win
			 * over that and force four columns onto a phone.
			 */
			$rules[] = '@media (min-width:881px){.acreage-w-grid{grid-template-columns:repeat('
				. (int) self::get( 'ui_card_columns' ) . ',minmax(0,1fr));}}';
		}

		if ( self::changed( 'ui_card_ratio' ) ) {
			$rules[] = '.acreage-w-card__media{aspect-ratio:' . self::get( 'ui_card_ratio' ) . ';}';
		}

		if ( self::changed( 'ui_card_radius' ) ) {
			$rules[] = '.acreage-w-card__media,.acreage-w-gallery__item{border-radius:'
				. (int) self::get( 'ui_card_radius' ) . 'px;overflow:hidden;}';
		}

		if ( $root ) {
			array_unshift( $rules, ':root{' . implode( ';', $root ) . ';}' );
		}

		return implode( "\n", $rules );
	}

	/**
	 * The Google Fonts URL for the chosen faces, or '' when neither needs one.
	 *
	 * Both faces are requested in a single stylesheet rather than two, because
	 * two <link>s to the same host is a wasted round trip.
	 *
	 * @return string
	 */
	public static function google_fonts_url() {
		$fonts    = self::fonts();
		$families = array();

		foreach ( array( 'type_heading', 'type_body' ) as $control ) {
			$key = self::get( $control );

			if ( isset( $fonts[ $key ] ) && $fonts[ $key ]['google'] ) {
				$families[ $fonts[ $key ]['google'] ] = true;
			}
		}

		if ( ! $families ) {
			return '';
		}

		/*
		 * Built by hand rather than with add_query_arg(). The Google Fonts v2
		 * API takes "family" once per typeface, and add_query_arg() treats a
		 * repeated key as a replacement — so asking for a heading face and a
		 * body face silently requested only the second one.
		 */
		$query = array();

		foreach ( array_keys( $families ) as $family ) {
			$query[] = 'family=' . rawurlencode( $family );
		}

		// swap keeps text visible while the font loads instead of hiding it.
		$query[] = 'display=swap';

		return 'https://fonts.googleapis.com/css2?' . implode( '&', $query );
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
	 * Colour pickers, and the script that starts them.
	 *
	 * @param string $hook Current admin page.
	 */
	public function assets( $hook ) {
		if ( 'appearance_page_' . self::PAGE !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(function($){$(".acreage-color").wpColorPicker();});'
		);
	}

	/**
	 * Put every control on this screen back to its design default.
	 *
	 * Only keys this screen owns are removed. The demo importer writes into the
	 * same option, and a reset that wiped its record would leave content that
	 * "Remove demo content" could no longer see.
	 */
	public function handle_reset() {
		if ( empty( $_GET['acreage-reset'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'acreage-reset' );

		$saved = get_option( ACREAGE_CONTENT_OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();

		foreach ( array_keys( self::fields() ) as $key ) {
			unset( $saved[ $key ] );
		}

		/*
		 * register_setting() hooks sanitize() to sanitize_option_{option}, which
		 * fires on every update_option() call for this option — not just saves
		 * from the form. sanitize() merges by design (a key absent from the
		 * incoming array is left untouched, so saving one tab doesn't wipe the
		 * others), which would otherwise restore every key this reset just
		 * removed straight from the untouched DB row.
		 */
		remove_filter( 'sanitize_option_' . ACREAGE_CONTENT_OPTION, array( $this, 'sanitize' ) );
		update_option( ACREAGE_CONTENT_OPTION, $saved );

		wp_safe_redirect( admin_url( 'themes.php?page=' . self::PAGE . '&acreage-reset-done=1' ) );
		exit;
	}

	/**
	 * Clean every submitted value according to its declared type.
	 *
	 * MERGES rather than replaces, for two reasons. The screen is tabbed, so any
	 * one submission carries only that tab's fields — a straight overwrite would
	 * wipe the other tabs every time somebody pressed Save. And the demo importer
	 * writes keys into this same option that no field here owns.
	 *
	 * Selects are validated against their own choices and colours against a hex
	 * pattern, rather than merely escaped: these values are written into a
	 * stylesheet, so anything unexpected has to be refused rather than passed
	 * through.
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
				case 'color':
					$value = sanitize_hex_color( $value );
					$value = $value ? $value : $field['default'];
					break;

				case 'select':
					$value = array_key_exists( $value, $field['choices'] ) ? $value : $field['default'];
					break;

				case 'number':
					$value = (int) $value;
					if ( $value < $field['min'] || $value > $field['max'] ) {
						$value = (int) $field['default'];
					}
					$value = (string) $value;
					break;

				case 'email':
					$value = sanitize_email( $value );
					break;

				case 'textarea':
					$value = sanitize_textarea_field( $value );
					break;

				default:
					$value = sanitize_text_field( $value );
			}

			/*
			 * A value identical to the default would make ui_css() behave the
			 * same but leave the database carrying rows that mean "unchanged".
			 * Drop them, so "has this site been customised?" stays answerable.
			 */
			if ( '' === $value || (string) $value === (string) $field['default'] ) {
				unset( $existing[ $key ] );
				continue;
			}

			$existing[ $key ] = $value;
		}

		return $existing;
	}

	/* --------------------------------------------------------------- render */

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'acreage' ) );
		}

		$tabs = self::tabs();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab switch.
		$current = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'colours';

		if ( ! isset( $tabs[ $current ] ) ) {
			$current = 'colours';
		}

		$reset_url = wp_nonce_url(
			admin_url( 'themes.php?page=' . self::PAGE . '&acreage-reset=1' ),
			'acreage-reset'
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Theme Options', 'acreage' ); ?></h1>

			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<?php if ( ! empty( $_GET['acreage-reset-done'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php esc_html_e( 'Every setting is back to the design default.', 'acreage' ); ?>
				</p></div>
			<?php endif; ?>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $tabs as $slug => $tab ) : ?>
					<a class="nav-tab <?php echo $current === $slug ? 'nav-tab-active' : ''; ?>"
						href="<?php echo esc_url( admin_url( 'themes.php?page=' . self::PAGE . '&tab=' . $slug ) ); ?>">
						<?php echo esc_html( $tab['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<p style="max-width:60em"><?php echo esc_html( $tabs[ $current ]['blurb'] ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( self::GROUP ); ?>

				<table class="form-table" role="presentation">
					<tbody>
					<?php foreach ( $tabs[ $current ]['fields'] as $key => $field ) : ?>
						<?php $this->render_field( $key, $field ); ?>
					<?php endforeach; ?>
					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr>

			<h2><?php esc_html_e( 'Start again', 'acreage' ); ?></h2>
			<p class="description" style="max-width:60em">
				<?php esc_html_e( 'Puts every control on every tab back to the design default. Your content, pages and farms are not touched.', 'acreage' ); ?>
			</p>
			<p>
				<a class="button" href="<?php echo esc_url( $reset_url ); ?>"
					onclick="return confirm('<?php echo esc_js( __( 'Reset every Theme Options setting to the default?', 'acreage' ) ); ?>');">
					<?php esc_html_e( 'Reset all settings', 'acreage' ); ?>
				</a>
			</p>

			<?php if ( 'site' !== $current ) : ?>
				<p class="description" style="max-width:60em">
					<?php esc_html_e( 'Looking for the page wording? That is edited on the page itself — open it with Elementor. These settings are site-wide appearance.', 'acreage' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * One row of the settings table.
	 *
	 * @param string $key   Option key.
	 * @param array  $field Schema entry.
	 */
	private function render_field( $key, $field ) {
		$name  = ACREAGE_CONTENT_OPTION . '[' . $key . ']';
		$id    = 'acreage-' . $key;
		$value = self::get( $key );
		?>
		<tr>
			<th scope="row">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			</th>
			<td>
				<?php if ( 'color' === $field['type'] ) : ?>
					<input type="text" class="acreage-color" id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						data-default-color="<?php echo esc_attr( $field['default'] ); ?>">

				<?php elseif ( 'select' === $field['type'] ) : ?>
					<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
						<?php foreach ( $field['choices'] as $choice => $label ) : ?>
							<option value="<?php echo esc_attr( $choice ); ?>" <?php selected( $value, $choice ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>

				<?php elseif ( 'number' === $field['type'] ) : ?>
					<input type="number" class="small-text" id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						min="<?php echo esc_attr( $field['min'] ); ?>"
						max="<?php echo esc_attr( $field['max'] ); ?>">
					<?php echo isset( $field['suffix'] ) ? esc_html( $field['suffix'] ) : ''; ?>

				<?php elseif ( 'textarea' === $field['type'] ) : ?>
					<textarea class="large-text" rows="3" id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>

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
}
