<?php

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */


/**
 * DB settings
 */
define('DB_NAME', $_SERVER["DB_NAME"]);
define('DB_USER', $_SERVER["DB_USER"]);
define('DB_PASSWORD', $_SERVER["DB_PASS"]);
define('DB_HOST', $_SERVER["DB_HOST"]);
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

/**
 * WordPress debugging mode
 */
if ($_SERVER['PHP_ENV'] == 'development') {
    define('WP_DEBUG', true);
    define('JETPACK_DEV_DEBUG', true);
    define('WP_DEBUG_LOG', true);
    define('WP_DEBUG_DISPLAY', false);
    define('SAVEQUERIES', true);
}

/**
 * Disable WP scripts concatenation
 */
define('CONCATENATE_SCRIPTS', false);

/**
 * File access method
 */
define('FS_METHOD', 'direct');

/**
 * Automatic updates
 */
define('AUTOMATIC_UPDATER_DISABLED', true);
// define('WP_AUTO_UPDATE_CORE', minor);

/**
 * WP Cron config
 * On production systems wp-cron should get triggered by an actual cron job
 */
if ($_SERVER['PHP_ENV'] == 'production') {
    define('DISABLE_WP_CRON', true);
} else {
    define('DISABLE_WP_CRON', false);
}

/**
 * Disable the WP file editor
 */
define('DISALLOW_FILE_EDIT', true);

/**
 * Set home_url and site_url defaults
 */
define('WP_HOME', 'http://' . $_SERVER['HTTP_HOST']);
define('WP_SITEURL', WP_HOME);

/**
 * Custom Content Directory
 */
define('WP_CONTENT_DIR', ABSPATH . '/wp-content');
define('WP_CONTENT_URL', WP_HOME . '/wp-content');

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

/**
 * HTTPS specific fixes
 */
// if (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
//   $_SERVER['HTTPS'] = 'on';
// }
// define('FORCE_SSL_ADMIN', true);

/**
 * Plugin licenses
 */
define('WPMDB_LICENCE', '4f8bac05-a3cc-49b6-ac59-0f8e208ae436');
define('AS3CFPRO_LICENCE', '66478932-749a-4773-92db-e9b8eefc76bb');
// define('ACF_PRO_LICENSE', 'xxxxx');
// define('GF_LICENSE_KEY', 'xxxxx');

/**
 * Configure JWT Authentication for WP REST API
 */
define('JWT_AUTH_SECRET_KEY', 'put your unique phrase here');
define('JWT_AUTH_CORS_ENABLE', true);

// define('MEDUSAWP_DEV', false);

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/* That's all, stop editing! Happy blogging. */

/**
 * Absolute path to the WordPress directory.
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

/**
 * Sets up WordPress vars and included files.
 */
require_once ABSPATH . 'wp-settings.php';
