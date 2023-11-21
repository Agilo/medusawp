<?php
/**
 * Table Collections
 *
 * @package MedusaWP
 */

namespace MedusaWP\DB;

class ProductCollections extends Table {

	public static $table_name = MEDUSAWP_TABLE_PRODUCT_COLLECTIONS;

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
			post_id BIGINT(20) NOT NULL,
			title MEDIUMTEXT NOT NULL,
			handle MEDIUMTEXT,
			created_at DATETIME,
			updated_at DATETIME,
			deleted_at DATETIME,
			metadata LONGTEXT,
			synced_at INTEGER NOT NULL,
			sync_status TEXT,
			PRIMARY KEY (id)
		) $charset_collate;";

		dbDelta( $sql );

		static::update_table_version_option();
	}
}
