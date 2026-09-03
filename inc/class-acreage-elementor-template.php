<?php
/**
 * The homepage, rebuilt as an Elementor template.
 *
 * page-home.php reproduces the approved mockup exactly, but it is PHP: opening
 * it in Elementor shows an empty canvas, because the design is not page content.
 * That is the right trade for a site nobody needs to rearrange, and the wrong one
 * for a template people buy.
 *
 * So this builds the same homepage out of Elementor sections and the listings
 * plugin's own widgets. The result opens properly in the editor, every section
 * can be moved, restyled or deleted, and it still needs nothing from Elementor
 * Pro — the farm grid, search, tiles and cards are ours.
 */

defined( 'ABSPATH' ) || exit;

class Acreage_Elementor_Template {

	/** Mockup palette, so the generated template matches the PHP one. */
	const PAPER  = '#F5F0E4';
	const RAISED = '#FCFAF2';
	const GREEN  = '#354027';
	const DARK   = '#232B18';
	const OCHRE  = '#9C6423';
	const RULE   = '#DED5C0';
	const INK    = '#1F2A21';
	const MUTED  = '#6E7566';
	const STONE  = '#8A8172';
	const LIGHT  = '#E8E6DB';

	const SERIF = 'Georgia, \'Times New Roman\', serif';

	/** @var int Counter so generated element ids are unique but stable in order. */
	private $seq = 0;

	private function id() {
		$this->seq++;

		return substr( md5( 'acreage-el-' . $this->seq . wp_rand( 0, PHP_INT_MAX ) ), 0, 7 );
	}

	private function image( $file ) {
		return get_template_directory_uri() . '/assets/demo/' . $file;
	}

	/* ------------------------------------------------------------ builders */

	/*
	 * EVERY element needs an "elements" array, widgets included.
	 *
	 * Elementor's front-end renderer is forgiving and will happily print a widget
	 * that has no "elements" key. The EDITOR is not: it builds a backbone model
	 * tree from the same JSON, and a node without that key cannot be instantiated,
	 * so the section renders on the page but has no handles and cannot be clicked.
	 *
	 * That mismatch is horrible to debug, because the page looks perfect and only
	 * the editor is broken. If you add another builder helper here, give it an
	 * empty "elements" array even when it can never have children.
	 *
	 * "isInner" is likewise expected on sections and columns; Elementor writes it
	 * on everything it creates itself.
	 */

