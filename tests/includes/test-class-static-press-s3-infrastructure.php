<?php
/**
 * Class Static_Press_S3_Infrastructure_Test
 *
 * @package static_press\tests\includes
 */

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-infrastructure.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-magic-for-test.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-path-creator.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-polyfill-wp-unittestcase.php';

use static_press_s3\includes\Static_Press_S3_Infrastructure;
use static_press_s3\tests\testlibraries\Magic_For_Test;
use static_press_s3\tests\testlibraries\Path_Creator;
use static_press_s3\tests\testlibraries\Polyfill_WP_UnitTestCase;

/**
 * Static_Press_S3_Helper test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Infrastructure_Test extends Polyfill_WP_UnitTestCase {

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
	 * Test steps for mime_type().
	 *
	 * @dataProvider provider_mime_type
	 * @param string $file_name File name.
	 * @param string $expect    Expect.
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_mime_type( $file_name, $expect ) {
		$file_path = Path_Creator::create_file_path( $file_name );
		$result    = Static_Press_S3_Infrastructure::mime_type( $file_path );
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
			array( 'upper-case.JPG', 'image/jpeg' ),
		);
	}
}