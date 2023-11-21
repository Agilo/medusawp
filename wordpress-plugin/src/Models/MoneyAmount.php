<?php
/**
 * Collection
 *
 * @package MedusaWP
 */

namespace MedusaWP\Models;

class MoneyAmount extends Model {

	public static $table_name = MEDUSAWP_TABLE_MONEY_AMOUNT;

	public static $primary_key = 'id';

	public static $columns_format = array(
		'id'            => '%s',
		'currency_code' => '%s',
		'amount'        => '%d',
		'variant_id'    => '%s',
		'region_id'     => '%s',
		'created_at'    => '%s',
		'updated_at'    => '%s',
		'deleted_at'    => '%s',
		'min_quantity'  => '%d',
		'max_quantity'  => '%d',
		'synced_at'     => '%d',
	);

	public static $validation_rules = array(
		'id'            => 'required',
		'currency_code' => 'required|string',
		'amount'        => 'required|integer',
		'created_at'    => 'date',
		'updated_at'    => 'nullable|date',
		'deleted_at'    => 'nullable|date',
		'min_quantity'  => 'nullable|integer',
		'max_quantity'  => 'nullable|integer',
		'synced_at'     => 'integer',
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

		$id      = $data['id'];
		$update  = static::exists( $id ) ? true : false;
		$db_data = array();

		// Foreach value in received data, check if we have its column in DB and add to $db_data;
		foreach ( $data as $key => $value ) {
			if ( isset( static::$columns_format[ $key ] ) ) {
				$db_data[ $key ] = $value;
			}
		}

		// Custom columns which values we don't receive from Medusa.
		$db_data['synced_at'] = time();

		if ( $update ) {
			return static::update( $id, $db_data );
		}

		return static::insert( $db_data );
	}
}
