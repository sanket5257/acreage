<?php
/**
 * Site footer.
 *
 * Same three-tier arrangement as the header: an assigned Elementor page wins,
 * otherwise the coded footer below runs.
 *
 * The coded footer itself has two modes. If the customer has put widgets in the
 * Footer widget area, it renders those — which is the answer for anyone who does
 * not use Elementor and does not want to. If they have not, it falls back to the
 * columns the design was built around.
 *
 * PHASE 3: the province column is single-client structure and goes when the
 * Elementor kit replaces it. The widget path below is the one that survives.
 *
 * @package Acreage
 */

defined( 'ABSPATH' ) || exit;
?>

<?php acreage_hook( 'after_content' ); ?>

</main>

<?php Acreage_Elementor_Layout::render( 'after_content' ); ?>

<?php acreage_hook( 'before_footer' ); ?>

<?php if ( ! acreage_do_location( 'footer' ) ) : ?>
<footer id="colophon" class="acreage-ft" role="contentinfo">

	<?php if ( is_active_sidebar( 'acreage-footer' ) ) : ?>

		<div class="acreage-ft__grid">
			<?php dynamic_sidebar( 'acreage-footer' ); ?>
		</div>

	<?php else : ?>

		<div class="acreage-ft__grid">
			<div>
				<div class="acreage-ft__name"><?php bloginfo( 'name' ); ?></div>
				<div class="acreage-ft__contact">
					<?php $acreage_tel = acreage_option( 'phone' ); ?>
					<?php if ( $acreage_tel ) : ?>
						<div><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $acreage_tel ) ); ?>"><?php echo esc_html( $acreage_tel ); ?></a></div>
					<?php endif; ?>

					<?php $acreage_email = acreage_option( 'email' ); ?>
					<?php if ( $acreage_email ) : ?>
						<div><a href="mailto:<?php echo esc_attr( $acreage_email ); ?>"><?php echo esc_html( $acreage_email ); ?></a></div>
					<?php endif; ?>
				</div>
			</div>

			<div>
				<div class="acreage-ft__label"><?php esc_html_e( 'Browse', 'acreage' ); ?></div>
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<div class="acreage-ft__links">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>

			<div>
				<div class="acreage-ft__label"><?php esc_html_e( 'Regions', 'acreage' ); ?></div>
				<div class="acreage-ft__provinces">
					<?php foreach ( acreage_home_provinces( 12 ) as $acreage_region ) : ?>
						<a href="<?php echo esc_url( $acreage_region['url'] ); ?>"><?php echo esc_html( $acreage_region['name'] ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	<?php endif; ?>

	<div class="acreage-ft__bar">
		<?php
		$acreage_legal = acreage_option( 'legal' );

		if ( $acreage_legal ) :
			?>
			<span><?php echo esc_html( $acreage_legal ); ?></span>
		<?php endif; ?>

		<span>
			<?php
			printf(
				/* translators: 1: current year, 2: site name. */
				esc_html__( '© %1$s %2$s', 'acreage' ),
				esc_html( wp_date( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</span>
	</div>
</footer>
<?php endif; ?>

<?php acreage_hook( 'after_footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
