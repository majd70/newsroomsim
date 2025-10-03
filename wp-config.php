<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'i8928288_b9rc1' );

/** Database username */
define( 'DB_USER', 'i8928288_b9rc1' );

/** Database password */
define( 'DB_PASSWORD', 'T.DNUnWjHz7xRTzlDkA45' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define('AUTH_KEY',         'HW1G9OoIX6Tju1lbzSlP7md8MRd6Gx1N0PSpNqTjYjjzo6W5Lo9vj4Ria23Nw9NT');
define('SECURE_AUTH_KEY',  'Tkk69ByQhfVJ5m6meOVLNcaqV0CbUIRdJHnHLBy19XdvZ0u6sCVpxCc6L40gQL5g');
define('LOGGED_IN_KEY',    'p08kcejfv8X1xv90oOeWUoRR7b2Z20xrO0rUaVqWCpSxY6mk3AUuilnCPvZ3HWrq');
define('NONCE_KEY',        'ka900MhBt1MijxyRnf9jaqIK7uRaYCVpQY3feI4BqtssO8v8L7iS1V7RGOK1J9Iw');
define('AUTH_SALT',        'WRr8YUMVr346BVCEoWJqA61HjvtypsTwrqANtWXRYtnkwOCYBlnRm3zPp7Ze4GNl');
define('SECURE_AUTH_SALT', 'iVpth4y0JyDykzAXr7QxHJ8M3TuDqlhSruu1KwoANZhSIcWUMy8gxVxxwK6z7ZnY');
define('LOGGED_IN_SALT',   'SZ4tLyVyYDgPWvGDBCjCBuasyte1RHYMLMNb4FWFUIWil7CGlN3BAW587EFvftYY');
define('NONCE_SALT',       'wvDLQwCh4BD6D4o55ve1HV1Z7LJlU4ik9HXEdz0UdhXrnzqbK8aUqjaeTeGRNUrA');

/**
 * Other customizations.
 */
define('WP_TEMP_DIR',dirname(__FILE__).'/wp-content/uploads');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'jp6v_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
