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

class ProductCollection extends Route {

	public function __construct() {
		$this->namespace = MEDUSAWP_REST_ROUTE_NAMESPACE;
		$this->route     = 'product-collection';
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

		register_rest_route(
			$this->namespace,
			$this->route . '/(?P<id>[\d]+)/products',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_collection_products' ),
				'show_in_index'       => false,
				'permission_callback' => '__return_true',
			)
		);

		// Register meta field for medusa-collection endpoint.
		register_rest_field(
			'medusa-collection',
			'meta',
			array(
				'get_callback'    => array( $this, 'get_meta' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}

	/**
	 * Get products from collection.
	 *
	 * @param  \WP_REST_Request $req
	 * @return WP_REST_Response
	 */
	public function get_collection_products( $req ) {
		$collection_id = $req['id'];

		$params        = $req->get_params();
		$post_per_page = isset( $params['per_page'] ) ? $params['per_page'] : null;
		$paged         = isset( $params['page'] ) ? $params['page'] : null;

		// Get products from collection
		$filtered = Models\ProductCollection::get_products_post_ids( (int) $collection_id );

		if ( ! is_array( $filtered ) || empty( $filtered ) ) {
			return new WP_REST_Response( array(), 200 );
		}

		$args = array(
			'posts_per_page' => $post_per_page,
			'paged'          => $paged,
			'post_type'      => 'medusa-product',
			'post__in'       => $filtered,
		);

		$query = new \WP_Query( $args );

		$max_pages = $query->max_num_pages;
		$total     = $query->found_posts;
		$posts     = $query->posts;

		$controller = new \WP_REST_Posts_Controller( 'medusa-product' );

		foreach ( $posts as $post ) {
			$response = $controller->prepare_item_for_response( $post, $req );
			$data[]   = $controller->prepare_response_for_collection( $response );
		}

		return new WP_REST_Response(
			$data,
			200,
			array(
				'X-WP-Total'      => $total,
				'X-WP-TotalPages' => $max_pages,
			)
		);
	}

	/**
	 * Get meta data from custom table for each object in API response.
	 *
	 * @param  mixed $product_collection
	 * @return object|null
	 */
	public function get_meta( $product_collection ) {
		return Models\ProductCollection::find_by( 'post_id', $product_collection['id'] );
	}

	/**
	 * Update collections.
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

		$processing_item_id = Utils::save_sync_progress( 'product-collection', $data, $sync_timestamp );

		// Save data for each item
		Models\ProductCollection::save( $data );
		$save_errors = Models\ProductCollection::get_errors();

		if ( ! $sync_timestamp ) {
			return new WP_REST_Response( null );
		}

		// While sync in progress, save sync messages for each model and update sync progress.
		Utils::update_sync_progress( $processing_item_id, $data, $save_errors );

		// If all items are synced, remove old data from DB.
		if ( Utils::is_total_synced( 'product-collection', $sync_timestamp ) ) {
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

		$delete_products = DB\ProductCollections::delete( 'synced_at < %s OR synced_at IS NULL', array( $sync_time ) );
		$delete_posts    = Utils::delete_posts_without_related_collection();

		if ( $delete_products === false || $delete_posts === false ) {
			return false;
		}

		return true;
	}
}
