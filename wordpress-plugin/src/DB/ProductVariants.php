<?php
/**
 * Table Product Variants
 *
 * @package MedusaWP
 */

namespace MedusaWP\DB;

class ProductVariants extends Table {

	public static $table_name = MEDUSAWP_TABLE_PRODUCT_VARIANTS;

	public static $version = '1.0';

	/**
	 * Create custom table.
	 *
	 * @return void
	 */
	public static function create_table() {
		if ( ! static::table_needs_upgrade() && static::table_exists() ) {
			return;
		}

		global $wpdb;

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$charset_collate = $wpdb->get_charset_collate();
		$sql             = 'CREATE TABLE ' . static::$table_name . " (
			id VARCHAR(191),
			product_id VARCHAR(191) NOT NULL,
			title MEDIUMTEXT NOT NULL,
			sku MEDIUMTEXT,
			barcode MEDIUMTEXT,
			ean MEDIUMTEXT,
			upc MEDIUMTEXT,
			inventory_quantity INT NOT NULL,
			allow_backorder BOOLEAN NOT NULL DEFAULT FALSE,
			manage_inventory BOOLEAN NOT NULL DEFAULT TRUE,
			hs_code MEDIUMTEXT,
			origin_country MEDIUMTEXT,
			mid_code MEDIUMTEXT,
			material MEDIUMTEXT,
			width INT,
			length INT,
			height INT,
			created_at DATETIME,
			updated_at DATETIME,
			deleted_at DATETIME,
			type_name MEDIUMTEXT,
			metadata LONGTEXT,
			variant_rank INT,
			synced_at INTEGER NOT NULL,
			sync_status TEXT,
			PRIMARY KEY (id)
		) $charset_collate;";

		dbDelta( $sql );

		static::update_table_version_option();
	}
}
