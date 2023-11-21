<?php
/**
 * Settings, saving to DB.
 *
 * @package MedusaWP
 */

namespace MedusaWP;

use MedusaWP\Models\Country;

class Settings {
	/**
	 * Get default ISO 2 character country code.
	 *
	 * @return string
	 */
	public static function get_default_country_code() {
		$option_value = get_option( 'medusawp_settings_default_country_code' );

		if ( $option_value === false ) {
			$countries = Country::all(
				array(
					'per_page' => 1,
				)
			);

			if ( ! empty( $countries ) ) {
				$option_value = $countries[0]['iso_2'];
			}
		}

		return apply_filters( 'medusawp_default_country_code', $option_value );
	}

	/**
	 * Save default country code.
	 *
	 * @param string $country_code A 2 character ISO code for the country.
	 * @return void
	 */
	public static function save_default_country_code( $country_code ) {
		update_option( 'medusawp_settings_default_country_code', strtolower( $country_code ) );
	}

	/**
	 * Get default always import thumbnails choice.
	 *
	 * @return boolean
	 */
	public static function get_default_always_import_thumbnails() {
		$option = get_option( 'medusawp_settings_default_always_import_thumbnails', 'false' );

		return $option === 'true' ? true : false;
	}

	/**
	 * Save default always import thumbnails choice.
	 *
	 * @param boolean $import_thumbnails
	 * @return void
	 */
	public static function save_default_always_import_thumbnails( $import_thumbnails ) {
		update_option( 'medusawp_settings_default_always_import_thumbnails', $import_thumbnails ? 'true' : 'false' );
	}

	/**
	 * Get Medusa URL.
	 *
	 * @return mixed
	 */
	public static function get_medusa_url() {
		return get_option( 'medusawp_settings_medusa_url' );
	}

	/**
	 * Get Medusa email.
	 *
	 * @return mixed
	 */
	public static function get_medusa_email() {
		return get_option( 'medusawp_settings_medusa_email' );
	}

	/**
	 * Save Medusa settings.
	 *
	 * @param  string $url
	 * @param  string $email
	 * @return void
	 */
	public static function save_medusa_settings( $url, $email ) {
		update_option( 'medusawp_settings_medusa_url', $url );
		update_option( 'medusawp_settings_medusa_email', $email );
	}

	/**
	 * Remove Medusa settings.
	 *
	 * @return void
	 */
	public static function remove_medusa_settings() {
		delete_option( 'medusawp_settings_medusa_url' );
		delete_option( 'medusawp_settings_medusa_email' );
	}

	/**
	 * Get sync response.
	 *
	 * @return mixed
	 */
	public static function get_sync_progress() {
		$value = get_option( 'medusawp_sync_progress' );

		if ( empty( $value ) ) {
			return null;
		}

		return json_decode( get_option( 'medusawp_sync_progress' ), true );
	}

	/**
	 * Save sync progress.
	 *
	 * @param array $arr
	 */
	public static function save_sync_progress( $arr ) {
		update_option( 'medusawp_sync_progress', wp_json_encode( $arr ) );
	}

	/**
	 * Get secret key.
	 *
	 * @return string|false
	 */
	public static function get_medusa_secret_key() {
		return get_option( 'medusawp_auth_secret' );
	}

	/**
	 * Save secret key.
	 *
	 * @param  mixed $value
	 * @return void
	 */
	public static function save_medusa_secret_key( $value ) {
		update_option( 'medusawp_auth_secret', $value );
	}

	/**
	 * Remove secret key.
	 *
	 * @return void
	 */
	public static function remove_medusa_secret_key() {
		delete_option( 'medusawp_auth_secret' );
	}

	/**
	 * Get API token.
	 *
	 * @return mixed
	 */
	public static function get_medusa_api_token() {
		return get_option( 'medusawp_auth_api_token' );
	}

	/**
	 * Save API token.
	 *
	 * @param  mixed $value
	 * @return void
	 */
	public static function save_medusa_api_token( $value ) {
		update_option( 'medusawp_auth_api_token', $value );
	}

	/**
	 * Remove API token.
	 *
	 * @return void
	 */
	public static function remove_medusa_api_token() {
		delete_option( 'medusawp_auth_api_token' );
	}
}
