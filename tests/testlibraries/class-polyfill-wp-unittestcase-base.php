<?php
/**
 * Class Polyfill_WP_UnitTestCase
 *
 * @package static_press_s3\tests\testlibraries
 */

namespace static_press_s3\tests\testlibraries;

/**
 * Polyfill WP unit test case base.
 * phpcs:disable Generic.Classes.DuplicateClassName.Found -- To polyfill
 */
abstract class Polyfill_WP_UnitTestCase_Base extends \WP_UnitTestCase {
	// phpcs:enable
	/**
	 * Set up.
	 */
	public function set_up() {
	}
	/**
	 * Calls set_up.
	 */
	public function setUp() {
		parent::setUp();
		$this->set_up();
	}

	/**
	 * Tear down.
	 */
	public function tear_down() {
	}

	/**
	 * Calls tear_down.
	 */
	public function tearDown() {
		$this->tear_down();
		parent::tearDown();
	}
}
