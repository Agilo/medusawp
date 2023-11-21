<?php

/**
 * Region
 *
 * @package MedusaWP
 */

namespace MedusaWP\Models;

class Region extends Model {

	public static $table_name = MEDUSAWP_TABLE_REGIONS;

	public static $primary_key = 'id';

	public static $columns_format = array(
		'id'                 => '%s',
		'name'               => '%s',
		'currency_code'      => '%s',
		'tax_rate'           => '%d',
		'tax_code'           => '%s',
		'created_at'         => '%s', // datetime
		'updated_at'         => '%s', // datetime
		'deleted_at'         => '%s', // datetime
		'metadata'           => '%s',
		'gift_cards_taxable' => '%d', // bool,tinyint(1)
		'automatic_taxes'    => '%d', // bool,tinyint(1)
		'includes_tax'       => '%d', // bool,tinyint(1)
		'tax_provider_id'    => '%s',
		'sync_status'        => '%s',
		'synced_at'          => '%d',
	);

	public static $validation_rules = array(
		'id'                 => 'required',
		'name'               => 'required',
		'currency_code'      => 'required',
		'tax_rate'           => 'int|required',
		'created_at'         => 'date',
		'updated_at'         => 'nullable|date',
		'deleted_at'         => 'nullable|date',
		'gift_cards_taxable' => 'boolean',
		'automatic_taxes'    => 'boolean',
		'includes_tax'       => 'boolean',
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

		$id        = $data['id'];
		$update    = static::exists( $id ) ? true : false;
		$db_data   = array();
		$countries = isset( $data['countries'] ) ? $data['countries'] : array();

		// Foreach value in received data, check if we have its column in DB and add to $db_data;
		foreach ( $data as $key => $value ) {
			if ( isset( static::$columns_format[ $key ] ) ) {
				$db_data[ $key ] = $value;
			}
		}

		// Custom columns which values we don't receive from Medusa.
		$db_data['synced_at'] = time();

		// Save countries.
		static::save_countries( $countries );

		if ( $update ) {
			return static::update( $id, $db_data );
		}

		return static::insert( $db_data );
	}

	/**
	 * Save countries to Countries table.
	 *
	 * @param  array $countries
	 * @return bool
	 */
	public static function save_countries( $countries ) {
		if ( ! is_array( $countries ) || empty( $countries ) ) {
			return false;
		}

		$errors = array();

		foreach ( $countries as $item ) {
			Country::save( $item );
			$errors = Country::get_errors();
		}

		if ( ! empty( $errors ) ) {
			static::$errors['countries'] = $errors;
		}

		return true;
	}
}
