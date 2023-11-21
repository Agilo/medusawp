<?php
	$regions = medusawp_get_regions();
	$columns = array(
		'id'                 => 'ID',
		'name'               => 'Name',
		'currency_code'      => 'Currency Code',
		'tax_rate'           => 'Tax Rate',
		'tax_code'           => 'Tax Code',
		'gift_cards_taxable' => 'Gift Cards Taxable',
		'automatic_taxes'    => 'Automatic Taxes',
		'includes_tax'       => 'Includes Tax',
		'created_at'         => 'Created At',
		'updated_at'         => 'Updated At',
		'deleted_at'         => 'Deleted At',
		'synced_at'          => 'Synced At',
		'sync_status'        => 'Sync Status',
	);
	?>

<h1><?php esc_html_e( 'MedusaWP > Regions', 'medusawp' ); ?></h1>
<table class="widefat fixed">
	<thead>
		<tr>
			<?php foreach ( $columns as $column ) : ?>
				<th class="manage-column column-columnname"><?php echo esc_html( $column ); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $regions as $region ) : ?>
		<tr>
			<?php foreach ( $columns as $property => $value ) : ?>
				<td class="column-columnname"><?php echo esc_html( $region[ $property ] ); ?></td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
