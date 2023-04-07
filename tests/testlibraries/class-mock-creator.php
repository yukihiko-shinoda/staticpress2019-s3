<?php
/**
 * Class Mock_Creator
 *
 * @package static_press_s3\tests\testlibraries
 */

namespace static_press_s3\tests\testlibraries;

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-helper.php';
use Mockery;
use ReflectionClass;
use static_press_s3\includes\Static_Press_S3_Helper;

/**
 * Mock creator.
 */
class Mock_Creator {
	/**
	 * Creates S# client partial mock.
	 * 
	 * @param mixed  $expected Expected.
	 * @param string $response Response.
	 * @return S3Client S3 helper partial mock.
	 */
	public static function create_s3_client_partial_mock_put_object( $expected, $response ) {
		$mock_s3_client = self::create_s3_client_partial_mock();
		$mock_s3_client->shouldReceive( 'putObject' )
		->with( $expected )
		->andReturn( $response );
		return $mock_s3_client;
	}

	/**
	 * Creates S# client partial mock.
	 * 
	 * @param mixed $expected Expected.
	 * @param bool  $response Response.
	 * @return S3Client S3 helper partial mock.
	 */
	public static function create_s3_client_partial_mock_does_bucket_exist( $expected, $response ) {
		$mock_s3_client = self::create_s3_client_partial_mock();
		$mock_s3_client->shouldReceive( 'doesBucketExist' )
		->withArgs( $expected )
		->andReturn( $response );
		return $mock_s3_client;
	}

	/**
	 * Creates S3 client partial mock.
	 */
	public static function create_s3_client_partial_mock() {
		$s3_helper = new Static_Press_S3_Helper();
		return Mockery::mock( $s3_helper->init_s3( '', '' ) );
	}
	/**
	 * Creates S3 helper partial mock which S3 client replaced.
	 * 
	 * @param string $s3_client S3 client.
	 * @return Static_Press_S3_Helper S3 helper partial mock.
	 */
	public static function create_s3_helper_partial_mock( $s3_client ) {
		$s3_helper           = new Static_Press_S3_Helper();
		$reflection          = new ReflectionClass( $s3_helper );
		$reflection_property = $reflection->getProperty( 's3' );
		$reflection_property->setAccessible( true );
		$reflection_property->setValue( $s3_helper, $s3_client );
		return $s3_helper;
	}

	/**
	 * Creates expected argument.
	 * 
	 * @param string $bucket Bucket name.
	 * @param string $file_path File path.
	 */
	public static function create_expected_argument( $bucket, $file_path ) {
		return Mockery::on(
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
	}
}
