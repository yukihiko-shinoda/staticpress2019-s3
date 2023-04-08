<?php
/**
 * Class Magic_For_Test
 *
 * @package static_press_s3\tests\testlibraries
 */

namespace static_press_s3\tests\testlibraries;

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-infrastructure.php';

use static_press_s3\includes\Static_Press_S3_Infrastructure;

/**
 * Plugin switcher.
 */
class Magic_For_Test {
	/**
	 * Gets magic file path.
	 * 
	 * @return string File path.
	 */
	public static function get() {
		$magic_for_test = getenv( 'MAGIC_FOR_TEST' );
		return false === $magic_for_test ? Static_Press_S3_Infrastructure::get_magic_file() : $magic_for_test;
	}
}
