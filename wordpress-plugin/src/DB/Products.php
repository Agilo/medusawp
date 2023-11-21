<?php
/**
 * Table Products
 *
 * @package MedusaWP
 */

namespace MedusaWP\DB;

class Products extends Table {

	public static $table_name = MEDUSAWP_TABLE_PRODUCTS;

	public static $version = '1.1';

	/**
	 * Create custom table
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
			post_id BIGINT(20) NOT NULL UNIQUE,
			collection_id MEDIUMTEXT,
			title MEDIUMTEXT NOT NULL,
			subtitle MEDIUMTEXT,
			description MEDIUMTEXT,
			handle MEDIUMTEXT,
			is_giftcard BOOLEAN NOT NULL DEFAULT FALSE,
			thumbnail MEDIUMTEXT,
			shipping_profile_name MEDIUMTEXT,
			weight INT,
			length INT,
			height INT,
			hs_code MEDIUMTEXT,
			origin_country MEDIUMTEXT,
			mid_code MEDIUMTEXT,
			material MEDIUMTEXT,
			created_at DATETIME,
			updated_at DATETIME,
			deleted_at DATETIME,
			type_name MEDIUMTEXT,
			discountable BOOLEAN NOT NULL DEFAULT FALSE,
			external_id MEDIUMTEXT,
			status VARCHAR(50),
			synced_at INTEGER NOT NULL,
			sync_status MEDIUMTEXT,
			thumbnail_imported MEDIUMTEXT,
			PRIMARY KEY (id)
		) $charset_collate;";

		dbDelta( $sql );

		static::update_table_version_option();
	}
}
