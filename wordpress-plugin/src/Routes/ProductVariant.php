<?php
/**
 * Defines product route.
 *
 * @package MedusaWP
 */

namespace MedusaWP\Routes;

use MedusaWP\DB;
use MedusaWP\Utils;
use MedusaWP\Models;
use WP_REST_Response;
use MedusaWP\Settings;
use MedusaWP\Routes\Route;

class ProductVariant extends Route {

	public function __construct() {
		$this->namespace = MEDUSAWP_REST_ROUTE_NAMESPACE;
		$this->route     = 'product-variant';
	}

	public function register_route() {
		register_rest_route(
			$this->namespace,
			$this->route,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_items' ),
				'show_in_index'       => false,
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Route callback: Update product variants.
	 *
	 * @param  \WP_REST_Request $req
	 * @return WP_REST_Response
	 */
	public function update_items( $req ) {
		$body           = $req->get_json_params();
		$signature      = isset( $body['signature'] ) ? $body['signature'] : array();
		$data           = isset( $body['data'] ) ? $body['data'] : array();
		$sync_timestamp = ! empty( $body['sync_timestamp'] ) && is_int( $body['sync_timestamp'] ) ? $body['sync_timestamp'] : null;

		// Validate signature.
		if ( ! $this->validate_request_signature( $signature ) ) {
			return new WP_REST_Response( __( 'Unauthorized access.', 'medusawp' ), 401 );
		}

		if ( ! is_array( $data ) || empty( $data ) ) {
			return new WP_REST_Response( __( 'Missing data.', 'medusawp' ), 400 );
		}

		if ( $sync_timestamp && ! Utils::is_sync_in_progress( $sync_timestamp ) ) {
			return new WP_REST_Response( __( 'Sync is not in progress.', 'medusawp' ), 400 );
		}

		$processing_item_id = Utils::save_sync_progress( 'product-variant', $data, $sync_timestamp );

		Models\ProductVariant::save( $data );
		$save_errors = Models\ProductVariant::get_errors();

		if ( ! $sync_timestamp ) {
			return new WP_REST_Response( null );
		}

		// While sync in progress, save sync messages for each model and update sync progress.
		Utils::update_sync_progress( $processing_item_id, $data, $save_errors );

		// If all items are synced, remove old data from DB.
		if ( Utils::is_total_synced( 'product-variant', $sync_timestamp ) ) {
			Utils::update_is_sync_finished( $sync_timestamp );

			$this->db_cleanup();
		}

		return new WP_REST_Response( null );
	}

	/**
	 * After resync of variants, delete from DB the ones that don't exist in Medusa anymore.
	 *
	 * @return bool
	 */
	private function db_cleanup() {
		$sync_progress = Settings::get_sync_progress();
		$sync_time     = $sync_progress['started_at'];

		$delete_variants = DB\ProductVariants::delete( 'synced_at < %s OR synced_at IS NULL', array( $sync_time ) );
		$delete_prices   = DB\MoneyAmount::delete( 'synced_at < %s OR synced_at IS NULL', array( $sync_time ) );
		$delete_prices   = Utils::delete_prices_without_related_variant();

		if ( $delete_variants === false || $delete_prices === false ) {
			return false;
		}

		return true;
	}
}
