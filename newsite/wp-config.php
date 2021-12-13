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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'newsite' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
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
define( 'AUTH_KEY',         'L`g-u~-1RDv#kBkH{BE2-M{b%}Lw?RyVd[^2[2pO_R&<&$GoLu#Lj jTX&PBf}8a' );
define( 'SECURE_AUTH_KEY',  'b(YoS %_HU6,fyb<<0{@-=K.4vXN~$gL ]zhC qEvn+}Mv7TN$?K3c<]`#@_.?q0' );
define( 'LOGGED_IN_KEY',    'i(O$AfH%hf4FY6xvI9j,fLybdd#:0y7h(w_~ePuFq<)Duj@+M= .,oN-=3{ubkdy' );
define( 'NONCE_KEY',        '~?3D26g/:FG5VF7yKAoe$,nAHwJ]loJlsN wIy0,]`U#WbQ?$7CyI#0xga O]qmr' );
define( 'AUTH_SALT',        'UDx5J[S(B)wx0!s)5Zl]Z<&7j{g#s,vqKcizB;]O<;fjkO8uzD;gye>G}z:9ruC1' );
define( 'SECURE_AUTH_SALT', 'nA>n&J|)US@lQ;8J>=J8NI4r7zado8/CeWj4+V2k=^AL-|cQG/Tb !,,5ySKq$:7' );
define( 'LOGGED_IN_SALT',   's BOZm|ep(Nc`M,9WbU%,}zO.:%1ZuHlk]XF+jSVT83aG!oTLiTAIPy6#zs;ENXf' );
define( 'NONCE_SALT',       'epE(aS;yJVn2s4z^R}GCCd`AuzUC4{4w>X$Qi[AaIJ^sQj@-fu=VH7Spj7+W fWk' );

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



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
