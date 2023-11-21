<?php
/**
 * Ajax actions.
 *
 * @package MedusaWP
 */

namespace MedusaWP;

use MedusaWP\Models\Country;
use MedusaWP\Settings;

class AjaxActions {
	/**
	 * Region (country/currency) switcher.
	 *
	 * Changes region based on the given URL GET parameter / cookie 'country_code' and updates cart.
	 * If no country code is provided, it fallbacks to default country code.
	 * Finds region that contains country to which the provided country code belongs.
	 * Then updates cart region_id, uses `POST /store/carts/{id}`.
	 *
	 * @return void
	 */
	public function switch_country() {
		// get user-provided country code
		if ( ! empty( $_GET['country_code'] ) ) {
			$country_code = $_GET['country_code'];
		} elseif ( ! empty( $_COOKIE['country_code'] ) ) {
			$country_code = $_COOKIE['country_code'];
		} else {
			$country_code = Settings::get_default_country_code();
		}

		if ( ! $country_code ) {
			wp_send_json_error( array( 'error' => __( 'No country code provided.', 'medusawp' ) ) );
		}

		$cart_id = isset( $_COOKIE['cart_id'] ) ? $_COOKIE['cart_id'] : '';

		if ( ! $cart_id ) {
			wp_send_json_error( array( 'error' => __( 'Cart does not exist, first create the cart.', 'medusawp' ) ) );
		}

		// See if given country code is valid - it exists as country in DB
		$country = Country::find_by( 'iso_2', strtolower( $country_code ) );

		if ( ! $country ) {
			wp_send_json_error( array( 'error' => __( 'Country with given code does not exist in the database.', 'medusawp' ) ) );
		}

		// Find out region_id based on country
		$region_id = $country['region_id'];

		// Update cart (pass region_id to endpoint)
		$cart_data = array(
			'region_id'    => $region_id,
			'country_code' => strtolower( $country_code ),
		);

		$medusa_store = new MedusaStore();
		$response     = $medusa_store->update_a_cart( $cart_id, $cart_data );

		$cart = $response->getCart();

		$GLOBALS['medusawp_cart']         = $cart;
		$GLOBALS['medusawp_country_code'] = $cart_data['country_code'];
		$GLOBALS['medusawp_region']       = $cart->getRegion();

		setcookie( 'country_code', $GLOBALS['medusawp_country_code'], 0, '/' );

		wp_send_json_success( $cart );
	}
}
