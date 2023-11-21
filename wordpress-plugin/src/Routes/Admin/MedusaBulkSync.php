<?php
/**
 * Admin routes.
 *
 * @package MedusaWP
 */

namespace MedusaWP\Routes\Admin;

use MedusaWP\MedusaClient\Admin\Api\DefaultApi;
use MedusaWP\MedusaClient\Admin\ApiException;
use MedusaWP\MedusaClient\Admin\Configuration;
use MedusaWP\MedusaClient\Admin\Model\MedusaWPSyncRequest;
use MedusaWP\Models\Product;
use MedusaWP\Models\SyncProgress;
use MedusaWP\Settings;
use MedusaWP\Utils;
use WP_REST_Response;

class MedusaBulkSync {
	/**
	 * Namespace
	 *
	 * @var string
	 */
	public $namespace;

	public function __construct() {
		$this->namespace = MEDUSAWP_REST_ADMIN_ROUTE_NAMESPACE;
	}

	private function get_sync_message_schema() {
		return array(
			'title'                => 'MedusaWPSyncMessage',
			'type'                 => 'object',
			'description'          => __( 'Sync message item.', 'medusawp' ),
			'additionalProperties' => false,
			'required'             => array(
				'sync_timestamp',
				'type',
				'message',
				'data',
				'started_at',
				'ended_at',
				'medusa_admin_link',
				'model',
			),
			'properties'           => array(
				'id'                => array(
					'type'        => 'number',
					'description' => __( 'Sync message id.', 'medusawp' ),
				),
				'model'             => array(
					'type'        => 'string',
					'description' => __( 'Sync message model.', 'medusawp' ),
				),
				'status'            => array(
					'type'        => 'string',
					'description' => __( 'Sync message status.', 'medusawp' ),
				),
				'message'           => array(
					'type'        => 'string',
					'description' => __( 'Sync message.', 'medusawp' ),
				),
				'data'              => array(
					'type'        => 'string',
					'description' => __( 'Sync message json encoded data.', 'medusawp' ),
				),
				'medusa_admin_link' => array(
					'type'        => array( 'string', 'null' ),
					'format'      => 'uri',
					'description' => __( 'Sync message medusa admin link.', 'medusawp' ),
				),
				'sync_timestamp'    => array(
					'type'        => array( 'number', 'null' ),
					'description' => __( 'Sync timestamp.', 'medusawp' ),
				),
				'started_at'        => array(
					'type'        => 'number',
					'description' => __( 'Sync message started at.', 'medusawp' ),
				),
				'ended_at'          => array(
					'type'        => 'number',
					'description' => __( 'Sync message started at.', 'medusawp' ),
				),
			),
		);
	}

