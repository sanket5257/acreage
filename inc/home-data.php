<?php
/**
 * LEGACY — scheduled for deletion in Phase 3.
 *
 * This file holds page copy inside PHP. That was correct for a single client
 * site and is wrong for a product: a buyer cannot edit a PHP file, and every
 * default sentence in here is a sentence about one South African farm agency.
 *
 * It survives only so the site keeps rendering while the Elementor demo kit is
 * built. When the kit lands, this file and page-home.php are deleted together
 * and the homepage becomes something the customer edits in the builder.
 *
 * Do not add anything to it.
 *
 * Data and copy for the homepage.
 *
 * Two rules:
 *
 *  1. Anything that is *data* — farms, counts, terms — is read from the
 *     acreage-listings plugin, so the page can never contradict the inventory.
 *  2. Anything that is *copy* goes through acreage_option(), which reads a saved
 *     setting and falls back to the mockup's wording. That is what makes every
 *     sentence on the homepage editable without touching code.
 *
 * When the plugin is absent, sample records stand in so the design can still be
 * shown — clearly marked, never passed off as real inventory.
 */

defined( 'ABSPATH' ) || exit;

const ACREAGE_CONTENT_OPTION = 'acreage_home_content';

/** One editable string, with the mockup's wording as the fallback. */
function acreage_option( $key, $default = '' ) {
	$saved = get_option( ACREAGE_CONTENT_OPTION, array() );

	if ( is_array( $saved ) && ! empty( $saved[ $key ] ) ) {
		return $saved[ $key ];
	}

	return $default;
}

/** An editable image, falling back to one bundled with the theme. */
function acreage_option_image( $key, $fallback_file ) {
	$id = (int) acreage_option( $key, 0 );

	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) {
			return $url;
		}
	}

	return get_template_directory_uri() . '/assets/demo/' . $fallback_file;
}

/** Is the listings plugin active with published farms? */
function acreage_home_has_listings() {
	if ( ! post_type_exists( 'listing' ) ) {
		return false;
	}

	return (bool) get_posts( array(
		'post_type'      => 'listing',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
	) );
}

/** How many farms are live. */
function acreage_home_live_count() {
	if ( ! post_type_exists( 'listing' ) ) {
		return 63; // The mockup's figure, until the plugin holds the real one.
	}

	$counts = wp_count_posts( 'listing' );

	return isset( $counts->publish ) ? (int) $counts->publish : 0;
}

/** The farms archive, or the Farms for Sale page. */
function acreage_farms_url() {
	if ( post_type_exists( 'listing' ) ) {
		$archive = get_post_type_archive_link( 'listing' );
		if ( $archive ) {
			return $archive;
		}
	}

	$page = get_page_by_path( 'farms-for-sale' );

	return $page ? get_permalink( $page ) : home_url( '/' );
}

/** A taxonomy archive URL filtered to one term. */
function acreage_term_url( $taxonomy, $slug ) {
	return add_query_arg(
		array_merge(
			post_type_exists( 'listing' ) ? array( 'post_type' => 'listing' ) : array(),
			array( $taxonomy => $slug )
		),
		acreage_farms_url()
	);
}

/* ------------------------------------------------------------------ search */

/** The five axes in the hero search, in the mockup's order. */
function acreage_home_search_axes() {
	return array(
		'listing_category' => __( 'Category', 'acreage' ),
		'province'         => __( 'Province', 'acreage' ),
		'region'           => __( 'Region', 'acreage' ),
		'size_band'        => __( 'Size band', 'acreage' ),
		'price_band'       => __( 'Price band', 'acreage' ),
	);
}

/** The mockup's "All categories" / "Any size" wording per axis. */
function acreage_home_any_label( $taxonomy ) {
	$labels = array(
		'listing_category' => __( 'All categories', 'acreage' ),
		'province'         => __( 'All provinces', 'acreage' ),
		'region'           => __( 'All regions', 'acreage' ),
		'size_band'        => __( 'Any size', 'acreage' ),
		'price_band'       => __( 'Any price', 'acreage' ),
	);

	return isset( $labels[ $taxonomy ] ) ? $labels[ $taxonomy ] : __( 'Any', 'acreage' );
}

function acreage_home_terms( $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );

	return ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
}

/* ------------------------------------------------------------------- farms */

/**
 * Farm cards for the homepage.
 *
 * @param int $count How many.
 * @return array[]
 */
