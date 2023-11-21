<?php
/**
 * Product
 *
 * @package MedusaWP
 */

namespace MedusaWP\Models;

class Product extends Model {

	public static $table_name = MEDUSAWP_TABLE_PRODUCTS;

	public static $primary_key = 'id';

	public static $cpt = 'medusa-product';

	public static $columns_format = array(
		'id'                    => '%s',
		'post_id'               => '%d',
		'collection_id'         => '%s',
		'title'                 => '%s',
		'subtitle'              => '%s',
		'description'           => '%s',
		'handle'                => '%s',
		'is_giftcard'           => '%d', // bool,tinyint(1)
		'thumbnail'             => '%s',
		'shipping_profile_name' => '%s',
		'weight'                => '%d',
		'length'                => '%d',
		'height'                => '%d',
		'hs_code'               => '%s',
		'origin_country'        => '%s',
		'mid_code'              => '%s',
		'material'              => '%s',
		'created_at'            => '%s', // datetime
		'updated_at'            => '%s', // datetime
		'deleted_at'            => '%s', // datetime
		'type_name'             => '%s',
		'discountable'          => '%d', // bool,tinyint(1)
		'external_id'           => '%s',
		'status'                => '%s',
		'synced_at'             => '%d',
		'sync_status'           => '%s',
		'thumbnail_imported'    => '%s',
	);

	public static $validation_rules = array(
		'id'           => 'required',
		'title'        => 'string|required',
		'handle'       => 'string',
		'is_giftcard'  => 'boolean',
		'thumbnail'    => 'nullable|url',
		'created_at'   => 'date',
		'updated_at'   => 'nullable|date',
		'deleted_at'   => 'nullable|date',
		'discountable' => 'boolean',
		'synced_at'    => 'integer',
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
			'post_status'  => $post_status,
			'post_content' => $data['description'],
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

		// Foreach value in received data, check if we have its column in DB and add to $db_data.
		foreach ( $data as $key => $value ) {
			if ( isset( static::$columns_format[ $key ] ) ) {
				$db_data[ $key ] = $value;
			}
		}

		// Custom columns which values we don't receive from Medusa.
		$db_data['post_id']   = $post_insert;
		$db_data['synced_at'] = time();

		if ( $update ) {
			return static::update( $id, $db_data );
		}

		return static::insert( $db_data );
	}

	/**
	 * Get product variants.
	 *
	 * @param  string $product_id
	 * @return array Database query results.
	 */
	public static function get_variants( string $product_id ) {
		return \MedusaWP\Models\ProductVariant::find_all_by( 'product_id', $product_id );
	}

	/**
	 * Count all products that have thumbnail.
	 *
	 * @return int
	 */
	public static function count_all_thumbnails() {
		global $wpdb;
		$table_name = static::$table_name;

		return intval( $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE thumbnail != ''" ) );
	}
}
