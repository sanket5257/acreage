<?php defined( 'ABSPATH' ) || exit; get_header(); ?>

<div class="acreage-wrap acreage-page acreage-page--narrow acreage-404">
	<h1 class="acreage-page__title"><?php esc_html_e( 'Page not found', 'acreage' ); ?></h1>
	<p><?php esc_html_e( 'That page has moved or never existed. Try a search, or head back to the homepage.', 'acreage' ); ?></p>
	<?php get_search_form(); ?>
	<p><a class="acreage-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'acreage' ); ?></a></p>
</div>

<?php get_footer(); ?>
