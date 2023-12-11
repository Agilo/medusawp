<?php
/**
 * Defines product route.
 *
 * @package MedusaWP
 */

namespace MedusaWP\Routes;

use MedusaWP\Routes\Route;
use MedusaWP\Models;
use MedusaWP\DB;
use MedusaWP\Utils;
use MedusaWP\Settings;
use WP_REST_Response;

class Product extends Route {

	public function __construct() {
		$this->namespace = MEDUSAWP_REST_ROUTE_NAMESPACE;
		$this->route     = 'product';
	}

	public function register_route() {
		register_rest_route(
			$this->namespace,
			$this->route,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_items' ),
					'show_in_index'       => false,
					'permission_callback' => '__return_true',
				),
			)
		);

		// Register meta field for medusa-product endpoint.
		register_rest_field(
			'medusa-product',
			'meta',
			array(
				'get_callback'    => array( $this, 'get_meta' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}

	/**
	 * Get meta data from custom table for each object in API response.
	 *
	 * @param  mixed $product_object
	 * @return mixed
	 */
	public function get_meta( $product_object ) {
		$product             = Models\Product::find_by( 'post_id', $product_object['id'] );
		$product_variants    = Models\Product::get_variants( $product['id'] );
		$product['variants'] = $product_variants;

		return $product;
	}

	/**
	 * Route callback: Update products.
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

		$processing_item_id = Utils::save_sync_progress( 'product', $data, $sync_timestamp );

		// Save data
		Models\Product::save( $data );
		$save_errors = Models\Product::get_errors();

		// While sync in progress, save sync messages for each model and update sync progress.
		Utils::update_sync_progress( $processing_item_id, $data, $save_errors );

		if ( ! $sync_timestamp ) {
			if ( Settings::get_default_always_import_thumbnails() && $data['thumbnail'] ) {
				as_enqueue_async_action( 'medusawp_import_product_thumbnail', array( $data['id'], null ) );
			}

			return new WP_REST_Response( null );
		}

		$sync_progress = Settings::get_sync_progress();

		if ( $sync_progress['type'] === 'bulk_sync_and_import_thumbnails' && $data['thumbnail'] ) {
			as_enqueue_async_action( 'medusawp_import_product_thumbnail', array( $data['id'], $sync_timestamp ) );
		}

		// If all items are synced, enqueue thumbnail import and remove old data from DB.
		if ( Utils::is_total_synced( 'product', $sync_timestamp ) ) {
			Utils::update_is_sync_finished( $sync_timestamp );

			$this->db_cleanup();
		}

		return new WP_REST_Response( null );
	}

	/**
	 * After resync, delete from DB items that don't exist in Medusa anymore.
	 *
	 * @return bool
	 */
	private function db_cleanup() {
		$sync_progress = Settings::get_sync_progress();
		$sync_time     = $sync_progress['started_at'];

		$delete_products = DB\Products::delete( 'synced_at < %s OR synced_at IS NULL', array( $sync_time ) );
		$delete_posts    = Utils::delete_posts_without_related_product();

		if ( $delete_products === false || $delete_posts === false ) {
			// phpcs:ignore
			error_log( '[MedusaWP] Cleanup of products failed. Please delete old data manaully.', 'medusawp' );
			return false;
		}

		return true;
	}
}
