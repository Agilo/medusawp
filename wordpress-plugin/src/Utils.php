<?php

namespace MedusaWP;

use MedusaWP\Models;

class Utils {
	/**
	 * Return value of transient which is set when resync begins.
	 *
	 * @param integer|null $sync_timestamp
	 * @return bool
	 */
	public static function is_sync_in_progress( ?int $sync_timestamp = null ) {
		$sync_progress = Settings::get_sync_progress();

		if ( empty( $sync_progress ) || empty( $sync_progress['started_at'] ) ) {
			return false;
		}

		if ( $sync_timestamp && (int) $sync_progress['started_at'] !== $sync_timestamp ) {
			return false;
		}

		if ( $sync_progress['ended_at'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Delete posts from wp_posts that don't have related product.
	 *
	 * @return int|bool
	 */
	public static function delete_posts_without_related_product() {
		global $wpdb;
		$products_table = MEDUSAWP_TABLE_PRODUCTS;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE post FROM $wpdb->posts post LEFT JOIN $products_table product ON post.id = product.post_id WHERE post.post_type = 'medusa-product' AND product.post_id IS NULL"
			)
		);
	}

	/**
	 * Delete posts that don't have related collection.
	 *
	 * @return int|bool
	 */
	public static function delete_posts_without_related_collection() {
		global $wpdb;
		$collections_table = MEDUSAWP_TABLE_PRODUCT_COLLECTIONS;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE post FROM $wpdb->posts post LEFT JOIN $collections_table pcol ON post.id = pcol.post_id WHERE post.post_type = 'medusa-collection' AND pcol.post_id IS NULL"
			)
		);
	}

	/**
	 * Delete prices that don't have related variant.
	 *
	 * @return int|bool
	 */
	public static function delete_prices_without_related_variant() {
		global $wpdb;
		$variants_table = MEDUSAWP_TABLE_PRODUCT_VARIANTS;
		$prices_table   = MEDUSAWP_TABLE_MONEY_AMOUNT;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE price FROM $prices_table price LEFT JOIN $variants_table variant ON price.variant_id = variant.id WHERE variant.id IS NULL"
			)
		);
	}

	/**
	 * Get collection name by its id.
	 *
	 * @param  string $collection_id
	 * @return array
	 */
	public static function get_collection_name( $collection_id ) {
		global $wpdb;
		$collections_table = MEDUSAWP_TABLE_PRODUCT_COLLECTIONS;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT title FROM $collections_table WHERE id = %s LIMIT 1;",
				array( $collection_id )
			),
			'ARRAY_A'
		);
	}

	/**
	 * Get all regions.
	 *
	 * @return array
	 */
	public static function get_regions() {
		global $wpdb;
		$table = MEDUSAWP_TABLE_REGIONS;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare( "SELECT * FROM $table" )
		);
	}

	/**
	 * Get variant prices.
	 *
	 * @param  string $variant_id
	 * @return array
	 */
	public static function get_variant_prices( $variant_id ) {
		global $wpdb;
		$prices_table = MEDUSAWP_TABLE_MONEY_AMOUNT;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM $prices_table WHERE variant_id = %s;",
				array( $variant_id )
			)
		);
	}

	/**
	 * Format meta field for meta info metabox.
	 *
	 * @param  mixed $value
	 * @param  string $format
	 * @return array
	 */
	public static function format_meta_field( $value, $format = '%s' ) {
		if ( ! $value ) {
			return array(
				'value'  => 'null',
				'format' => 'null',
			);
		}

		if ( $format === '%d' ) {
			return array(
				'value'  => $value,
				'format' => 'int',
			);
		}

		return array(
			'value'  => '"' . $value . '"',
			'format' => 'string',
		);
	}

	public static function create_tables() {
		\MedusaWP\DB\Products::create_table();
		\MedusaWP\DB\ProductCollections::create_table();
		\MedusaWP\DB\ProductVariants::create_table();
		\MedusaWP\DB\MoneyAmount::create_table();
		\MedusaWP\DB\Regions::create_table();
		\MedusaWP\DB\Countries::create_table();
		\MedusaWP\DB\SyncProgress::create_table();
	}

	/**
	 * Upload image to media library from URL.
	 *
	 * @param  mixed $url
	 * @return int|\WP_Error
	 */
	public static function upload_image_from_url( $url ) {
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$file             = array();
		$file['name']     = wp_basename( $url );
		$file['tmp_name'] = \download_url( $url );

		if ( is_wp_error( $file['tmp_name'] ) ) {
			return $file['tmp_name'];
		}

		$id = \media_handle_sideload( $file );
		return $id;
	}

	/*
	 * Save sync progress to DB for each item.
	 *
	 * @param  string $model
	 * @param  array $data
	 * @param  int|null $sync_timestamp
	 * @return bool|int
	 */
	public static function save_sync_progress( string $model, array $data, ?int $sync_timestamp = null ) {
		return Models\SyncProgress::save(
			array(
				'model'          => $model,
				'message'        => 'Syncing ' . $data['id'] . '...',
				'status'         => 'syncing',
				'data'           => wp_json_encode( $data ),
				'sync_timestamp' => $sync_timestamp,
				'started_at'     => time(),
			)
		);
	}

	/*
	 * Update sync progress for each item.
	 *
	 * @param  int $id
	 * @return int|false
	 */
	public static function update_sync_progress( int $id, array $data, ?array $errors ) {
		if ( ! $id || ! is_array( $data ) ) {
			return null;
		}

		if ( empty( $errors ) ) {
			return Models\SyncProgress::update(
				$id,
				array(
					'status'            => 'success',
					'message'           => $data['id'] . ' successfully synced.',
					'data'              => wp_json_encode( $data ),
					'medusa_admin_link' => null,
					'ended_at'          => time(),
				)
			);
		}

		return Models\SyncProgress::update(
			$id,
			array(
				'status'            => 'error',
				'message'           => 'Syncing ' . $data['id'] . ' failed.',
				'data'              => wp_json_encode(
					array(
						'errors' => $errors,
						'data'   => $data,
					)
				),
				'medusa_admin_link' => null,
				'ended_at'          => time(),
			)
		);
	}

	/*
	 * Get received totals for the given model.
	 *
	 * @param  string $model
	 * @return int
	 */
	public static function get_sync_progress_totals( $model ) {
		$sync_progress = Settings::get_sync_progress();

		if ( empty( $sync_progress ) || empty( $sync_progress['totals'] ) ) {
			return 0;
		}

		switch ( $model ) {
			case 'product':
				return $sync_progress['totals']['products'];
			case 'product-collection':
				return $sync_progress['totals']['collections'];
			case 'product-variant':
				return $sync_progress['totals']['product_variants'];
			case 'region':
				return $sync_progress['totals']['regions'];
			case 'thumbnail':
				return $sync_progress['totals']['thumbnails'];
			default:
				return 0;
		}
	}

	/*
	 * Check if all items for the given model are synced.
	 *
	 * @param  string  $model
	 * @param  integer $sync_timestamp
	 * @return bool
	 */
	public static function is_total_synced( string $model, int $sync_timestamp ) {
		if ( ! self::is_sync_in_progress( $sync_timestamp ) ) {
			return true;
		}

		$model_total  = self::get_sync_progress_totals( $model );
		$model_synced = Models\SyncProgress::count_synced( $model, $sync_timestamp );

		if ( $model_synced >= $model_total ) {
			return true;
		}

		return false;
	}

	/*
	 * Check if all items are synced.
	 *
	 * @param  integer $sync_timestamp
	 * @return bool
	 */
	public static function update_is_sync_finished( int $sync_timestamp ) {
		$sync_progress = Settings::get_sync_progress();

		if ( ! self::is_sync_in_progress( $sync_timestamp ) ) {
			return true;
		}

		if ( $sync_progress['type'] === 'bulk_sync' || $sync_progress['type'] === 'bulk_sync_and_import_thumbnails' ) {
			if ( ! self::is_total_synced( 'product', $sync_timestamp ) ) {
				return false;
			}

			if ( ! self::is_total_synced( 'product-collection', $sync_timestamp ) ) {
				return false;
			}

			if ( ! self::is_total_synced( 'product-variant', $sync_timestamp ) ) {
				return false;
			}

			if ( ! self::is_total_synced( 'region', $sync_timestamp ) ) {
				return false;
			}
		}

		if ( $sync_progress['type'] === 'bulk_sync_and_import_thumbnails' || $sync_progress['type'] === 'import_thumbnails' ) {
			if ( ! self::is_total_synced( 'thumbnail', $sync_timestamp ) ) {
				return false;
			}
		}

		$sync_progress['ended_at'] = time();

		Settings::save_sync_progress( $sync_progress );

		return true;
	}
}
