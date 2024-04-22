<?php
/**
 * Class Plugin_Test
 *
 * @package static_press_s3\tests
 */

/**
 * Require once.
 * (This comment prevents to PHP_CodeSniffer detect "Missing file doc comment".)
 * 
 * @see https://qiita.com/juthaDDA/items/fa2590c1032abbf87334
 */
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-option-setter.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-polyfill-wp-unittestcase.php';
use static_press_s3\tests\testlibraries\Option_Setter;
use static_press_s3\tests\testlibraries\Polyfill_WP_UnitTestCase;
/**
 * Plugin test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Plugin_Test extends Polyfill_WP_UnitTestCase {
	/**
	 * Path to plugin.php file.
	 * 
	 * @var string
	 */
	private $path_to_plugin_file;
	/**
	 * Sets path to plugin file and require.
	 */
	public function set_up() {
		parent::set_up();
		$this->path_to_plugin_file = dirname( dirname( __FILE__ ) ) . '/plugin.php';
		require $this->path_to_plugin_file;
		remove_action( 'plugins_loaded', 'static_press_s3_run' );
	}

	/**
	 * File plugin.php should define variable named "$staticpress".
	 * (because StaticPress-S3 already refer it.)
	 * File plugin.php should add filters.
	 */
	public function test() {
		Option_Setter::set_up_option();
		global $staticpress;
		if ( ! isset( $staticpress ) ) {
			$staticpress = 'temp';
		}
		$this->assertEquals( false, has_action( 'plugins_loaded', 'static_press_s3_run' ) );
		require $this->path_to_plugin_file;
		$this->assertEquals( 10, has_action( 'plugins_loaded', 'static_press_s3_run' ) );
		do_action( 'plugins_loaded' );
		$this->assertTrue( class_exists( 'static_press_s3\includes\Static_Press_S3_Helper' ) );
		$this->assertTrue( class_exists( 'static_press_s3\includes\Static_Press_S3_Admin' ) );
		$this->assertTrue( class_exists( 'static_press_s3\includes\Static_Press_S3' ) );
	}
}
