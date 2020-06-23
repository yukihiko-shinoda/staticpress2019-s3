<?php
/**
 * Class Static_Press_S3_Finfo_Factory_Test
 *
 * @package static_press\tests\includes
 */

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-finfo-factory.php';
use static_press_s3\includes\Static_Press_S3_Finfo_Factory;
/**
 * Static_Press_S3_Helper test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Finfo_Factory_Test extends \WP_UnitTestCase {
	/**
	 * Function create() should call create_without_file() when magic file does not exist.
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_without_magic_file() {
		$magic_file = '*/:<>?\|';
		$finfo      = new FInfo( FILEINFO_MIME_TYPE );
		$mock       = Mockery::mock( new Static_Press_S3_Finfo_Factory() );
		$mock->shouldReceive( 'create_without_file' )
		->withNoArgs()
		->andReturn( $finfo )
		->atLeast()
		->times( 1 );
		$result = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}

	/**
	 * Function create() should call create_with_file() when magic file does exists.
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_with_magic_file() {
		$magic_file = '/usr/share/misc/magic';
		$finfo      = new FInfo( FILEINFO_MIME_TYPE, $magic_file );
		$mock       = Mockery::mock( new Static_Press_S3_Finfo_Factory() );
		$mock->shouldReceive( 'create_with_file' )
		->with( $magic_file )
		->andReturn( $finfo )
		->atLeast()
		->times( 1 );
		$result = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}
}
