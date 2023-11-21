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

class Region extends Route {

	public function __construct() {
		$this->namespace = MEDUSAWP_REST_ROUTE_NAMESPACE;
		$this->route     = 'region';
	}

	private function get_country_schema() {
		return array(
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => array(
				'id'           => array(
					'type' => 'string',
				),
				'iso_2'        => array(
					'type' => 'string',
				),
				'iso_3'        => array(
					'type' => 'string',
				),
				'num_code'     => array(
					'type' => 'string',
				),
				'name'         => array(
					'type' => 'string',
				),
				'display_name' => array(
					'type' => 'string',
				),
				'region_id'    => array(
					'type' => 'string',
				),
				'synced_at'    => array(
					'type' => 'string',
				),
			),
			'required'             => array(
				'id',
				'iso_2',
				'iso_3',
				'num_code',
				'name',
				'display_name',
				'region_id',
				'synced_at',
			),
		);
	}

	private function get_region_schema() {
		return array(
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => array(
				'id'                 => array(
					'type' => 'string',
				),
				'name'               => array(
					'type' => 'string',
				),
				'currency_code'      => array(
					'type' => 'string',
				),
				'tax_rate'           => array(
					'type' => 'string',
				),
				'tax_code'           => array(
					'type' => array( 'null', 'string' ),
				),
				'created_at'         => array(
					'type' => 'string',
				),
				'updated_at'         => array(
					'type' => 'string',
				),
				'deleted_at'         => array(
					'type' => array( 'null', 'string' ),
				),
				'metadata'           => array(
					'type'                 => 'object',
					'additionalProperties' => true,
				),
				'countries'          => array(
					'type'  => 'array',
					'items' => $this->get_country_schema(),
				),
				'gift_cards_taxable' => array(
					'type' => 'string',
				),
				'automatic_taxes'    => array(
					'type' => 'string',
				),
				'includes_tax'       => array(
					'type' => 'string',
				),
				'tax_provider_id'    => array(
					'type' => array( 'null', 'string' ),
				),
				'synced_at'          => array(
					'type' => 'string',
				),
				'sync_status'        => array(
					'type' => array( 'null', 'string' ),
				),
			),
			'required'             => array(
				'id',
				'name',
				'currency_code',
				'tax_rate',
				'tax_code',
				'created_at',
				'updated_at',
				'deleted_at',
				'metadata',
				'countries',
				'gift_cards_taxable',
				'automatic_taxes',
				'includes_tax',
				'tax_provider_id',
				'synced_at',
				'sync_status',
			),
		);
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
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_items' ),
					'show_in_index'       => true,
					'permission_callback' => '__return_true',
				),
				'schema' => function () {
					return array_merge(
						array(
							'$schema' => 'http://json-schema.org/draft-04/schema#',
							'title'   => 'MedusaWPRegion',
						),
						$this->get_region_schema()
					);
				},
			)
		);

		register_rest_route(
			$this->namespace,
			$this->route . '/(?P<id>[\w]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_item' ),
					'show_in_index'       => false,
					'permission_callback' => '__return_true',
				),
				'schema' => function () {
					return array_merge(
						array(
							'$schema' => 'http://json-schema.org/draft-04/schema#',
							'title'   => 'MedusaWPRegion',
						),
						$this->get_region_schema()
					);
				},
			)
		);
	}

	/**
	 * Get region.
	 *
	 * @param  \WP_REST_Request $req
	 * @return WP_REST_Response
	 */
	public function get_item( $req ) {
		$params = $req->get_params();

		$regions              = Models\Region::find( $params['id'] );
		$regions['countries'] = Models\Country::find_all_by( 'region_id', $params['id'] );

		return new WP_REST_Response( $regions, 200 );
	}

	/**
	 * Get regions.
	 *
	 * @return WP_REST_Response
	 */
	public function get_items() {
		$regions = array_map(
			function ( $region ) {
				$region['countries'] = Models\Country::find_all_by( 'region_id', $region['id'] );
				return $region;
			},
			Models\Region::all()
		);

		return new WP_REST_Response( $regions, 200 );
	}

	/**
	 * Update regions.
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

		$processing_item_id = Utils::save_sync_progress( 'region', $data, $sync_timestamp );

		Models\Region::save( $data );
		$save_errors = Models\Region::get_errors();

		if ( ! $sync_timestamp ) {
			return new WP_REST_Response( null );
		}

		// While sync in progress, save sync messages for each model and update sync progress.
		Utils::update_sync_progress( $processing_item_id, $data, $save_errors );

		// If all items are synced, remove old data from DB.
		if ( Utils::is_total_synced( 'region', $sync_timestamp ) ) {
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

		$delete_variants = DB\Regions::delete( 'synced_at < %s OR synced_at IS NULL', array( $sync_time ) );

		if ( $delete_variants === false ) {
			return false;
		}

		return true;
	}
}
