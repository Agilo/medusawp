<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage MedusaWP_Demo
 * @since MedusaWP Demo 1.0
 */

get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/content/content-single' );

	if ( is_attachment() ) {
		// Parent post navigation.
		the_post_navigation(
			array(
				/* translators: %s: Parent post link. */
				'prev_text' => sprintf( __( '<span class="meta-nav">Published in</span><span class="post-title">%s</span>', 'medusawp-demo' ), '%title' ),
			)
		);
	}

	// If comments are open or there is at least one comment, load up the comment template.
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

	// Previous/next post navigation.
	$medusawp - demo_next = is_rtl() ? medusawp_demo_get_icon_svg( 'ui', 'arrow_left' ) : medusawp_demo_get_icon_svg( 'ui', 'arrow_right' );
	$medusawp - demo_prev = is_rtl() ? medusawp_demo_get_icon_svg( 'ui', 'arrow_right' ) : medusawp_demo_get_icon_svg( 'ui', 'arrow_left' );

	$medusawp - demo_next_label     = esc_html__( 'Next post', 'medusawp-demo' );
	$medusawp - demo_previous_label = esc_html__( 'Previous post', 'medusawp-demo' );

	the_post_navigation(
		array(
			'next_text' => '<p class="meta-nav">' . $medusawp - demo_next_label . $medusawp - demo_next . '</p><p class="post-title">%title</p>',
			'prev_text' => '<p class="meta-nav">' . $medusawp - demo_prev . $medusawp - demo_previous_label . '</p><p class="post-title">%title</p>',
		)
	);
endwhile; // End of the loop.

get_footer();
