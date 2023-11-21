<?php

use PHPUnit\Framework\TestCase;

final class UtilitiesTest extends TestCase {

	/**
	 * @group FormatVariantPrice
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L105C3-L117C5
	 */
	public function testGivenAVariantAndRegionItShouldReturnADecimalLocalizedAmountIncludingTaxesAndTheRegionsCurrencyCode(): void {
		$product_variant = array(
			'prices' => array(
				0 => array(
					'currency_code' => 'usd',
					'amount'        => 1000,
				),
			),
		);

		$region = array(
			'currency_code' => 'usd',
			'tax_rate'      => 15,
		);

		$price = medusawp_format_variant_price( $product_variant, $region );

		$this->assertEquals( $price, '$11.50' );
	}

	/**
	 * @group FormatVariantPrice
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L119C3-L132C5
	 */
	public function testGivenAVariantRegionAndACustomLocaleItShouldReturnADecimalLocalizedAmountIncludingTaxesAndTheRegionsCurrencyCode(): void {
		$product_variant = array(
			'prices' => array(
				0 => array(
					'currency_code' => 'usd',
					'amount'        => 1000,
				),
			),
		);

		$region = array(
			'currency_code' => 'usd',
			'tax_rate'      => 15,
		);

		$price = medusawp_format_variant_price( $product_variant, $region, true, null, null, 'fr_FR' );

		// UTF-8 non-breaking space character ("\xC2\xA0") is added by \NumberFormatter to its output
		$this->assertEquals( str_replace( "\xc2\xa0", ' ', $price ), '11,50 $US' );
	}

	/**
	 * @group ComputeVariantPrice
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L77C3-L86C5
	 */
	public function testItFindsTheVariantPriceAndReturnsADecimalAmountNotIncludingTaxes(): void {
		$product_variant = array(
			'prices' => array(
				0 => array(
					'currency_code' => 'usd',
					'amount'        => 1000,
				),
			),
		);

		$region = array(
			'currency_code' => 'usd',
			'tax_rate'      => 0,
		);

		$price = medusawp_compute_variant_price( $product_variant, $region, false );

		$this->assertEquals( $price, 10 );
	}

	/**
	 * @group ComputeVariantPrice
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L88C3-L101C5
	 */
	public function testItFindsTheVariantPriceAndReturnsADecimalAmountIncludingTaxes(): void {
		$product_variant = array(
			'prices' => array(
				0 => array(
					'currency_code' => 'usd',
					'amount'        => 1000,
				),
			),
		);

		$region = array(
			'currency_code' => 'usd',
			'tax_rate'      => 15,
		);

		$price = medusawp_compute_variant_price( $product_variant, $region );

		$this->assertEquals( $price, 11.5 );
	}

	/**
	 * @group GetVariantPrice
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L12C3-L21C5
	 */
	public function testItFindsTheVariantPriceAndReturnsItsAmount(): void {
		$product_variant = array(
			'prices' => array(
				0 => array(
					'currency_code' => 'usd',
					'amount'        => 1000,
				),
			),
		);

		$region = array(
			'currency_code' => 'usd',
		);

		$amount = medusawp_get_variant_price( $product_variant, $region );

		$this->assertEquals( $amount, 1000 );
	}

	/**
	 * @group GetVariantPrice
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L23C3-L31C5
	 */
	public function testWhenNoRegionIsProvidedThenItShouldReturn0(): void {
		$region = array(
			'currency_code' => 'usd',
		);

		$amount = medusawp_get_variant_price( null, $region );

		$this->assertEquals( $amount, 0 );
	}

	/**
	 * @group GetVariantPrice
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L33C3-L38C5
	 */
	public function testWhenNoProductVariantIsProvidedThenItShouldReturn0(): void {
		$product_variant = array(
			'prices' => array(
				0 => array(
					'currency_code' => 'usd',
					'amount'        => 1000,
				),
			),
		);

		$amount = medusawp_get_variant_price( $product_variant, null );

		$this->assertEquals( $amount, 0 );
	}

	/**
	 * @group GetVariantPrice
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L40C3-L45C5
	 */
	public function testWhenNoProductVariantAndRegionAreProvidedThenItShouldReturn0(): void {
		$amount = medusawp_get_variant_price( null, null );

		$this->assertEquals( $amount, 0 );
	}

	/**
	 * @group FormatAmount
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L136C3-L147C5
	 */
	public function testGivenAnAmountAndRegionItShouldReturnADecimalLocalizedAmountIncludingTaxesAndTheRegionsCurrencyCode(): void {
		$region = array(
			'currency_code' => 'usd',
			'tax_rate'      => 15,
		);

		$price = medusawp_format_amount( 3000, $region );

		$this->assertEquals( $price, '$34.50' );
	}

	/**
	 * @group FormatAmount
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L149C3-L153C5
	 */
	public function testGivenAnAmountAndNoRegionItShouldReturnADecimalLocalizedAmount(): void {
		$price = medusawp_format_amount( 3000, null );

		$this->assertEquals( $price, '30' );
	}

	/**
	 * @group ComputeAmount
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L48C3-L53C5
	 */
	public function testGivenAnAmountAndARegionItShouldReturnADecimalAmountNotIncludingTaxes(): void {
		$region = array(
			'currency_code' => 'usd',
			'tax_rate'      => 10,
		);

		$amount = medusawp_compute_amount( 3000, $region, false );

		$this->assertEquals( $amount, 30 );
	}

	/**
	 * @group ComputeAmount
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L55C3-L66C5
	 */
	public function testGivenAnAmountAndARegionItShouldReturnADecimalAmountIncludingTaxes(): void {
		$region = array(
			'currency_code' => 'usd',
			'tax_rate'      => 10,
		);

		$amount = medusawp_compute_amount( 3000, $region );

		$this->assertEquals( $amount, 33 );
	}

	/**
	 * @group ComputeAmount
	 *
	 * @link https://github.com/medusajs/medusa/blob/e1e7d8e5e4dff6b36ae27f7d4370bdc6217dcb2e/packages/medusa-react/test/utils/utils.test.ts#L68C3-L73C5
	 */
	public function testWhenNoRegionIsProvidedThenItShouldReturnTheDecimalAmount(): void {
		$amount = medusawp_compute_amount( 2000, null );

		$this->assertEquals( $amount, 20 );
	}
}
