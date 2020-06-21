<?php
/**
 * Class Plugin_Test
 *
 * @package static_press_s3\tests
 */

/**
 * Plugin test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Plugin_Test extends \WP_UnitTestCase {
	/**
	 * Path to plugin.php file.
	 * 
	 * @var string
	 */
	private $path_to_plugin_file;
	/**
	 * Sets path to plugin file and require.
	 */
	public function setUp() {
		parent::setUp();
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
		global $staticpress;
		if ( ! isset( $staticpress ) ) {
			$staticpress = 'temp';
		}
		$this->assertEquals( false, has_action( 'plugins_loaded', 'static_press_s3_run' ) );
		require $this->path_to_plugin_file;
		$this->assertEquals( 10, has_action( 'plugins_loaded', 'static_press_s3_run' ) );
		do_action( 'plugins_loaded' );
		$this->assertTrue( class_exists( 'Static_Press_S3_Helper' ) );
		$this->assertTrue( class_exists( 'Static_Press_S3_Admin' ) );
		$this->assertTrue( class_exists( 'Static_Press_S3' ) );
	}
}