function acreage_home_farms( $count = 3 ) {
	if ( ! acreage_home_has_listings() ) {
		return array_slice( acreage_home_sample_farms(), 0, $count );
	}

	$posts = get_posts( array(
		'post_type'      => 'listing',
		'posts_per_page' => $count,
		'post_status'    => 'publish',
	) );

	$farms = array();

	foreach ( $posts as $post ) {
		$price    = (float) get_post_meta( $post->ID, 'acreage_price', true );
		$hectares = (float) get_post_meta( $post->ID, 'acreage_hectares', true );
		$status   = acreage_home_first_term( $post->ID, 'status' );
		$region   = acreage_home_first_term( $post->ID, 'region' );
		$province = acreage_home_first_term( $post->ID, 'province' );

		$farms[] = array(
			'title'     => get_the_title( $post ),
			'url'       => get_permalink( $post ),
			'image'     => get_the_post_thumbnail_url( $post, 'large' ),
			'category'  => acreage_home_first_term( $post->ID, 'listing_category' ),
			'place'     => trim( implode( ', ', array_filter( array( $region, $province ) ) ), ', ' ),
			'status'    => $status,
			'sold'      => (bool) preg_match( '/sold|off market/i', $status ),
			'price'     => $price > 0 ? 'R' . number_format_i18n( $price ) : __( 'Price on application', 'acreage' ),
			'has_price' => $price > 0,
			/* translators: %s: hectares. */
			'extent'    => $hectares > 0 ? sprintf( __( '%s ha', 'acreage' ), number_format_i18n( $hectares ) ) : '',
			'excerpt'   => wp_trim_words( get_the_excerpt( $post ), 26 ),
		);
	}

	return $farms;
}

function acreage_home_first_term( $post_id, $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return '';
	}

	$terms = get_the_terms( $post_id, $taxonomy );

	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
}

/** Stand-ins so the design can be shown before the plugin holds real farms. */
function acreage_home_sample_farms() {
	$image = get_template_directory_uri() . '/assets/demo/';

	return array(
		array(
			'title' => __( 'Mopani Ridge Reserve', 'acreage' ), 'url' => acreage_farms_url(),
			'image' => $image . 'farm-01.jpg', 'category' => __( 'Game farm', 'acreage' ),
			'place' => __( 'Waterberg, Limpopo', 'acreage' ), 'status' => __( 'New listing', 'acreage' ), 'sold' => false,
			'price' => 'R28 500 000', 'has_price' => true, 'extent' => __( '1 240 ha', 'acreage' ),
			'excerpt' => __( 'Big Five capable, fully game fenced, with a lodge and two boreholes.', 'acreage' ),
		),
		array(
			'title' => __( 'Karoo Vlakte', 'acreage' ), 'url' => acreage_farms_url(),
			'image' => $image . 'farm-03.jpg', 'category' => __( 'Cattle farm', 'acreage' ),
			'place' => __( 'Beaufort West, Northern Cape', 'acreage' ), 'status' => '', 'sold' => false,
			'price' => 'R12 400 000', 'has_price' => true, 'extent' => __( '3 600 ha', 'acreage' ),
			'excerpt' => __( 'Open veld with strong borehole water and full handling facilities.', 'acreage' ),
		),
		array(
			'title' => __( 'Palala River Frontage', 'acreage' ), 'url' => acreage_farms_url(),
			'image' => $image . 'farm-05.jpg', 'category' => __( 'Game farm', 'acreage' ),
			'place' => __( 'Lephalale, Limpopo', 'acreage' ), 'status' => __( 'New listing', 'acreage' ), 'sold' => false,
			'price' => 'R41 000 000', 'has_price' => true, 'extent' => __( '2 180 ha', 'acreage' ),
			'excerpt' => __( 'Two kilometres of river frontage and established plains game.', 'acreage' ),
		),
	);
}

/* --------------------------------------------------------------- provinces */

/**
 * Provinces with live counts.
 *
 * @param int $limit How many.
 * @return array[] name, slug, count, url
 */
function acreage_home_provinces( $limit = 12 ) {
	if ( taxonomy_exists( 'province' ) ) {
		$terms = get_terms( array(
			'taxonomy'   => 'province',
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $limit,
		) );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$out = array();

			foreach ( $terms as $term ) {
				$out[] = array(
					'name'  => $term->name,
					'slug'  => $term->slug,
					'count' => (int) $term->count,
					'url'   => acreage_term_url( 'province', $term->slug ),
				);
			}

			return $out;
		}
	}

	// The live site's own distribution, until the plugin holds the real terms.
	$sample = array(
		array( 'Limpopo', 'limpopo', 28 ),
		array( 'Northern Cape', 'northern-cape', 9 ),
		array( 'Western Cape', 'western-cape', 8 ),
		array( 'Eastern Cape', 'eastern-cape', 6 ),
		array( 'Free State', 'free-state', 4 ),
		array( 'KwaZulu-Natal', 'kwazulu-natal', 3 ),
		array( 'North West', 'north-west', 3 ),
		array( 'Mpumalanga', 'mpumalanga', 2 ),
		array( 'Gauteng', 'gauteng', 1 ),
		array( 'Namibia', 'namibia', 2 ),
	);

	$out = array();

	foreach ( array_slice( $sample, 0, $limit ) as $row ) {
		$out[] = array(
			'name'  => $row[0],
			'slug'  => $row[1],
			'count' => $row[2],
			'url'   => acreage_term_url( 'province', $row[1] ),
		);
	}

	return $out;
}

