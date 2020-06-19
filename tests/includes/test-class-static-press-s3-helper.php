<?php
/**
 * Class Static_Press_S3_Helper_Test
 *
 * @package static_press\tests\includes
 */

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-S3_helper.php';

/**
 * Static_Press_S3_Helper test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Helper_Test extends \WP_UnitTestCase {
	/**
	 * Test steps for mime_type().
	 *
	 * @dataProvider provider_mime_type
	 * @param string $fime_name File name.
	 * @param string $expect    Expect.
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_mime_type( $fime_name, $expect ) {
		$file_path = STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testresources/' . $fime_name;
		$result    = $this->call_private_method( 'mime_type', array( $file_path ) );
		$this->assertEquals( $expect, $result );
	}

	/**
	 * Function mime_type() should returns appropriate mime type.
	 */
	public function provider_mime_type() {
		return array(
			array( 'not-found.txt', false ),
			array( 'empty.txt', version_compare( PHP_VERSION, '7.4', '>=' ) ? 'application/x-empty' : 'inode/x-empty' ),
			array( 'file.aac', 'application/octet-stream' ),
			array( 'file.css', 'text/css' ),
			array( 'file.csv', 'text/plain' ),
			array( 'file.flac', 'application/octet-stream' ),
			array( 'file.gif', 'application/octet-stream' ),
			array( 'file.htm', 'text/html' ),
			array( 'file.html', 'text/html' ),
			array( 'file.jpe', 'application/octet-stream' ),
			array( 'file.jpeg', 'application/octet-stream' ),
			array( 'file.jpg', 'application/octet-stream' ),
			array( 'file.js', 'application/x-javascript' ),
			array( 'file.m4a', 'application/octet-stream' ),
			array( 'file.mp3', 'application/octet-stream' ),
			array( 'file.png', 'application/octet-stream' ),
			array( 'file.txt', 'text/plain' ),
			array( 'file.wav', 'application/octet-stream' ),
			array( 'file.xml', 'application/xml' ),
			array( 'no-extension', 'text/plain' ),
		);
	}

	/**
	 * Call private method.
	 * 
	 * @param string $method_name     Method name.
	 * @param array  $array_parameter Array of parameter.
	 */
	private function call_private_method( $method_name, $array_parameter ) {
		$transient_manager = new S3_helper();
		$reflection        = new \ReflectionClass( get_class( $transient_manager ) );
		$method            = $reflection->getMethod( $method_name );
		$method->setAccessible( true );
		return $method->invokeArgs( $transient_manager, $array_parameter );
	}
}
