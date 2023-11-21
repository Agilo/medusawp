<?php
/**
 * Table Money Amount (Prices)
 *
 * @package MedusaWP
 */

namespace MedusaWP\DB;

class MoneyAmount extends Table {

	public static $table_name = MEDUSAWP_TABLE_MONEY_AMOUNT;

	public static $version = '1.1';

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
			id VARCHAR(191) NOT NULL,
			currency_code MEDIUMTEXT NOT NULL,
			amount INT NOT NULL,
			variant_id VARCHAR(191),
			region_id VARCHAR(191),
			created_at DATETIME,
			updated_at DATETIME,
			deleted_at DATETIME,
			min_quantity INT,
			max_quantity INT,
			synced_at INTEGER NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		dbDelta( $sql );

		static::update_table_version_option();
	}
}