/* -------------------------------------------------------------- categories */

/** The two category panels. */
function acreage_home_categories() {
	$defaults = array(
		'game'   => array(
			'slug'  => 'game-farms',
			'title' => __( 'Game farms', 'acreage' ),
			'text'  => __( 'Reserves and hunting farms from a few hundred hectares to Big Five country. Each listing sets out wildlife and vegetation, improvements and land claim status.', 'acreage' ),
			'link'  => __( 'Browse game farms', 'acreage' ),
			'image' => 'category-game.jpg',
			'alt'   => __( 'Sable on open veld below hills at sunset', 'acreage' ),
			'count' => 37,
		),
		'cattle' => array(
			'slug'  => 'cattle-farms',
			'title' => __( 'Cattle farms', 'acreage' ),
			'text'  => __( 'Working grazing land, judged on carrying capacity, water and infrastructure. Listings cover description, improvements and land claim status.', 'acreage' ),
			'link'  => __( 'Browse cattle farms', 'acreage' ),
			'image' => 'category-cattle.jpg',
			'alt'   => __( 'Cattle grazing under a tree on rolling pasture', 'acreage' ),
			'count' => 26,
		),
	);

	$out = array();

	foreach ( $defaults as $id => $cat ) {
		$count = $cat['count'];

		if ( taxonomy_exists( 'listing_category' ) ) {
			$term = get_term_by( 'slug', $cat['slug'], 'listing_category' );
			if ( $term ) {
				$count = (int) $term->count;
			}
		}

		$out[] = array(
			'id'    => $id,
			'title' => acreage_option( $id . '_title', $cat['title'] ),
			'text'  => acreage_option( $id . '_text', $cat['text'] ),
			'link'  => $cat['link'],
			'image' => acreage_option_image( $id . '_image', $cat['image'] ),
			'alt'   => $cat['alt'],
			'count' => $count,
			'url'   => acreage_term_url( 'listing_category', $cat['slug'] ),
		);
	}

	return $out;
}

/* ------------------------------------------------------------------ stats */

/** The four figures beside the About copy. */
function acreage_home_stats( $live ) {
	return array(
		array(
			'value' => acreage_option( 'stat1_value', number_format_i18n( $live ) ),
			'label' => acreage_option( 'stat1_label', __( 'Live farms', 'acreage' ) ),
		),
		array(
			'value' => acreage_option( 'stat2_value', '9+1' ),
			'label' => acreage_option( 'stat2_label', __( 'Provinces and international', 'acreage' ) ),
		),
		array(
			'value' => acreage_option( 'stat3_value', '17' ),
			'label' => acreage_option( 'stat3_label', __( 'Years trading', 'acreage' ) ),
		),
		array(
			'value' => acreage_option( 'stat4_value', '400+' ),
			'label' => acreage_option( 'stat4_label', __( 'Farms sold to date', 'acreage' ) ),
		),
	);
}

/* ----------------------------------------------------------------- social */

/**
 * The agency's social profiles, in the order they are shown.
 *
 * WHY THIS IS A FUNCTION AND NOT MARKUP IN THE FOOTER
 *
 * There are two footers — the coded one in footer.php, for a site not using
 * Elementor, and the one the kit builds. Both need the same links, and a second
 * network added later must appear in both without anybody remembering there was
 * a second place. So the list lives here and both callers read it.
 *
 * Entries with no URL configured are dropped, so a customer who only has a
 * Facebook page gets one icon rather than one icon and a dead space.
 *
 * @return array[] key, label and url for each configured profile.
 */
