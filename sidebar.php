<?php
/**
 * The blog sidebar.
 *
 * Prints nothing at all when the customer has not put widgets in it, so a site
 * that does not want a sidebar does not get an empty column pushing the content
 * off centre. The decision itself lives in acreage_has_sidebar() so every
 * template agrees.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;

if ( ! acreage_has_sidebar() ) {
	return;
}
?>

<aside id="secondary" class="acreage-sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'acreage' ); ?>">
	<?php dynamic_sidebar( 'acreage-sidebar' ); ?>
</aside>
