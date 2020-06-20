<?php
/**
 * Class Path_Creator
 *
 * @package static_press_s3\tests\testlibraries
 */

namespace static_press_s3\tests\testlibraries;

/**
 * Plugin switcher.
 */
class Path_Creator {
	/**
	 * Creates file path.
	 * 
	 * @param string $file_name File name.
	 * @return string File path.
	 */
	public static function create_file_path( $file_name ) {
		return STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testresources/' . $file_name;
	}
}
