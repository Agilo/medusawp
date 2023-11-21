<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

?>

<?php
$cart                 = medusawp_get_cart();
$countries            = medusawp_get_countries();
$current_country_code = medusawp_get_current_country_code();
$current_currency     = medusawp_get_current_currency();
$region               = medusawp_get_current_region();
?>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- #content -->

	<?php get_template_part( 'template-parts/footer/footer-widgets' ); ?>

	<footer id="colophon" class="site-footer">

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Secondary menu', 'medusawp-demo' ); ?>" class="footer-navigation">
				<ul class="footer-navigation-wrapper">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'items_wrap'     => '%3$s',
							'container'      => false,
							'depth'          => 1,
							'link_before'    => '<span>',
							'link_after'     => '</span>',
							'fallback_cb'    => false,
						)
					);
					?>
				</ul><!-- .footer-navigation-wrapper -->
			</nav><!-- .footer-navigation -->
		<?php endif; ?>
		<?php if ( $cart ) : ?>
			<div class="cart-details-demo">
				<h4><?php esc_html_e( 'My Cart Details', 'medusawp-demo' ); ?></h4>
				<dl>
					<dt><?php esc_html_e( 'id', 'medusawp-demo' ); ?></dt>
					<dd><?php echo esc_html( $cart->getId() ); ?></dd>

					<?php if ( $region ) : ?>
						<dt><?php esc_html_e( 'region', 'medusawp-demo' ); ?></dt>
						<dd>
							<dl>
								<dt><?php esc_html_e( 'id', 'medusawp-demo' ); ?></dt>
								<dd id="region-id"><?php echo esc_html( $region->getId() ); ?></dd>

								<dt><?php esc_html_e( 'name', 'medusawp-demo' ); ?></dt>
								<dd id="region-name"><?php echo esc_html( $region->getName() ); ?></dd>
							</dl>
						</dd>
					<?php endif; ?>

					<dt><?php esc_html_e( 'type', 'medusawp-demo' ); ?></dt>
					<dd><?php echo esc_html( $cart->getType() ); ?></dd>

					<dt><?php esc_html_e( 'created_at', 'medusawp-demo' ); ?></dt>
					<dd><?php echo esc_html( $cart->getCreatedAt()->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></dd>

					<dt><?php esc_html_e( 'context', 'medusawp-demo' ); ?></dt>
					<dd>
						<dl>
							<dt><?php esc_html_e( 'ip', 'medusawp-demo' ); ?></dt>
							<dd><?php echo esc_html( $cart->getContext()['ip'] ); ?></dd>

							<dt><?php esc_html_e( 'user_agent', 'medusawp-demo' ); ?></dt>
							<dd><?php echo esc_html( $cart->getContext()['user_agent'] ); ?></dd>
						</dl>
					</dd>
				</dl>
			</div><!-- .cart-details-demo -->
		<?php endif; ?>
		<h4><?php esc_html_e( 'Country and Currency', 'medusawp-demo' ); ?></h4>
		<div>
			<form method="get" id="country-switcher">
				<select name="country_code">
					<option value="" disabled hidden <?php echo ! isset( $current_country_code ) ? 'selected' : ''; ?>
					>
						<?php esc_html_e( 'Select Country', 'medusawp-demo' ); ?>
					</option>
					<?php foreach ( $countries as $country ) : ?>
						<option value="<?php echo esc_html( $country['iso_2'] ); ?>"
							<?php echo isset( $country ) && $country['iso_2'] === $current_country_code ? 'selected' : ''; ?>
						>
							<?php echo esc_html( $country['display_name'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<noscript><button type="submit">Change</button></noscript>
			</form>
			<?php if ( $current_currency ) : ?>
				<p id="currency_code"><?php echo esc_html( $current_currency ); ?></p>
			<?php endif; ?>
		</div>
		<div class="site-info">
			<div class="site-name">
				<?php if ( has_custom_logo() ) : ?>
					<div class="site-logo"><?php the_custom_logo(); ?></div>
				<?php else : ?>
					<?php if ( get_bloginfo( 'name' ) && get_theme_mod( 'display_title_and_tagline', true ) ) : ?>
						<?php if ( is_front_page() && ! is_paged() ) : ?>
							<?php bloginfo( 'name' ); ?>
						<?php else : ?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
			</div><!-- .site-name -->

			<?php
			if ( function_exists( 'the_privacy_policy_link' ) ) {
				the_privacy_policy_link( '<div class="privacy-policy">', '</div>' );
			}
			?>

			<div class="powered-by">
				<?php
				printf(
					/* translators: %s: WordPress. */
					esc_html__( 'Proudly powered by %s.', 'medusawp-demo' ),
					'<a href="' . esc_url( __( 'https://wordpress.org/', 'medusawp-demo' ) ) . '">WordPress</a>'
				);
				?>
			</div><!-- .powered-by -->

		</div><!-- .site-info -->
	</footer><!-- #colophon -->

</div><!-- #page -->

<script>
	jQuery( function () {
		let xhr;

		jQuery( 'select[name="country_code"]' ).on( 'change', function ( e ) {
			e.preventDefault();

			if ( xhr ) {
				// ajax request already in progress
				return;
			}

			xhr = jQuery.ajax( {
				url: window.medusawp_demo.ajaxUrl,
				type: 'GET',
				data: {
					action: 'medusawp_switch_country',
					country_code: jQuery( 'select[name="country_code"]' ).val(),
				},
				dataType: 'json',
				success: function( response ) {
					console.log( response );

					jQuery( '#region-id' ).text( response.data.region_id );
					jQuery( '#region-name' ).text( response.data.region.name );
					jQuery( '#currency_code' ).text( response.data.region.currency_code );
				},
				error: function() {
					console.log( 'error' );
				},
				complete: function() {
					xhr = undefined;
				}
			} );
		} );
	} );
</script>

<?php wp_footer(); ?>

</body>
</html>
