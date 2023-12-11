<?php
/**
 * Collection
 *
 * @package MedusaWP
 */

namespace MedusaWP\Models;

class ProductCollection extends Model {

	public static $table_name = MEDUSAWP_TABLE_PRODUCT_COLLECTIONS;

	public static $primary_key = 'id';

	public static $cpt = 'medusa-collection';

	public static $columns_format = array(
		'id'          => '%s',
		'post_id'     => '%d',
		'title'       => '%s',
		'created_at'  => '%s',
		'updated_at'  => '%s',
		'deleted_at'  => '%s',
		'synced_at'   => '%d',
		'sync_status' => '%s',
	);

	public static $validation_rules = array(
		'id'         => 'required',
		'title'      => 'required|string',
		'created_at' => 'date',
		'updated_at' => 'nullable|date',
		'deleted_at' => 'nullable|date',
		'synced_at'  => 'integer',
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

		$validate = static::validate_data( $data );

		if ( ! $validate ) {
			return false;
		}

		$db_data     = array();
		$id          = $data['id'];
		$post_id     = 0;
		$post_status = $data['deleted_at'] ? 'trash' : 'publish';
		$update      = static::exists( $id ) ? true : false;

		if ( $update ) {
			$post_id     = static::get_column( 'post_id', $id );
			$post_status = get_post_status( $post_id );

			// If post status false, post doesn't exist.
			if ( ! $post_status ) {
				$post_id     = 0;
				$post_status = $data['deleted_at'] ? 'trash' : 'publish';
				// If post status is trash, but deleted_at is null, set post status to publish.
			} elseif ( $post_status === 'trash' && ! $data['deleted_at'] ) {
				$post_status = 'publish';
				// If post status is publish, but deleted_at is not null, set post status to trash.
			} elseif ( $post_status === 'publish' && $data['deleted_at'] ) {
				$post_status = 'trash';
			}
		}

		$post_args = array(
			'post_title'   => $data['title'],
			'post_name'    => $data['handle'],
			'post_content' => '',
			'post_status'  => $post_status,
			'post_type'    => static::$cpt,
			'post_date'    => $data['created_at'],
			'ID'           => $post_id,
		);

		// Update or create WP post.
		$post_insert = wp_insert_post( $post_args, true );

		if ( ! $post_insert || is_wp_error( $post_insert ) ) {
			static::$errors[] = __( 'Post could not be created.', 'medusawp' );

			return false;
		}

		// Foreach value in received data, check if we have its column in DB and add to $db_data;
		foreach ( $data as $key => $value ) {
			if ( isset( static::$columns_format[ $key ] ) ) {
				$db_data[ $key ] = $value;
			}
		}

		// Custom columns which values we don't receive from Medusa.
		$db_data['post_id']   = $post_insert;
		$db_data['synced_at'] = time();

		// Validate data.
		$validate = static::validate_data( $db_data );

		if ( $update ) {
			return static::update( $id, $db_data );
		}

		return static::insert( $db_data );
	}

	/**
	 * Get products from collection.
	 *
	 * @param  int $collection_post_id
	 * @return array
	 */
	public static function get_products( string $collection_id ) {
		return Product::find_all_by( 'collection_id', $collection_id );
	}

	/**
	 * Get products posts ids from collection.
	 *
	 * @param  int $collection_post_id
	 * @return array
	 */
	public static function get_products_post_ids( int $collection_post_id ) {
		global $wpdb;
		$products_table = MEDUSAWP_TABLE_PRODUCTS;
		$table_name     = static::$table_name;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT product.post_id FROM $products_table product INNER JOIN $table_name pcol ON pcol.id = product.collection_id WHERE pcol.post_id = %d;",
				array( $collection_post_id )
			)
		);
	}
}
