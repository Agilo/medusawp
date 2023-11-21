<?php
/**
 * Country
 *
 * @package MedusaWP
 */

namespace MedusaWP\Models;

class Country extends Model {

	public static $table_name = MEDUSAWP_TABLE_COUNTRIES;

	public static $primary_key = 'id';

	public static $columns_format = array(
		'id'           => '%d',
		'iso_2'        => '%s',
		'iso_3'        => '%s',
		'num_code'     => '%d',
		'name'         => '%s',
		'display_name' => '%s',
		'region_id'    => '%s',
		'synced_at'    => '%d',
	);

	public static $validation_rules = array(
		'id'           => 'required',
		'iso_2'        => 'required',
		'iso_3'        => 'required',
		'num_code'     => 'int|required',
		'name'         => 'required',
		'display_name' => 'required',
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

		// Validate data.
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

	/**
	 * Check if given country code is valid - is a two letter string.
	 *
	 * @param string $country_code The country code.
	 * @return bool `true` if country code valid, `false` if not.
	 */
	public static function is_valid_country_code( $country_code ) {
		return is_string( $country_code ) ? (bool) preg_match( '/^[a-zA-Z]{2}$/', $country_code ) : false;
	}
}
