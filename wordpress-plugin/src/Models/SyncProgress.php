<?php
/**
 * Sync Progress
 *
 * @package MedusaWP
 */

namespace MedusaWP\Models;

class SyncProgress extends Model {

	public static $table_name = MEDUSAWP_TABLE_SYNC_PROGRESS;

	public static $primary_key = 'id';

	public static $columns_format = array(
		'id'                => '%d',
		'model'             => '%s',
		'model_id'          => '%s',
		'status'            => '%s',
		'message'           => '%s',
		'data'              => '%s',
		'medusa_admin_link' => '%s',
		'sync_timestamp'    => '%d',
		'started_at'        => '%d',
		'ended_at'          => '%d',
	);

	public static $validation_rules = array(
		'model'             => 'required|in:product,product-collection,product-variant,region,thumbnail',
		'model_id'          => 'required|string',
		'status'            => 'required|in:syncing,success,error',
		'message'           => 'required|string',
		'data'              => 'required|string',
		'medusa_admin_link' => 'nullable|string',
		'sync_timestamp'    => 'nullable|integer',
		'started_at'        => 'required|integer',
		'ended_at'          => 'nullable|integer',
	);

	/**
	 * Save data.
	 *
	 * @param  array $data
	 * @return bool|int
	 */
	public static function save( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		// Validate data.
		$validate = static::validate_data( $data );

		if ( ! $validate ) {
			return false;
		}

		$db_data = array();
		$id      = isset( $data['id'] ) ? $data['id'] : null;
		$update  = static::exists( $id ) ? true : false;

		// Foreach value in received data, check if we have its column in DB and add to $db_data;
		foreach ( $data as $key => $value ) {
			if ( isset( static::$columns_format[ $key ] ) ) {
				$db_data[ $key ] = $value;
			}
		}

		if ( $update ) {
			return static::update( $id, $db_data );
		}

		return static::insert( $db_data );
	}

	/**
	 * Count all synced items per model.
	 *
	 * @return int
	 */
	public static function count_synced( string $model, int $sync_timestamp ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$table_name = static::$table_name;

		return intval(
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->get_var(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT COUNT(*) FROM $table_name WHERE model = %s AND sync_timestamp = %d",
					$model,
					$sync_timestamp
				)
			)
		);
	}

	/**
	 * Overall count of synced items.
	 *
	 * @return int
	 */
	public static function count_all_synced( int $sync_timestamp ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$table_name = static::$table_name;

		return intval(
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->get_var(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT COUNT(DISTINCT model_id) FROM $table_name WHERE sync_timestamp = %d",
					$sync_timestamp
				)
			)
		);
	}

	public static function get_troubleshoot_messages( int $sync_timestamp, $options = array() ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$table_name = static::$table_name;

		if ( empty( $options ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM $table_name"
				),
				ARRAY_A
			);
		}

		$default_options = array(
			'page'     => 1,
			'per_page' => get_option( 'posts_per_page', 10 ),
		);

		$options = array_merge( $default_options, $options );

		$options['page']     = abs( intval( $options['page'] ) );
		$options['per_page'] = abs( intval( $options['per_page'] ) );

		if ( $options['per_page'] < 1 ) {
			$options['per_page'] = $default_options['per_page'];
		}

		if ( $options['page'] < 1 ) {
			$options['page'] = $default_options['page'];
		}

		$offset = ( $options['page'] - 1 ) * $options['per_page'];

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM $table_name WHERE sync_timestamp = %d AND status = 'error' ORDER BY started_at DESC LIMIT %d OFFSET %d;",
				array( $sync_timestamp, $options['per_page'], $offset )
			),
			'ARRAY_A'
		);
	}

	public static function count_troubleshoot_messages( int $sync_timestamp ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$table_name = static::$table_name;

		return intval(
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->get_var(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT COUNT(*) FROM $table_name WHERE sync_timestamp = %d AND status = 'error'",
					$sync_timestamp
				)
			)
		);
	}
}
