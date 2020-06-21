<?php
/**
 * Class Static_Press_S3_Helper_Test
 *
 * @package static_press\tests\includes
 */

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/aws.phar';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-helper.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-path-creator.php';
use static_press_s3\tests\testlibraries\Path_Creator;
/**
 * Static_Press_S3_Helper test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Helper_Test extends \WP_UnitTestCase {
	const REGION_NORTH_VIRGINIA = 'us-east-1';
	const REGION_TOKYO          = 'ap-northeast-1';
	const REGION_OREGON         = 'us-west-2';
	/**
	 * Region list.
	 * 
	 * @see https://docs.aws.amazon.com/general/latest/gr/s3.html
	 * @see https://docs.aws.amazon.com/govcloud-us/latest/UserGuide/using-govcloud-endpoints.html
	 */
	const ARRAY_REGION = array(
		'af-south-1',
		'ap-northeast-1',
		'ap-northeast-2',
		'ap-northeast-3',
		'ap-southeast-1',
		'ap-southeast-2',
		'ap-east-1',
		'ap-south-1',
		'ca-central-1',
		'cn-north-1',
		'cn-northwest-1',
		'eu-central-1',
		'eu-west-1',
		'eu-west-2',
		'eu-west-3',
		'eu-north-1',
		'eu-south-1',
		'us-east-1',
		'us-east-2',
		'us-west-1',
		'us-west-2',
		'sa-east-1',
		'us-gov-west-1',
		'us-gov-east-1',
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
		$s3_helper   = new Static_Press_S3_Helper();
		$s3_client   = $s3_helper->init_s3( $access_key, $secret_key, $region );
		$credentials = $s3_client->getCredentials()->wait();
		$config      = $s3_client->getConfig();
		$this->assertEquals( $access_key, $credentials->getAccessKeyId() );
		$this->assertEquals( $secret_key, $credentials->getSecretKey() );
		$this->assertEquals( $expected, $s3_client->getRegion() );
		$this->assertEquals( 's3', $config['signing_name'] );
		$this->assertEquals( 's3v4', $config['signature_version'] );
		$this->assertEquals( '2006-03-01', $s3_client->getApi()->getApiVersion() );
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
		$s3_helper = new Static_Press_S3_Helper();
		$s3_client = $s3_helper->init_s3( '', '' );
		$this->assertEquals( self::REGION_TOKYO, $s3_client->getRegion() );
	}

	/**
	 * Function get_regions() should return appropriate array.
	 */
	public function test_get_regions() {
		$s3_helper = new Static_Press_S3_Helper();
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
		$s3_helper = new Static_Press_S3_Helper();
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
		$file_path         = Path_Creator::create_file_path( $filename );
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
		$this->assertEquals( $response, $s3_helper->upload( Path_Creator::create_file_path( $filename ), $upload_path, $bucket ) );
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
		$this->assertEquals( $expected_response, $s3_helper->upload( Path_Creator::create_file_path( $filename ), $upload_path, $bucket ) );
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
		$this->assertEquals( $expected_response, $s3_helper->upload( Path_Creator::create_file_path( $filename ), $upload_path, $bucket ) );
	}

	/**
	 * Creates S# client pertial mock.
	 * 
	 * @param mixed  $expected Expected.
	 * @param string $response Response.
	 * @return S3Client S3 helper partial mock.
	 */
	private function create_s3_client_partial_mock( $expected, $response ) {
		$s3_helper      = new Static_Press_S3_Helper();
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
	 * @return Static_Press_S3_Helper S3 helper partial mock.
	 */
	private function create_s3_helper_partial_mock( $s3_client ) {
		$s3_helper           = new Static_Press_S3_Helper();
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
		$file_path = Path_Creator::create_file_path( $file_name );
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
		$s3_helper  = new Static_Press_S3_Helper();
		$reflection = new \ReflectionClass( get_class( $s3_helper ) );
		$method     = $reflection->getMethod( $method_name );
		$method->setAccessible( true );
		return $method->invokeArgs( $s3_helper, $array_parameter );
	}
}
