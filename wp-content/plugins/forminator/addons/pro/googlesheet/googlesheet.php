<?php
/**
 * Integration Name: Google Sheets
 * Version: 1.1
 * Plugin URI:  https://wpmudev.com/
 * Description: Integrate Forminator Custom Forms and Polls with Google Sheets to get notified in real time.
 * Author: WPMU DEV
 * Author URI: http://wpmudev.com
 *
 * @package Forminator
 */

define( 'FORMINATOR_ADDON_GOOGLESHEET_VERSION', '1.1' );

/**
 * Forminator addon Google sheet directory path
 *
 * @return string
 */
function forminator_addon_googlesheet_dir() {
	return trailingslashit( __DIR__ );
}

/**
 * Forminator Google sheet API client autoload
 *
 * @param string $class_name Class name.
 * @return void
 */
function forminator_addon_googlesheet_google_api_client_autoload( $class_name ) {
	$class_path = explode( '_', $class_name );
	if ( 'Google' !== $class_path[0] ) {
		return;
	}
	// Drop 'Google', and maximum class file path depth in this project is 3.
	$google_api_client_path = __DIR__ . '/lib/Google';
	$class_path             = array_slice( $class_path, 1, 2 );
	$file_path              = $google_api_client_path . '/' . implode( '/', $class_path ) . '.php';

	if ( file_exists( $file_path ) ) {
		// @noinspection PhpIncludeInspection.
		require_once $file_path;
	}
}

// only enable autoload when needed to avoid further conflicts.
// spl_autoload_register( 'forminator_addon_googlesheet_google_api_client_autoload' );.

require_once __DIR__ . '/lib/class-forminator-addon-wp-googlesheet-client-logger.php';

Forminator_Integration_Loader::get_instance()->register( 'googlesheet' );
