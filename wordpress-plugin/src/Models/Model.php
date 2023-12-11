<?php
/**
 * Model abstract class
 *
 * @package MedusaWP
 */

namespace MedusaWP\Models;

use MedusaWP\ValidatorFactory;

// TODO: add wp cache
/**
 * Base Model
 */
abstract class Model {
	/**
	 * Table associated with Model.
	 *
	 * @var string
	 */
	public static $table_name;

	/**
	 * Primary key.
	 *
	 * @var mixed
	 */
	public static $primary_key;

	/**
	 * Table columns structure.
	 *
	 * @var mixed
	 */
	public static $columns_format;

	/**
	 * Dedicated CPT name if exists, false if does not exist
	 *
	 * @var string|bool
	 */
	public static $cpt;

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public static $validation_rules;

	/**
	 * Validation errors
	 *
	 * @var array
	 */
	public static $errors;

	/**
	 * Check if exists.
	 *
	 * @param  mixed $id
	 * @return object|null
	 */
	public static function exists( $id ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;

		if ( ! $id ) {
			return false;
		}

		$table_name  = static::$table_name;
		$primary_key = static::$primary_key;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM $table_name WHERE $primary_key = %s LIMIT 1;",
				array( $id )
			)
		);
	}

	/**
	 * Get column value.
	 *
	 * @param  string $column
	 * @param  string|int $id
	 * @return string|null
	 */
	public static function get_column( $column, $id ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;

		if ( ! $id ) {
			return;
		}

		$table_name  = static::$table_name;
		$primary_key = static::$primary_key;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT $column FROM $table_name WHERE $primary_key = %s LIMIT 1;",
				array( $id )
			)
		);
	}

	/**
	 * Delete row from DB.
	 *
	 * @param  mixed $id
	 * @return int|false
	 */
	public static function delete( $id ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;

		if ( ! $id ) {
			return;
		}

		$table_name  = static::$table_name;
		$primary_key = static::$primary_key;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete( $table_name, array( $primary_key => $id ) );
	}

	/**
	 * Query by column and return one row.
	 *
	 * @param  string $column
	 * @param  mixed $value
	 * @return array|null
	 */
	public static function find_by( $column, $value ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$format     = static::$columns_format[ $column ];
		$table_name = static::$table_name;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				"SELECT * FROM $table_name WHERE $column = $format;",
				array( $value )
			),
			'ARRAY_A'
		);
	}

	/**
	 * Query by column and return multiple rows.
	 *
	 * @param  string $column
	 * @param  mixed $value
	 * @param array $options {
	 *     Optional. An array of pagination options. Default empty array.
	 *
	 *     @type int $page     Current page.
	 *     @type int $per_page Number of results per page.
	 * }
	 * @return array|null
	 */
	public static function find_all_by( $column, $value, $options = array() ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$format     = static::$columns_format[ $column ];
		$table_name = static::$table_name;

		if ( empty( $options ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
					"SELECT * FROM $table_name WHERE $column = $format;",
					array( $value )
				),
				'ARRAY_A'
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
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM $table_name WHERE $column = $format LIMIT %d OFFSET %d;",
				array( $value, $options['per_page'], $offset )
			),
			'ARRAY_A'
		);
	}

	/**
	 * Get row by id
	 *
	 * @param  mixed $value
	 * @return object|null
	 */
	public static function find( $value ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$table_name  = static::$table_name;
		$primary_key = static::$primary_key;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM $table_name WHERE $primary_key = %s LIMIT 1;",
				array( $value )
			),
			ARRAY_A
		);
	}

	/**
	 * Update row.
	 *
	 * @param  string|int $id
	 * @param  array $data
	 * @return int|false
	 */
	public static function update( mixed $id, array $data ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$table_name     = static::$table_name;
		$primary_key    = static::$primary_key;
		$columns_format = static::$columns_format;

		$format = array_map(
			function ( $key ) use ( $columns_format ) {
				return $columns_format[ $key ];
			},
			array_keys( $data )
		);

		$where_format = array( $columns_format[ $primary_key ] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$update = $wpdb->update( $table_name, $data, array( $primary_key => $id ), $format, $where_format );

		if ( $update === false ) {
			return false;
		}

		return true;
	}

	/**
	 * Insert row.
	 *
	 * @param  array $data
	 * @return int|false
	 */
	public static function insert( array $data ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$table_name     = static::$table_name;
		$columns_format = static::$columns_format;

		$format = array_map(
			function ( $key ) use ( $columns_format ) {
				return $columns_format[ $key ];
			},
			array_keys( $data )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$insert = $wpdb->insert( $table_name, $data, $format );

		if ( ! $insert ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get columns format.
	 *
	 * @return array
	 */
	public static function get_columns_format() {
		return static::$columns_format;
	}

	/**
	 * Validate data before update.
	 *
	 * @param  mixed $data
	 * @return bool
	 */
	public static function validate_data( $data ) {
		$factory   = new ValidatorFactory();
		$errors    = array();
		$validator = $factory->make(
			$data,
			static::$validation_rules
		);

		try {
			if ( $validator->fails() ) {
				$errors = $validator->errors();

				foreach ( $errors->all() as $message ) {
					static::$errors[] = $message;
					// phpcs:ignore
					error_log( '[MedusaWP] Validation error for ' . static::$table_name . ':' . $message );
				}

				return false;
			}
		} catch ( \Exception $e ) {
			// phpcs:ignore
			error_log( $e );
			static::$errors[] = __( 'Data validation failed.', 'medusawp' );

			return false;
		}

		return true;
	}

	/**
	 * Get validation errors.
	 *
	 * @return array
	 */
	public static function get_errors() {
		return static::$errors;
	}

	/**
	 * Get all rows.
	 *
	 * @param array $options {
	 *     Optional. An array of pagination options. Default empty array.
	 *
	 *     @type int $page     Current page.
	 *     @type int $per_page Number of results per page.
	 * }
	 * @return array
	 */
	public static function all( $options = array() ) {
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
				"SELECT * FROM $table_name LIMIT %d OFFSET %d",
				array( $options['per_page'], $offset )
			),
			ARRAY_A
		);
	}

	/**
	 * Count all rows.
	 *
	 * @return int
	 */
	public static function count_all() {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$table_name = static::$table_name;

		return intval(
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->prepare( "SELECT COUNT(*) FROM $table_name" )
			)
		);
	}

	/**
	 * Count all rows queried by column.
	 *
	 * @return int
	 */
	public static function count_all_by( $column, $value ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$format     = static::$columns_format[ $column ];
		$table_name = static::$table_name;

		return intval(
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->get_var(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
					"SELECT COUNT(*) FROM $table_name WHERE $column = $format;",
					array( $value )
				)
			)
		);
	}
}