	private function widget( $type, $settings = array() ) {
		return array(
			'id'         => $this->id(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	private function column( $elements, $size = 100, $settings = array() ) {
		return array(
			'id'       => $this->id(),
			'elType'   => 'column',
			/*
			 * Columns carry padding too — the sell band's dark panel is 80/72 —
			 * so they get the same responsive treatment as sections.
			 *
			 * And a column with no padding of its own gets an explicit zero
			 * rather than Elementor's default 10px. That default is applied to
			 * every column, but any column that sets its own padding overrides
			 * it — so the kit had columns sitting on the section gutter and
			 * columns sitting 10px inside it, on the same page. On the single
			 * farm page that produced three different left edges: the hero at
			 * 10px (in a full-bleed band that was supposed to have none), the
			 * gallery and body at 32, and the aside card at 22.
			 *
			 * Zeroing it means every column starts on the section's gutter and
			 * indentation is only ever something a caller asked for. Cards that
			 * need inner padding — the aside, the filter panel — still set it.
			 */
			'settings' => $this->responsive_padding( array_merge(
				array( '_column_size' => $size, '_inline_size' => null, 'padding' => $this->padding( 0, 0, 0, 0 ) ),
				$settings
			) ),
			'elements' => $elements,
			'isInner'  => false,
		);
	}

	private function section( $columns, $settings = array() ) {
		return array(
			'id'       => $this->id(),
			'elType'   => 'section',
			'settings' => $this->responsive_padding( $settings ),
			'elements' => $columns,
			'isInner'  => false,
		);
	}

	/** Padding shorthand in the shape Elementor expects. */
	private function padding( $top, $right, $bottom, $left ) {
		return array(
			'unit'     => 'px',
			'top'      => (string) $top,
			'right'    => (string) $right,
			'bottom'   => (string) $bottom,
			'left'     => (string) $left,
			'isLinked' => false,
		);
	}

	/**
	 * Give every section and column a tablet and a mobile padding.
	 *
	 * THE BUG THIS EXISTS TO MAKE IMPOSSIBLE
	 *
	 * Elementor's padding control is per-device. A section given only a desktop
	 * padding keeps that padding at every width — so a band set to 88px 72px,
	 * which is right at 1440, was still 88px 72px on a 375px phone. That is
	 * 144px of gutter on a 375px screen: 38% of the display spent on empty sand,
	 * leaving 231px for the content. On the homepage it squeezed the category
	 * tab strip into a 211px box and pushed the third tab out of sight.
	 *
	 * Thirty-four sections set a padding here and only thirteen set a mobile
	 * one, so this was not one section that got missed — it was the default
	 * outcome, and the next section anybody adds would have inherited it too.
	 *
	 * Deriving the two smaller steps centrally means a section CANNOT ship
	 * without them. An explicit padding_tablet or padding_mobile in a caller
	 * still wins: this only fills in what is absent.
	 *
	 * @param array $settings Section or column settings.
	 * @return array
	 */
	private function responsive_padding( $settings ) {
		if ( empty( $settings['padding'] ) || ! is_array( $settings['padding'] ) ) {
			return $settings;
		}

		if ( ! isset( $settings['padding_tablet'] ) ) {
			$settings['padding_tablet'] = $this->scale_padding( $settings['padding'], 0.72, 40 );
		}

		if ( ! isset( $settings['padding_mobile'] ) ) {
			$settings['padding_mobile'] = $this->scale_padding( $settings['padding'], 0.55, 22 );
		}

		return $settings;
	}

	/**
	 * A padding stepped down for a narrower screen.
	 *
	 * Vertical and horizontal are treated differently on purpose. Vertical space
	 * is rhythm — it scales, so a band that breathed more than its neighbour at
	 * 1440 still does at 375. Horizontal space is a gutter, and a gutter has a
	 * sane maximum regardless of what the desktop uses: past about 22px on a
	 * phone it stops being margin and starts being lost column.
	 *
	 * Zero stays zero, so the full-bleed bands — the sell panel, the hero — are
	 * not given a gutter they were deliberately built without. Nothing is ever
	 * scaled UP; a padding that is already small is already small enough.
	 *
	 * @param array $padding  Desktop padding.
	 * @param float $factor   Vertical scale.
	 * @param int   $max_side Ceiling for the left and right gutters.
	 * @return array
	 */
	private function scale_padding( $padding, $factor, $max_side ) {
		$step = function ( $key, $is_vertical ) use ( $padding, $factor, $max_side ) {
			$value = isset( $padding[ $key ] ) ? (int) $padding[ $key ] : 0;

			if ( $value <= 0 ) {
				return '0';
			}

			return (string) ( $is_vertical ? (int) round( $value * $factor ) : min( $max_side, $value ) );
		};

		return array(
			'unit'     => 'px',
			'top'      => $step( 'top', true ),
			'right'    => $step( 'right', false ),
			'bottom'   => $step( 'bottom', true ),
			'left'     => $step( 'left', false ),
			'isLinked' => false,
		);
	}

	/**
	 * A heading, set to the reference type scale.
	 *
	 * Elementor writes per-widget typography as inline CSS at a specificity the
	 * theme stylesheet cannot beat, so the scale has to be applied HERE — setting
	 * it only in theme.css leaves every generated heading in whatever the builder
	 * last hardcoded, which is how this page ended up in Georgia 56/400 while the
	 * stylesheet said Jost 54/500.
	 *
	 * $size is the desktop size; tablet and mobile steps are derived so headings
	 * do not overflow narrow screens.
	 *
	 * @param string $text   Heading text.
	 * @param int    $size   Desktop font size in px.
	 * @param string $colour Hex colour.
	 * @param string $tag    HTML tag.
	 * @param array  $extra  Extra Elementor settings.
	 * @return array
	 */
	private function heading( $text, $size, $colour, $tag = 'h2', $extra = array() ) {
		$tablet = max( 22, (int) round( $size * 0.78 ) );
		$mobile = max( 20, (int) round( $size * 0.6 ) );

		return $this->widget( 'heading', array_merge( array(
			'title'                          => $text,
			'header_size'                    => $tag,
			'title_color'                    => $colour,
			'typography_typography'          => 'custom',
			'typography_font_family'         => 'Georgia',
			'typography_font_size'           => array( 'unit' => 'px', 'size' => $size ),
			'typography_font_size_tablet'    => array( 'unit' => 'px', 'size' => $tablet ),
			'typography_font_size_mobile'    => array( 'unit' => 'px', 'size' => $mobile ),
			'typography_font_weight'         => '400',
			'typography_line_height'         => array( 'unit' => 'em', 'size' => 1.15 ),
			'typography_letter_spacing'      => array( 'unit' => 'px', 'size' => -0.4 ),
		), $extra ) );
	}

	private function eyebrow( $text, $colour = self::OCHRE ) {
		return $this->widget( 'heading', array(
			'title'                     => $text,
			'header_size'               => 'div',
			'title_color'               => $colour,
			'typography_typography'     => 'custom',
			'typography_font_size'      => array( 'unit' => 'px', 'size' => 10 ),
			'typography_font_weight'    => '700',
			'typography_letter_spacing' => array( 'unit' => 'px', 'size' => 2.4 ),
			'typography_text_transform' => 'uppercase',
		) );
	}

	private function text( $html, $colour = self::MUTED, $size = 15 ) {
		return $this->widget( 'text-editor', array(
			'editor'                 => wpautop( $html ),
			'text_color'             => $colour,
			'typography_typography'  => 'custom',
			'typography_font_size'   => array( 'unit' => 'px', 'size' => $size ),
			'typography_line_height' => array( 'unit' => 'em', 'size' => 1.65 ),
		) );
	}

	/**
	 * A button.
	 *
	 * TWO VARIANTS, AND ONLY TWO.
	 *
	 * This used to take a background, a text colour and a border colour, and
	 * bake them — plus its own padding and letter-spacing — into each button as
	 * Elementor settings. Two call sites therefore produced two buttons that
	 * matched neither each other nor the theme's own .acreage-btn: one green
	 * outline at 15/28 with 1.9px tracking, one light-grey fill with dark text.
	 * Four different buttons on one site, and none of them answered Appearance >
	 * Theme Options, because a value stored in the page always beats a stylesheet.
	 *
	 * Now the widget carries nothing but its text, its link and a class. What it
	 * looks like is decided by that class, in CSS, from the same variables every
	 * other button on the site reads.
	 *
	 * @param string $text    Label.
	 * @param string $link    URL.
	 * @param string $variant 'primary' (solid fill) or 'outline'.
	 * @param array  $extra   Layout-only settings, e.g. sitting two buttons side
	 *                        by side. Never appearance — that is what the class
	 *                        is for, and a colour baked in here would be exactly
	 *                        the problem this rewrite removed.
	 * @return array
	 */
	private function button( $text, $link, $variant = 'primary', $extra = array() ) {
		return $this->widget( 'button', array_merge( array(
			'text'         => $text,
			'link'         => array( 'url' => $link ),
			// Widgets take _css_classes; sections and columns take css_classes.
			/*
			 * A WRAPPER class, not a button class.
			 *
			 * Elementor puts _css_classes on the widget's outer <div>, never on
			 * the <a class="elementor-button"> inside it. Using .acreage-btn here
			 * therefore painted a button-shaped box AROUND the button and left
			 * the button itself at Elementor's default grey. Hence a separate
			 * name whose rules reach through to the anchor.
			 */
			'_css_classes' => 'outline' === $variant ? 'acreage-cta acreage-cta--outline' : 'acreage-cta',
		), $extra ) );
	}

	/* -------------------------------------------------------------- the page */

	/**
	 * The whole homepage as Elementor data.
	 *
	 * @return array
	 */
	public function build() {
		return array(
			$this->hero(),
			$this->search(),
			$this->recently_listed(),

			/*
			 * NO "Featured farms" band.
			 *
			 * It is not in the approved comp — it was added while matching a
			 * different reference, and it put 896px of extra page between the
			 * listings and the province index. featured() is kept below because the
			 * "Feature this farm" flag and the carousel are genuinely useful, but
			 * the homepage does not carry them unless the client asks.
			 */
			$this->provinces(),
			$this->categories(),
			$this->about(),
			$this->sell(),
		);
	}

	/**
	 * The land ledger — the signature element of the hero.
	 *
	 * A land buyer asks two questions before any other: how much, and where.
	 * So the hero states the live inventory in the trade's own units rather than
	 * a marketing claim. It is set small, spaced and tabular so it reads like a
	 * line on a survey document, not a dashboard: the photograph is the loud
	 * thing on this page, and one loud thing is the limit.
	 *
	 * Every figure is queried, never written down — a hardcoded "80 farms" is
	 * wrong the day after the client lists the eighty-first.
	 *
	 * @return string HTML for a text widget.
	 */
	private function ledger() {
		$farms      = 0;
		$hectares   = 0;
		$provinces  = 0;

		if ( post_type_exists( 'listing' ) ) {
			$ids = get_posts( array(
				'post_type'      => 'listing',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			) );

			$farms = count( $ids );

			foreach ( $ids as $id ) {
				$hectares += (float) get_post_meta( $id, 'acreage_hectares', true );
			}

			$terms     = get_terms( array( 'taxonomy' => 'province', 'hide_empty' => true, 'fields' => 'ids' ) );
			$provinces = is_wp_error( $terms ) ? 0 : count( $terms );
		}

		$parts = array();

		if ( $farms ) {
			/* translators: %s: number of farms currently listed. */
			$parts[] = sprintf( _n( '%s farm listed', '%s farms listed', $farms, 'acreage' ), number_format_i18n( $farms ) );
		}

		if ( $hectares > 0 ) {
			/* translators: %s: total hectares across all listings. */
			$parts[] = sprintf( __( '%s hectares', 'acreage' ), number_format_i18n( $hectares ) );
		}

		if ( $provinces ) {
			/* translators: %s: number of provinces with listings. */
			$parts[] = sprintf( _n( '%s province', '%s provinces', $provinces, 'acreage' ), number_format_i18n( $provinces ) );
		}

		if ( ! $parts ) {
			return '';
		}

		return '<span class="acreage-ledger">' .
			implode( '<span class="acreage-ledger__sep" aria-hidden="true"></span>', array_map( 'esc_html', $parts ) ) .
			'</span>';
	}

	/**
	 * The hero.
	 *
	 * A full-bleed photograph is the right opening for this subject — the land is
	 * the product, and the client's drone photography is the strongest asset the
	 * site has. Three decisions make it work rather than merely look nice:
	 *
	 *  1. MEASURE. The headline is capped at 15ch, so on a 1920px monitor it
	 *     breaks into two or three deliberate lines instead of running the full
	 *     width as one thin band. Long measures are the single most common way a
	 *     full-bleed hero reads as amateur.
	 *
	 *  2. AN ANCHORED SCRIM, not a flat wash. The gradient is aimed at the corner
	 *     the text occupies. The client changes this photograph, so legibility
	 *     cannot depend on which one they upload — it has to hold over a bright
	 *     sunrise as well as a dark bushveld.
	 *
	 *  3. THE SEARCH SITS INSIDE. It used to be a shadowed card pulled up by a
	 *     negative margin, which left it half-overlapping and fragile at every
	 *     width between the breakpoints. It now belongs to the hero and shares
	 *     its container, so the two can never disagree about where the edge is.
	 */
	private function hero() {
		$content = array(
			$this->heading(
				acreage_option( 'hero_title', __( 'Land with game on it, and land that feeds cattle.', 'acreage' ) ),
				66,
				'#FBF8ED',
				'h1',
				array(
					'typography_line_height'     => array( 'unit' => 'em', 'size' => 1.05 ),
					'_element_custom_width'      => 'yes',
					'_element_width'             => 'initial',
					'_element_custom_width_size' => array( 'unit' => 'px', 'size' => 770 ),
				)
			),
			$this->text(
				acreage_option( 'hero_lede', __( 'Every farm on this site is listed by the owner of the business, walked or flown before it goes up.', 'acreage' ) ),
				'#EAE4D2',
				19
			),
		);

		/*
		 * The inventory ledger does NOT go here.
		 *
		 * It was added while matching a different reference. The comp puts that
		 * information in a meta row at the TOP of the photograph — "63 live farms ·
		 * Nine provinces · International" on the left, the location on the right —
		 * not stacked under the lede. Keeping it here also pushed the hero from the
		 * comp's 700px band out to 895px, because three stacked text blocks plus
		 * padding simply do not fit in 700.
		 *
		 * ledger() is retained for that top meta row, which is the next piece of
		 * this section to build.
		 */

		/*
		 * The search is NOT part of this column.
		 *
		 * It was briefly placed here, and that pushed the headline to the top of
		 * the photograph — where the comp's gradient is at 5% and the type became
		 * unreadable over a bright sky. The comp puts the copy at the FOOT of the
		 * image, where the gradient is heaviest, and floats the search panel over
		 * the join as its own section. See search() below.
		 */

		return $this->section(
			array( $this->column( $content ) ),
			array(
				'layout'                => 'full_width',
				'css_classes'           => 'acreage-hero',
				'background_background' => 'classic',
				'background_image'      => array( 'url' => $this->image( 'hero.jpg' ), 'id' => '' ),
				'background_size'       => 'cover',
				'background_position'   => 'center center',

				/*
				 * The comp's gradient, exactly: bottom-to-top, heaviest at the foot
				 * where the headline sits, easing off through the sky and lifting a
				 * little again at the very top so the meta line stays readable.
				 *
				 *   linear-gradient(to top,
				 *     rgba(24,29,16,.72) 0%, rgba(24,29,16,.34) 34%,
				 *     rgba(24,29,16,.05) 62%, rgba(24,29,16,.22) 100%)
				 *
				 * Elementor exposes only two stops, so the two middle ones are
				 * approximated and the four-stop version is applied in CSS on
				 * .acreage-hero. The pair here is the fallback if that file is ever
				 * dropped by a child theme.
				 */
				'background_overlay_background'     => 'gradient',
				'background_overlay_color'          => 'rgba(24,29,16,0.72)',
				'background_overlay_color_b'        => 'rgba(24,29,16,0.10)',
				'background_overlay_gradient_type'  => 'linear',
				'background_overlay_gradient_angle' => array( 'unit' => 'deg', 'size' => 0 ),

				// Comp: clamp(340px, 50vw, 700px) on the image band.
				'height'            => 'min-height',
				'custom_height'     => array( 'unit' => 'px', 'size' => 700 ),
				'custom_height_tablet' => array( 'unit' => 'px', 'size' => 520 ),
				'custom_height_mobile' => array( 'unit' => 'px', 'size' => 420 ),
				'min_height'        => array( 'unit' => 'px', 'size' => 700 ),
				'min_height_tablet' => array( 'unit' => 'px', 'size' => 520 ),
				'min_height_mobile' => array( 'unit' => 'px', 'size' => 420 ),

				/*
				 * NO content_position here.
				 *
				 * Elementor implements it as align-items on the widget wrap. The wrap
				 * is a column flex (see theme.css), so align-items controls the
				 * HORIZONTAL axis — setting it to bottom shoved the headline to the
				 * right edge instead of lowering it. The vertical position is handled
				 * by justify-content in CSS instead.
				 */

				/*
				 * Bottom padding leaves room for the search panel, which is pulled
				 * up over the photograph by a negative margin — that overlap is in
				 * the comp and is the thing that stops the hero reading as a plain
				 * banner with a form under it.
				 */
				/*
				 * ZERO section padding, on purpose.
				 *
				 * Elementor applies min-height to the CONTAINER, not the section,
				 * and then adds section padding outside it. With 44 top and 150
				 * bottom the comp's 700px band rendered as 894. The inset now lives
				 * on the container in theme.css, so the band is exactly 700.
				 */
				'padding'        => $this->padding( 0, 0, 0, 0 ),
				'padding_tablet' => $this->padding( 32, 40, 120, 40 ),
				'padding_mobile' => $this->padding( 26, 22, 96, 22 ),

				/*
				 * The comp has no container cap — it runs full width with clamped
				 * gutters. On a 2560px monitor that stretched the lede to 90
				 * characters on one line, so the content is capped here. This is a
				 * deliberate, single deviation from the comp, made because the comp
				 * was drawn at laptop width and never tested wider.
				 */
				'content_width'  => array( 'unit' => 'px', 'size' => 1440 ),
			)
		);
	}

	private function search() {
		return $this->section(
			array(
				$this->column(
					array( $this->widget( 'acreage-farm-search', array(
						'heading'       => __( 'Search all listings', 'acreage' ),
						'submit_text'   => __( 'Search farms', 'acreage' ),
						'bg'            => self::RAISED,
						'field_columns' => '5',
					) ) ),
					100,
					array(
						'background_background' => 'classic',
						'background_color'      => self::RAISED,
						'border_border'         => 'solid',
						'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true ),
						'border_color'          => self::RULE,
						'box_shadow_box_shadow_type' => 'yes',
						'box_shadow_box_shadow' => array( 'horizontal' => 0, 'vertical' => 26, 'blur' => 60, 'spread' => -34, 'color' => 'rgba(28,34,18,0.7)' ),
					)
				),
			),
			array(
				'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
				'margin'        => array( 'unit' => 'px', 'top' => '-100', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
				'margin_tablet' => array( 'unit' => 'px', 'top' => '-72', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
				'margin_mobile' => array( 'unit' => 'px', 'top' => '-56', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
				'padding'       => $this->padding( 0, 72, 56, 72 ),
				'z_index'       => 5,
			)
		);
	}

	private function recently_listed() {
		return $this->section(
			array(
				$this->column( array(
					$this->heading( acreage_option( 'farms_title', __( 'Recently listed', 'acreage' ) ), 40, self::DARK ),
					$this->text( __( 'The full inventory sits on the farms for sale page.', 'acreage' ), self::MUTED, 14 ),
					$this->widget( 'acreage-farm-grid', array(
						'source'         => 'recent',
						'count'          => 3,
						'columns'        => '3',
						'show_excerpt'   => 'yes',
						'accent'         => self::OCHRE,
						'link_text'      => __( 'View listing', 'acreage' ),
						// Tabs and Load more: browse the whole inventory without leaving
						// the homepage. Both degrade to a plain grid without JavaScript.
						'presentation'   => 'grid',
						'show_tabs'      => 'yes',
						'tab_all_label'  => __( 'All farms', 'acreage' ),
						'load_more'      => 'yes',
						'load_more_text' => __( 'Show more farms', 'acreage' ),
					) ),
					/*
					 * No "Browse all farms" button here.
					 *
					 * The grid above it already carries tabs and a Load more, so
					 * the section offered three different ways to see more farms
					 * stacked on top of each other. The sub-heading still says
					 * where the full inventory lives, and the main navigation has
					 * carried Farms for Sale all along.
					 */
				) ),
			),
			array(
				'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
				'padding'       => $this->padding( 88, 72, 88, 72 ),
			)
		);
	}

	/**
	 * The featured band — a short, hand-picked strip that scrolls sideways.
	 *
	 * Tick "Feature this farm" on a farm to put it here. Deliberately a scroller
	 * rather than a grid: it signals "a few chosen ones" instead of "here is
	 * everything again", and it costs no vertical space on a phone.
	 */
	private function featured() {
		return $this->section(
			array(
				$this->column( array(
					$this->eyebrow( __( 'Hand-picked', 'acreage' ), self::OCHRE ),
					$this->heading( acreage_option( 'feat_title', __( 'Featured farms', 'acreage' ) ), 40, self::DARK ),
					$this->text( acreage_option( 'feat_sub', __( 'A short list worth a second look. Swipe for more.', 'acreage' ) ), self::MUTED, 14 ),
					$this->widget( 'acreage-farm-grid', array(
						'source'       => 'featured',
						'count'        => 8,
						'presentation' => 'carousel',
						'show_excerpt' => '',
						'accent'       => self::OCHRE,
						'link_text'    => __( 'View listing', 'acreage' ),
						'empty_text'   => __( 'No farms are featured yet — tick “Feature this farm” on any farm to fill this band.', 'acreage' ),
					) ),
				) ),
			),
			array(
				'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
				'padding'       => $this->padding( 88, 72, 88, 72 ),
			)
		);
	}

	private function provinces() {
		return $this->section(
			array(
				$this->column( array(
					$this->heading( acreage_option( 'prov_title', __( 'Browse by province', 'acreage' ) ), 40, self::DARK ),
					$this->text( acreage_option( 'prov_sub', __( 'All nine provinces, plus farms listed outside South Africa.', 'acreage' ) ), self::MUTED ),
					$this->widget( 'acreage-province-tiles', array(
						'taxonomy'    => 'province',
						'hide_empty'  => '',
						'limit'       => 12,
						'columns'     => '4',
						'tile_bg'     => self::PAPER,
						'hover_bg'    => self::GREEN,
						'hover_text'  => self::PAPER,
					) ),
				) ),
			),
			array(
				'content_width'         => array( 'unit' => 'px', 'size' => 1440 ),
				'background_background' => 'classic',
				'background_color'      => self::RAISED,
				'padding'               => $this->padding( 88, 72, 88, 72 ),
				'border_border'         => 'solid',
				'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '1', 'left' => '0', 'isLinked' => false ),
				'border_color'          => self::RULE,
			)
		);
	}

	private function categories() {
		return $this->section(
			array(
				$this->column( array(
					$this->widget( 'acreage-category-cards', array(
						'columns' => '2',
						'accent'  => self::OCHRE,
						'cards'   => array(
							array(
								'_id'       => 'acreagegame',
								'term'      => 'game-farms',
								'title'     => __( 'Game farms', 'acreage' ),
								'text'      => __( 'Reserves and hunting farms from a few hundred hectares to Big Five country. Each listing sets out wildlife and vegetation, improvements and land claim status.', 'acreage' ),
								'link_text' => __( 'Browse game farms', 'acreage' ),
								'image'     => array( 'url' => $this->image( 'category-game.jpg' ), 'id' => '' ),
							),
							array(
								'_id'       => 'acreagecatt',
								'term'      => 'cattle-farms',
								'title'     => __( 'Cattle farms', 'acreage' ),
								'text'      => __( 'Working grazing land, judged on carrying capacity, water and infrastructure. Listings cover description, improvements and land claim status.', 'acreage' ),
								'link_text' => __( 'Browse cattle farms', 'acreage' ),
								'image'     => array( 'url' => $this->image( 'category-cattle.jpg' ), 'id' => '' ),
							),
						),
					) ),
				) ),
			),
			array(
				/*
				 * Edge to edge, as in the comp. This band is two photographs with
				 * coloured panels under them; gutters would leave a strip of page
				 * background down each side and break the full-bleed effect the
				 * design depends on. The padding lives inside each panel instead.
				 */
				'layout'  => 'full_width',
				'padding' => $this->padding( 0, 0, 0, 0 ),
				'gap'     => 'no',
			)
		);
	}

	/**
	 * About + the statistics block.
	 *
	 * WHY THE STATS ARE ONE HTML BLOCK, NOT FOUR ELEMENTOR COLUMNS
	 *
	 * They used to be four nested columns at 25% each. On a 1440 screen that
	 * works out at 116px per cell, and "Provinces and international" simply does
	 * not fit — the labels truncated mid-word and the block read as broken.
	 * Elementor columns also never wrap; they just get narrower.
	 *
	 * The comp uses a CSS grid with a 130px minimum, which wraps to two rows
	 * rather than crushing the labels. That behaviour cannot be expressed with
	 * columns, so the cells are emitted as markup and the grid lives in CSS.
	 * The figures are still queried live.
	 */
	private function about() {
		$stats = function_exists( 'acreage_home_stats' ) ? acreage_home_stats( acreage_home_live_count() ) : array();
		$cells = '';

		foreach ( $stats as $stat ) {
			$cells .= sprintf(
				'<div class="acreage-stat"><div class="acreage-stat__value">%1$s</div><div class="acreage-stat__label">%2$s</div></div>',
				esc_html( $stat['value'] ),
				esc_html( $stat['label'] )
			);
		}

		$grid = $cells ? '<div class="acreage-stats">' . $cells . '</div>' : '';

		/*
		 * TWO ROWS, not two columns.
		 *
		 * The old layout put the copy and the figures side by side, which left the
		 * numbers in a 283px column — four cells at 116px each, labels truncating
		 * mid-word. Narrowing the type would have hidden the problem rather than
		 * fixed it: four statistics simply do not belong in a third of the page.
		 *
		 * So the copy shares the top row with a photograph, and the figures run
		 * the full content width beneath as a single banded row. Each label now
		 * has roughly 300px, the claim in the heading is answered directly below
		 * it by the evidence, and the band reads like the record line on a survey
		 * document rather than a dashboard.
		 */
		$top = array(
			'id'       => $this->id(),
			'elType'   => 'section',
			'isInner'  => true,
			'settings' => array( 'gap' => 'extended' ),
			'elements' => array(
				$this->column( array(
					$this->eyebrow( __( 'About', 'acreage' ) ),
					$this->heading( acreage_option( 'about_title', __( 'One owner, one inventory, no middle layer.', 'acreage' ) ), 40, self::DARK ),
					$this->text( acreage_option( 'about_body', __( 'Africa Game Farms lists game and cattle farms across South Africa and, occasionally, across the border. The owner loads every listing himself and photographs most of them from the air. Enquiries go straight to him, not to a call centre.', 'acreage' ) ), '#5A5F52', 16 ),
				), 50, array( 'css_classes' => 'acreage-about__copy' ) ),
				$this->column(
					array(
						$this->widget( 'image', array(
							'image'      => array( 'url' => $this->image( 'hero-alt.jpg' ), 'id' => '' ),
							'image_size' => 'full',
						) ),
					),
					50,
					array( 'css_classes' => 'acreage-about__media' )
				),
			),
			'isInner'  => true,
		);

		return $this->section(
			array(
				$this->column( array(
					$top,
					$this->widget( 'html', array( 'html' => $grid ) ),
				) ),
			),
			array(
				'css_classes'    => 'acreage-about',
				'content_width'  => array( 'unit' => 'px', 'size' => 1440 ),
				'padding'        => $this->padding( 104, 72, 104, 72 ),
				'padding_tablet' => $this->padding( 72, 40, 72, 40 ),
				'padding_mobile' => $this->padding( 48, 22, 48, 22 ),
			)
		);
	}

	/**
	 * "Selling a farm?" — matched to the comp's #sell section.
	 *
	 * Comp values:
	 *   section  display:flex, border-top 1px #DED5C0
	 *   image    flex 1 1 340px, min-height clamp(200px,22vw,300px), cover
	 *   panel    flex 1 1 460px, background #232B18, colour #E8E6DB,
	 *            padding clamp(32px,4.5vw,80px) clamp(20px,4vw,72px),
	 *            vertically centred
	 *   buttons  TWO — a solid light one and a bordered ghost (#6F7F69)
	 *
	 * The columns are 42/58 rather than 50/50: the comp gives the copy more room
	 * than the photograph, which is what stops the panel text wrapping short.
	 */
	private function sell() {
		return $this->section(
			array(
				$this->column(
					array(
						$this->widget( 'image', array(
							'image'      => array( 'url' => $this->image( 'karoo-grass-detail.jpg' ), 'id' => '' ),
							'image_size' => 'full',
						) ),
					),
					42,
					array(
						'css_classes'           => 'acreage-sell__media',
						'background_background' => 'classic',
						'background_color'      => '#E4DCCA',
						'padding'               => $this->padding( 0, 0, 0, 0 ),
					)
				),
				$this->column(
					array(
						$this->heading( acreage_option( 'sell_title', __( 'Selling a farm?', 'acreage' ) ), 40, '#E8E6DB' ),
						$this->text(
							acreage_option( 'sell_body', __( 'Send the province, size, carrying capacity or game count, and asking price. Photographs help, but they are not needed to start the conversation.', 'acreage' ) ),
							'#BFC6B6',
							16
						),
						/*
						 * Points at the seller page now rather than at the
						 * footer. Scrolling somebody to a telephone number was
						 * the best this could do before the page existed; now
						 * the button leads to the form that actually takes the
						 * farm's details.
						 */
						$this->button( __( 'List your farm', 'acreage' ), acreage_sell_url(), 'primary', array(
							'_element_custom_width' => 'yes',
							'_element_width'        => 'initial',
							'_inline_size'          => 'auto',
						) ),
						// The comp's second, quieter action. It was missing entirely.
						$this->button( __( 'Ask a question', 'acreage' ), '#footer', 'outline', array(
							'_element_custom_width' => 'yes',
							'_element_width'        => 'initial',
							'_inline_size'          => 'auto',
						) ),
					),
					58,
					array(
						'css_classes'           => 'acreage-sell__panel acreage-on-dark',
						'background_background' => 'classic',
						'background_color'      => self::DARK,
						'padding'               => $this->padding( 80, 72, 80, 72 ),
						'padding_tablet'        => $this->padding( 48, 40, 48, 40 ),
						'padding_mobile'        => $this->padding( 36, 22, 36, 22 ),
					)
				),
			),
			array(
				'css_classes'   => 'acreage-sell',
				'layout'        => 'full_width',
				'gap'           => 'no',
				'padding'       => $this->padding( 0, 0, 0, 0 ),
				'border_border' => 'solid',
				'border_width'  => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
				'border_color'  => self::RULE,
			)
		);
	}
	/* ============================================================ HEADER */

	/**
	 * The site header.
	 *
	 * The menu uses the core WordPress nav-menu widget, which Elementor Free
	 * exposes as wp-widget-nav_menu. Elementor's own Nav Menu widget is Pro, and
	 * this does the same job for nothing.
	 */
	/**
	 * Site header.
	 *
	 * One Site Nav widget rather than three columns of brand / menu / button.
	 *
	 * The three-column version looked right on a desktop and fell apart on a
	 * phone: WordPress's core nav-menu widget has no responsive behaviour, so the
	 * menu became a tall vertical list that ate the whole first screen. The Site
	 * Nav widget owns the whole bar, which is the only way it can collapse the
	 * menu behind a burger and keep the logo and phone button in place.
	 */
	public function build_header() {
		return array(
			$this->section(
				array(
					$this->column( array(
						$this->widget( 'acreage-nav', array(
							'menu'         => $this->primary_menu_id(),
							'show_brand'   => 'yes',
							'show_tagline' => 'yes',
							'sticky'       => 'yes',
							'bg'           => self::RAISED,
							'link_colour'  => '#3C4A3F',
							'breakpoint'   => array( 'unit' => 'px', 'size' => 900 ),
						) ),
					) ),
				),
				array(
					// Tagged so the front page can lift it over the hero.
					'css_classes'   => 'acreage-headbar',

					// The widget handles its own width and padding, so the section
					// adds none — two sets of gutters is how headers end up
					// mysteriously off-centre.
					// Same 1440 grid as every other section, or the wordmark sits 80px
				// outboard of the page content on a wide monitor.
				'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'       => $this->padding( 0, 0, 0, 0 ),
				)
			),
		);
	}

	private function primary_menu_id() {
		$locations = get_nav_menu_locations();

		if ( ! empty( $locations['primary'] ) ) {
			return (int) $locations['primary'];
		}

		$menus = wp_get_nav_menus();

		return $menus ? (int) $menus[0]->term_id : 0;
	}

	/* ============================================================ FOOTER */

	/**
	 * The footer.
	 *
	 * WHAT WAS WRONG WITH THE OLD ONE
	 *
	 * Three columns, and the middle one held a single link under a "Browse"
	 * heading, so a third of the footer read as unfinished. The provinces were
	 * a run of names separated by slashes that wrapped mid-list. The legal line
	 * sat in its own section with a large gap above it and no rule to separate
	 * it, so it looked stranded rather than deliberate.
	 *
	 * WHAT THIS DOES INSTEAD
	 *
	 * Four columns of real navigation — brand and contact, the two things
	 * somebody browses by, the pages about the agency, and the provinces as a
	 * proper two-column list rather than a sentence. Then a bottom bar,
	 * separated by a hairline, carrying the copyright and the disclaimer on one
	 * line. Links are marked up as lists and styled by the theme, so they line
	 * up and take a hover state.
	 *
	 * @return array
	 */
	public function build_footer() {
		$phone = acreage_option( 'phone', '' );
		$email = acreage_option( 'email', '' );

		/*
		 * Tel and mailto rather than plain text. Half of this footer's readers
		 * are on a phone, where a telephone number that cannot be tapped is
		 * just an inconvenience.
		 */
		$contact = array();

		if ( $phone ) {
			$contact[] = sprintf(
				'<a class="acreage-f__contact" href="tel:%s">%s</a>',
				esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ),
				esc_html( $phone )
			);
		}

		if ( $email ) {
			$contact[] = sprintf(
				'<a class="acreage-f__contact" href="mailto:%1$s">%1$s</a>',
				esc_html( $email )
			);
		}

		/*
		 * The social row goes under the contact lines rather than in its own
		 * column: two icons do not fill a quarter of a footer, and sitting them
		 * beneath the telephone number keeps every way of reaching the agency in
		 * one block. Empty when no profile is configured, and the text widget
		 * then simply has one less line in it.
		 */
		$social = function_exists( 'acreage_social_html' ) ? acreage_social_html( 'acreage-social--footer' ) : '';

		return array(
			$this->section(
				array(
					$this->column( array(
						$this->heading( get_bloginfo( 'name' ), 22, self::GREEN, 'div' ),
						$this->text(
							'<p class="acreage-f__tag">' . esc_html( get_bloginfo( 'description' ) ) . '</p>'
							. implode( '', $contact )
							. $social,
							self::MUTED,
							14
						),
					), 28 ),

					$this->column( array(
						$this->eyebrow( __( 'Farms', 'acreage' ), self::STONE ),
						$this->links_widget( 'footer_farms', 'listing_category' ),
					), 20 ),

					$this->column( array(
						$this->eyebrow( __( 'Agency', 'acreage' ), self::STONE ),
						$this->links_widget( 'footer' ),
					), 20 ),

					$this->column( array(
						$this->eyebrow( __( 'Provinces', 'acreage' ), self::STONE ),
						$this->links_widget( '', 'province', true ),
					), 32 ),
				),
				array(
					'content_width'         => array( 'unit' => 'px', 'size' => 1440 ),
					'background_background' => 'classic',
					'background_color'      => self::RAISED,
					'padding'               => $this->padding( 64, 72, 40, 72 ),
					'padding_mobile'        => $this->padding( 48, 22, 32, 22 ),
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'border_color'          => self::RULE,
					'css_classes'           => 'acreage-f',
				)
			),

			// The bottom bar. A hairline above it is what stops the legal line
			// looking like a paragraph somebody forgot to delete.
			$this->section(
				array(
					$this->column( array(
						$this->text(
							'<p class="acreage-f__bottom">'
							. '<span>' . sprintf(
								/* translators: 1: year, 2: site name. */
								esc_html__( '© %1$s %2$s', 'acreage' ),
								esc_html( gmdate( 'Y' ) ),
								esc_html( get_bloginfo( 'name' ) )
							) . '</span>'
							. '<span>' . esc_html__( 'All prices exclude VAT if applicable. Details subject to confirmation.', 'acreage' ) . '</span>'
							. '</p>',
							self::STONE,
							12
						),
					) ),
				),
				array(
					'content_width'         => array( 'unit' => 'px', 'size' => 1440 ),
					'background_background' => 'classic',
					'background_color'      => self::RAISED,
					'padding'               => $this->padding( 18, 72, 22, 72 ),
					'padding_mobile'        => $this->padding( 18, 22, 22, 22 ),
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'border_color'          => self::RULE,
				)
			),
		);
	}

	/**
	 * A footer link column, as a widget rather than as frozen HTML.
	 *
	 * WHY THIS IS NOT A TEXT WIDGET ANY MORE
	 *
	 * It used to be: footer_links() built <a href="…"> from the archive URL as
	 * it stood at the moment the template was generated, and Elementor stored
	 * that markup as page content. Content does not re-evaluate. So the first
	 * time anyone changed the farms archive base — a setting we ship, on
	 * Settings > Permalinks — every link in this footer pointed at a URL that
	 * no longer existed, on a page nobody thinks to re-check.
	 *
	 * The Link List widget stores the intent instead: a menu location, or a
	 * taxonomy to list. Both are resolved during the render, so the footer
	 * follows the archive wherever it goes and picks up new provinces on its
	 * own.
	 *
	 * @param string $location Menu location to render, if any.
	 * @param string $fallback Taxonomy to list when that location has no menu —
	 *                         a site that never imported the demo still gets a
	 *                         working column rather than an empty one.
	 * @param bool   $split    Two columns.
	 * @return array
	 */
	private function links_widget( $location, $fallback = '', $split = false ) {
		$locations = get_nav_menu_locations();
		$menu_id   = ( $location && ! empty( $locations[ $location ] ) ) ? (int) $locations[ $location ] : 0;

		$settings = array(
			'colour' => self::MUTED,
			'size'   => array( 'unit' => 'px', 'size' => 14 ),
			'split'  => $split ? 'yes' : '',
		);

		if ( $menu_id ) {
			$settings['source'] = 'menu';
			$settings['menu']   = $menu_id;
		} else {
			$settings['source']     = 'taxonomy';
			$settings['taxonomy']   = $fallback ? $fallback : 'province';
			$settings['limit']      = 10;
			$settings['hide_empty'] = 'yes';
		}

		return $this->widget( 'acreage-links', $settings );
	}

	public function build_archive() {
		return array(
			$this->section(
				array(
					/*
					 * Zero padding so the masthead lines up with what it heads.
					 *
					 * Elementor gives every column a default 10px inner padding.
					 * The two columns below override it — the filter card sets
					 * its own, the farms column sets none — so the heading was
					 * the only thing on this page still carrying it, sitting
					 * 10px inboard of the filter card and the farms beneath it.
					 * Small, and exactly the kind of misalignment that reads as
					 * carelessness on a phone where everything is one column.
					 */
					$this->column( array(
						$this->heading( __( 'Farms for sale', 'acreage' ), 40, self::DARK, 'h1' ),
						$this->text( __( 'Filter by kind, province, size and price. Every combination is a linkable address.', 'acreage' ), self::MUTED ),
					), 100, array( 'padding' => $this->padding( 0, 0, 0, 0 ) ) ),
				),
				array(
					'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'       => $this->padding( 64, 72, 24, 72 ),
				)
			),
			$this->section(
				array(
					$this->column(
						array( $this->widget( 'acreage-farm-filters', array(
							'heading'     => __( 'Filter', 'acreage' ),
							'show_counts' => 'yes',
							'accent'      => self::OCHRE,
							'submit_text' => __( 'Apply filters', 'acreage' ),
						) ) ),
						25,
						array(
							'background_background' => 'classic',
							'background_color'      => self::RAISED,
							'padding'               => $this->padding( 28, 26, 28, 26 ),
						)
					),
					$this->column(
						array( $this->widget( 'acreage-farm-grid', array(
							'source'       => 'archive',
							'columns'      => '2',
							'show_excerpt' => 'yes',
							'accent'       => self::OCHRE,
						) ) ),
						75,
						array(
							'padding' => $this->padding( 0, 0, 0, 36 ),
							/*
							 * The 36px on the left is the gap between the filter
							 * sidebar and the farms — it exists to separate two
							 * columns sitting side by side.
							 *
							 * Once they stack there is nothing to its left to be
							 * separated from, so it stops being a gap and starts
							 * being an indent: the farms sat 22px right of the
							 * page heading with their right edge 22px past it.
							 * Explicit zero on the left, because the derived
							 * mobile padding only caps a gutter, and a gutter
							 * that should not exist at all cannot be reached by
							 * capping.
							 *
							 * The separation itself does not disappear when they
							 * stack, it turns through ninety degrees — and
							 * nothing was supplying it, so the results bar began
							 * on the exact pixel the filter card ended and the
							 * two read as one mis-drawn block. Hence 28px above,
							 * which is the gap the filter card already keeps
							 * from the intro paragraph over it.
							 */
							'padding_mobile' => $this->padding( 28, 0, 0, 0 ),
						)
					),
				),
				array(
					'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'       => $this->padding( 0, 72, 88, 72 ),
				)
			),
		);
	}

	/* ============================================================ SINGLE */

	/** One farm: gallery, the four sections, the facts panel and the enquiry form. */
	public function build_single() {
		return array(
			/*
			 * NO BREADCRUMB BAR HERE, DELIBERATELY.
			 *
			 * The demo used to open every farm page with a strip reading
			 * "Home / Cattle farms / Namibia / Otjiwarongo Cattle Post", then
			 * "Back to results" and "Prev / Next" — five lines of navigation
			 * before a buyer saw a single photograph, repeating a farm name that
			 * the hero was about to say again anyway. It pushed the product
			 * below the fold to solve a problem the browser Back button already
			 * solves.
			 *
			 * The widget still exists, so anyone who wants it can drop the Farm
			 * Details widget on the page and set its part to Breadcrumb. It is
			 * simply not what a new site starts with.
			 */

			// The hero: photograph beside the farm's particulars.
			$this->section(
				array(
					$this->column( array(
						$this->widget( 'acreage-farm-details', array( 'part' => 'hero' ) ),
					) ),
				),
				array(
					'layout'  => 'full_width',
					'padding' => $this->padding( 0, 0, 0, 0 ),
				)
			),

			$this->section(
				array(
					$this->column( array(
						// No column count: the gallery sizes its thumbnails and fits
						// as many to the row as the width allows.
						$this->widget( 'acreage-farm-details', array( 'part' => 'gallery' ) ),
					) ),
				),
				array(
					'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'       => $this->padding( 40, 72, 40, 72 ),
				)
			),
			$this->section(
				array(
					$this->column(
						array(
							$this->widget( 'acreage-farm-details', array( 'part' => 'sections', 'show_headings' => 'yes' ) ),
							$this->heading( __( 'Species on this farm', 'acreage' ), 24, self::DARK ),
							$this->widget( 'acreage-farm-details', array( 'part' => 'species' ) ),
							$this->widget( 'acreage-farm-details', array( 'part' => 'video' ) ),
						),
						62,
						array( 'css_classes' => 'acreage-single__main' )
					),
					$this->column(
						array(
							$this->widget( 'acreage-farm-details', array( 'part' => 'price' ) ),
							$this->widget( 'acreage-farm-details', array( 'part' => 'facts' ) ),
							$this->widget( 'acreage-enquiry-form', array(
								'heading'   => __( 'Ask about this farm', 'acreage' ),
							) ),
						),
						38,
						array(
							'css_classes'           => 'acreage-single__aside',
							'background_background' => 'classic',
							'background_color'      => self::RAISED,
							'padding'               => $this->padding( 28, 28, 28, 28 ),
						)
					),
				),
				array(
					'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'       => $this->padding( 0, 72, 88, 72 ),
				)
			),

			/*
			 * Location.
			 *
			 * "Where is it?" is the second question every buyer asks, after the
			 * price, and until now the layout had no answer on it — the widget
			 * existed but a new site started without a slot for it, so the map a
			 * client had filled in had nowhere to appear.
			 *
			 * The band folds itself away on any farm with no location set, so
			 * shipping it switched on costs nothing on the farms that have not
			 * been given one yet.
			 */
			$this->section(
				array(
					$this->column( array(
						$this->widget( 'acreage-farm-details', array(
							'part'             => 'location',
							'show_headings'    => 'yes',
							'location_heading' => __( 'Location', 'acreage' ),
						) ),
					) ),
				),
				array(
					'content_width'         => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'               => $this->padding( 72, 72, 88, 72 ),
					'background_background' => 'classic',
					'background_color'      => self::RAISED,
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'border_color'          => self::RULE,
				)
			),

			/*
			 * Similar farms.
			 *
			 * A buyer who reaches the foot of a listing without enquiring is about
			 * to leave. Three comparable farms is the cheapest thing that keeps
			 * them on the site, and it is in the comp for that reason.
			 */
			$this->section(
				array(
					$this->column( array(
						$this->heading( __( 'Similar farms', 'acreage' ), 34, self::DARK ),
						$this->widget( 'acreage-farm-details', array( 'part' => 'similar' ) ),
					) ),
				),
				array(
					'content_width'         => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'               => $this->padding( 72, 72, 88, 72 ),
					'background_background' => 'classic',
					'background_color'      => self::RAISED,
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'border_color'          => self::RULE,
				)
			),
		);
	}

	/* ======================================================== FLAT PAGES */

	/**
	 * The About page.
	 *
	 * Was a single band with an empty body — about_body defaults to an empty
	 * string, so the page shipped with a heading and nothing under it.
	 *
	 * Built from the facts in the brief: trading since 2008, the founder in
	 * property since 2004, farms across South Africa plus Namibia and Botswana,
	 * every listing loaded and mostly photographed by the owner himself.
	 *
	 * The structure answers the three questions a seller actually has before
	 * picking an agent — who are you, how do you work, and where do you sell —
	 * and closes on the contact. The copy is a starting point and should be
	 * replaced with the client's own words before launch.
	 */
	public function build_about() {
		$phone = acreage_option( 'phone', '' );

		return array(

			/* ------------------------------------------------- 1. the opening */
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'About', 'acreage' ) ),
						$this->heading(
							acreage_option( 'about_title', __( 'One owner, one inventory, no middle layer.', 'acreage' ) ),
							44,
							self::DARK,
							'h1'
						),
						$this->text(
							acreage_option(
								'about_body',
								$this->pp( __( 'Africa Game Farms has been selling game and cattle farms since 2008. The founder has been in property since 2004, and every farm on this site is listed by him — walked or flown before it goes up, and photographed from the air wherever the terrain allows.', 'acreage' ) ) .
								$this->pp( __( 'There is no branch network and no call centre. When you send an enquiry it reaches the person who visited the farm, which is why answers tend to arrive the same day and tend to be specific.', 'acreage' ) )
							),
							'#5A5F52',
							17
						),
					), 55, array( 'css_classes' => 'acreage-about__copy' ) ),
					$this->column( array(
						$this->widget( 'image', array(
							'image'      => array( 'url' => $this->image( 'farm-02.jpg' ), 'id' => '' ),
							'image_size' => 'full',
						) ),
					), 45, array( 'css_classes' => 'acreage-about__media' ) ),
				),
				array(
					'content_width'  => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'        => $this->padding( 88, 72, 72, 72 ),
					'padding_mobile' => $this->padding( 48, 22, 40, 22 ),
				)
			),

			/* ---------------------------------------------- 2. how we work */
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'How we work', 'acreage' ) ),
						$this->heading( __( 'Three things that do not change.', 'acreage' ), 34, self::DARK ),
						$this->widget( 'html', array(
							'html' =>
								'<div class="acreage-pillars">' .
								$this->pillar(
									__( 'The owner lists it', 'acreage' ),
									__( 'Every farm is loaded by the person who visited it. Nothing is copied off another agent’s sheet, and nothing goes up that has not been seen.', 'acreage' )
								) .
								$this->pillar(
									__( 'Photographed from the air', 'acreage' ),
									__( 'Extent and layout are almost impossible to judge from the ground. Most listings carry drone photography so you can read the camps, the water and the access before you drive out.', 'acreage' )
								) .
								$this->pillar(
									__( 'Enquiries go straight to him', 'acreage' ),
									__( 'No call centre, no lead routing. The reply comes from the person who knows the carrying capacity and the land claim position.', 'acreage' )
								) .
								'</div>',
						) ),
					) ),
				),
				array(
					'content_width'         => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'               => $this->padding( 80, 72, 80, 72 ),
					'padding_mobile'        => $this->padding( 48, 22, 48, 22 ),
					'background_background' => 'classic',
					'background_color'      => self::RAISED,
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '1', 'left' => '0', 'isLinked' => false ),
					'border_color'          => self::RULE,
				)
			),

			/* -------------------------------------------- 3. where we sell */
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'Where we sell', 'acreage' ) ),
						$this->heading( __( 'Nine provinces, and across the border.', 'acreage' ), 34, self::DARK ),
						$this->text(
							__( 'Most of the inventory sits in Limpopo and the Northern Cape, but farms come up everywhere from the Waterberg to the Karoo. Listings outside South Africa — Namibia and Botswana — appear under International.', 'acreage' ),
							'#5A5F52',
							16
						),
						$this->widget( 'acreage-province-tiles', array(
							'taxonomy'   => 'province',
							'hide_empty' => '',
							'limit'      => 12,
							'columns'    => '4',
							'tile_bg'    => self::PAPER,
							'hover_bg'   => self::GREEN,
							'hover_text' => self::PAPER,
						) ),
					) ),
				),
				array(
					'content_width'  => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'        => $this->padding( 80, 72, 80, 72 ),
					'padding_mobile' => $this->padding( 48, 22, 48, 22 ),
				)
			),

			/* ------------------------------------------------- 4. the record */
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'The record', 'acreage' ) ),
						$this->widget( 'html', array( 'html' => $this->stats_record() ) ),
					) ),
				),
				array(
					'css_classes'    => 'acreage-about',
					'content_width'  => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'        => $this->padding( 0, 72, 88, 72 ),
					'padding_mobile' => $this->padding( 0, 22, 48, 22 ),
				)
			),

			/* ----------------------------------------------------- 5. contact */
			$this->section(
				array(
					$this->column( array(
						$this->heading( __( 'Talk to the owner', 'acreage' ), 34, '#E8E6DB' ),
						$this->text(
							$phone
								/* translators: %s: telephone number. */
								? sprintf( __( 'Call %s, or send a message and it will reach him directly.', 'acreage' ), esc_html( $phone ) )
								: __( 'Send a message and it will reach him directly.', 'acreage' ),
							'#BFC6B6',
							16
						),
						$this->button( __( 'Contact us', 'acreage' ), home_url( '/contact-us/' ), 'primary' ),
					) ),
				),
				array(
					'content_width'         => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'               => $this->padding( 72, 72, 72, 72 ),
					'padding_mobile'        => $this->padding( 48, 22, 48, 22 ),
					'background_background' => 'classic',
					'background_color'      => self::DARK,
					/*
					 * Dark ground. This does not add a button variant — it flips
					 * the button variables for its own subtree, so the SAME
					 * primary button stays readable here.
					 */
					'css_classes'           => 'acreage-on-dark',
				)
			),
		);
	}

	/** One "how we work" pillar. */
	private function pillar( $title, $body ) {
		return sprintf(
			'<div class="acreage-pillar"><h3 class="acreage-pillar__title">%1$s</h3><p class="acreage-pillar__body">%2$s</p></div>',
			esc_html( $title ),
			esc_html( $body )
		);
	}

	/**
	 * The figures, as a read record rather than a scan band.
	 *
	 * The homepage already carries these four numbers as a horizontal strip. On
	 * a page somebody is actually reading, repeating that strip says nothing new
	 * — so here each figure becomes a row with a line of context beside it. The
	 * gloss is the thing the homepage band cannot carry, and it is what turns a
	 * number into a claim: "17" means little, "trading since 2008, in property
	 * since 2004" is the reason to believe it.
	 *
	 * Ordered by weight of evidence, not by inventory: how long, how many sold,
	 * how wide the reach, what is on the books today.
	 *
	 * @return string
	 */
	private function stats_record() {
		$live = function_exists( 'acreage_home_live_count' ) ? acreage_home_live_count() : 0;

		$rows = array(
			array(
				'value' => acreage_option( 'stat3_value', '17' ),
				'label' => __( 'Years trading', 'acreage' ),
				'gloss' => __( 'Selling game and cattle farms since 2008, with the founder in property since 2004.', 'acreage' ),
			),
			array(
				'value' => acreage_option( 'stat4_value', '400+' ),
				'label' => __( 'Farms sold to date', 'acreage' ),
				'gloss' => __( 'From weekend smallholdings to Big Five reserves, most of them sold to buyers who had never met us before the first enquiry.', 'acreage' ),
			),
			array(
				'value' => acreage_option( 'stat2_value', '9+1' ),
				'label' => __( 'Provinces and international', 'acreage' ),
				'gloss' => __( 'All nine South African provinces, plus listings in Namibia and Botswana.', 'acreage' ),
			),
			array(
				'value' => number_format_i18n( $live ),
				'label' => __( 'On the books today', 'acreage' ),
				'gloss' => __( 'Live inventory, refreshed as farms come on and go under offer.', 'acreage' ),
			),
		);

		$html = '';

		foreach ( $rows as $row ) {
			$html .= sprintf(
				'<div class="acreage-record">
					<div class="acreage-record__figure">%1$s</div>
					<div class="acreage-record__text">
						<div class="acreage-record__label">%2$s</div>
						<p class="acreage-record__gloss">%3$s</p>
					</div>
				</div>',
				esc_html( $row['value'] ),
				esc_html( $row['label'] ),
				esc_html( $row['gloss'] )
			);
		}

		return '<div class="acreage-records">' . $html . '</div>';
	}

	public function build_contact() {
		$phone = acreage_option( 'phone', '' );
		$email = acreage_option( 'email', '' );
		$fax   = acreage_option( 'fax', '' );

		/*
		 * A labelled list rather than three sentences run together with <br>.
		 *
		 * "Telephone +27 82 441 7118" as flat text made the reader parse a label
		 * and a number out of one line, and neither the number nor the address
		 * could be tapped — on a contact page, on a phone, which is most of this
		 * page's traffic. Now the label sits above the value and the two things
		 * worth acting on are links.
		 *
		 * The fax stays plain text. There is no useful href for one, and a link
		 * that does nothing when tapped is worse than no link.
		 */
		$rows = array();

		if ( $phone ) {
			$rows[] = array(
				__( 'Telephone', 'acreage' ),
				$phone,
				'tel:' . preg_replace( '/[^0-9+]/', '', $phone ),
			);
		}
		if ( $email ) {
			$rows[] = array( __( 'Email', 'acreage' ), $email, 'mailto:' . $email );
		}
		if ( $fax ) {
			$rows[] = array( __( 'Fax', 'acreage' ), $fax, '' );
		}

		$lines = '<dl class="acreage-contact__lines">';

		foreach ( $rows as $row ) {
			list( $label, $value, $href ) = $row;

			$lines .= '<dt>' . esc_html( $label ) . '</dt><dd>'
				. (
					$href
						? sprintf( '<a href="%s">%s</a>', esc_url( $href, array( 'tel', 'mailto', 'http', 'https' ) ), esc_html( $value ) )
						: esc_html( $value )
				)
				. '</dd>';
		}

		$lines .= '</dl>';

		return array(
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'Contact', 'acreage' ) ),
						$this->heading( acreage_option( 'contact_title', __( 'Talk to the owner, not a call centre.', 'acreage' ) ), 40, self::DARK, 'h1' ),
						$this->text( $lines, '#5A5F52', 16 ),
						/*
						 * The photograph is here to fill the column, not to
						 * decorate the page.
						 *
						 * The form is tall and the contact details are three
						 * lines, so this column ran out of content half way down
						 * and left roughly five hundred pixels of empty sand
						 * beside the message box — which is what made the page
						 * read as unfinished rather than as calm.
						 *
						 * Landscape rather than a portrait of the agent: this is
						 * the country being sold, it dates far more slowly than
						 * a photograph of a person, and it is the only image on
						 * the page so it has to carry the whole register.
						 */
						$this->widget( 'image', array(
							'image'      => array( 'url' => $this->image( 'farm-05.jpg' ), 'id' => '' ),
							'image_size' => 'full',
						) ),
					), 40, array( 'css_classes' => 'acreage-contact__copy' ) ),
					$this->column( array(
						/*
						 * The only enquiry form on the site with no farm behind
						 * it, so it is the one place the subject and the
						 * "Regarding" dropdown earn their keep — without them
						 * every message from this page arrives titled with the
						 * site's own name.
						 */
						$this->widget( 'acreage-enquiry-form', array(
							'heading'        => __( 'Send a message', 'acreage' ),
							'show_subject'   => 'yes',
							'show_regarding' => 'yes',
						) ),
					), 60 ),
				),
				array(
					'css_classes'   => 'acreage-cols',
					'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'       => $this->padding( 88, 72, 88, 72 ),
				)
			),
		);
	}

	/**
	 * Sell your farm.
	 *
	 * WHY THIS IS A PAGE AND NOT THE HOMEPAGE BAND IT USED TO BE
	 *
	 * The homepage already carries a "Selling a farm?" section, and the footer
	 * used to link to it as /#sell. That is fine as a prompt to somebody already
	 * reading the homepage, and useless to everybody else: an anchor is not a
	 * URL a search engine can rank, cannot be linked to from an advert, and
	 * cannot carry a title or a description of its own.
	 *
	 * A seller looking for an agent does not browse a farms site — they search
	 * for "sell my game farm". That query needs somewhere to land. The client's
	 * current site has had /sell-my-farm/ indexed for years, which is the whole
	 * reason it still gets seller enquiries, and dropping it in a redesign would
	 * quietly cost them the supply side of the business.
	 *
	 * The band on the homepage stays — it now points here rather than at the
	 * footer, so the two are one funnel instead of two dead ends.
	 *
	 * The form is the page. Their current version ends in "phone Peet", which
	 * loses everybody who is browsing at eleven at night, and it asks for none
	 * of the four things needed to value a farm — so the copy beside the form
	 * says exactly what to send, and the form is preset to "Selling a farm" so
	 * the enquiry lands already sorted.
	 */
	public function build_sell() {
		$phone = acreage_option( 'phone', '' );

		return array(

			/* -------------------------------------------------- 1. the opening */
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'Sell a farm', 'acreage' ) ),
						$this->heading(
							acreage_option( 'sell_page_title', __( 'List your farm with the person who will sell it.', 'acreage' ) ),
							44,
							self::DARK,
							'h1'
						),
						$this->text(
							acreage_option(
								'sell_page_body',
								$this->pp( __( 'Every farm on this site was listed by the same person, and that is who reads this form. There is no branch to be passed to and no junior to call you back — the reply comes from the person who will walk the property and write the advertisement.', 'acreage' ) ) .
								$this->pp( __( 'Send what you have. A conversation can start from a province, an extent and a price; the photographs and the paperwork can follow.', 'acreage' ) )
							),
							'#5A5F52',
							17
						),
					), 55, array( 'css_classes' => 'acreage-about__copy' ) ),
					$this->column( array(
						$this->widget( 'image', array(
							'image'      => array( 'url' => $this->image( 'farm-04.jpg' ), 'id' => '' ),
							'image_size' => 'full',
						) ),
					), 45, array( 'css_classes' => 'acreage-about__media' ) ),
				),
				array(
					'content_width'  => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'        => $this->padding( 88, 72, 72, 72 ),
					'padding_mobile' => $this->padding( 48, 22, 40, 22 ),
				)
			),

			/* --------------------------------------------- 2. why list here */
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'Why here', 'acreage' ) ),
						$this->heading( __( 'A small audience of the right people.', 'acreage' ), 34, self::DARK ),
						$this->widget( 'html', array(
							'html' =>
								'<div class="acreage-pillars">' .
								$this->pillar(
									__( 'Buyers who came looking', 'acreage' ),
									__( 'Nobody arrives here by accident. The traffic is people searching for a game or cattle farm to buy, from South Africa and from well beyond it — which is a smaller number than a general property portal and a far better one.', 'acreage' )
								) .
								$this->pillar(
									__( 'Listed properly, once', 'acreage' ),
									__( 'Extent, carrying capacity, water, improvements, species and land claim status all have their own place on a listing. A farm described in full is a farm that stops being asked the same four questions.', 'acreage' )
								) .
								$this->pillar(
									__( 'Photographed from the air', 'acreage' ),
									__( 'Camps, water and access are almost impossible to read from the ground. Drone photography is flown wherever the terrain allows, and it is what turns an enquiry into a viewing.', 'acreage' )
								) .
								'</div>',
						) ),
					) ),
				),
				array(
					'css_classes'    => 'acreage-about',
					'content_width'  => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'        => $this->padding( 0, 72, 80, 72 ),
					'padding_mobile' => $this->padding( 0, 22, 48, 22 ),
				)
			),

			/* ------------------------------------------- 3. what to send + form */
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'What to send', 'acreage' ) ),
						$this->heading( __( 'Four things start the conversation.', 'acreage' ), 34, self::DARK ),
						$this->text(
							/*
							 * A list, not a paragraph. A seller reading this on a
							 * phone is deciding whether they can answer it now or
							 * have to go and find something first — four lines
							 * they can scan says "now", and a paragraph does not.
							 */
							'<ul class="acreage-sell__list">'
							. '<li>' . esc_html__( 'Where it is — province and nearest town.', 'acreage' ) . '</li>'
							. '<li>' . esc_html__( 'How big it is, in hectares.', 'acreage' ) . '</li>'
							. '<li>' . esc_html__( 'Carrying capacity, or roughly what game is on it.', 'acreage' ) . '</li>'
							. '<li>' . esc_html__( 'What you are asking, or what you hope to get.', 'acreage' ) . '</li>'
							. '</ul>'
							. $this->pp( esc_html__( 'Photographs, a title deed number and a game count all help, and none of them are needed to send this form. Nothing goes on the site without your say-so.', 'acreage' ) )
							. (
								$phone
									/* translators: %s: telephone number. */
									? $this->pp( sprintf( esc_html__( 'If you would rather talk it through, the number is %s.', 'acreage' ), esc_html( $phone ) ) )
									: ''
							),
							'#5A5F52',
							16
						),
						/*
						 * 40/60, NOT 42/58.
						 *
						 * Elementor ships a fixed set of .elementor-col-NN rules
						 * and nothing outside it. A column asked for 42 gets the
						 * class .elementor-col-42, no rule matches it, and the
						 * column falls back to its content width — which put the
						 * copy at 1175px and squeezed this form to 217px. Stick
						 * to the widths Elementor's own presets use: 100, 50,
						 * 33/66, 25/75, 20/40/60/80, 30/70, 55/45.
						 */
					), 40 ),
					$this->column( array(
						$this->widget( 'acreage-enquiry-form', array(
							'heading'           => __( 'Tell us about the farm', 'acreage' ),
							'show_subject'      => 'yes',
							'show_regarding'    => 'yes',
							// The visitor said this by being on this page.
							'regarding_default' => __( 'Selling a farm', 'acreage' ),
							'button_text'       => __( 'Send the details', 'acreage' ),
							'success_text'      => __( 'Thank you — the details are on their way, and you will hear back directly from the owner rather than from an office.', 'acreage' ),
						) ),
					), 60 ),
				),
				array(
					'css_classes'    => 'acreage-cols',
					'content_width'  => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'        => $this->padding( 80, 72, 88, 72 ),
					'padding_mobile' => $this->padding( 48, 22, 48, 22 ),
					'border_border'  => 'solid',
					'border_width'   => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'border_color'   => self::RULE,
				)
			),
		);
	}

	/**
	 * Articles & News.
	 *
	 * The posts themselves are listed by WordPress once this page is set as the
	 * Posts page, so this builds only the masthead above that list.
	 */
	public function build_articles() {
		return array(
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'Articles & news', 'acreage' ) ),
						$this->heading( acreage_option( 'articles_title', __( 'What to know before you buy.', 'acreage' ) ), 40, self::DARK, 'h1' ),
						$this->text(
							acreage_option( 'articles_sub', __( 'Notes on carrying capacity, land claims, water rights and the questions worth asking before an offer.', 'acreage' ) ),
							'#5A5F52',
							16
						),
					) ),
				),
				array(
					'content_width' => array( 'unit' => 'px', 'size' => 1440 ),
					'padding'       => $this->padding( 80, 72, 40, 72 ),
				)
			),
		);
	}

	/**
	 * Agency Disclaimer.
	 *
	 * Present on the client's current site and legally load-bearing, so it is
	 * built rather than left for someone to remember. The wording is a starting
	 * point and should be checked by whoever signs it off.
	 */
	public function build_disclaimer() {
		return array(
			$this->section(
				array(
					$this->column( array(
						$this->eyebrow( __( 'Legal', 'acreage' ) ),
						$this->heading( __( 'Agency disclaimer', 'acreage' ), 36, self::DARK, 'h1' ),
						$this->text(
							$this->pp( __( 'Every particular on this website — extent, price, carrying capacity, improvements, species and land claim status — is supplied by the seller and believed correct at the time of listing. It is published in good faith and does not form part of any offer or contract.', 'acreage' ) ) .
							$this->pp( __( 'Prospective purchasers must satisfy themselves as to the accuracy of any particular by their own inspection and enquiry. Neither the agency nor the seller accepts liability for any error, omission or subsequent change.', 'acreage' ) ) .
							$this->pp( __( 'Prices exclude VAT unless expressly stated otherwise. Availability is subject to prior sale.', 'acreage' ) ),
							'#5A5F52',
							16
						),
					) ),
				),
				array(
					'content_width' => array( 'unit' => 'px', 'size' => 980 ),
					'padding'       => $this->padding( 80, 72, 96, 72 ),
				)
			),
		);
	}

	/** Wrap a sentence in a paragraph, for multi-paragraph text widgets. */
	private function pp( $text ) {
		return '<p>' . $text . '</p>';
	}
}
