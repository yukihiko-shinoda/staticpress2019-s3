<?php
/**
 * Class Static_Press_S3_Test
 *
 * @package static_press\tests\includes
 */

namespace static_press_s3\tests\includes;

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-admin.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-helper.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-path-creator.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-mock-creator.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-magic-for-test.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-polyfill-wp-unittestcase.php';
use static_press_s3\includes\Static_Press_S3;
use static_press_s3\includes\Static_Press_S3_Admin;
use static_press_s3\tests\testlibraries\Path_Creator;
use static_press_s3\tests\testlibraries\Mock_Creator;
use static_press_s3\tests\testlibraries\Magic_For_Test;
use static_press_s3\tests\testlibraries\Polyfill_WP_UnitTestCase;

/**
 * Static_Press_S3 test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Test extends Polyfill_WP_UnitTestCase {
	/**
	 * Sets environment variable "MAGIC".
	 * Since test_upload_file() calls function to crate FInfo instance.
	 */
	public function set_up() {
		parent::set_up();
		putenv( 'MAGIC=' . Magic_For_Test::get() );
	}

	/**
	 * Unsets environment variable "MAGIC".
	 */
	public function tear_down() {
		putenv( 'MAGIC' );
		parent::tear_down();
	}

	/**
	 * Test steps for init_s3().
	 */
	public function test_s3() {
		$s3_bucket          = 'example.com';
		$static_press_s3    = $this->create_static_press_s3();
		$reflection         = new \ReflectionClass( get_class( $static_press_s3 ) );
		$expected_argument  = array( $s3_bucket, true );
		$s3_helper          = Mock_Creator::create_s3_helper_partial_mock( Mock_Creator::create_s3_client_partial_mock_does_bucket_exist( $expected_argument, true ) );
		$reflector_property = $reflection->getProperty( 's3' );
		$reflector_property->setAccessible( true );
		$reflector_property->setValue( $static_press_s3, $s3_helper );
		$method = $reflection->getMethod( 's3' );
		$method->setAccessible( true );
		$this->assertNotFalse( $method->invokeArgs( $static_press_s3, array( $s3_bucket ) ) );
	}

	/**
	 * Test steps for init_s3().
	 */
	public function test_s3_upload() {
		$s3_bucket          = 'example.com';
		$static_press_s3    = $this->create_static_press_s3( $s3_bucket );
		$filename           = 'file.txt';
		$response           = 'response';
		$file_path          = Path_Creator::create_file_path( $filename );
		$reflection         = new \ReflectionClass( get_class( $static_press_s3 ) );
		$reflector_property = $reflection->getProperty( 's3' );
		$reflector_property->setAccessible( true );
		$mock_s3_client = Mock_Creator::create_s3_client_partial_mock();
		$mock_s3_client->shouldReceive( 'doesBucketExist' )->with( $s3_bucket, true )->andReturn( true );
		$expected_argument = Mock_Creator::create_expected_argument( $s3_bucket, $filename );
		$mock_s3_client->shouldReceive( 'putObject' )->with( $expected_argument )->andReturn( $response );
		$s3_helper = Mock_Creator::create_s3_helper_partial_mock( $mock_s3_client );
		$reflector_property->setValue( $static_press_s3, $s3_helper );
		$reflection = new \ReflectionClass( get_class( $static_press_s3 ) );
		$method     = $reflection->getMethod( 's3_upload' );
		$method->setAccessible( true );
		$this->assertNotFalse( $method->invokeArgs( $static_press_s3, array( $file_path, 'https://' . $s3_bucket . '/' . $filename ) ) );
	}

	/**
	 * Creates Static_Press_S3 instance from Static_Press_S3_Admin::$options.
	 * 
	 * @param string|null $bucket Bucket name.
	 * @return Static_Press_S3
	 */
	private function create_static_press_s3( $bucket = null ) {
		$static_press_admin = new Static_Press_S3_Admin();
		$option             = $static_press_admin->get_option();
		if ( null !== $bucket ) {
			$option['bucket'] = $bucket;
		}
		return new Static_Press_S3( $option );
	}
}
