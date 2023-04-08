<?php
/**
 * Class Static_Press_S3_Helper
 *
 * @package static_press_s3\includes
 */

namespace static_press_s3\includes;

/**
 * Require once.
 * (This comment prevents to PHP_CodeSniffer detect "Missing file doc comment".)
 * 
 * @see https://qiita.com/juthaDDA/items/fa2590c1032abbf87334
 */
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/aws-sdk-php-from-zip/aws-autoloader.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-finfo-factory.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-log.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-mime-type-checker.php';
use static_press_s3\includes\Static_Press_S3_Finfo_Factory;
use static_press_s3\includes\Static_Press_S3_Log;
use static_press_s3\includes\Static_Press_S3_Mime_Type_Checker;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\CredentialsException;

/**
 * S3 Helper.
 */
class Static_Press_S3_Helper {
	/**
	 * Region list.
	 * 
	 * @see https://docs.aws.amazon.com/general/latest/gr/s3.html
	 * @see https://docs.aws.amazon.com/govcloud-us/latest/UserGuide/using-govcloud-endpoints.html
	 */
	const REGIONS               = array(
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
	const REGION_NORTH_VIRGINIA = 'us-east-1';
	/**
	 * S3 client.
	 * 
	 * @var S3Client
	 */
	private $s3;
	/**
	 * Options.
	 * 
	 * @var array
	 */
	private $options = array(
		'Bucket'       => '',
		'StorageClass' => 'STANDARD',
	);

	/**
	 * Constructor.
	 * 
	 * @param string $access_key Access key.
	 * @param string $secret_key Secret key.
	 * @param string $region     Region.
	 * @param string $endpoint   S3 compatible endpointURL.
	 */
	public function __construct( $access_key = null, $secret_key = null, $region = null, $endpoint = null ) {
		$this->s3 = $this->init_s3( $access_key, $secret_key, $region, $endpoint );
	}

	/**
	 * Inits S3 instance.
	 * 
	 * @param string $access_key Access key.
	 * @param string $secret_key Secret key.
	 * @param string $region     Region.
	 * @param string $endpoint   S3 compatible endpointURL.
	 * @return S3Client S3 client.
	 */
	public function init_s3( $access_key, $secret_key, $region = null, $endpoint = null ) {
		if ( ! isset( $region ) ) {
			$region = 'ap-northeast-1';
		}

		if ( 'other' == $region ) {
			$args = array(
				'region'                  => 'other',
				'endpoint'                => $endpoint,
				'version'                 => 'latest',
				'use_path_style_endpoint' => true,
			);
		} else {
			$args = array(
				'region'  => $this->get_region( $region ),
				'version' => 'latest',
			);
		};
		if ( $access_key && $secret_key ) {
			$args['credentials'] = array(
				'key'    => $access_key,
				'secret' => $secret_key,
			);
		}
		return new S3Client( $args );
	}

	/**
	 * Gets regions.
	 * 
	 * @return string[] Array of region.
	 */
	public function get_regions() {
		return self::REGIONS;
	}

	/**
	 * Gets region.
	 * 
	 * @param array $region Region.
	 * @return string Region.
	 */
	public function get_region( $region ) {
		return in_array( $region, self::REGIONS, true ) ? $region : self::REGION_NORTH_VIRGINIA;
	}

	/**
	 * Uploads.
	 * 
	 * @param string      $bucket         Bucket.
	 * @param string      $filename       Filename.
	 * @param string|null $upload_path    Upload path.
	 * @param bool        $put_public_acl Put public ACL.
	 * @throws \InvalidArgumentException $filename has to exist.
	 * @throws \S3Exception              Failed to put object.
	 */
	public function upload( $bucket, $filename, $upload_path = null, $put_public_acl = false ) {
		if ( ! file_exists( $filename ) ) {
			throw new \InvalidArgumentException( '$filename has to exist. $filename = ' . $filename );
		}
		if ( ! $upload_path ) {
			$upload_path = $filename;
		}
		$args = array_merge(
			$this->options,
			array(
				'Bucket'      => $bucket,
				'Key'         => $upload_path,
				'Body'        => $this->file_body( $filename ),
				'ContentType' => $this->mime_type( $filename ),
			)
		);
		/**
		 * Backward compatibility:
		 * - Setting permissions for website access - Amazon Simple Storage Service
		 *   https://docs.aws.amazon.com/AmazonS3/latest/userguide/WebsiteAccessPermissionsReqd.html#object-acl
		 */
		if ( $put_public_acl ) {
			$args['ACL'] = 'public-read';
		}
		try {
			return $this->s3->putObject( $args );
		} catch ( S3Exception $e ) {
			Static_Press_S3_Log::log( $e );
			// To allow fail, StaticPress2019 still seems to fail dump in some case...
			// throw $e;
			return false;
		}
	}

	/**
	 * Downloads.
	 * 
	 * @param string $key           Key.
	 * @param string $download_path Download path.
	 * @param string $bucket        Bucket.
	 */
	public function download($key, $download_path = null, $bucket = null) {
		try {
			if (!$download_path)
				$download_path = dirname(__FILE__).'/'.basename($key);
			$args = array_merge($this->options, array(
				'Key'         => $key,
				));
			if (isset($bucket))
				$args['Bucket'] = $bucket;
			if (!isset($args['Bucket']))
				return false;
			$response = $this->s3->getObject($args);
			$response['Body']->rewind();
			file_put_contents($download_path, $response['Body']->read($response['ContentLength']));
			return $response;
		} catch (S3Exception $e) {
			Static_Press_S3_Log::log( $e );
			return false;
		}
	}

	/**
	 * Deletes.
	 * 
	 * @param string $upload_path Upload path.
	 * @param string $bucket      Bucket.
	 */
	public function delete($upload_path, $bucket = null) {
		try {
			$args = array_merge($this->options, array(
				'Key'         => $upload_path,
				));
			if (isset($bucket))
				$args['Bucket'] = $bucket;
			if (!isset($args['Bucket']))
				return false;
			$response = $this->s3->deleteObject($args);
			return $response;
		} catch (S3Exception $e) {
			Static_Press_S3_Log::log( $e );
			return false;
		}
	}

	/**
	 * Lists buckets.
	 * 
	 * @return array[]|false List of buckets when succeed, otherwise false.
	 */
	public function list_buckets() {
		try {
			$list_buckets = $this->s3->listBuckets();
			return isset( $list_buckets['Buckets'] ) ? $list_buckets['Buckets'] : false;
		} catch ( S3Exception $e ) {
			Static_Press_S3_Log::log( $e );
			return false;
		} catch ( CredentialsException $e ) {
			Static_Press_S3_Log::log( $e );
			return false;
		}
	}

	/**
	 * Does bucket exists.
	 * 
	 * @param string $bucket    Bucket.
	 * @param bool   $accept403 Whether accept 403 error or not.
	 * @return bool Whether bucket exist or not.
	 */
	public function bucket_exists( $bucket, $accept403 = true ) {
		return $this->s3->doesBucketExist( $bucket, $accept403 );
	}

	/**
	 * Does object exists.
	 * 
	 * @param string $key Key.
	 * @return bool Whether object exist or not.
	 */
	public function object_exists($key) {
		if (!isset($this->options['Bucket']))
			return false;
		return $this->s3->doesObjectExist($this->options['Bucket'], $key);
	}

	/**
	 * Gets file_body.
	 * 
	 * @param string $filename File name.
	 * @return string File body.
	 */
	private function file_body($filename) {
		$filebody =
			file_exists($filename)
			? fopen($filename, 'r')
			: null;
		return $filebody;
 	}

	/**
	 * Gets mime type.
	 * 
	 * @param string $filename Path to file.
	 * @return string Mime type.
	 */
	private function mime_type( $filename ) {
		static $info;
		if ( ! isset( $info ) ) {
			$finfo_factory = new Static_Press_S3_Finfo_Factory();
			$info          = $finfo_factory->create( self::get_magic_file() );
		}
		$mime_type         = file_exists( $filename ) ? $info->file( $filename ) : false;
		$mime_type_checker = new Static_Press_S3_Mime_Type_Checker( $filename, $mime_type );
		return $mime_type_checker->get_mime_type();
	}

	/**
	 * Gets magic file.
	 * 
	 * @return string|null Magic file.
	 */
	public static function get_magic_file() {
		$array_candidate_magic_file = array( '/usr/share/misc/magic', '/usr/share/file/magic', '/etc/magic' );
		foreach ( $array_candidate_magic_file as $candidate_magic_file ) {
			if ( is_file( $candidate_magic_file ) ) {
				return $candidate_magic_file;
			}
		}
		return null;
	}
}
