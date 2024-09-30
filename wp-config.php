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
define( 'DB_NAME', 'sekolahku' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'c3&*la?XjLp]>O]7_kg-47gZwLE^Top{ZzA#7sxzHG={k*oZ6SUND{mPP`k_N~g?' );
define( 'SECURE_AUTH_KEY',  'N)QPeTnG:pwnu|6es)8c3jUwJ/Vp-=U_L]as#.R.GO`^#G]taZ6Ud#g&UH8Z(Wwk' );
define( 'LOGGED_IN_KEY',    '3>-dlF+dU2},aV<otc[v6k/i75H66z_<<;r;l: 6tR6-_&k |P_HHROhB>VA5;sp' );
define( 'NONCE_KEY',        '_BA448{uP{CB(SI-,>Ig>J3@26>*#v3qS9bCz&Qjko5{3)_kG|%*!3BH0@o,}$CX' );
define( 'AUTH_SALT',        'Z+Eg!/+6eZ9!E?>?wLk9?ACzNW{*jD?_3TBG T?.V^n4E4Euzjaa7p E_JAJRkcz' );
define( 'SECURE_AUTH_SALT', '$?[CdvL1-=k;|(P5@TR7(%W`-HOlee`cP>Dku_g,IhYkC%Y`wiAc0Tx6R]AxNe?~' );
define( 'LOGGED_IN_SALT',   'l#AzL;+sW}M 6qa3wQ%>r*~}9wCFw0{kEbXcr8wcB]?W-odor8xMCN{9 &C}v?03' );
define( 'NONCE_SALT',       ')nV#*&z@gf_p`+$d853>*^|v,MXn}$B74yRQQG<qTBptT@EiwmNOkq2z`+BNI2~u' );

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
