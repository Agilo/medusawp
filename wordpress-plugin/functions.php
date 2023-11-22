<?php
/**
 * Get all collections.
 *
 * @return array|null Database query results.
 */
function medusawp_get_collections() {
	return MedusaWP\Models\ProductCollection::all();
}

/**
 * Get all countries.
 *
 * @return array|null Array of countries.
 */
function medusawp_get_countries() {
	return MedusaWP\Models\Country::all();
}

/**
 * Get all products.
 *
 * @return array|null Database query results.
 */
function medusawp_get_products() {
	return MedusaWP\Models\Product::all();
}

/**
 * Get all regions.
 *
 * @return array|null Database query results.
 */
function medusawp_get_regions() {
	return MedusaWP\Models\Region::all();
}

/**
 * Get collection by its id.
 *
 * @param  string $id
 * @return array|null|void Database query result.
 */
function medusawp_get_collection( string $id ) {
	return MedusaWP\Models\ProductCollection::find( $id );
}

/**
 * Get collection by its post id.
 *
 * @param  int $post_id
 * @return array|null|void Database query result.
 */
function medusawp_get_collection_by_post_id( int $post_id ) {
	return MedusaWP\Models\ProductCollection::find_by( 'post_id', $post_id );
}

/**
 * Get country by its id.
 *
 * @param  string $id
 * @return array|null|void Database query result.
 */
function medusawp_get_country( string $id ) {
	return MedusaWP\Models\Country::find( $id );
}

/**
 * Get product by its id.
 *
 * @param  string $id
 * @return array|null|void Database query result.
 */
function medusawp_get_product( string $id ) {
	return MedusaWP\Models\Product::find( $id );
}

/**
 * Get product by its post id.
 *
 * @param  int $post_id
 * @return array|null|void Database query result.
 */
function medusawp_get_product_by_post_id( int $post_id ) {
	return MedusaWP\Models\Product::find_by( 'post_id', $post_id );
}

/**
 * Get products from collection.
 *
 * @param  string $collection_id
 * @return array
 */
function medusawp_get_collection_products( string $collection_id ) {
	return MedusaWP\Models\ProductCollection::get_products( $collection_id );
}

/**
 * Get region by its id.
 *
 * @param  string $id
 * @return array|null|void Database query result.
 */
function medusawp_get_region( string $id ) {
	return MedusaWP\Models\Region::find( $id );
}

/**
 * Get variant by its id.
 *
 * @param  string $id
 * @return array|null|void Database query result.
 */
function medusawp_get_variant( string $id ) {
	return MedusaWP\Models\ProductVariant::find( $id );
}

/**
 * Get variants for given product id.
 *
 * @param  string $product_id
 * @return array|null Array of variants.
 */
function medusawp_get_product_variants( string $product_id ) {
	return MedusaWP\Models\Product::get_variants( $product_id );
}

/**
 * Get collection for given product id.
 *
 * @param  string $id
 * @return array|null|void Database query result.
 */
function medusawp_get_product_collection( string $product_id ) {
	$collection_id = MedusaWP\Models\Product::get_column( 'collection_id', $product_id );
	$collection    = MedusaWP\Models\ProductCollection::find( $collection_id );

	return $collection;
}

/**
 * Get current cart object from Medusa.
 *
 * @return \MedusaWP\MedusaClient\Store\Model\Cart|null Cart details or `null`.
 */
function medusawp_get_cart() {
	global $medusawp_cart;

	return isset( $medusawp_cart ) ? $medusawp_cart : null;
}

/**
 * Get current country.
 *
 * @return object|null Country object or `null`.
 */
function medusawp_get_current_country() {
	$country_code = medusawp_get_current_country_code();

	if ( ! empty( $country_code ) ) {
		return MedusaWP\Models\Country::find_by( 'iso_2', strtolower( $country_code ) );
	}

	return null;
}

/**
 * Get current cart country code.
 *
 * @return string|null Country code.
 */
function medusawp_get_current_country_code() {
	global $medusawp_country_code;

	return isset( $medusawp_country_code ) ? $medusawp_country_code : null;
}

/**
 * Get current cart currency.
 *
 * @return string|null Currency code.
 */
function medusawp_get_current_currency() {
	$cart = medusawp_get_cart();

	if ( ! empty( $cart ) ) {
		$region = $cart->getRegion();

		if ( $region ) {
			return $region->getCurrencyCode();
		}
	}

	return null;
}

