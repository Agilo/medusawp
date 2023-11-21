<?php
namespace MedusaWP\DB;

abstract class Table {
	/**
	 * Table name
	 *
	 * @var string
	 */
	public static $table_name;

	/**
	 * Table version
	 *
	 * @var string
	 */
	public static $version;

	/**
	 * Create custom table.
	 *
	 * @return void
	 */
	abstract public static function create_table();

	/**
	 * Check if table exists.
	 *
	 * @return bool
	 */
	public static function table_exists() {
		global $wpdb;
		$table_name = static::$table_name;

		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;
	}

	/**
	 * Check if table needs to be upgraded.
	 *
	 * @return int
	 */
	public static function table_needs_upgrade() {
		$version_option = get_option( static::$table_name . '_db_version' );

		return version_compare( static::$version, $version_option );
	}

	/**
	 * Update version in the option.
	 *
	 * @return void
	 */
	public static function update_table_version_option() {
		update_option( static::$table_name . '_db_version', static::$version );
	}

	public static function delete( $where, $values ) {
		global $wpdb;
		$table_name = static::$table_name;

		return $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE $where", array_merge( $values ) ) );
	}
}
