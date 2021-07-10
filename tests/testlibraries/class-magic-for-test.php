<?php
/**
 * Class Magic_For_Test
 *
 * @package static_press_s3\tests\testlibraries
 */

namespace static_press_s3\tests\testlibraries;

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
		return false === $magic_for_test ? '/usr/share/misc/magic' : $magic_for_test;
	}
}
