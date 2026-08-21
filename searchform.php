<?php defined( 'ABSPATH' ) || exit; ?>
<form role="search" method="get" class="acreage-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="acreage-s"><?php esc_html_e( 'Search', 'acreage' ); ?></label>
	<input id="acreage-s" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search the site…', 'acreage' ); ?>">
	<button class="acreage-btn" type="submit"><?php esc_html_e( 'Search', 'acreage' ); ?></button>
</form>
