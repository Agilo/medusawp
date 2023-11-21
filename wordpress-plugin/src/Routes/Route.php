<?php
/**
 * Route abstract class
 *
 * @package MedusaWP
 */

namespace MedusaWP\Routes;

abstract class Route {
	/**
	 * Namespace
	 *
	 * @var string
	 */
	public $namespace;

	/**
	 * Route
	 *
	 * @var string
	 */
	public $route;

	/**
	 * Register route.
	 *
	 * @return void
	 */
	abstract public function register_route();

	/**
	 * Validate signature.
	 *
	 * @param  array $signature
	 * @return bool
	 */
	public function validate_request_signature( $signature ) {
		$secret_key = get_option( 'medusawp_auth_secret' );

		if ( ! $secret_key ) {
			return false;
		}

		if ( empty( $signature['timestamp'] ) ||
			empty( $signature['token'] ) ||
			empty( $signature['signature'] )
		) {
			return false;
		}

		if ( hash_hmac( 'sha256', $signature['timestamp'] . $signature['token'], $secret_key ) !== $signature['signature'] ) {
			return false;
		}

		return true;
	}
}
