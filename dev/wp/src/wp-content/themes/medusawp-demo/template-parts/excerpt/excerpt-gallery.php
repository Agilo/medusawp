<?php
/**
 * Show the appropriate content for the Gallery post format.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage MedusaWP_Demo
 * @since MedusaWP Demo 1.0
 */

// Print the 1st gallery found.
if ( has_block( 'core/gallery', get_the_content() ) ) {

	medusawp_demo_print_first_instance_of_block( 'core/gallery', get_the_content() );
}

the_excerpt();
