<?php
/**
 * Class Static_Press_S3_Helper_Test
 *
 * @package static_press\tests\includes
 */

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/aws.phar';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-S3_helper.php';
use Mockery;
/**
 * Static_Press_S3_Helper test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Helper_Test extends \WP_UnitTestCase {
	const REGION_NORTH_VIRGINIA = 'us-east-1';
	const REGION_TOKYO          = 'ap-northeast-1';
	const REGION_OREGON         = 'us-west-2';
	const ARRAY_REGION          = array(
		'US_EAST_1',
		'US_WEST_1',
		'US_WEST_2',
		'EU_WEST_1',
		'AP_SOUTHEAST_1',
		'AP_SOUTHEAST_2',
		'AP_NORTHEAST_1',
		'SA_EAST_1',
		'CN_NORTH_1',
		'US_GOV_WEST_1',
	);
	/**
	 * Test steps for init_s3().
	 * 
	 * @dataProvider provider_init_s3
	 * @param string $access_key Access key.
	 * @param string $secret_key Secret key.
	 * @param string $region Region.
	 * @param string $expected Expected.
	 */
	public function test_init_s3( $access_key, $secret_key, $region, $expected ) {
		$s3_helper = new S3_helper();
		$s3_client = $s3_helper->init_s3( $access_key, $secret_key, $region );
		$config    = $s3_client->getConfig();
		$this->assertEquals( $access_key, $config->get( 'key' ) );
		$this->assertEquals( $secret_key, $config->get( 'secret' ) );
		$this->assertEquals( $expected, $config->get( 'region' ) );
		$this->assertEquals( 's3', $config->get( 'service' ) );
		$this->assertEquals( '2006-03-01', $config->get( 'version' ) );
	}

	/**
	 * Function init_s3() should returns S3 client which has appropriate configuration.
	 */
	public function provider_init_s3() {
		return array(
			array( 'accessKey', 'secretKey', self::REGION_OREGON, self::REGION_OREGON ),
			array( 'AccessKey', 'SecretKey', '', self::REGION_NORTH_VIRGINIA ),
			array( 'access_key', 'secret_key', null, self::REGION_TOKYO ),
		);
	}

	/**
	 * Default region should be "ap-northeast-1".
	 */
	public function test_init_s3_default_region() {
		$s3_helper = new S3_helper();
		$s3_client = $s3_helper->init_s3( '', '' );
		$config    = $s3_client->getConfig();
		$this->assertEquals( self::REGION_TOKYO, $config->get( 'region' ) );
	}

	/**
	 * Function get_regions() should return appropriate array.
	 */
	public function test_get_regions() {
		$s3_helper = new S3_helper();
		$this->assertEquals( self::ARRAY_REGION, $s3_helper->get_regions() );
	}

	/**
	 * Test steps for get_region().
	 * 
	 * @dataProvider provider_get_region
	 * @param string $argument Argument.
	 * @param string $expected Expected.
	 */
	public function test_get_region( $argument, $expected ) {
		$s3_helper = new S3_helper();
		$this->assertEquals( $expected, $s3_helper->get_region( $argument ) );
	}

	/**
	 * Function get_region() should return appropriate array.
	 */
	public function provider_get_region() {
		$array_provide = array();
		foreach ( self::ARRAY_REGION as $region ) {
			$expected        = str_replace( '_', '-', strtolower( $region ) );
			$array_provide[] = array( $region, $expected );
		}
		return $array_provide;
	}

	/**
	 * Function test_upload() should return appropriate array.
	 */
	public function test_upload_file() {
		$filename          = 'file.txt';
		$upload_path       = '';
		$bucket            = '';
		$response          = 'response';
		$file_path         = $this->create_file_path( $filename );
		$expected_argument = Mockery::on(
			function ( $argument ) use ( $bucket, $file_path ) {
				$bucket_is_set        = isset( $argument['Bucket'] ) && $bucket === $argument['Bucket'];
				$storage_class_is_set = isset( $argument['StorageClass'] ) && 'STANDARD' === $argument['StorageClass'];
				$acl_is_set           = isset( $argument['ACL'] ) && 'public-read' === $argument['ACL'];
				$key_is_set           = isset( $argument['Key'] ) && $file_path === $argument['Key'];
				$body_is_set          = isset( $argument['Body'] );
				$content_type_is_set  = isset( $argument['ContentType'] ) && 'text/plain' === $argument['ContentType'];
				return $bucket_is_set && $storage_class_is_set && $acl_is_set && $key_is_set && $body_is_set && $content_type_is_set;
			}
		);
		$s3_helper         = $this->create_s3_helper_partial_mock( $this->create_s3_client_partial_mock( $expected_argument, $response ) );
		$this->assertEquals( $response, $s3_helper->upload( $this->create_file_path( $filename ), $upload_path, $bucket ) );
	}

	/**
	 * Function test_upload() should return false when file not exist.
	 */
	public function test_upload_file_not_exist() {
		$filename          = '*/:<>?\|';
		$upload_path       = '';
		$bucket            = '';
		$response          = 'response';
		$expected_argument = array();
		$expected_response = false;
		$s3_helper         = $this->create_s3_helper_partial_mock( $this->create_s3_client_partial_mock( $expected_argument, $response ) );
		$this->assertEquals( $expected_response, $s3_helper->upload( $this->create_file_path( $filename ), $upload_path, $bucket ) );
	}

	/**
	 * Function test_upload() should return false when S3 client not exist.
	 */
	public function test_upload_s3_cilent_not_exist() {
		$filename          = 'file.txt';
		$upload_path       = '';
		$bucket            = '';
		$expected_response = false;
		$s3_helper         = $this->create_s3_helper_partial_mock( false );
		$this->assertEquals( $expected_response, $s3_helper->upload( $this->create_file_path( $filename ), $upload_path, $bucket ) );
	}

	/**
	 * Creates S# client pertial mock.
	 * 
	 * @param mixed  $expected Expected.
	 * @param string $response Response.
	 * @return S3Client S3 helper partial mock.
	 */
	private function create_s3_client_partial_mock( $expected, $response ) {
		$s3_helper      = new S3_helper();
		$mock_s3_client = Mockery::mock( $s3_helper->init_s3( '', '' ) );
		$mock_s3_client->shouldReceive( 'putObject' )
		->with( $expected )
		->andReturn( $response );
		return $mock_s3_client;
	}

	/**
	 * Creates S3 helper partial mock which S3 client replaced.
	 * 
	 * @param string $s3_client S3 client.
	 * @return S3_helper S3 helper partial mock.
	 */
	private function create_s3_helper_partial_mock( $s3_client ) {
		$s3_helper           = new S3_helper();
		$reflection          = new ReflectionClass( $s3_helper );
		$reflection_property = $reflection->getProperty( 's3' );
		$reflection_property->setAccessible( true );
		$reflection_property->setValue( $s3_helper, $s3_client );
		return $s3_helper;
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
		$file_path = $this->create_file_path( $file_name );
		$result    = $this->call_private_method( 'mime_type', array( $file_path ) );
		$this->assertEquals( $expect, $result );
	}

	/**
	 * Creates file path.
	 * 
	 * @param string $file_name File name.
	 * @return string File path.
	 */
	private function create_file_path( $file_name ) {
		return STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testresources/' . $file_name;
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