function acreage_social_links() {
	$networks = array(
		'facebook'  => __( 'Facebook', 'acreage' ),
		'instagram' => __( 'Instagram', 'acreage' ),
	);

	$links = array();

	foreach ( $networks as $key => $label ) {
		$url = acreage_option( $key );

		if ( ! $url ) {
			continue;
		}

		$links[] = array(
			'key'   => $key,
			'label' => $label,
			'url'   => $url,
		);
	}

	/**
	 * Filter the social profiles the theme prints.
	 *
	 * A child theme adding a network supplies its own icon through
	 * acreage_social_icon, below.
	 *
	 * @param array[] $links key, label, url.
	 */
	return apply_filters( 'acreage_social_links', $links );
}

/**
 * The icon for one network.
 *
 * Inline SVG rather than an icon font or a sprite: two glyphs do not justify a
 * font request, and inline means the mark takes the link's colour on hover with
 * no extra rule. currentColor throughout, aria-hidden because the link already
 * carries its name for a screen reader.
 *
 * @param string $key Network key.
 * @return string SVG markup, or '' when the network is not one we draw.
 */
function acreage_social_icon( $key ) {
	$paths = array(
		'facebook'  => '<path d="M14 8.5h2V5.7h-2.3C11.1 5.7 10 7 10 9v1.6H8V13.4h2V19h2.8v-5.6h2l.4-2.8h-2.4V9.3c0-.6.2-.8.8-.8Z"/>',
		'instagram' => '<path d="M8.6 4h6.8A4.6 4.6 0 0 1 20 8.6v6.8a4.6 4.6 0 0 1-4.6 4.6H8.6A4.6 4.6 0 0 1 4 15.4V8.6A4.6 4.6 0 0 1 8.6 4Zm0 1.7A2.9 2.9 0 0 0 5.7 8.6v6.8a2.9 2.9 0 0 0 2.9 2.9h6.8a2.9 2.9 0 0 0 2.9-2.9V8.6a2.9 2.9 0 0 0-2.9-2.9H8.6Zm7.3 1.3a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2ZM12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 1.7a2.3 2.3 0 1 0 0 4.6 2.3 2.3 0 0 0 0-4.6Z"/>',
	);

	/**
	 * Filter the icon drawn for a network.
	 *
	 * @param string $svg Path markup, without the wrapping <svg>.
	 * @param string $key Network key.
	 */
	$path = apply_filters( 'acreage_social_icon_path', isset( $paths[ $key ] ) ? $paths[ $key ] : '', $key );

	if ( ! $path ) {
		return '';
	}

	/*
	 * The width and height are a fallback only — the stylesheet sizes the glyph
	 * from --acreage-social-glyph, and CSS beats a presentation attribute. They
	 * are kept, and kept in step with it, so the mark is still the right size in
	 * the moment before the stylesheet applies.
	 */
	return '<svg class="acreage-social__icon" viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true" focusable="false">' . $path . '</svg>';
}

/**
 * The social row, ready to print.
 *
 * @param string $class Extra class on the wrapper, so each footer can space it
 *                      to its own layout without a second markup builder.
 * @return string
 */
function acreage_social_html( $class = '' ) {
	$links = acreage_social_links();

	if ( ! $links ) {
		return '';
	}

	$out = '<div class="acreage-social' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '">';

	foreach ( $links as $link ) {
		$out .= sprintf(
			/*
			 * rel="noopener" because these open off-site; the accessible name is
			 * on the link itself rather than in a title attribute, which screen
			 * readers announce inconsistently and touch devices never show.
			 */
			'<a class="acreage-social__link acreage-social__link--%1$s" href="%2$s" rel="noopener" aria-label="%3$s">%4$s</a>',
			esc_attr( $link['key'] ),
			esc_url( $link['url'] ),
			esc_attr( $link['label'] ),
			acreage_social_icon( $link['key'] )
		);
	}

	return $out . '</div>';
}

/**
 * Where "Sell your farm" points.
 *
 * The dedicated page is the right destination, but it only exists on a site
 * that has run the importer — and on one that has not, a link to /sell-my-farm/
 * is a 404 in the footer of every page. So the page is asked for first and the
 * homepage band is the fallback, which is where this link went before the page
 * existed.
 *
 * Looked up by slug rather than stored as an ID: a customer who rebuilds the
 * page, or renames it and lets WordPress keep the slug, keeps a working link,
 * and an ID in an option would not survive either.
 *
 * @return string
 */
function acreage_sell_url() {
	$page = get_page_by_path( 'sell-my-farm' );

	if ( $page && 'publish' === $page->post_status ) {
		return (string) get_permalink( $page );
	}

	/**
	 * Filter the "sell your farm" destination.
	 *
	 * For a site whose seller page lives somewhere else entirely.
	 *
	 * @param string $url Fallback URL.
	 */
	return apply_filters( 'acreage_sell_url', home_url( '/#sell' ) );
}
