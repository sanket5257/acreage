<?php
/**
 * Theme setup — what this theme tells WordPress it can do.
 *
 * "Theme support" is how a theme opts in to a core feature. Until you declare
 * it, WordPress will not show the feature's UI or render its markup. Declaring
 * one is the difference between a customer seeing the "Logo" control in the
 * Customizer and never knowing it exists.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'acreage_setup' );
/**
 * Register everything the theme supports.
 */
function acreage_setup() {

	/*
	 * Translation. Loads languages/{locale}.mo when one exists. The text domain
	 * must match the theme folder, which is why it is the literal string
	 * 'acreage' everywhere and never a variable — translation tools read the
	 * source code, and they cannot resolve a variable.
	 */
	load_theme_textdomain( 'acreage', get_theme_file_path( 'languages' ) );

	// WordPress prints <title>, not the theme. Required since 4.1.
	add_theme_support( 'title-tag' );

	// Featured images.
	add_theme_support( 'post-thumbnails' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );

	// Modern markup instead of WordPress's 2010-era HTML.
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 90,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Widgets update live in the Customizer preview instead of reloading it.
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Block editor: core block styles on the front end, and wide/full alignment.
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );

	/*
	 * Editor styles — makes the block editor render content with the theme's
	 * own typography instead of the browser's defaults.
	 */
	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'acreage' ),
			'footer'  => __( 'Footer menu', 'acreage' ),
		)
	);

	/*
	 * Image sizes. Property photography is wide, so these crop rather than
	 * letterbox. Existing images are not re-cut when a size changes — a customer
	 * needs a regenerate-thumbnails plugin for that, which the docs will say.
	 */
	add_image_size( 'acreage-card', 800, 560, true );
	add_image_size( 'acreage-hero', 1920, 900, true );
}

/**
 * $content_width tells WordPress the widest an embed or image may be.
 *
 * Core and several plugins read this global. Without it, oEmbeds can overflow
 * their container on narrow layouts.
 */
add_action( 'after_setup_theme', 'acreage_content_width', 0 );
function acreage_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'acreage_content_width', 1180 );
}

add_action( 'widgets_init', 'acreage_widget_areas' );
/**
 * Widget areas.
 *
 * A "widget area" (sidebar) is a named slot a customer can drag widgets into.
 * Registering one costs nothing if it stays empty — the templates check
 * is_active_sidebar() before printing anything.
 */
function acreage_widget_areas() {

	register_sidebar(
		array(
			'name'          => __( 'Blog sidebar', 'acreage' ),
			'id'            => 'acreage-sidebar',
			'description'   => __( 'Appears beside posts and archives.', 'acreage' ),
			'before_widget' => '<section id="%1$s" class="acreage-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="acreage-widget__title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer', 'acreage' ),
			'id'            => 'acreage-footer',
			'description'   => __( 'Shown in the footer when no Elementor footer is assigned.', 'acreage' ),
			'before_widget' => '<div id="%1$s" class="acreage-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="acreage-widget__title">',
			'after_title'   => '</h4>',
		)
	);
}

add_filter( 'body_class', 'acreage_body_class' );
/**
 * Extra body classes so CSS can target page types without inline styles.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function acreage_body_class( $classes ) {

	/*
	 * The homepage base styles (page background, link colour) live under
	 * .acreage-home in home.css. That class used to be added only for the legacy
	 * page-home.php template — so the moment the homepage was rebuilt in
	 * Elementor on a different template, those base styles stopped applying and
	 * the front end no longer matched the editor preview.
	 *
	 * The class belongs to "this is the front page", not "this is one particular
	 * template".
	 */
	if ( is_front_page() || is_page_template( 'page-home.php' ) ) {
		$classes[] = 'acreage-home';
	}

	if ( ! is_active_sidebar( 'acreage-sidebar' ) ) {
		$classes[] = 'acreage-no-sidebar';
	}

	return $classes;
}
