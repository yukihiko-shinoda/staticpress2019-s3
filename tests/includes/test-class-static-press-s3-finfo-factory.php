<?php
/**
 * Class Static_Press_S3_Finfo_Factory_Test
 *
 * @package static_press\tests\includes
 */

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-finfo-factory.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-magic-for-test.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-path-creator.php';
use static_press_s3\includes\Static_Press_S3_Finfo_Factory;
use static_press_s3\tests\testlibraries\Magic_For_Test;
use static_press_s3\tests\testlibraries\Path_Creator;
/**
 * Static_Press_S3_Helper test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Finfo_Factory_Test extends \WP_UnitTestCase {
	/**
	 * Errors.
	 * 
	 * @var array
	 */
	private $errors;
	/**
	 * Temporary magic file.
	 * 
	 * @var string
	 */
	private $temporary_magic_file;

	/**
	 * Unsets environment variable "MAGIC".
	 */
	public function setUp() {
		parent::setUp();
		$this->errors = array();
		set_error_handler( array( $this, 'handleError' ) );
		$this->temporary_magic_file = Path_Creator::create_file_path( 'magic' );
		if ( file_exists( $this->temporary_magic_file ) ) {
			$this->rm( $this->temporary_magic_file );
		}
		putenv( 'MAGIC' );
	}

	/**
	 * Remove temoporary magic file.
	 */
	public function tearDown() {
		Mockery::close();
		if ( file_exists( $this->temporary_magic_file ) ) {
			$this->rm( $this->temporary_magic_file );
		}
		restore_error_handler();
		parent::tearDown();
	}

	/**
	 * Function create() should call create_without_file() when:
	 * - environment variable "MAGIC" is not set
	 * - Magic file from argument does not exist
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
	 * Function create() should call create_with_file() when:
	 * - environment variable "MAGIC" is not set
	 * - Magic file from argument exists
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_with_magic_file() {
		$magic_file = Magic_For_Test::get();
		$finfo      = new FInfo( FILEINFO_MIME_TYPE, $magic_file );
		$mock       = $this->should_call_create_with_file( $magic_file, $finfo );
		$result     = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}

	/**
	 * Function create() should call create_without_file() when:
	 * - environment variable "MAGIC" is set and magic file exists
	 * - Magic file from argument does not exist
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_without_magic_file_with_file_in_environment_variable() {
		$this->copy( Magic_For_Test::get(), $this->temporary_magic_file );
		putenv( "MAGIC=$this->temporary_magic_file" );
		$magic_file = '*/:<>?\|';
		$finfo      = new FInfo( FILEINFO_MIME_TYPE );
		$mock       = $this->should_call_create_without_file( $finfo );
		$result     = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}

	/**
	 * Function create() should call create_without_file() when:
	 * - environment variable "MAGIC" is set and magic file exists
	 * - Magic file from argument exists
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_with_magic_file_with_file_in_environment_variable() {
		$this->copy( Magic_For_Test::get(), $this->temporary_magic_file );
		putenv( "MAGIC=$this->temporary_magic_file" );
		$magic_file = Magic_For_Test::get();
		$finfo      = new FInfo( FILEINFO_MIME_TYPE );
		$mock       = $this->should_call_create_without_file( $finfo );
		$result     = $mock->create( $magic_file );
		$this->assertEquals( $finfo, $result );
	}

	/**
	 * Function create() should throw exception when:
	 * - environment variable "MAGIC" is set and magic file does not exist
	 * - Magic file from argument does not exist
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_without_magic_file_without_file_in_environment_variable() {
		putenv( "MAGIC=$this->temporary_magic_file" );
		$magic_file = '*/:<>?\|';
		if ( version_compare( PHP_VERSION, '7.0', '>=' ) ) {
			$this->expectException( Exception::class );
			$this->expectExceptionMessageRegExp( '/magic\)\: failed to open stream\: No such file or directory/' );
		}
		$finfo_factory = new Static_Press_S3_Finfo_Factory();
		$finfo_factory->create( $magic_file );
		if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
			$this->checkError( 'failed to open stream: No such file or directory', E_WARNING );
		}
	}

	/**
	 * Function create() should call create_with_file() when:
	 * - environment variable "MAGIC" is set and magic file does not exist
	 * - Magic file from argument exists
	 *
	 * @throws ReflectionException When fail to create ReflectionClass instance.
	 */
	public function test_create_finfo_with_magic_file_without_file_in_environment_variable() {
		putenv( "MAGIC=$this->temporary_magic_file" );
		$magic_file = Magic_For_Test::get();
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
	 * Copies file or directory.
	 * 
	 * @param string $src Source.
	 * @param string $dst Destination.
	 */
	private function copy( $src, $dst ) {
		switch ( true ) {
			case is_dir( $src ):
				$this->recurse_copy( $src, $dst );
				break;
			default:
				copy( $src, $dst );
				break;
		}
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

	/**
	 * Removes file or directory.
	 * 
	 * @param string $src Source.
	 */
	private function rm( $src ) {
		switch ( true ) {
			case is_dir( $src ):
				rmdir( $src );
				break;
			default:
				unlink( $src );
				break;
		}
	}
	/**
	 * Checks error.
	 * 
	 * @param string $errstr The second parameter, errstr, contains the error message, as a string.
	 * @param int    $errno      The first parameter, errno, contains the level of the error raised, as an integer.
	 * @see https://www.sitepoint.com/testing-error-conditions-with-phpunit/
	 */
	public function checkError( $errstr, $errno ) {
		foreach ( $this->errors as $error ) {
			if ( strpos( $error['errstr'], $errstr ) !== false && $error['errno'] === $errno ) {
				return;
			}
		}
		$this->fail( '"Error with level ' . $errno . "Error with message '" . $errstr . "' not found in ", var_export( $this->errors, true ) );
	}
	
	/**
	 * Handles error.
	 * 
	 * @param int    $errno      The first parameter, errno, contains the level of the error raised, as an integer.
	 * @param string $errstr     The second parameter, errstr, contains the error message, as a string.
	 * @param string $errfile    The third parameter is optional, errfile, which contains the filename that the error was raised in, as a string.
	 * @param int    $errline    The fourth parameter is optional, errline, which contains the line number the error was raised at, as an integer.
	 * @param array  $errcontext The fifth parameter is optional, errcontext, which is an array that points to the active symbol table at the point the error occurred.
	 *                           In other words, errcontext will contain an array of every variable that existed in the scope the error was triggered in.
	 *                           User error handler must not modify error context.
	 */
	public function handleError( $errno, $errstr, $errfile, $errline, $errcontext ) {
		$this->errors[] = compact( 'errno', 'errstr', 'errfile', 'errline', 'errcontext' );
	}
}
