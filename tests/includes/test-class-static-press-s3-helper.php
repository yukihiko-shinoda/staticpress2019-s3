<?php
/**
 * Class Static_Press_S3_Helper_Test
 *
 * @package static_press\tests\includes
 */

/**
 * Require once.
 * (This comment prevents to PHP_CodeSniffer detect "Missing file doc comment".)
 * 
 * @see https://qiita.com/juthaDDA/items/fa2590c1032abbf87334
 */
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/aws-sdk-php-from-zip/aws-autoloader.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-helper.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-mock-creator.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-magic-for-test.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-path-creator.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'tests/testlibraries/class-polyfill-wp-unittestcase.php';
use static_press_s3\includes\Static_Press_S3_Helper;
use static_press_s3\tests\testlibraries\Mock_Creator;
use static_press_s3\tests\testlibraries\Magic_For_Test;
use static_press_s3\tests\testlibraries\Path_Creator;
use static_press_s3\tests\testlibraries\Polyfill_WP_UnitTestCase;
/**
 * Static_Press_S3_Helper test case.
 *
 * @noinspection PhpUndefinedClassInspection
 */
class Static_Press_S3_Helper_Test extends Polyfill_WP_UnitTestCase {
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
		'other',
	);

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
	 * Test steps for init_s3().
	 * 
	 * @dataProvider provider_init_s3
	 * @param string $access_key Access key.
	 * @param string $secret_key Secret key.
	 * @param string $region Region.
	 * @param string $expected Expected.
	 */
	public function test_construct( $access_key, $secret_key, $region, $expected ) {
		$s3_helper          = new Static_Press_S3_Helper( $access_key, $secret_key, $region );
		$reflection         = new \ReflectionClass( get_class( $s3_helper ) );
		$reflector_property = $reflection->getProperty( 's3' );
		$reflector_property->setAccessible( true );
		$s3_client = $reflector_property->getValue( $s3_helper );
		$this->assert_s3_client( $s3_client, $access_key, $secret_key, $expected );
	}

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
		$s3_helper = new Static_Press_S3_Helper();
		$s3_client = $s3_helper->init_s3( $access_key, $secret_key, $region );
		$this->assert_s3_client( $s3_client, $access_key, $secret_key, $expected );
	}

	/**
	 * Test S3 client.
	 * 
	 * @dataProvider provider_init_s3
	 * @param S3Client $s3_client S3 client.
	 * @param string   $access_key Access key.
	 * @param string   $secret_key Secret key.
	 * @param string   $expected Expected.
	 */
	private function assert_s3_client( $s3_client, $access_key, $secret_key, $expected ) {
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
		$expected_argument = Mock_Creator::create_expected_argument( $bucket, $file_path );
		$s3_helper         = Mock_Creator::create_s3_helper_partial_mock( Mock_Creator::create_s3_client_partial_mock_put_object( $expected_argument, $response ) );
		$this->assertEquals( $response, $s3_helper->upload( $bucket, Path_Creator::create_file_path( $filename ), $upload_path ) );
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
		$s3_helper         = Mock_Creator::create_s3_helper_partial_mock( Mock_Creator::create_s3_client_partial_mock_put_object( $expected_argument, $response ) );
		$this->expectException( InvalidArgumentException::class );
		$s3_helper->upload( $bucket, Path_Creator::create_file_path( $filename ), $upload_path );
	}

	/**
	 * Function test_upload() should return false when S3 client not exist.
	 */
	public function test_list_buckets() {
		$s3_helper = new Static_Press_S3_Helper();
		$this->assertEquals( false, $s3_helper->list_buckets() );
	}
}
