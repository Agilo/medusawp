<?php
/**
 * Table Sync Progress
 *
 * @package MedusaWP
 */

namespace MedusaWP\DB;

class SyncProgress extends Table {

	public static $table_name = MEDUSAWP_TABLE_SYNC_PROGRESS;

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
            id INT NOT NULL AUTO_INCREMENT,
            model TEXT NOT NULL,
            status MEDIUMTEXT NOT NULL,
            message TEXT NOT NULL,
            data MEDIUMTEXT NOT NULL,
            medusa_admin_link MEDIUMTEXT,
            sync_timestamp INTEGER,
            started_at INTEGER NOT NULL,
            ended_at INTEGER,
            PRIMARY KEY (id)
        ) $charset_collate;";

		dbDelta( $sql );

		static::update_table_version_option();
	}
}
