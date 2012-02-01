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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'hitfigure');

/** MySQL database username */
define('DB_USER', 'hfadmin');

/** MySQL database password */
define('DB_PASSWORD', 'j1gAleeK');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'K*.%c8-a6eLuBfc}HlW$YkZfT0iG#tgmP+Ub/|8;AFrQVK)rr:ybY:P4L6`e]`x~');
define('SECURE_AUTH_KEY',  'oIDYL(})OJg,IYIfz(g%%D(N(hZwaTcx@VW3bA~F8-K&7eCqDQsA_rG+95vi}8H7');
define('LOGGED_IN_KEY',    'gqBHMbHJ|0t:i+WqBksP08H--CL{i7TTW;4>Z(9]7K#pOuQ~RH3j!YF |0v49x.@');
define('NONCE_KEY',        'q]B8tPm+hs|?LzT2u+0?!g0+-l#ElMMqT@[4GaX6F^|m:F.G-h||+Z f7`fR5g]p');
define('AUTH_SALT',        'vl9Ic nlu;tn%9TE~r_pqzX-eEE+F)r8K9-W4>qblk6t_(bFy(/V2p`1}Z4Yo7y5');
define('SECURE_AUTH_SALT', ']NSGMY#qevs>d/AK5E]^?F|!ZNlFaf{8)D*7^9]-8S*#j95%XD!Y}A1D;T8/&G9Q');
define('LOGGED_IN_SALT',   '`Z2[gQd+}IJIKuhwjO42-bg XCao].>f>mZ^N+,!*fV1ON+)4 FBctAsaR2~QCZ+');
define('NONCE_SALT',       '?]&n~:7}^C8Sir*:+xNV95YO6M|gb*,#hPU||5Nc2+x#Tvt)VJ9weJfxK_drr&}F');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
