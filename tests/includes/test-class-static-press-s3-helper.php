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
	 * 
	 * @see https://en.wikipedia.org/wiki/Media_type
	 * @see https://www.freeformatter.com/mime-types-list.html#mime-types-list
	 * @see https://github.com/symfony/symfony/blob/v5.1.2/src/Symfony/Component/Mime/MimeTypes.php
	 */
	public function provider_mime_type() {
		return array(
			array( 'not-found.txt', false ),
			array( 'empty.txt', version_compare( PHP_VERSION, '7.4', '>=' ) ? 'application/x-empty' : 'inode/x-empty' ),
			array( 'file.aac', 'audio/aac' ),
			array( 'file.css', 'text/css' ),
			array( 'file.csv', 'text/plain' ),
			array( 'file.flac', 'audio/flac' ),
			array( 'file.gif', 'image/gif' ),
			array( 'file.htm', 'text/html' ),
			array( 'file.html', 'text/html' ),
			array( 'file.jpe', 'image/jpeg' ),
			array( 'file.jpeg', 'image/jpeg' ),
			array( 'file.jpg', 'image/jpeg' ),
			array( 'file.js', 'application/javascript' ),
			array( 'file.m4a', 'audio/m4a' ),
			array( 'file.mp3', 'audio/mpeg' ),
			array( 'file.png', 'image/png' ),
			array( 'file.svg', 'image/svg+xml' ),
			array( 'file.txt', 'text/plain' ),
			array( 'file.wav', 'audio/wav' ),
			array( 'file.xml', 'application/xml' ),
			array( 'no-extension-text', 'text/plain' ),
			array( 'no-extension-media', 'application/octet-stream' ),
		);
	}

	/**
	 * Call private method.
	 * 
	 * @param string $method_name     Method name.
	 * @param array  $array_parameter Array of parameter.
	 */
	private function call_private_method( $method_name, $array_parameter ) {
		$s3_helper  = new S3_helper();
		$reflection = new \ReflectionClass( get_class( $s3_helper ) );
		$method     = $reflection->getMethod( $method_name );
		$method->setAccessible( true );
		return $method->invokeArgs( $s3_helper, $array_parameter );
	}
}
