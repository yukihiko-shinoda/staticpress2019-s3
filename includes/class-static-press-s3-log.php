<?php
/**
 * Class Static_Press_S3_Log
 *
 * @package static_press_s3\includes
 */

namespace static_press_s3\includes;

/**
 * S3 Log.
 */
class Static_Press_S3_Log {
	/**
	 * Logs.
	 * 
	 * @param string $message debug message.
	 */
	public static function log( $message ) {
		// Check if WP_DEBUG is enabled.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			// Set the log file location.
			$log_file = plugin_dir_path( __FILE__ ) . 'debug.log';
	
			// Add a timestamp to the message.
			$message = date( 'Y-m-d H:i:s' ) . ' - ' . $message;
	
			// Append the message to the log file.
			error_log( $message . PHP_EOL, 3, $log_file );
		}
	}
}
