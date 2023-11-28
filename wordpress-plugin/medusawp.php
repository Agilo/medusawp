<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package           MedusaWP
 * @link              https://github.com/Agilo/medusawp
 * @since             0.1.0
 * @author            Agilo
 * @copyright         2023 Agilo
 * @license           MIT
 *
 * @wordpress-plugin
 * Plugin Name:       MedusaWP
 * Plugin URI:        https://github.com/Agilo/medusawp
 * Description:       Enables you to use WordPress as a headless CMS or as a storefront for your Medusa shop.
 * Version:           0.3.2
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Agilo
 * Author URI:        https://agilo.co
 * License:           MIT
 * License URI:       https://github.com/Agilo/medusawp/blob/master/wordpress-plugin/LICENSE
 * Text Domain:       medusawp
 * Domain Path:       /languages
 */

use MedusaWP\MedusaClient\Admin\Api\DefaultApi;
use MedusaWP\MedusaClient\Admin\Configuration;
use MedusaWP\Plugin;
use MedusaWP\Settings;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . '/functions.php';

\MedusaWP\Updater::init( __FILE__ );

/**
 * The code that runs during plugin activation.
 *
 * @since  0.1.0
 * @return void
 */
function activate_medusawp() {
	\MedusaWP\Utils::create_tables();
}

/**
 * The code that runs during plugin deactivation.
 *
 * @since  0.1.0
 * @return void
 */
function deactivate_medusawp() {
	$url       = Settings::get_medusa_url();
	$api_token = Settings::get_medusa_api_token();
	$origin    = site_url();

	$api_configuration = new Configuration();
	$api_configuration->setHost( $url );
	$api_configuration->setApiKey( 'x-medusa-access-token', $api_token );

	$api = new DefaultApi( null, $api_configuration );

	try {
		$api->medusaWPDisconnect( $origin );
	} catch ( \Exception $e ) {
		// phpcs:ignore
		error_log( 'MedusaWP: Failed to disconnect from Medusa with an error bellow' );
		// phpcs:ignore
		error_log( $e->getMessage() );
	}

	// Remove credentials
	Settings::remove_medusa_settings();
	Settings::remove_medusa_api_token();
	Settings::remove_medusa_secret_key();
}

register_activation_hook( __FILE__, 'activate_medusawp' );
register_deactivation_hook( __FILE__, 'deactivate_medusawp' );

/**
 * Begins execution of the plugin.
 *
 * @since 0.1.0
 */
function run_medusawp() {
	$plugin = new Plugin();
	$plugin->run();
}
run_medusawp();
