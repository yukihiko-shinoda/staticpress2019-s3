<?php
/**
 * Class Static_Press_S3_Admin_Test
 *
 * @package static_press\tests\includes
 */

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-admin.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-path-creator.php';
use static_press_s3\tests\testlibraries\Path_Creator;
/**
 * Static_Press_S3_Admin test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Admin_Test extends \WP_UnitTestCase {
	/**
	 * Test steps for init_s3().
	 */
	public function test_init_s3_empty_options() {
		$this->expectOutputRegex( $this->create_regex( '', '' ) );
		$admin = new Static_Press_S3_Admin();
		$admin->options_page();
	}

	/**
	 * Test steps for init_s3().
	 */
	public function test_init_s3() {
		update_option(
			Static_Press_S3_Admin::OPTION_KEY,
			array(
				'access_key' => 'accessKey',
				'secret_key' => 'secretKey',
				'region'     => 'region',
				'bucket'     => 'bucket',
			)
		);
		$this->expectOutputRegex( $this->create_regex( 'accessKey', 'secretKey' ) );
		$admin = new Static_Press_S3_Admin();
		$admin->options_page();
	}

	/**
	 * Creates regex.
	 * 
	 * @param string $access_key Access key.
	 * @param string $secret_key Secret key.
	 * @return string Regex.
	 */
	private function create_regex( $access_key, $secret_key ) {
		$regex = preg_quote( file_get_contents( Path_Creator::create_file_path( 'options-page.html' ) ), null );
		$regex = str_replace( 'WP_NONCE', '[0-9a-z]*', $regex );
		$regex = str_replace( 'ACCESS_KEY_VALUE', $access_key, $regex );
		$regex = str_replace( 'SECRET_KEY_VALUE', $secret_key, $regex );
		return "|$regex|";
	}
}