	public function sync_messages_schema() {
		return array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'MedusaWPSyncMessages',
			'type'                 => 'object',
			'additionalProperties' => false,
			'required'             => array(
				'messages',
				'total',
				'current_page',
				'last_page',
			),
			'properties'           => array(
				'messages'     => array(
					'type'        => 'array',
					'items'       => $this->get_sync_message_schema(),
					'description' => __( 'Messages.', 'medusawp' ),
				),
				'total'        => array(
					'type'        => 'number',
					'description' => __( 'Total number of messages.', 'medusawp' ),
				),
				'current_page' => array(
					'type'        => 'number',
					'description' => __( 'Current page.', 'medusawp' ),
				),
				'last_page'    => array(
					'type'        => 'number',
					'description' => __( 'Last page.', 'medusawp' ),
				),
			),
		);
	}

	private function get_sync_response_object_schema( $with_messages = false ) {
		$title      = 'MedusaWPSyncResponse';
		$required   = array(
			'started_at',
			'ended_at',
			'totals',
			'synced',
			'import_thumbnails',
		);
		$properties = array(
			'started_at'        => array(
				'type'        => 'number',
				'description' => __( 'Timestamp when sync started.', 'medusawp' ),
			),
			'ended_at'          => array(
				'type'        => array( 'number', 'null' ),
				'description' => __( 'Timestamp when sync ended.', 'medusawp' ),
			),
			'totals'            => array(
				'oneOf' => array(
					array(
						'type'                 => 'object',
						'description'          => __( 'Total numbers to sync.', 'medusawp' ),
						'additionalProperties' => false,
						'required'             => array(
							'products',
							'product_variants',
							'collections',
							'regions',
						),
						'properties'           => array(
							'products'         => array(
								'type'        => 'number',
								'description' => __( 'Total number of products to sync.', 'medusawp' ),
							),
							'product_variants' => array(
								'type'        => 'number',
								'description' => __( 'Total number of product variants to sync.', 'medusawp' ),
							),
							'collections'      => array(
								'type'        => 'number',
								'description' => __( 'Total number of collections to sync.', 'medusawp' ),
							),
							'regions'          => array(
								'type'        => 'number',
								'description' => __( 'Total number of regions to sync.', 'medusawp' ),
							),
							'thumbnails'       => array(
								'type'        => 'number',
								'description' => __( 'Total number of thumbnails to sync.', 'medusawp' ),
							),
						),
					),
					array(
						'type'                 => 'object',
						'description'          => __( 'Total numbers to sync.', 'medusawp' ),
						'additionalProperties' => false,
						'required'             => array(
							'thumbnails',
						),
						'properties'           => array(
							'thumbnails' => array(
								'type'        => 'number',
								'description' => __( 'Total number of thumbnails to sync.', 'medusawp' ),
							),
						),
					),
				),
			),
			'synced'            => array(
				'oneOf' => array(
					array(
						'type'                 => 'object',
						'description'          => __( 'Totals of synced data.', 'medusawp' ),
						'additionalProperties' => false,
						'required'             => array(
							'products',
							'product_variants',
							'collections',
							'regions',
						),
						'properties'           => array(
							'products'         => array(
								'type'        => 'number',
								'description' => __( 'Total number of synced products.', 'medusawp' ),
							),
							'product_variants' => array(
								'type'        => 'number',
								'description' => __( 'Total number of synced product variants.', 'medusawp' ),
							),
							'collections'      => array(
								'type'        => 'number',
								'description' => __( 'Total number of synced collections.', 'medusawp' ),
							),
							'regions'          => array(
								'type'        => 'number',
								'description' => __( 'Total number of synced regions.', 'medusawp' ),
							),
							'thumbnails'       => array(
								'type'        => 'number',
								'description' => __( 'Total number of synced thumbnails.', 'medusawp' ),
							),
						),
					),
					array(
						'type'                 => 'object',
						'description'          => __( 'Totals of synced data.', 'medusawp' ),
						'additionalProperties' => false,
						'required'             => array(
							'thumbnails',
						),
						'properties'           => array(
							'thumbnails' => array(
								'type'        => 'number',
								'description' => __( 'Total number of synced thumbnails.', 'medusawp' ),
							),
						),
					),
				),
			),
			'import_thumbnails' => array(
				'type'        => 'boolean',
				'description' => __( 'Import thumbnails or not.', 'medusawp' ),
			),
			'type'              => array(
				'type'        => 'string',
				'description' => __( 'Sync type.', 'medusawp' ),
			),
		);

		if ( $with_messages ) {
			$title                  = 'MedusaWPSyncResponseWithMessages';
			$required[]             = 'messages';
			$properties['messages'] = array(
				'type'  => 'array',
				'items' => $this->get_sync_message_schema(),
			);
		}

		return array(
			'title'                => $title,
			'type'                 => 'object',
			'additionalProperties' => false,
			'required'             => $required,
			'properties'           => $properties,
		);
	}

	public function sync_response_schema() {
		return array_merge(
			array(
				'$schema' => 'http://json-schema.org/draft-04/schema#',
			),
			$this->get_sync_response_object_schema()
		);
	}

	public function sync_progress_response_schema() {
		return array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'MedusaWPSyncProgressResponse',
			'type'                 => 'object',
			'additionalProperties' => false,
			'required'             => array(
				'progress',
			),
			'properties'           => array(
				'progress' => array(
					'oneOf' => array(
						$this->get_sync_response_object_schema( true ),
						array(
							'type' => 'null',
						),
					),
				),
			),
		);
	}

	public function register_admin_routes() {
		register_rest_route(
			$this->namespace,
			'/sync',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'sync' ),
					'args'                => array(
						'import_thumbnails' => array(
							'type'     => 'boolean',
							'required' => false,
							'default'  => false,
						),
					),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				),
				'schema' => array( $this, 'sync_response_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/sync-messages',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_sync_messages' ),
					'args'                => array(
						'page'     => array(
							'type'     => 'integer',
							'required' => false,
							'default'  => 1,
						),
						'per_page' => array(
							'type'     => 'integer',
							'required' => false,
							'default'  => intval( get_option( 'posts_per_page', 10 ) ),
						),
						'status'   => array(
							'type'     => 'string',
							'required' => false,
						),
					),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				),
				'schema' => array( $this, 'sync_messages_schema' ),
			),
		);

		register_rest_route(
			$this->namespace,
			'/sync-progress',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_sync_progress' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				),
				'schema' => array( $this, 'sync_progress_response_schema' ),
			),
		);

		register_rest_route(
			$this->namespace,
			'/remove-synced',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'remove_synced_data' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			),
		);

		register_rest_route(
			$this->namespace,
			'/import-thumbnails',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'import_thumbnails' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				),
				'schema' => array( $this, 'sync_response_schema' ),
			),
		);
	}

	/**
	 * Route callback: Sync.
	 *
	 * @param  \WP_REST_Request $req
	 * @return WP_REST_Response|\WP_Error
	 */
	public function sync( $req ) {
		$params = $req->get_json_params();
		/**
		 * @var bool
		 */
		$import_thumbnails = $params['import_thumbnails'];

		$url       = Settings::get_medusa_url();
		$api_token = Settings::get_medusa_api_token();

		if ( ! $url ) {
			return new WP_REST_Response(
				array(
					'error' => 'Can not find Medusa connection URL. Please connect Medusa before running sync.',
				),
				400
			);
		}

		if ( ! $api_token ) {
			return new WP_REST_Response(
				array(
					'error' => 'Can not find Medusa connection API token. Please connect Medusa before running sync.',
				),
				400
			);
		}

		if ( Utils::is_sync_in_progress() ) {
			return new WP_REST_Response(
				array(
					'error' => 'Sync already in progress.',
				),
				400
			);
		}

		$api_configuration = new Configuration();
		$api_configuration->setHost( $url );
		$api_configuration->setApiKey( 'x-medusa-access-token', $api_token );

		$api            = new DefaultApi( null, $api_configuration );
		$sync_timestamp = time();

		$request = new MedusaWPSyncRequest(
			array(
				'import_thumbnails' => $import_thumbnails,
				'sync_timestamp'    => $sync_timestamp,
			)
		);

		try {
			$response = $api->medusaWPSync( site_url(), $request );
		} catch ( ApiException $e ) {
			return new WP_REST_Response(
				array(
					'message' => $e->getMessage(),
				),
				$e->getCode()
			);
		}

		$totals = $response->getTotals()->jsonSerialize();
		$synced = array();

		foreach ( $totals as $key => $value ) {
			$synced[ $key ] = 0;
		}

		Settings::save_sync_progress(
			array(
				'started_at' => $sync_timestamp,
				'ended_at'   => null,
				'totals'     => $totals,
				'synced'     => $synced,
				'type'       => $import_thumbnails ? 'bulk_sync_and_import_thumbnails' : 'bulk_sync',
			)
		);

		return new WP_REST_Response(
			array(
				'started_at'        => $sync_timestamp,
				'ended_at'          => null,
				'totals'            => $totals,
				'synced'            => $synced,
				'import_thumbnails' => $import_thumbnails,
				'type'              => $import_thumbnails ? 'bulk_sync_and_import_thumbnails' : 'bulk_sync',
			)
		);
	}

	/**
	 * Route callback: Get sync messages.
	 *
	 * @param  \WP_REST_Request $req
	 * @return WP_REST_Response
	 */
	public function get_sync_messages( $req ) {
		$params = $req->get_params();

		/**
		 * @var int
		 */
		$page = $params['page'];
		/**
		 * @var int
		 */
		$per_page = $params['per_page'];
		/**
		 * @var string|null
		 */
		$status = $params['status'];

		$options = array(
			'page'     => $page,
			'per_page' => $per_page,
		);

		$total_records = ! empty( $status )
			? SyncProgress::count_all_by( 'status', $status )
			: SyncProgress::count_all();

		$total_pages = ceil( $total_records / $per_page );

		$options['page'] = filter_var(
			$page,
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'default'   => $page > $total_pages ? $total_pages : 1,
					'min_range' => 1,
					'max_range' => $total_pages,
				),
			)
		);

		$messages = ! empty( $status )
			? SyncProgress::find_all_by( 'status', $status, $options )
			: SyncProgress::all( $options );

		return new WP_REST_Response(
			array(
				'messages'     => $messages,
				'total'        => $total_records,
				'current_page' => $options['page'],
				'last_page'    => $total_pages,
			)
		);
	}

	/**
	 * Route callback: Get sync progress.
	 *
	 * @return WP_REST_Response
	 */
	public function get_sync_progress() {
		$progress = Settings::get_sync_progress();

		if ( empty( $progress ) || empty( $progress['started_at'] ) || empty( $progress['type'] ) ) {
			return new WP_REST_Response(
				array(
					'progress' => null,
				)
			);
		}

		$sync_timestamp = $progress['started_at'];
		$synced         = array();

		if ( $progress['type'] === 'bulk_sync' || $progress['type'] === 'bulk_sync_and_import_thumbnails' ) {
			$synced = array(
				'products'         => SyncProgress::count_synced( 'product', $sync_timestamp ),
				'product_variants' => SyncProgress::count_synced( 'product-variant', $sync_timestamp ),
				'collections'      => SyncProgress::count_synced( 'product-collection', $sync_timestamp ),
				'regions'          => SyncProgress::count_synced( 'region', $sync_timestamp ),
			);

			if ( $progress['type'] === 'bulk_sync_and_import_thumbnails' ) {
				$synced['thumbnails'] = SyncProgress::count_synced( 'thumbnail', $sync_timestamp );
			}
		} else {
			$synced = array(
				'thumbnails' => SyncProgress::count_synced( 'thumbnail', $sync_timestamp ),
			);
		}

		return new WP_REST_Response(
			array(
				'progress' => array(
					'started_at'        => $progress['started_at'],
					'ended_at'          => $progress['ended_at'],
					'totals'            => $progress['totals'],
					'synced'            => $synced,
					'import_thumbnails' => $progress['type'] === 'bulk_sync_and_import_thumbnails' || $progress['type'] === 'import_thumbnails',
					'messages'          => SyncProgress::get_sync_progress_troubleshoot_messages( $sync_timestamp ),
					'type'              => $progress['type'],
				),
			)
		);
	}

	/**
	 * Route callback: Remove synced data.
	 *
	 * @return WP_REST_Response
	 */
	public function remove_synced_data() {
		global $wpdb;
		$errors = array();

		$tables = array(
			MEDUSAWP_TABLE_PRODUCTS,
			MEDUSAWP_TABLE_PRODUCT_VARIANTS,
			MEDUSAWP_TABLE_PRODUCT_COLLECTIONS,
			MEDUSAWP_TABLE_REGIONS,
			MEDUSAWP_TABLE_MONEY_AMOUNT,
			MEDUSAWP_TABLE_COUNTRIES,
			MEDUSAWP_TABLE_SYNC_PROGRESS,
		);

		foreach ( $tables as $table ) {
			$truncate = $wpdb->query( "TRUNCATE TABLE $table" );
			if ( ! $truncate ) {
				$errors[] = $wpdb->last_error;
			}
		}

		// Delete product posts from wp_posts and product posts meta from wp_postmeta
		$posts_delete = $wpdb->query(
			$wpdb->prepare(
				"DELETE post, postmeta FROM $wpdb->posts post LEFT JOIN $wpdb->postmeta postmeta ON post.id = postmeta.post_id LEFT JOIN $tables[0] product ON post.id = product.post_id WHERE post.post_type = 'medusa-product' AND product.post_id IS NULL",
			)
		);

		if ( $posts_delete === false ) {
			$errors[] = __( 'Cleanup of posts for products failed. Please try deleting data manually.', 'medusawp' );
		}

		// Delete collections posts from wp_posts
		$collections_delete = $wpdb->query(
			$wpdb->prepare(
				"DELETE post FROM $wpdb->posts post LEFT JOIN $tables[2] collection ON post.id = collection.post_id WHERE post.post_type = 'medusa-collection' AND collection.post_id IS NULL"
			)
		);

		if ( $collections_delete === false ) {
			$errors[] = __( 'Cleanup of posts for collections failed. Please try deleting data manually.', 'medusawp' );
		}

		// Delete plugin's custom options from wp_options
		delete_option( 'medusawp_settings_default_country_code' );
		delete_option( 'medusawp_settings_default_always_import_thumbnails' );
		delete_option( 'medusawp_sync_progress' );

		if ( ! empty( $errors ) ) {
			return new WP_REST_Response(
				array(
					'errors' => $errors,
				),
				400
			);
		}

		return new WP_REST_Response();
	}

	/**
	 * Route callback: Import thumbnails.
	 *
	 * @return WP_REST_Response
	 */
	public function import_thumbnails() {
		if ( ! function_exists( 'as_enqueue_async_action' ) ) {
			return new WP_REST_Response(
				array(
					'message' => __( 'Action scheduler is missing.', 'medusawp' ),
				),
				400
			);
		}

		if ( Utils::is_sync_in_progress() ) {
			return new WP_REST_Response(
				array(
					'error' => 'Sync already in progress.',
				),
				400
			);
		}

		$sync_timestamp = time();
		$totals         = array(
			'thumbnails' => Product::count_all_thumbnails(),
		);
		$synced         = array(
			'thumbnails' => 0,
		);

		Settings::save_sync_progress(
			array(
				'started_at' => $sync_timestamp,
				'ended_at'   => null,
				'totals'     => $totals,
				'synced'     => $synced,
				'type'       => 'import_thumbnails',
			)
		);

		as_enqueue_async_action( 'medusawp_schedule_import_product_thumbnail_batch', array( 0, $sync_timestamp ) );

		return new WP_REST_Response(
			array(
				'started_at'        => $sync_timestamp,
				'ended_at'          => null,
				'totals'            => $totals,
				'synced'            => $synced,
				'import_thumbnails' => true,
				'type'              => 'import_thumbnails',
			)
		);
	}
}
