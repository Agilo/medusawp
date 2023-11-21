<?php
/**
 * Admin routes: Medusa connection.
 *
 * @package MedusaWP
 */

namespace MedusaWP\Routes\Admin;

use WP_REST_Response;
use MedusaWP\Settings;
use MedusaWP\MedusaClient\Admin\Api\DefaultApi;
use MedusaWP\MedusaClient\Admin\ApiException;
use MedusaWP\MedusaClient\Admin\Configuration;
use MedusaWP\MedusaClient\Admin\Model\MedusaWPConnectRequest;

class MedusaConnection {
	/**
	 * Namespace
	 *
	 * @var string
	 */
	public $namespace;

	public function __construct() {
		$this->namespace = MEDUSAWP_REST_ADMIN_ROUTE_NAMESPACE;
	}

	public function register_admin_routes() {
		register_rest_route(
			$this->namespace,
			'/connect',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'connect' ),
					'args'                => array(
						'url'      => array(
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_string( $param ) && ! empty( $param ) && filter_var( $param, FILTER_VALIDATE_URL );
							},
						),
						'email'    => array(
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_string( $param ) && ! empty( $param ) && filter_var( $param, FILTER_VALIDATE_EMAIL );
							},
						),
						'password' => array(
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_string( $param ) && ! empty( $param );
							},
						),
					),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' ); // Super Admin & Administrator
					},
				),
			),
		);

		register_rest_route(
			$this->namespace,
			'/disconnect',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'disconnect' ),
					'args'                => array(
						'force' => array(
							'type'              => 'boolean',
							'required'          => false,
							'default'           => false,
							'validate_callback' => function ( $param ) {
								return is_bool( $param );
							},
						),
					),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' ); // Super Admin & Administrator
					},
				),
			),
		);
	}

	/**
	 * Route callback: Connect.
	 *
	 * @param  \WP_REST_Request $req
	 * @return WP_REST_Response
	 */
	public function connect( $req ) {
		$params = $req->get_json_params();

		/**
		 * @var string
		 */
		$url = $params['url'];
		/**
		 * @var string
		 */
		$email = $params['email'];
		/**
		 * @var string
		 */
		$password = $params['password'];

		$api_configuration = new Configuration();
		$api_configuration->setHost( $url );

		$api = new DefaultApi( null, $api_configuration );

		$origin  = site_url();
		$request = new MedusaWPConnectRequest(
			array(
				'email'    => $email,
				'password' => $password,
			)
		);

		try {
			$response = $api->medusaWPConnect( $origin, $request );
		} catch ( ApiException $e ) {
			return new WP_REST_Response(
				array(
					'message' => $e->getMessage(),
				),
				$e->getCode()
			);
		}

		$api_token    = $response->getUser()->getApiToken();
		$secret_token = $response->getWordpress()->getSecret();

		// Store credentials
		Settings::save_medusa_settings( $url, $email );
		Settings::save_medusa_api_token( $api_token );
		Settings::save_medusa_secret_key( $secret_token );

		return new WP_REST_Response(
			null,
			200
		);
	}

	/**
	 * Route callback: Disconnect.
	 *
	 * @param  \WP_REST_Request $req
	 * @return WP_REST_Response
	 */
	public function disconnect( $req ) {
		$params = $req->get_json_params();

		/**
		 * @var bool
		 */
		$force = ! empty( $params ) && ! empty( $params['force'] ) && $params['force'];

		$url       = Settings::get_medusa_url();
		$api_token = Settings::get_medusa_api_token();
		$origin    = site_url();

		$api_configuration = new Configuration();
		$api_configuration->setHost( $url );
		$api_configuration->setApiKey( 'x-medusa-access-token', $api_token );

		$api = new DefaultApi( null, $api_configuration );

		try {
			$api->medusaWPDisconnect( $origin );
		} catch ( \Exception $e ) {
			if ( ! $force ) {
				return new WP_REST_Response(
					array(
						'medusa_disconnect_failed' => true,
						'message'                  => $e->getMessage(),
					),
					400
				);
			}
		}

		// Remove credentials
		Settings::remove_medusa_settings();
		Settings::remove_medusa_api_token();
		Settings::remove_medusa_secret_key();

		return new WP_REST_Response(
			null,
			200
		);
	}
}
