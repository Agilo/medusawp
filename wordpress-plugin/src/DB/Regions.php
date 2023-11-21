<?php
/**
 * Table Regions
 *
 * @package MedusaWP
 */

namespace MedusaWP\DB;

class Regions extends Table {

	public static $table_name = MEDUSAWP_TABLE_REGIONS;

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
			id VARCHAR(191) NOT NULL,
			name MEDIUMTEXT NOT NULL,
			currency_code MEDIUMTEXT NOT NULL,
			tax_rate SMALLINT NOT NULL,
			tax_code MEDIUMTEXT,
			created_at DATETIME,
			updated_at DATETIME,
			deleted_at DATETIME,
			metadata LONGTEXT,
			gift_cards_taxable BOOLEAN NOT NULL DEFAULT TRUE,
			automatic_taxes BOOLEAN NOT NULL DEFAULT TRUE,
			includes_tax BOOLEAN NOT NULL DEFAULT FALSE,
			tax_provider_id MEDIUMTEXT,
			synced_at INTEGER NOT NULL,
			sync_status TEXT,
			PRIMARY KEY (id)
		) $charset_collate;";

		dbDelta( $sql );

		static::update_table_version_option();
	}
}
