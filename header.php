<?php
/**
 * Site header.
 *
 * Three ways this can render, in order of preference:
 *
 *   1. A page assigned to the Header slot under Appearance > Layout, rendered
 *      through Elementor. This is how an Elementor Free customer designs their
 *      own header.
 *   2. (Phase 3) An Elementor Pro Theme Builder header, when Pro is active.
 *   3. The coded fallback below, which is what a site sees with no builder at
 *      all. It is deliberately plain and deliberately complete: logo or site
 *      name, menu, and a skip link.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
/*
 * The skip link must be the first focusable thing on the page. A keyboard user
 * arriving on a page should be able to jump past a thirty-item menu in one
 * keystroke instead of tabbing through it on every single page.
 */
?>
<a class="acreage-skip" href="#acreage-main"><?php esc_html_e( 'Skip to content', 'acreage' ); ?></a>

<?php acreage_hook( 'before_header' ); ?>

<?php if ( ! acreage_do_location( 'header' ) ) : ?>
<header class="acreage-hd" role="banner">
	<?php if ( has_custom_logo() ) : ?>
		<div class="acreage-hd__brand"><?php the_custom_logo(); ?></div>
	<?php else : ?>
		<a class="acreage-hd__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<span class="acreage-hd__name"><?php bloginfo( 'name' ); ?></span>
			<?php $acreage_tagline = get_bloginfo( 'description', 'display' ); ?>
			<?php if ( $acreage_tagline ) : ?>
				<span class="acreage-hd__tag"><?php echo esc_html( $acreage_tagline ); ?></span>
			<?php endif; ?>
		</a>
	<?php endif; ?>

	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<button class="acreage-hd__burger" aria-expanded="false" aria-controls="acreage-nav">
			<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'acreage' ); ?></span>
		</button>

		<nav id="acreage-nav" class="acreage-hd__nav" aria-label="<?php esc_attr_e( 'Primary', 'acreage' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => false,
				)
			);

			$acreage_tel = acreage_option( 'phone' );

			if ( $acreage_tel ) :
				?>
				<a class="acreage-hd__tel" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $acreage_tel ) ); ?>">
					<?php echo esc_html( $acreage_tel ); ?>
				</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>
</header>
<?php endif; ?>

<?php acreage_hook( 'after_header' ); ?>

<?php Acreage_Elementor_Layout::render( 'before_content' ); ?>

<main id="acreage-main" class="acreage-main">

<?php acreage_hook( 'before_content' ); ?>
