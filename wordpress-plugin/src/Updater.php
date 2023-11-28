<?php
/**
 * Fired during plugin activation
 *
 * @package    MedusaWP
 */

namespace MedusaWP;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Updater {
	public static function init( string $full_path ) {
		$update_checker = PucFactory::buildUpdateChecker(
			'https://github.com/Agilo/medusa-wp-wordpress-plugin',
			$full_path,
			'medusawp'
		);
	}
}
