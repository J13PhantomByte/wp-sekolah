<?php
/**
 * Forminator Google sheet Client logger
 *
 * @package Forminator
 */

/**
 * In case you are wondering
 * this is the entry-point of autoload-ing Google library
 * Google/Logger/Abstract.php does include autoloder itself
 */
if ( ! class_exists( 'Forminator_Google_Logger_Abstract' ) ) {
	require_once __DIR__ . '/external/Google/Logger/Abstract.php';
}


/**
 * Class Forminator_Wp_Googlesheet_Client_Logger
 */
class Forminator_Wp_Googlesheet_Client_Logger extends Forminator_Google_Logger_Abstract {

	/**
	 * Writes a message to the current log implementation.
	 *
	 * @param string $message The message.
	 */
	protected function write( $message ) {
		forminator_addon_maybe_log( $message );
	}
}
