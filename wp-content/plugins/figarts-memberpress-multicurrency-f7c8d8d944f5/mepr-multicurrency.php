<?php
/**
 * The plugin bootstrap file
 *
 * @link              http://example.com
 * @since             1.0
 * @package           MemberPress_MultiCurrency
 *
 * @wordpress-plugin
 * Plugin Name:       MemberPress MultiCurrency
 * Plugin URI:        https://pluginette.com
 * Description:       Adds multi-currency option to MemberPress
 * Version:           1.5.9
 * Author:            David Towoju
 * Author URI:        https://pluginette.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       memberpress-multicurrency
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Current plugin version.
define( 'MPMC_NAME', 'MemberPress Multicurrency' );

// Current plugin version.
define( 'MPMC_VERSION', '1.5.9' );

// Defines the minimum version of MemberPress required to run MPMULTICURRENCY.
define( 'MPMC_MIN_PARENT_PLUGIN_VERSION', '1.8.7' );

// this is the URL our updater / license checker pings. This should be the URL of the site with MPMULTICURRENCY installed.
define( 'MPMC_STORE_URL', 'https://pluginette.com' );

// the download ID. This is the ID of your product.
define( 'MPMC_ITEM_ID', 55 );

// the name of the settings page for the license input to be displayed.
define( 'MPMC_LICENSE_PAGE', 'mpmc-license' );

// the name of the settings page for the license input to be displayed.
define( 'MPMC_DIRPATH', plugin_dir_path( __FILE__ ) );

// the name of the settings page for the license input to be displayed.
define( 'MPMC_DIRURI', plugin_dir_url( __FILE__ ) );

define( 'MPMC_BASENAME', plugin_basename( __FILE__ ) );

// the name of the settings page for the license input to be displayed.
define( 'MPMC_DB_NAME', 'mpmc_subtxn_currencies' );

// After all plugins are loaded, load this add-on.
add_action( 'plugins_loaded', 'mepr_multicurrency_run', 1 );

/**
 * Begins execution of the plugin.
 *
 * @since    0.1
 */
function mepr_multicurrency_run() {
	// Make sure MemberPress is active
	if ( ! defined( 'MEPR_VERSION' ) ) {
		return;
	}

	// Is MemberPress 1.8.7 the plugin in use?
	if ( version_compare( MPMC_MIN_PARENT_PLUGIN_VERSION, MEPR_VERSION ) > 0 ) { // is 2nd is lower
		mpmc_show_admin_notice( 'parent_minimum' );
		return;
	}

	// Load language.
	load_plugin_textdomain( 'memberpress-multicurrency', false, MPMC_DIRPATH . '/i18n' );

	// if __autoload is active, put it on the spl_autoload stack!
	if ( is_array( spl_autoload_functions() ) && in_array( '__autoload', spl_autoload_functions() ) ) {
		spl_autoload_register( '__autoload' );
	}
	spl_autoload_register( 'mpmc_autoloader' );

	new MpmcAppCtrl();
	new MpmcCurrencyCtrl();
}


// Create or Update database upon addon activation.
function mpmc_after_plugin_activation() {
	require MPMC_DIRPATH . 'models/MpmcDb.php';
	$db = new MpmcDb();
	$db->upgrade();
}
register_activation_hook( __FILE__, 'mpmc_after_plugin_activation' );



/**
 * Autoload all the requisite classes
 *
 * @param  string $class_name
 *
 * @return mixed
 */
function mpmc_autoloader( $class_name ) {
	// Only load MemberPress classes here
	if ( preg_match( '/^Mpmc.+$/', $class_name ) ) {
		$filepath = '';
		$filename = $class_name . '.php';

		if ( preg_match( '/^.+Ctrl$/', $class_name ) ) {
			$filepath = MPMC_DIRPATH . 'controllers/' . $filename;
		} else {
			if ( file_exists( MPMC_DIRPATH . 'models/' . $filename ) ) {
				$filepath = MPMC_DIRPATH . 'models/' . $filename;
			} elseif ( file_exists( MPMC_DIRPATH . 'lib/' . $filename ) ) {
				$filepath = MPMC_DIRPATH . 'lib/' . $filename;
			}
		}
		if ( file_exists( $filepath ) ) {
			require_once $filepath;
		}
	}
}

/**
 * mpmc_show_admin_notice
 *
 * @param  string $notice
 * @return mixed
 */
function mpmc_show_admin_notice( $notice ) {
	switch ( $notice ) {
		case 'parent_minimum':
			$class   = 'notice notice-error';
			$message = __( 'MultiCurrency addon requires MemberPress 1.8.7 and above. Please update your MemberPress plugin.', 'memberpress-multicurrency' );

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			break;
		default:
			break;
	}
}
