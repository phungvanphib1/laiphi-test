<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'laiphi' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Dd3VEsndGuS1J6oxY4kNLNsrYI73qdQRnwy68riq71dettUPIfryCygF45AkfJ1W' );
define( 'SECURE_AUTH_KEY',  'BQWhpozcPbvc6FM7Blr4BOj6FEp4yJFDQLg0fVYmyUbMENnARDEOQs7ZGeisF7qj' );
define( 'LOGGED_IN_KEY',    'nvMeLrHUFXLPPWKUvbf6yBCAnL4BY3t4P6K71LuA1E2TkwADoVZeCaNbPNiRY8wV' );
define( 'NONCE_KEY',        'eFEnkrmgFHdeRRqBMfHeX1HEAi2SKWhF4RQBWqAatsqIfyr4ILg48YzrJE0CkjMp' );
define( 'AUTH_SALT',        '30Po70kcy8TsmmD30v4D4EB1syrZCsKOsGw66civVyMol3xdbTeWN4XZ8DLOk4gD' );
define( 'SECURE_AUTH_SALT', 'cLLbfXAr2LSelgqkteu7LVYM0jsutbyWoPseAj8bpASm2WgHXnl4u9VqenbGRL1M' );
define( 'LOGGED_IN_SALT',   'jymT19ZurKsoyWY1lxndkjLHHWFoTIHlVubRHtLJSgrLh685M2OGiQcw6HZMIJmi' );
define( 'NONCE_SALT',       'Cj230bsMbFp0nVUrUiAwMX6KenAM1tR7V58AahyZn69A3Oe0fK0byIBKpBwBH42A' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

//upload file
define('ALLOW_UNFILTERED_UPLOADS', true);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
