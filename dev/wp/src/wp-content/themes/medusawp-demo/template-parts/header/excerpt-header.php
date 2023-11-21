<?php
/**
 * Displays the post header
 *
 * @package WordPress
 * @subpackage MedusaWP_Demo
 * @since MedusaWP Demo 1.0
 */

// Don't show the title if the post-format is `aside` or `status`.
$post_format = get_post_format();
if ( 'aside' === $post_format || 'status' === $post_format ) {
	return;
}
?>

<header class="entry-header">
	<?php
	the_title( sprintf( '<h2 class="entry-title default-max-width"><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' );
	medusawp_demo_post_thumbnail();
	?>
</header><!-- .entry-header -->
