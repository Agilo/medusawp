<?php
/**
 * Displays the footer widget area.
 *
 * @package WordPress
 * @subpackage MedusaWP_Demo
 * @since MedusaWP Demo 1.0
 */

if ( is_active_sidebar( 'sidebar-1' ) ) : ?>

	<aside class="widget-area">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</aside><!-- .widget-area -->

	<?php
endif;