/**
 * Get current cart region.
 *
 * @return \MedusaWP\MedusaClient\Store\Model\Region|null Region object or `null`.
 */
function medusawp_get_current_region() {
	global $medusawp_region;

	return isset( $medusawp_region ) ? $medusawp_region : null;
}

/**
 * Takes a product variant and a region, and converts the variant's price to a localized decimal format.
 *
 * @link https://docs.medusajs.com/medusa-react/overview#formatvariantprice
 * @link https://github.com/medusajs/medusa/blob/6ee80794c905bfa0f439a4168e3d3abd3ce36336/packages/medusa-react/src/helpers/index.ts#L16C1-L29C2
 *
 * @param array|null $variant The product variant.
 * @param array|null $region The region.
 * @param bool $include_taxes Optional. Whether the computed price should include taxes or not. Default `true`.
 * @param null|int $minimum_fraction_digits Optional. The minimum number of fraction digits to use when formatting the price.
 * @param null|int $maximum_fraction_digits Optional. The maximum number of fraction digits to use when formatting the price.
 * @param null|string $locale Optional. Locale in which the number would be formatted. Default 'en_US'.
 * @return string|false The formatted price's amount.
 */
function medusawp_format_variant_price(
	?array $variant,
	?array $region,
	bool $include_taxes = true,
	?int $minimum_fraction_digits = null,
	?int $maximum_fraction_digits = null,
	?string $locale = null
) {
	$amount = medusawp_compute_variant_price( $variant, $region, $include_taxes );

	$currency_code = isset( $region['currency_code'] ) ? $region['currency_code'] : '';

	return medusawp_convert_to_locale(
		$amount,
		$currency_code,
		$minimum_fraction_digits,
		$maximum_fraction_digits,
		$locale
	);
}

/**
 * Takes a product variant and region, and returns the variant price as a decimal number.
 *
 * @link https://docs.medusajs.com/medusa-react/overview#computevariantprice
 * @link https://github.com/medusajs/medusa/blob/6ee80794c905bfa0f439a4168e3d3abd3ce36336/packages/medusa-react/src/helpers/index.ts#L43C1-L55C2
 *
 * @param array|null $variant The product variant.
 * @param array|null $region The region.
 * @param bool $include_taxes Optional. Whether to include taxes or not. Default `true`.
 * @return float The price's amount without formatting.
 */
function medusawp_compute_variant_price( ?array $variant, ?array $region, bool $include_taxes = true ): float {
	$amount = medusawp_get_variant_price( $variant, $region );

	return medusawp_compute_amount( $amount, $region, $include_taxes );
}

/**
 * Finds the price amount corresponding to the region selected.
 *
 * @link https://github.com/medusajs/medusa/blob/6ee80794c905bfa0f439a4168e3d3abd3ce36336/packages/medusa-react/src/helpers/index.ts#L63C1-L73C2
 *
 * @param array|null $variant The product variant.
 * @param array|null $region The region.
 * @return float The price's amount.
 */
function medusawp_get_variant_price( ?array $variant, ?array $region ): float {
	$prices = isset( $variant['prices'] ) ? $variant['prices'] : array();

	$prices_regions = is_array( $prices ) ? array_column( $prices, 'currency_code' ) : array();

	$price = is_array( $prices_regions ) && isset( $region['currency_code'] )
		? array_search( $region['currency_code'], $prices_regions, true )
		: null;

	return $prices[ $price ]['amount'] ?? 0;
}

/**
 * Takes an amount and a region, and converts the amount to a localized decimal format.
 *
 * @link https://docs.medusajs.com/medusa-react/overview#formatamount
 * @link https://github.com/medusajs/medusa/blob/6ee80794c905bfa0f439a4168e3d3abd3ce36336/packages/medusa-react/src/helpers/index.ts#L110C1-L126C2
 *
 * @param float $amount The number that should be used for computation.
 * @param array|null $region The region.
 * @param bool $include_taxes Optional. Whether to include taxes or not. Default `true`.
 * @param null|int $minimum_fraction_digits Optional. The minimum number of fraction digits to use when formatting the price.
 * @param null|int $maximum_fraction_digits Optional. The maximum number of fraction digits to use when formatting the price.
 * @param null|string $locale Optional. Locale in which the number would be formatted. Default 'en_US'.
 * @return string|false The formatted amount.
 */
