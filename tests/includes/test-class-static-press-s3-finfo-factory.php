<?php
/**
 * Class Static_Press_S3_Finfo_Factory_Test
 *
 * @package static_press\tests\includes
 */

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-finfo-factory.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-path-creator.php';
use static_press_s3\includes\Static_Press_S3_Finfo_Factory;
use static_press_s3\tests\testlibraries\Path_Creator;
/**
 * Static_Press_S3_Helper test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Finfo_Factory_Test extends \WP_UnitTestCase {
	/**
	 * Unsets environment variable "MAGIC".
	 */
	public function setUp() {
		parent::setUp();
		$temporary_magic_file = Path_Creator::create_file_path( 'magic' );
		if ( file_exists( $temporary_magic_file ) ) {
			rmdir( $temporary_magic_file );
		}
		putenv( 'MAGIC' );
	}

	/**
	 * Remove temoporary magic file.
	 */
	public function tearDown() {
		mockery::close();
		parent::tearDown();
	}

	/**
	 * Function create() should call create_without_file() when magic file does not exist.
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_without_magic_file() {
		$magic_file = '*/:<>?\|';
		$finfo      = new FInfo( FILEINFO_MIME_TYPE );
		$mock       = $this->should_call_create_without_file( $finfo );
		$result     = $mock->create( $magic_file );
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
		$mock       = $this->should_call_create_with_file( $magic_file, $finfo );
		$result     = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}

	/**
	 * Function create() should call create_without_file() when magic file does not exist.
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_without_magic_file_with_file_in_environment_variable() {
		$temporary_magic_file = Path_Creator::create_file_path( 'magic' );
		$this->recurse_copy( '/usr/share/misc/magic', $temporary_magic_file );
		putenv( "MAGIC=$temporary_magic_file" );
		$magic_file = '*/:<>?\|';
		$finfo      = new FInfo( FILEINFO_MIME_TYPE );
		$mock       = $this->should_call_create_without_file( $finfo );
		$result     = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}

	/**
	 * Function create() should call create_with_file() when magic file does exists.
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_with_magic_file_with_file_in_environment_variable() {
		$temporary_magic_file = Path_Creator::create_file_path( 'magic' );
		$this->recurse_copy( '/usr/share/misc/magic', $temporary_magic_file );
		putenv( "MAGIC=$temporary_magic_file" );
		$magic_file = '/usr/share/misc/magic';
		$finfo      = new FInfo( FILEINFO_MIME_TYPE );
		$mock       = $this->should_call_create_without_file( $finfo );
		$result     = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}

	/**
	 * Function create() should call create_without_file() when magic file does not exist.
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_without_magic_file_without_file_in_environment_variable() {
		$temporary_magic_file = Path_Creator::create_file_path( 'magic' );
		putenv( "MAGIC=$temporary_magic_file" );
		$magic_file = '*/:<>?\|';
		$this->expectException( Exception::class );
		$this->expectExceptionMessageRegExp( '/magic\)\: failed to open stream\: No such file or directory/' );
		$finfo_factory = new Static_Press_S3_Finfo_Factory();
		$finfo_factory->create( $magic_file );
	}

	/**
	 * Function create() should call create_with_file() when magic file does exists.
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_with_magic_file_without_file_in_environment_variable() {
		$temporary_magic_file = Path_Creator::create_file_path( 'magic' );
		putenv( "MAGIC=$temporary_magic_file" );
		$magic_file = '/usr/share/misc/magic';
		$finfo      = new FInfo( FILEINFO_MIME_TYPE, $magic_file );
		$mock       = $this->should_call_create_with_file( $magic_file, $finfo );
		$result     = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}

	/**
	 * Define mock that should call create_without_file().
	 * 
	 * @param FInfo $finfo FInfo.
	 */
	private function should_call_create_without_file( $finfo ) {
		$mock = Mockery::mock( 'static_press_s3\includes\Static_Press_S3_Finfo_Factory' )->makePartial();
		$mock->shouldReceive( 'create_without_file' )
		->withNoArgs()
		->andReturn( $finfo )
		->atLeast()
		->times( 1 );
		return $mock;
	}

	/**
	 * Define mock that should call create_with_file().
	 * 
	 * @param string $magic_file Magic file.
	 * @param FInfo  $finfo      FInfo.
	 */
	private function should_call_create_with_file( $magic_file, $finfo ) {
		$mock = Mockery::mock( 'static_press_s3\includes\Static_Press_S3_Finfo_Factory' )->makePartial();
		$mock->shouldReceive( 'create_with_file' )
		->with( $magic_file )
		->andReturn( $finfo )
		->atLeast()
		->times( 1 );
		return $mock;
	}

	/**
	 * Copies recursivery.
	 * 
	 * @param string $src Source.
	 * @param string $dst Destination.
	 * @see https://stackoverflow.com/questions/2050859/copy-entire-contents-of-a-directory-to-another-using-php/2050909#2050909
	 */
	private function recurse_copy( $src, $dst ) { 
		$dir = opendir( $src );
		@mkdir( $dst );
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( '.' != $file ) && ( '..' != $file ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					$this->recurse_copy( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	}
}
