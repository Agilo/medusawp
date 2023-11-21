<?php
/**
 * Show the appropriate content for the Audio post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage MedusaWP_Demo
 * @since MedusaWP Demo 1.0
 */

$content = get_the_content();

if ( has_block( 'core/audio', $content ) ) {
	medusawp_demo_print_first_instance_of_block( 'core/audio', $content );
} elseif ( has_block( 'core/embed', $content ) ) {
	medusawp_demo_print_first_instance_of_block( 'core/embed', $content );
} else {
	medusawp_demo_print_first_instance_of_block( 'core-embed/*', $content );
}

// Add the excerpt.
the_excerpt();
