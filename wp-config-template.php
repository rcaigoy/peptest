<?php
/**
 * The base configuration for WordPress (SAMPLE)
 * 
 * Copy this file to "wp-config.php" and fill in the values.
 * You can also create a .env file for sensitive data.
 */

// ** Database settings ** //
define( 'DB_NAME', 'defaultdb' );
define( 'DB_USER', 'localuser' );
define( 'DB_PASSWORD', 'guest' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// ** WordPress URLs ** //
define('WP_HOME', 'http://peptest.local');
define('WP_SITEURL', 'http://peptest.local');

// ** Memory and Performance ** //
define('WP_MEMORY_LIMIT', '1024M');
define('WP_MAX_MEMORY_LIMIT', '2048M');
define('FS_METHOD', 'direct');

// ** Caching ** //
define( 'WP_CACHE', false ); // Set to true when ready for caching

// ** Authentication Unique Keys and Salts ** //
// Generate these at: https://api.wordpress.org/secret-key/1.1/salt/
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

// ** Database table prefix ** //
$table_prefix = 'wp_';

// ** Debugging ** //
define( 'WP_DEBUG', true);
define( 'WP_DEBUG_LOG', true);
define( 'WP_DEBUG_DISPLAY', false); // Hide errors on page
define( 'SCRIPT_DEBUG', true);
define( 'WP_ENVIRONMENT_TYPE', 'local');

// Enable error logging
@ini_set('display_errors', 0);
@ini_set('error_log', __DIR__ . '/wp-content/debug.log');

// ** WordPress absolute path ** //
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';