function medusawp_format_amount(
	float $amount,
	?array $region,
	bool $include_taxes = true,
	?int $minimum_fraction_digits = null,
	?int $maximum_fraction_digits = null,
	?string $locale = null
) {
	$tax_aware_amount = medusawp_compute_amount( $amount, $region, $include_taxes );

	$currency_code = isset( $region['currency_code'] ) ? $region['currency_code'] : '';

	return medusawp_convert_to_locale(
		$tax_aware_amount,
		$currency_code,
		$minimum_fraction_digits,
		$maximum_fraction_digits,
		$locale
	);
}

/**
 * Takes an amount, a region, and returns the amount as a decimal including or excluding taxes.
 *
 * @link https://docs.medusajs.com/medusa-react/overview#computeamount
 * @link https://github.com/medusajs/medusa/blob/6ee80794c905bfa0f439a4168e3d3abd3ce36336/packages/medusa-react/src/helpers/index.ts#L84C1-L96C2
 *
 * @param float $amount The number that should be used for computation.
 * @param array|null $region The region.
 * @param bool $include_taxes Optional. Whether to include taxes or not. Default `true`.
 * @return float The amount without formatting.
 */
function medusawp_compute_amount( float $amount, ?array $region, bool $include_taxes = true ): float {
	$to_decimal = medusawp_convert_to_decimal( $amount, $region );

	$tax_rate = $include_taxes ? medusawp_get_tax_rate( $region ) : 0;

	$amount_with_taxes = $to_decimal * ( 1 + $tax_rate );

	return $amount_with_taxes;
}

/**
 * Converts amount into decimal number.
 *
 * @link https://github.com/medusajs/medusa/blob/6ee80794c905bfa0f439a4168e3d3abd3ce36336/packages/medusa-react/src/helpers/index.ts#L131C1-L139C2
 *
 * @param float $amount The amount.
 * @param array|null $region The region.
 * @return float The amount as decimal number.
 */
function medusawp_convert_to_decimal( float $amount, ?array $region ): float {
	$no_division_currencies = array( 'krw', 'jpy', 'vnd' );
	$currency               = isset( $region['currency_code'] ) ? $region['currency_code'] : null;
	$currency               = is_string( $currency ) ? strtolower( $currency ) : null;

	$divisor = in_array( ( $currency ), $no_division_currencies, true ) ? 1 : 100;

	return floor( $amount ) / $divisor;
}

/**
 * Takes a region and returns its tax rate.
 *
 * @link https://github.com/medusajs/medusa/blob/6ee80794c905bfa0f439a4168e3d3abd3ce36336/packages/medusa-react/src/helpers/index.ts#L141C1-L143C2
 *
 * @param array|null $region The region.
 * @return float The corresponding tax rate.
 */
function medusawp_get_tax_rate( ?array $region ): float {
	return $region && ! empty( $region ) && isset( $region['tax_rate'] ) ? $region['tax_rate'] / 100 : 0;
}

/**
 * Converts amount into locale format using `intl` - the `NumberFormatter` class.
 *
 * @link https://github.com/medusajs/medusa/blob/6ee80794c905bfa0f439a4168e3d3abd3ce36336/packages/medusa-react/src/helpers/index.ts#L145C1-L160C2
 *
 * @param float $amount The amount.
 * @param string $currency_code The currency code.
 * @param null|int $minimum_fraction_digits Optional. The minimum number of fraction digits to use when formatting the price.
 * @param null|int $maximum_fraction_digits Optional. The maximum number of fraction digits to use when formatting the price.
 * @param null|string $locale Optional. Locale in which the number would be formatted. Default 'en_US'.
 * @return string|false The localized amount.
 */
function medusawp_convert_to_locale(
	float $amount,
	string $currency_code,
	?int $minimum_fraction_digits = null,
	?int $maximum_fraction_digits = null,
	?string $locale = null
) {
	$locale = $locale ?? 'en_US';

	if ( $currency_code && ! empty( $currency_code ) ) {
		$formatter = new \NumberFormatter( $locale, \NumberFormatter::CURRENCY );

		if ( $minimum_fraction_digits ) {
			$formatter->setAttribute( \NumberFormatter::MIN_FRACTION_DIGITS, $minimum_fraction_digits );
		}

		if ( $maximum_fraction_digits ) {
			$formatter->setAttribute( \NumberFormatter::MAX_FRACTION_DIGITS, $maximum_fraction_digits );
		}

		return $formatter->formatCurrency( $amount, $currency_code );
	}

	return strval( $amount );
}
