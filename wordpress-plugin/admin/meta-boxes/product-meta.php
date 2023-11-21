<?php

use MedusaWP\Models\Product;
use MedusaWP\Models\ProductVariant;
use MedusaWP\Utils;

$product_meta = Product::find_by( 'post_id', get_the_ID() );
$product_id   = $product_meta['id'];

if ( empty( $product_meta ) ) {
	return;
}

$output_format    = Product::get_columns_format();
$product_variants = medusawp_get_product_variants( $product_id );
$collection       = medusawp_get_product_collection( $product_id );

$variants_output_format = ProductVariant::get_columns_format();
?>

<div class="medusawp-product-thumbnail">
	<?php if ( $product_meta['thumbnail'] ) : ?>
		<img src="<?php echo esc_url( $product_meta['thumbnail'] ); ?>" />
	<?php endif; ?>
</div>

<div class="medusawp-meta">
	<?php foreach ( $product_meta as $property => $value ) : ?>
		<?php
		if ( ! empty( $output_format[ $property ] ) ) {
			$value_format = Utils::format_meta_field( $value, $output_format[ $property ] );
		} else {
			$value_format = Utils::format_meta_field( $value );
		}
		?>
	<div class="medusawp-meta-row">
		<span><?php echo esc_html( $property ); ?>: </span>
		<span class="medusawp-meta-value medusawp-meta-value--<?php echo esc_attr( $value_format['format'] ); ?>">
			<?php echo esc_html( $value_format['value'] ); ?>
			
			<?php if ( $property === 'collection_id' && ! empty( $collection ) ) : ?>
				(<?php echo esc_html( $collection['title'] ); ?>)
			<?php endif; ?>
		</span>
	</div>
	<?php endforeach; ?>

	<?php if ( is_array( $product_variants ) && ! empty( $product_variants ) ) : ?>
	<div class="medusawp-meta-row">
		<details>
			<summary><?php esc_html_e( 'variants', 'medusawp' ); ?> (<?php echo count( $product_variants ); ?>): </summary>
			<?php foreach ( $product_variants as $variant ) : ?>
				<details class="pl-1">
					<summary><?php echo esc_html( $variant['title'] ); ?></summary>
					<?php foreach ( $variant as $property => $value ) : ?>
						<?php
						if ( ! empty( $output_format[ $property ] ) ) {
							$value_format = Utils::format_meta_field( $value, $variants_output_format[ $property ] );
						} else {
							$value_format = Utils::format_meta_field( $value );
						}
						?>
						<div class="medusawp-meta-row pl-1">
							<span><?php echo esc_html( $property ); ?>: </span>
							<span class="medusawp-meta-value medusawp-meta-value--<?php echo esc_attr( $value_format['format'] ); ?>">
								<?php echo esc_html( $value_format['value'] ); ?>
							</span>
						</div>
						<?php endforeach; ?>
				</details>
			<?php endforeach; ?>
		</details>
	</div>
	<?php endif; ?>
</div>
