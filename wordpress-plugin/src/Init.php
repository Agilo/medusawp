<?php

/**
 * Init
 *
 * @package MedusaWP
 */

namespace MedusaWP;

use MedusaWP\MedusaClient\Store\ApiException;
use MedusaWP\Models\Country;

class Init {

	/**
	 * Register custom post types.
	 *
	 * @return void
	 */
	public function register_post_types() {
		register_post_type(
			'medusa-product',
			array(
				'labels'              => array(
					'name'               => __( 'Products', 'medusawp' ),
					'singular_name'      => __( 'Product', 'medusawp' ),
					'add_new'            => __( 'Add New', 'medusawp' ),
					'add_new_item'       => __( 'Add New Product', 'medusawp' ),
					'edit_item'          => __( 'Edit Product', 'medusawp' ),
					'new_item'           => __( 'New Product', 'medusawp' ),
					'view_item'          => __( 'View Product', 'medusawp' ),
					'search_items'       => __( 'Search Products', 'medusawp' ),
					'not_found'          => __( 'No Products found', 'medusawp' ),
					'not_found_in_trash' => __( 'No Products found in Trash', 'medusawp' ),
				),
				'supports'            => array( 'title', 'editor', 'thumbnail' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => 'medusawp',
				'menu_icon'           => 'dashicons-products',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'rewrite'             => array(
					'slug'       => 'medusa-product',
					'with_front' => false,
				),
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
				'show_in_rest'        => true,
				'rest_base'           => 'medusa/product',
			)
		);

		register_post_type(
			'medusa-collection',
			array(
				'labels'              => array(
					'name'               => __( 'Collections', 'medusawp' ),
					'singular_name'      => __( 'Collection', 'medusawp' ),
					'add_new'            => __( 'Add New', 'medusawp' ),
					'add_new_item'       => __( 'Add New Collection', 'medusawp' ),
					'edit_item'          => __( 'Edit Collection', 'medusawp' ),
					'new_item'           => __( 'New Collection', 'medusawp' ),
					'view_item'          => __( 'View Collection', 'medusawp' ),
					'search_items'       => __( 'Search Collections', 'medusawp' ),
					'not_found'          => __( 'No Collections found', 'medusawp' ),
					'not_found_in_trash' => __( 'No Collections found in Trash', 'medusawp' ),
				),
				'supports'            => array( 'title', 'thumbnail', 'editor' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => 'medusawp',
				'menu_icon'           => 'dashicons-products',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'rewrite'             => array(
					'slug'       => 'medusa-collection',
					'with_front' => false,
				),
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
				'show_in_rest'        => true,
				'rest_base'           => 'medusa/product-collection',
			)
		);
	}

	/**
	 * Create a cart when customer visits the site.
	 */
	public function create_cart() {
		if ( is_admin() || defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) || defined( 'MS_FILES_REQUEST' ) || ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) || wp_doing_ajax() || wp_is_json_request() || is_login() ) {
			return;
		}

		$cart_id = isset( $_COOKIE['cart_id'] ) ? $_COOKIE['cart_id'] : '';

		// get user-provided country code
		if ( ! empty( $_GET['country_code'] ) ) {
			$country_code = $_GET['country_code'];
		} elseif ( ! empty( $_COOKIE['country_code'] ) ) {
			$country_code = $_COOKIE['country_code'];
		}

		// load cart from Medusa if one exists, otherwise create a new cart
		if ( $cart_id ) {
			$medusa_store = new MedusaStore();

			try {
				$response = $medusa_store->get_a_cart( $cart_id );
			} catch ( ApiException $e ) {
				// phpcs:ignore
				error_log( 'Medusa API Exception' );
				// phpcs:ignore
				error_log( $e->getMessage() );

				return;
			}

			$cart = $response->getCart();

			// expose cart data to global scope
			$GLOBALS['medusawp_cart']         = $cart;
			$GLOBALS['medusawp_country_code'] = $cart->getShippingAddress() ? $cart->getShippingAddress()->getCountryCode() : null;
			$GLOBALS['medusawp_region']       = $cart->getRegion();

			if ( ! isset( $_COOKIE['country_code'] ) ) {
				setcookie( 'country_code', $GLOBALS['medusawp_country_code'] );
			}

			// update cart if selected country_code has changed
			if ( ! empty( $country_code ) && $GLOBALS['medusawp_country_code'] !== $country_code ) {
				$country = Country::find_by( 'iso_2', strtolower( $country_code ) );

				if ( $country ) {
					$region_id = $country['region_id'];

					$cart_data = array(
						'region_id'    => $region_id,
						'country_code' => strtolower( $country_code ),
					);

					try {
						$response = $medusa_store->update_a_cart( $cart_id, $cart_data );
					} catch ( ApiException $e ) {
						// phpcs:ignore
						error_log( 'Medusa API Exception' );
						// phpcs:ignore
						error_log( $e->getMessage() );

						return;
					}

					$cart = $response->getCart();

					// expose cart data to global scope
					$GLOBALS['medusawp_cart']         = $cart;
					$GLOBALS['medusawp_country_code'] = $cart_data['country_code'];
					$GLOBALS['medusawp_region']       = $cart->getRegion();
				} else {
					// set cart details as it is
					$GLOBALS['medusawp_country_code'] = medusawp_get_current_country_code();
					$GLOBALS['medusawp_region']       = medusawp_get_current_region();
					setcookie( 'country_code', $GLOBALS['medusawp_country_code'] );
				}
			}
		} else {
			$cart_data            = array();
			$default_country_code = Settings::get_default_country_code();

			// check if user-provided country code is valid and exists
			if ( ! empty( $country_code ) && Country::is_valid_country_code( $country_code ) ) {
				$country = Country::find_by( 'iso_2', strtolower( $country_code ) );

				// set region and country code (user-provided)
				if ( $country && $country['region_id'] ) {
					$cart_data['region_id']    = $country['region_id'];
					$cart_data['country_code'] = strtolower( $country_code );
					setcookie( 'country_code', $cart_data['country_code'] );
				} else {
					// switch to default country code
					$country = Country::find_by( 'iso_2', strtolower( $default_country_code ) );

					// set region and country code (default)
					if ( $country && $country['region_id'] ) {
						$cart_data['region_id']    = $country['region_id'];
						$cart_data['country_code'] = $default_country_code;
						setcookie( 'country_code', $default_country_code );
					}
				}
			} else {
				// use default country code
				$country = Country::find_by( 'iso_2', strtolower( $default_country_code ) );

				// set region and country code (default)
				if ( $country && $country['region_id'] ) {
					$cart_data['region_id']    = $country['region_id'];
					$cart_data['country_code'] = $default_country_code;
					setcookie( 'country_code', $default_country_code );
				}
			}

			// create new cart
			$medusa_store = new MedusaStore();

			try {
				$response = $medusa_store->create_a_cart( $cart_data );
			} catch ( ApiException $e ) {
				// phpcs:ignore
				error_log( 'Medusa API Exception' );
				// phpcs:ignore
				error_log( $e->getMessage() );

				return;
			}

			// expose cart id and cart data to global scope
			$cart    = $response->getCart();
			$cart_id = $cart->getId();

			$GLOBALS['medusawp_cart']         = $cart;
			$GLOBALS['medusawp_country_code'] = $cart_data['country_code'];
			$GLOBALS['medusawp_region']       = $cart->getRegion();

			setcookie( 'cart_id', $cart_id );
		}
	}
}
