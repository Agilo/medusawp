<?php
/**
 * Product Variant
 *
 * @package MedusaWP
 */

namespace MedusaWP\Models;

use MedusaWP\Models\MoneyAmount;

class ProductVariant extends Model {

	public static $table_name = MEDUSAWP_TABLE_PRODUCT_VARIANTS;

	public static $primary_key = 'id';

	public static $columns_format = array(
		'id'                 => '%s',
		'product_id'         => '%s',
		'title'              => '%s',
		'sku'                => '%s',
		'barcode'            => '%s',
		'ean'                => '%s',
		'upc'                => '%s',
		'inventory_quantity' => '%d',
		'allow_backorder'    => '%d',
		'manage_inventory'   => '%d',
		'hs_code'            => '%s',
		'origin_country'     => '%s',
		'mid_code'           => '%s',
		'material'           => '%s',
		'width'              => '%d',
		'length'             => '%d',
		'height'             => '%d',
		'created_at'         => '%s',
		'updated_at'         => '%s',
		'deleted_at'         => '%s',
		'type_name'          => '%s',
		'metadata'           => '%s',
		'variant_rank'       => '%d',
		'synced_at'          => '%d',
		'sync_status'        => '%s',
	);

	public static $validation_rules = array(
		'id'                 => 'required',
		'product_id'         => 'required',
		'title'              => 'required',
		'inventory_quantity' => 'int',
		'allow_backorder'    => 'boolean',
		'manage_inventory'   => 'boolean',
		'created_at'         => 'date',
		'updated_at'         => 'nullable|date',
		'deleted_at'         => 'nullable|date',
		'synced_at'          => 'integer',
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
		$id      = $data['id'];
		$update  = static::exists( $id ) ? true : false;
		$prices  = isset( $data['prices'] ) ? $data['prices'] : array();

		// Foreach value in received data, check if we have its column in DB and add to $db_data;
		foreach ( $data as $key => $value ) {
			if ( isset( static::$columns_format[ $key ] ) ) {
				$db_data[ $key ] = $value;
			}
		}

		// Custom columns which values we don't receive from Medusa.
		$db_data['synced_at'] = time();

		// Save variant prices.
		static::save_prices( $prices );

		// Check if update or insert.
		if ( $update ) {
			return static::update( $id, $db_data );
		}

		return static::insert( $db_data );
	}

	/**
	 * Save product variant prices to MoneyAmount table.
	 *
	 * @param  array $prices
	 * @return bool
	 */
	public static function save_prices( $prices ) {
		if ( ! is_array( $prices ) || empty( $prices ) ) {
			return false;
		}

		$errors = array();

		foreach ( $prices as $price ) {
			MoneyAmount::save( $price );
			$errors = MoneyAmount::get_errors();
		}

		if ( ! empty( $errors ) ) {
			static::$errors['prices'] = $errors;
		}

		return true;
	}
}
