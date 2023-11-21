<?php
/**
 * Admin functionality
 *
 * @package MedusaWP
 */

namespace MedusaWP;

class Admin {
	public function is_settings_screen() {
		$screen = get_current_screen();

		if ( ! $screen || $screen->id !== 'medusawp_page_medusawp-settings' ) {
			return false;
		}

		return true;
	}

	public function custom_admin_styles() {
		if ( ! $this->is_settings_screen() ) {
			return;
		}
		?>
<style>
	body.medusawp_page_medusawp-settings #wpcontent { padding-left: 0; }
</style>
		<?php
	}

	public function disable_admin_notices() {
		global $wp_filter;

		if ( ! $this->is_settings_screen() ) {
			return;
		}

		if ( isset( $wp_filter['network_admin_notices'] ) ) {
				unset( $wp_filter['network_admin_notices'] );
		}

		if ( isset( $wp_filter['user_admin_notices'] ) ) {
			unset( $wp_filter['user_admin_notices'] );
		}

		if ( isset( $wp_filter['admin_notices'] ) ) {
			unset( $wp_filter['admin_notices'] );
		}

		if ( isset( $wp_filter['all_admin_notices'] ) ) {
			unset( $wp_filter['all_admin_notices'] );
		}
	}

	/**
	 * Add admin pages.
	 *
	 * @return void
	 */
	public function admin_page() {
		add_menu_page(
			__( 'MedusaWP', 'medusawp' ),
			__( 'MedusaWP', 'medusawp' ),
			'manage_options',
			'medusawp'
		);

		add_submenu_page(
			'medusawp',
			__( 'Regions', 'medusawp' ),
			__( 'Regions', 'medusawp' ),
			'manage_options',
			'medusawp-regions',
			function () {
				$this->render_regions_screen();
			}
		);

		add_submenu_page(
			'medusawp',
			__( 'Settings', 'medusawp' ),
			__( 'Settings', 'medusawp' ),
			'manage_options',
			'medusawp-settings',
			function () {
				$this->render_settings_screen();
			}
		);
	}

	private function get_medusawp_js_data() {
		$connection        = null;
					$url   = Settings::get_medusa_url();
					$email = Settings::get_medusa_email();

		if ( ! empty( $url ) && ! empty( $email ) ) {
			$connection = array(
				'url'   => $url,
				'email' => $email,
			);
		}

		return array(
			'connection' => $connection,
			'distUrl'    => plugins_url( 'admin/dist', MEDUSAWP_PLUGIN_DIR . '/index.php' ),
			'rest'       => array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			),
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_settings_screen() ) {
			return;
		}

		if ( Plugin::is_dev_mode() ) {
			add_action(
				'admin_footer',
				function () {
					echo '<script>var medusawp = ' . wp_json_encode(
						$this->get_medusawp_js_data()
					) . ";</script>\n";
					?>
<?php // phpcs:ignore ?>
<script type="module">
	import RefreshRuntime from 'http://localhost:5173/@react-refresh'
	RefreshRuntime.injectIntoGlobalHook(window)
	window.$RefreshReg$ = () => {}
	window.$RefreshSig$ = () => (type) => type
	window.__vite_plugin_react_preamble_installed__ = true
</script>
<?php // phpcs:ignore ?>
<script type="module" src="http://localhost:5173/@vite/client"></script>
<?php // phpcs:ignore ?>
<script type="module" src="http://localhost:5173/src/main.tsx"></script>
					<?php
				}
			);
		} else {
			$manifest = wp_json_file_decode(
				MEDUSAWP_PLUGIN_DIR . '/admin/dist/manifest.json',
				array(
					'associative' => true,
				)
			);

			if ( ! empty( $manifest ) && is_array( $manifest ) ) {
				foreach ( $manifest as $key => $value ) {
					if ( $value['isEntry'] ) {
						wp_enqueue_script( 'medusawp-' . $key, plugins_url( 'admin/dist/' . $value['file'], MEDUSAWP_PLUGIN_DIR . '/index.php' ), array(), MEDUSAWP_VERSION, true );
						wp_localize_script( 'medusawp-' . $key, 'medusawp', $this->get_medusawp_js_data() );

						if ( ! empty( $value['css'] ) && is_array( $value['css'] ) ) {
							foreach ( $value['css'] as $css ) {
								wp_enqueue_style( 'medusawp-' . $css, plugins_url( 'admin/dist/' . $css, MEDUSAWP_PLUGIN_DIR . '/index.php' ), array(), MEDUSAWP_VERSION );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Render admin page for MedusaWP > Settings
	 *
	 * @since  1.0.0
	 * @return void
	 */
	private function render_settings_screen() {
		require_once MEDUSAWP_PLUGIN_DIR . '/admin/screens/settings.php';
	}

	/**
	 * Render page for MedusaWP > Regions.
	 *
	 * @return void
	 */
	private function render_regions_screen() {
		require_once MEDUSAWP_PLUGIN_DIR . '/admin/screens/regions.php';
	}

	/**
	 * Product and collection information meta box
	 *
	 * @return void
	 */
	public function product_and_collection_edit_screen() {
		add_meta_box(
			'product_info',
			__( 'Product Information', 'medusawp' ),
			function () {
				$this->product_information();
			},
			'medusa-product',
			'side',
			'high'
		);

		add_meta_box(
			'collection_info',
			__( 'Collection Information', 'medusawp' ),
			function () {
				$this->collection_information();
			},
			'medusa-collection',
			'side',
			'high'
		);
	}

	private function product_information() {
		require_once MEDUSAWP_PLUGIN_DIR . '/admin/meta-boxes/product-meta.php';
	}

	private function collection_information() {
		require_once MEDUSAWP_PLUGIN_DIR . '/admin/meta-boxes/collection-meta.php';
	}
}
