<?php
/**
 * Class Static_Press_S3_Admin_Test
 *
 * @package static_press\tests\includes
 */

/**
 * Require once.
 * (This comment prevents to PHP_CodeSniffer detect "Missing file doc comment".)
 * 
 * @see https://qiita.com/juthaDDA/items/fa2590c1032abbf87334
 */
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-admin.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-option-setter.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-path-creator.php';
use static_press_s3\includes\Static_Press_S3_Admin;
use static_press_s3\tests\testlibraries\Option_Setter;
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
		Option_Setter::set_up_option();
		$this->expectOutputRegex( $this->create_regex( 'accessKey', 'secretKey', false ) );
		$admin = new Static_Press_S3_Admin();
		$admin->options_page();
	}

	/**
	 * Test steps for init_s3().
	 */
	public function test_option_keys() {
		Option_Setter::set_up_option();
		$admin = new Static_Press_S3_Admin();
		$this->assertEquals(
			$admin->option_keys(),
			array(
				'access_key'     => 'AWS Access Key',
				'secret_key'     => 'AWS Secret Key',
				'region'         => 'AWS Region',
				'endpoint'       => 'S3 Endpoint',
				'bucket'         => 'S3 Bucket',
				'put_object_acl' => 'Put Object ACL',
			)
		);
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
				'endpoint'   => 'endpoint',
				'bucket'     => 'bucket',
			)
		);
		$this->expectOutputRegex( $this->create_regex( 'accessKey', 'secretKey', false ) );
		$admin = new Static_Press_S3_Admin();
		$admin->options_page();
	}

	/**
	 * Creates regex.
	 * 
	 * @param string $access_key     Access key.
	 * @param string $secret_key     Secret key.
	 * @param bool   $put_object_acl PutObjectACL.
	 * @return string Regex.
	 */
	private function create_regex( $access_key, $secret_key, $put_object_acl ) {
		$regex = preg_quote( file_get_contents( Path_Creator::create_file_path( 'options-page.html' ) ), null );
		$regex = str_replace( 'WP_NONCE', '[0-9a-z]*', $regex );
		$regex = str_replace( 'ACCESS_KEY_VALUE', $access_key, $regex );
		$regex = str_replace( 'SECRET_KEY_VALUE', $secret_key, $regex );
		$regex = str_replace( 'PUT_OBJECT_ACL_VALUE', $put_object_acl ? ' checked' : '', $regex );
		return "|$regex|";
	}
}
