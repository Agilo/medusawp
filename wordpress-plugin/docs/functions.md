# MedusaWP Functions  
This plugin offers a collection of PHP functions designed for seamless integration into WordPress themes.









| Name | Description |
|------|-------------|
|[medusawp_compute_amount](#medusawp_compute_amount)|Takes an amount, a region, and returns the amount as a decimal including or excluding taxes.|
|[medusawp_compute_variant_price](#medusawp_compute_variant_price)|Takes a product variant and region, and returns the variant price as a decimal number.|
|[medusawp_convert_to_decimal](#medusawp_convert_to_decimal)|Converts amount into decimal number.|
|[medusawp_convert_to_locale](#medusawp_convert_to_locale)|Converts amount into locale format using `intl` - the `NumberFormatter` class.|
|[medusawp_format_amount](#medusawp_format_amount)|Takes an amount and a region, and converts the amount to a localized decimal format.|
|[medusawp_format_variant_price](#medusawp_format_variant_price)|Takes a product variant and a region, and converts the variant's price to a localized decimal format.|
|[medusawp_get_cart](#medusawp_get_cart)|Get current cart object from Medusa.|
|[medusawp_get_collection](#medusawp_get_collection)|Get collection by its id.|
|[medusawp_get_collection_by_post_id](#medusawp_get_collection_by_post_id)|Get collection by its post id.|
|[medusawp_get_collection_products](#medusawp_get_collection_products)|Get products from collection.|
|[medusawp_get_collections](#medusawp_get_collections)|Get all collections.|
|[medusawp_get_countries](#medusawp_get_countries)|Get all countries.|
|[medusawp_get_country](#medusawp_get_country)|Get country by its id.|
|[medusawp_get_current_country](#medusawp_get_current_country)|Get current country.|
|[medusawp_get_current_country_code](#medusawp_get_current_country_code)|Get current cart country code.|
|[medusawp_get_current_currency](#medusawp_get_current_currency)|Get current cart currency.|
|[medusawp_get_current_region](#medusawp_get_current_region)|Get current cart region.|
|[medusawp_get_product](#medusawp_get_product)|Get product by its id.|
|[medusawp_get_product_by_post_id](#medusawp_get_product_by_post_id)|Get product by its post id.|
|[medusawp_get_product_collection](#medusawp_get_product_collection)|Get collection for given product id.|
|[medusawp_get_product_variants](#medusawp_get_product_variants)|Get variants for given product id.|
|[medusawp_get_products](#medusawp_get_products)|Get all products.|
|[medusawp_get_region](#medusawp_get_region)|Get region by its id.|
|[medusawp_get_regions](#medusawp_get_regions)|Get all regions.|
|[medusawp_get_tax_rate](#medusawp_get_tax_rate)|Takes a region and returns its tax rate.|
|[medusawp_get_variant](#medusawp_get_variant)|Get variant by its id.|
|[medusawp_get_variant_price](#medusawp_get_variant_price)|Finds the price amount corresponding to the region selected.|




### medusawp_compute_amount  

**Description**

```php
public medusawp_compute_amount (float $amount, array|null $region, bool $include_taxes)
```

Takes an amount, a region, and returns the amount as a decimal including or excluding taxes. 

 

**Parameters**

* `(float) $amount`
: The number that should be used for computation.  
* `(array|null) $region`
: The region.  
* `(bool) $include_taxes`
: Optional. Whether to include taxes or not. Default `true`.  

**Return Values**

`float`

> The amount without formatting.


<hr />


### medusawp_compute_variant_price  

**Description**

```php
public medusawp_compute_variant_price (array|null $variant, array|null $region, bool $include_taxes)
```

Takes a product variant and region, and returns the variant price as a decimal number. 

 

**Parameters**

* `(array|null) $variant`
: The product variant.  
* `(array|null) $region`
: The region.  
* `(bool) $include_taxes`
: Optional. Whether to include taxes or not. Default `true`.  

**Return Values**

`float`

> The price's amount without formatting.


<hr />


### medusawp_convert_to_decimal  

**Description**

```php
public medusawp_convert_to_decimal (float $amount, array|null $region)
```

Converts amount into decimal number. 

 

**Parameters**

* `(float) $amount`
: The amount.  
* `(array|null) $region`
: The region.  

**Return Values**

`float`

> The amount as decimal number.


<hr />


### medusawp_convert_to_locale  

**Description**

```php
public medusawp_convert_to_locale (float $amount, string $currency_code, null|int $minimum_fraction_digits, null|int $maximum_fraction_digits, null|string $locale)
```

Converts amount into locale format using `intl` - the `NumberFormatter` class. 

 

**Parameters**

* `(float) $amount`
: The amount.  
* `(string) $currency_code`
: The currency code.  
* `(null|int) $minimum_fraction_digits`
: Optional. The minimum number of fraction digits to use when formatting the price.  
* `(null|int) $maximum_fraction_digits`
: Optional. The maximum number of fraction digits to use when formatting the price.  
* `(null|string) $locale`
: Optional. Locale in which the number would be formatted. Default 'en_US'.  

**Return Values**

`string|false`

> The localized amount.


<hr />


### medusawp_format_amount  

**Description**

```php
public medusawp_format_amount (float $amount, array|null $region, bool $include_taxes, null|int $minimum_fraction_digits, null|int $maximum_fraction_digits, null|string $locale)
```

Takes an amount and a region, and converts the amount to a localized decimal format. 

 

**Parameters**

* `(float) $amount`
: The number that should be used for computation.  
* `(array|null) $region`
: The region.  
* `(bool) $include_taxes`
: Optional. Whether to include taxes or not. Default `true`.  
* `(null|int) $minimum_fraction_digits`
: Optional. The minimum number of fraction digits to use when formatting the price.  
* `(null|int) $maximum_fraction_digits`
: Optional. The maximum number of fraction digits to use when formatting the price.  
* `(null|string) $locale`
: Optional. Locale in which the number would be formatted. Default 'en_US'.  

**Return Values**

`string|false`

> The formatted amount.


<hr />


### medusawp_format_variant_price  

**Description**

```php
public medusawp_format_variant_price (array|null $variant, array|null $region, bool $include_taxes, null|int $minimum_fraction_digits, null|int $maximum_fraction_digits, null|string $locale)
```

Takes a product variant and a region, and converts the variant's price to a localized decimal format. 

 

**Parameters**

* `(array|null) $variant`
: The product variant.  
* `(array|null) $region`
: The region.  
* `(bool) $include_taxes`
: Optional. Whether the computed price should include taxes or not. Default `true`.  
* `(null|int) $minimum_fraction_digits`
: Optional. The minimum number of fraction digits to use when formatting the price.  
* `(null|int) $maximum_fraction_digits`
: Optional. The maximum number of fraction digits to use when formatting the price.  
* `(null|string) $locale`
: Optional. Locale in which the number would be formatted. Default 'en_US'.  

**Return Values**

`string|false`

> The formatted price's amount.


<hr />


### medusawp_get_cart  

**Description**

```php
public medusawp_get_cart ()
```

Get current cart object from Medusa. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\MedusaWP\MedusaClient\Store\Model\Cart|null`

> Cart details or `null`.


<hr />


### medusawp_get_collection  

**Description**

```php
public medusawp_get_collection (string $id)
```

Get collection by its id. 

 

**Parameters**

* `(string) $id`

**Return Values**

`array|null|void`

> Database query result.


<hr />


### medusawp_get_collection_by_post_id  

**Description**

```php
public medusawp_get_collection_by_post_id (int $post_id)
```

Get collection by its post id. 

 

**Parameters**

* `(int) $post_id`

**Return Values**

`array|null|void`

> Database query result.


<hr />


### medusawp_get_collection_products  

**Description**

```php
public medusawp_get_collection_products (string $collection_id)
```

Get products from collection. 

 

**Parameters**

* `(string) $collection_id`

**Return Values**

`array`




<hr />


### medusawp_get_collections  

**Description**

```php
public medusawp_get_collections ()
```

Get all collections. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array|null`

> Database query results.


<hr />


### medusawp_get_countries  

**Description**

```php
public medusawp_get_countries ()
```

Get all countries. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array|null`

> Array of countries.


<hr />


### medusawp_get_country  

**Description**

```php
public medusawp_get_country (string $id)
```

Get country by its id. 

 

**Parameters**

* `(string) $id`

**Return Values**

`array|null|void`

> Database query result.


<hr />


### medusawp_get_current_country  

**Description**

```php
public medusawp_get_current_country ()
```

Get current country. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`object|null`

> Country object or `null`.


<hr />


### medusawp_get_current_country_code  

**Description**

```php
public medusawp_get_current_country_code ()
```

Get current cart country code. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string|null`

> Country code.


<hr />


### medusawp_get_current_currency  

**Description**

```php
public medusawp_get_current_currency ()
```

Get current cart currency. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`string|null`

> Currency code.


<hr />


### medusawp_get_current_region  

**Description**

```php
public medusawp_get_current_region ()
```

Get current cart region. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\MedusaWP\MedusaClient\Store\Model\Region|null`

> Region object or `null`.


<hr />


### medusawp_get_product  

**Description**

```php
public medusawp_get_product (string $id)
```

Get product by its id. 

 

**Parameters**

* `(string) $id`

**Return Values**

`array|null|void`

> Database query result.


<hr />


### medusawp_get_product_by_post_id  

**Description**

```php
public medusawp_get_product_by_post_id (int $post_id)
```

Get product by its post id. 

 

**Parameters**

* `(int) $post_id`

**Return Values**

`array|null|void`

> Database query result.


<hr />


### medusawp_get_product_collection  

**Description**

```php
public medusawp_get_product_collection (string $id)
```

Get collection for given product id. 

 

**Parameters**

* `(string) $id`

**Return Values**

`array|null|void`

> Database query result.


<hr />


### medusawp_get_product_variants  

**Description**

```php
public medusawp_get_product_variants (string $product_id)
```

Get variants for given product id. 

 

**Parameters**

* `(string) $product_id`

**Return Values**

`array|null`

> Array of variants.


<hr />


### medusawp_get_products  

**Description**

```php
public medusawp_get_products ()
```

Get all products. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array|null`

> Database query results.


<hr />


### medusawp_get_region  

**Description**

```php
public medusawp_get_region (string $id)
```

Get region by its id. 

 

**Parameters**

* `(string) $id`

**Return Values**

`array|null|void`

> Database query result.


<hr />


### medusawp_get_regions  

**Description**

```php
public medusawp_get_regions ()
```

Get all regions. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array|null`

> Database query results.


<hr />


### medusawp_get_tax_rate  

**Description**

```php
public medusawp_get_tax_rate (array|null $region)
```

Takes a region and returns its tax rate. 

 

**Parameters**

* `(array|null) $region`
: The region.  

**Return Values**

`float`

> The corresponding tax rate.


<hr />


### medusawp_get_variant  

**Description**

```php
public medusawp_get_variant (string $id)
```

Get variant by its id. 

 

**Parameters**

* `(string) $id`

**Return Values**

`array|null|void`

> Database query result.


<hr />


### medusawp_get_variant_price  

**Description**

```php
public medusawp_get_variant_price (array|null $variant, array|null $region)
```

Finds the price amount corresponding to the region selected. 

 

**Parameters**

* `(array|null) $variant`
: The product variant.  
* `(array|null) $region`
: The region.  

**Return Values**

`float`

> The price's amount.


<hr />

