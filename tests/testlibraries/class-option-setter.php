<?php
/**
 * Class Mock_Creator
 *
 * @package static_press_s3\tests\testlibraries
 */

namespace static_press_s3\tests\testlibraries;

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-admin.php';
use static_press_s3\includes\Static_Press_S3_Admin;

/**
 * Mock creator.
 */
class Option_Setter {
	public static function set_up_option() {
		/**
		 * Set up option.
		 */
		update_option(
			Static_Press_S3_Admin::OPTION_KEY,
			array(
				'access_key' => 'accessKey',
				'secret_key' => 'secretKey',
				'region'     => 'region',
				'endpoint'   => 'endpoint',
				'bucket'     => 'bucket',
			)
		);
	}
}