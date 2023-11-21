<?php
/**
 * Customize API: MedusaWP_Demo_Customize_Notice_Control class
 *
 * @package WordPress
 * @subpackage MedusaWP_Demo
 * @since MedusaWP Demo 1.0
 */

/**
 * Customize Notice Control class.
 *
 * @since MedusaWP Demo 1.0
 *
 * @see WP_Customize_Control
 */
class MedusaWP_Demo_Customize_Notice_Control extends WP_Customize_Control {
	/**
	 * The control type.
	 *
	 * @since MedusaWP Demo 1.0
	 *
	 * @var string
	 */
	public $type = 'twenty-twenty-one-notice';

	/**
	 * Renders the control content.
	 *
	 * This simply prints the notice we need.
	 *
	 * @since MedusaWP Demo 1.0
	 *
	 * @return void
	 */
	public function render_content() {
		?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'To access the Dark Mode settings, select a light background color.', 'medusawp-demo' ); ?></p>
			<p><a href="<?php echo esc_url( __( 'https://wordpress.org/documentation/article/twenty-twenty-one/#dark-mode-support', 'medusawp-demo' ) ); ?>">
				<?php esc_html_e( 'Learn more about Dark Mode.', 'medusawp-demo' ); ?>
			</a></p>
		</div><!-- .notice -->
		<?php
	}
}
