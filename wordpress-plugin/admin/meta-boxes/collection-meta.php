<?php

use MedusaWP\Models\ProductCollection;
use MedusaWP\Utils;

$collection_meta = medusawp_get_collection_by_post_id( get_the_ID() );

if ( empty( $collection_meta ) ) {
	return;
}

$output_format = ProductCollection::get_columns_format();
?>

<div class="medusawp-meta">
	<?php foreach ( $collection_meta as $property => $value ) : ?>
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
		</span>
	</div>
	<?php endforeach; ?>
</div>
