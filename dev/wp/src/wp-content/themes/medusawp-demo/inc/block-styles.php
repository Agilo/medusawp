<?php
/**
 * Block Styles
 *
 * @link https://developer.wordpress.org/reference/functions/register_block_style/
 *
 * @package WordPress
 * @subpackage MedusaWP_Demo
 * @since MedusaWP Demo 1.0
 */

if ( function_exists( 'register_block_style' ) ) {
	/**
	 * Register block styles.
	 *
	 * @since MedusaWP Demo 1.0
	 *
	 * @return void
	 */
	function medusawp_demo_register_block_styles() {
		// Columns: Overlap.
		register_block_style(
			'core/columns',
			array(
				'name'  => 'medusawp-demo-columns-overlap',
				'label' => esc_html__( 'Overlap', 'medusawp-demo' ),
			)
		);

		// Cover: Borders.
		register_block_style(
			'core/cover',
			array(
				'name'  => 'medusawp-demo-border',
				'label' => esc_html__( 'Borders', 'medusawp-demo' ),
			)
		);

		// Group: Borders.
		register_block_style(
			'core/group',
			array(
				'name'  => 'medusawp-demo-border',
				'label' => esc_html__( 'Borders', 'medusawp-demo' ),
			)
		);

		// Image: Borders.
		register_block_style(
			'core/image',
			array(
				'name'  => 'medusawp-demo-border',
				'label' => esc_html__( 'Borders', 'medusawp-demo' ),
			)
		);

		// Image: Frame.
		register_block_style(
			'core/image',
			array(
				'name'  => 'medusawp-demo-image-frame',
				'label' => esc_html__( 'Frame', 'medusawp-demo' ),
			)
		);

		// Latest Posts: Dividers.
		register_block_style(
			'core/latest-posts',
			array(
				'name'  => 'medusawp-demo-latest-posts-dividers',
				'label' => esc_html__( 'Dividers', 'medusawp-demo' ),
			)
		);

		// Latest Posts: Borders.
		register_block_style(
			'core/latest-posts',
			array(
				'name'  => 'medusawp-demo-latest-posts-borders',
				'label' => esc_html__( 'Borders', 'medusawp-demo' ),
			)
		);

		// Media & Text: Borders.
		register_block_style(
			'core/media-text',
			array(
				'name'  => 'medusawp-demo-border',
				'label' => esc_html__( 'Borders', 'medusawp-demo' ),
			)
		);

		// Separator: Thick.
		register_block_style(
			'core/separator',
			array(
				'name'  => 'medusawp-demo-separator-thick',
				'label' => esc_html__( 'Thick', 'medusawp-demo' ),
			)
		);

		// Social icons: Dark gray color.
		register_block_style(
			'core/social-links',
			array(
				'name'  => 'medusawp-demo-social-icons-color',
				'label' => esc_html__( 'Dark gray', 'medusawp-demo' ),
			)
		);
	}
	add_action( 'init', 'medusawp_demo_register_block_styles' );
}
