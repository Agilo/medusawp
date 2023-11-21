<?php
/**
 * Admin routes: Plugin settings.
 *
 * @package MedusaWP
 */

namespace MedusaWP\Routes\Admin;

use MedusaWP\Models\Country;
use WP_REST_Response;
use MedusaWP\Settings;

class PluginSettings {
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

	public function __construct() {
		$this->namespace = MEDUSAWP_REST_ADMIN_ROUTE_NAMESPACE;
		$this->route     = 'settings';
	}

	public function register_admin_routes() {
		register_rest_route(
			$this->namespace,
			$this->route,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' ); // Super Admin & Administrator
					},
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => function () {
						return current_user_can( 'manage_options' ); // Super Admin & Administrator
					},
					'args'                => array(
						'default_country'          => array(
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_string( $param ) && ! empty( $param );
							},
						),
						'always_import_thumbnails' => array(
							'type'              => 'boolean',
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_bool( $param );
							},
						),
					),
				),
				'schema' => function () {
					return array(
						'$schema'              => 'http://json-schema.org/draft-04/schema#',
						'title'                => 'MedusaWPSettings',
						'type'                 => 'object',
						'additionalProperties' => false,
						'properties'           => array(
							'default_country'          => array(
								'type'        => 'string',
								'description' => __( 'Default country code.', 'medusawp' ),
								'required'    => true,
							),
							'always_import_thumbnails' => array(
								'type'        => 'boolean',
								'description' => __( 'Always import thumbnails.', 'medusawp' ),
								'required'    => true,
							),
						),
					);
				},
			),
		);
	}

	/**
	 * Route callback: Get settings.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings() {
		$default_country = Settings::get_default_country_code();

		return new WP_REST_Response(
			array(
				'default_country'          => empty( $default_country ) ? '' : $default_country,
				'always_import_thumbnails' => Settings::get_default_always_import_thumbnails(),
			)
		);
	}

	/**
	 * Route callback: Update settings.
	 *
	 * @param  \WP_REST_Request $req
	 * @return WP_REST_Response
	 */
	public function update_settings( $req ) {
		$params  = $req->get_json_params();
		$country = Country::find_by( 'iso_2', strtolower( $params['default_country'] ) );

		if ( ! $country ) {
			return new WP_REST_Response(
				array(
					'message' => __( 'Country with given code does not exist in the database.', 'medusawp' ),
				),
				400
			);
		} else {
			Settings::save_default_country_code( $params['default_country'] );
		}

		if ( isset( $params['always_import_thumbnails'] ) ) {
			Settings::save_default_always_import_thumbnails( $params['always_import_thumbnails'] );
		}

		$default_country = Settings::get_default_country_code();

		return new WP_REST_Response(
			array(
				'default_country'          => empty( $default_country ) ? '' : $default_country,
				'always_import_thumbnails' => Settings::get_default_always_import_thumbnails(),
			)
		);
	}
}
