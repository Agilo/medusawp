<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    MedusaWP
 */

namespace MedusaWP;

use MedusaWP\Routes;
use MedusaWP\Admin;
use MedusaWP\Loader;

define( 'MEDUSAWP_PLUGIN_DIR', plugin_dir_path( __DIR__ ) );
define( 'MEDUSAWP_VERSION', '0.3.1' );
define( 'MEDUSAWP_REST_ROUTE_NAMESPACE', 'wp/v2/medusa' );
define( 'MEDUSAWP_REST_ADMIN_ROUTE_NAMESPACE', 'wp/v2/admin/medusa' );

global $wpdb;
$prefix = $wpdb->prefix;

define( 'MEDUSAWP_TABLE_PRODUCTS', $prefix . 'medusa_products' );
define( 'MEDUSAWP_TABLE_PRODUCT_VARIANTS', $prefix . 'medusa_product_variants' );
define( 'MEDUSAWP_TABLE_PRODUCT_COLLECTIONS', $prefix . 'medusa_product_collections' );
define( 'MEDUSAWP_TABLE_REGIONS', $prefix . 'medusa_regions' );
define( 'MEDUSAWP_TABLE_MONEY_AMOUNT', $prefix . 'medusa_money_amount' );
define( 'MEDUSAWP_TABLE_COUNTRIES', $prefix . 'medusa_countries' );
define( 'MEDUSAWP_TABLE_SYNC_PROGRESS', $prefix . 'medusa_sync_progress' );

class Plugin {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name = 'medusawp';

	/**
	 * Plugin constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->define_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once MEDUSAWP_PLUGIN_DIR . 'public/class-medusawp-public.php';

		$this->loader = new Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_hooks() {
		$admin        = new Admin();
		$ajax_actions = new AjaxActions();
		$init         = new Init();

		$routes = array(
			new Routes\Product(),
			new Routes\ProductVariant(),
			new Routes\ProductCollection(),
			new Routes\Region(),
		);

		$admin_routes = array(
			new Routes\Admin\MedusaConnection(),
			new Routes\Admin\PluginSettings(),
			new Routes\Admin\MedusaBulkSync(),
		);

		foreach ( $routes as $route ) {
			$this->loader->add_action( 'rest_api_init', $route, 'register_route' );
		}

		foreach ( $admin_routes as $route ) {
			$this->loader->add_action( 'rest_api_init', $route, 'register_admin_routes' );
		}

		$this->loader->add_action( 'admin_print_styles', $admin, 'custom_admin_styles' );
		$this->loader->add_action( 'in_admin_header', $admin, 'disable_admin_notices' );
		$this->loader->add_action( 'admin_menu', $admin, 'admin_page' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'add_meta_boxes', $admin, 'product_and_collection_edit_screen' );
		$this->loader->add_action( 'init', $init, 'register_post_types' );
		$this->loader->add_action( 'parse_query', $init, 'create_cart' );
		$this->loader->add_action( 'wp_ajax_medusawp_switch_country', $ajax_actions, 'switch_country' );
		$this->loader->add_action( 'wp_ajax_nopriv_medusawp_switch_country', $ajax_actions, 'switch_country' );

		// Tables will be upgraded only if needed. TODO: Improve this functionality.
		add_action(
			'plugins_loaded',
			function () {
				Utils::create_tables();
			}
		);

		add_action(
			'medusawp_schedule_import_product_thumbnail_batch',
			function ( int $previous_post_id, int $sync_timestamp ) {
				ScheduledActions::schedule_import_product_thumbnail_batch( $previous_post_id, $sync_timestamp );
			},
			10,
			2
		);

		add_action(
			'medusawp_import_product_thumbnail',
			function ( string $product_id, ?int $sync_timestamp = null ) {
				ScheduledActions::import_product_thumbnail( $product_id, $sync_timestamp );
			},
			10,
			2
		);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.1.0
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	public static function is_dev_mode() {
		if ( defined( 'MEDUSAWP_DEV' ) ) {
			return \MEDUSAWP_DEV === true;
		}

		if ( isset( $_ENV['MEDUSAWP_DEV'] ) ) {
			return $_ENV['MEDUSAWP_DEV'] === 'true';
		}

		return false;
	}
}
