<?php
/**
 * Table Countries
 *
 * @package MedusaWP
 */

namespace MedusaWP\DB;

class Countries extends Table {

	public static $table_name = MEDUSAWP_TABLE_COUNTRIES;

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
			id INT NOT NULL,
			iso_2 MEDIUMTEXT NOT NULL,
			iso_3 MEDIUMTEXT NOT NULL,
			num_code INT NOT NULL,
			name MEDIUMTEXT NOT NULL,
			display_name MEDIUMTEXT NOT NULL,
			region_id VARCHAR(191),
			synced_at INTEGER NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		dbDelta( $sql );

		static::update_table_version_option();
	}
}
