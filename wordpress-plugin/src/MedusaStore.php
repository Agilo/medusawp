<?php

/**
 * Access to Medusa Store API endpoints.
 *
 * @package MedusaWP
 */

namespace MedusaWP;

use MedusaWP\Settings;
use MedusaWP\MedusaClient\Store\Api\CartsApi;
use MedusaWP\MedusaClient\Store\Configuration;
use MedusaWP\MedusaClient\Store\Model\StorePostCartReq;
use MedusaWP\MedusaClient\Store\Model\StorePostCartsCartReq;

class MedusaStore {

	/**
	 * Medusa url
	 *
	 * @var string
	 */
	private $url;

	/**
	 * API
	 *
	 * @var \MedusaWP\MedusaClient\Store\Api\CartsApi
	 */
	private $api;

	public function __construct() {
		$this->url = Settings::get_medusa_url();

		$config = new Configuration();
		$config->setHost( $this->url );
		$config->setUserAgent( 'MedusaWP/' . MEDUSAWP_VERSION . '/WordPress' );

		$this->api = new CartsApi( null, $config );
	}

	/**
	 * Create a Cart
	 *
	 * @param array $data Request body.
	 */
	public function create_a_cart( $data = array() ) {
		$body = array_merge(
			$data,
			array(
				'context' => array(
					'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null,
					'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null,
				),
			)
		);

		$request = new StorePostCartReq( $body );

		return $this->api->postCart( $request );
	}

	/**
	 * Update a Cart
	 *
	 * @param string $cart_id The id of the cart.
	 * @param string $body Request body.
	 */
	public function update_a_cart( $cart_id, $body ) {
		$request = new StorePostCartsCartReq( $body );

		return $this->api->postCartsCart( $cart_id, $request );
	}

	/**
	 * Get a Cart
	 *
	 * @param string $cart_id The id of the cart.
	 */
	public function get_a_cart( $cart_id ) {
		return $this->api->getCartsCart( $cart_id );
	}
}
