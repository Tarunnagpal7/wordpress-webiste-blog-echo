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
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpressdb' );

/** Database username */
define( 'DB_USER', 'echo' );

/** Database password */
define( 'DB_PASSWORD', 'w3blogwordpress' );

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
define( 'AUTH_KEY',         'g:#ugid/K!9,J_qJ}o*>]8[a:?z}d< .#NiBCAQdFCDLdw(&i=[lPJu}&OR3o~+>' );
define( 'SECURE_AUTH_KEY',  'Y^1NQb9%_0}8BJ3i>21R8EN.vZ=Xj/r0WEO[2vcPs3ywb6;fhXh@~|z8oL.4=Y|#' );
define( 'LOGGED_IN_KEY',    'hD!Zt$V(M-P&iTGTIHOKpUZsO$WznZY^b,6][yvJuYcx:s-ad[EFkt_8(_l&SwNu' );
define( 'NONCE_KEY',        'jYf>w8EiKPVhI0F7*<6~v13H:CIWgJMDuBu8hv_<{1AVj=;@@#n*?L&7gPtxhJSK' );
define( 'AUTH_SALT',        ',Gj?akRn80|vA&q<=>RRTjasi#RLP/vF`;oMAE}.#?3c<?D-`!zo%dkQU[it5.j-' );
define( 'SECURE_AUTH_SALT', ',waIe]k3%SHquHuG_e=Wss.6<H1?a2ua[*>7~f@(/&E<+:ba~ }=5&q+$ok;%wL2' );
define( 'LOGGED_IN_SALT',   '/65Dv]clL0~Y5)L|YKPZ!Aq,5g0(a_]lcRfkDhNhU~t#HJp~pO{?w%9Iwab&cQn%' );
define( 'NONCE_SALT',       'r|aCvy<uZp_|Lzy>;a0xksFPBX5E?96X3?zP1rVGASRB.cwH$WYl[9F,g2&Id5~:' );

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
