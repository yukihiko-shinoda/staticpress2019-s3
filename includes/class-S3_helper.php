<?php
require_once(dirname(__FILE__).'/aws.phar');
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-mime-type-checker.php';
use static_press_s3\includes\Static_Press_S3_Mime_Type_Checker;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

/**
 * Region list.
 * 
 * @see https://docs.aws.amazon.com/general/latest/gr/s3.html
 * @see https://docs.aws.amazon.com/govcloud-us/latest/UserGuide/using-govcloud-endpoints.html
 */
const REGIONS = [
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
];

class S3_helper {
	const REGION_NORTH_VIRGINIA = 'us-east-1';
	private $s3;
	private $options = array(
		'Bucket' => '',
		'StorageClass' => 'STANDARD',
		'ACL' => 'public-read',
		);

	function __construct($access_key = null, $secret_key = null, $region = null) {
		if ($access_key && $secret_key) {
			$this->init_s3($access_key, $secret_key, $region);
		}
	}

	public function init_s3($access_key, $secret_key, $region = null){
		if ( !isset($region) )
			$region = 'ap-northeast-1';
	
		$s3 = new Aws\S3\S3Client([
			'credentials' => [
				'key' => $access_key,
				'secret' => $secret_key,
			],
			'region' => $this->get_region($region),
			'version' => 'latest',
		]);
		$this->s3 = $s3;
		return $s3;
	}

	public function get_regions() {
		return REGIONS;
	}

	public function get_region($region) {
		return in_array($region, REGIONS, true) ? $region : self::REGION_NORTH_VIRGINIA;
	}

	public function set_option($option_array){
		if (!is_array($option_array))
			return false;
		$this->options = array_merge($this->options, $option_array);
	}

	// S3 Upload
	public function upload($filename, $upload_path = null, $bucket = null) {
		if (!file_exists($filename) || !$this->s3)
			return false;

		try {
			if (!$upload_path)
				$upload_path = $filename;
			$args = array_merge($this->options, array(
				'Key'         => $upload_path,
				'Body'        => $this->file_body($filename),
				'ContentType' => $this->mime_type($filename),
				));
			if (isset($bucket))
				$args['Bucket'] = $bucket;
			if (!isset($args['Bucket']))
				return false;
			$response = $this->s3->putObject($args);
			return $response;
		} catch (S3Exception $e) {
			return false;
		}
	}

	// S3 Download
	public function download($key, $download_path = null, $bucket = null) {
		if (!$this->s3)
			return false;

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
			return false;
		}
	}

	// S3 Delete
	public function delete($upload_path, $bucket = null) {
		if (!$this->s3)
			return false;

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
			return false;
		}
	}

	// list buckets
	public function list_buckets() {
		if (!isset($this->s3))
			return false;
		try {
			$list_buckets = $this->s3->listBuckets();
			return isset($list_buckets["Buckets"]) ? $list_buckets["Buckets"] : false;
		} catch (S3Exception $e) {
			return false;
		}
	}

	// return current bucket
	public function current_bucket(){
		return isset($this->options['Bucket']) ? $this->options['Bucket'] : false;
	}

	// set current bucket
	public function set_current_bucket($bucket){
		if ($this->bucket_exists($bucket)) {
			$this->options['Bucket'] = $bucket;
			return $bucket;
		} else {
			return false;
		}
	}

	// does Bucket exists
	public function bucket_exists($bucket = null, $accept403 = true) {
		if (!isset($this->s3))
			return false;
		if (!isset($bucket))
			$bucket = isset($this->options['Bucket']) ? $this->options['Bucket'] : false;
		return $bucket ? $this->s3->doesBucketExist($bucket, $accept403) : false;
	}

	// does Object exists
	public function object_exists($key) {
		if (!isset($this->s3))
			return false;
		if (!isset($this->options['Bucket']))
			return false;
		return $this->s3->doesObjectExist($this->options['Bucket'], $key);
	}

	// get file_body
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
			$magic_file = '/usr/share/misc/magic';
			$info       = file_exists( $magic_file )
			? new FInfo( FILEINFO_MIME_TYPE, $magic_file )
			: new FInfo( FILEINFO_MIME_TYPE );
		}
		$mime_type =
			file_exists( $filename )
			? $info->file( $filename )
			: false;

		$mime_type_checker = new Static_Press_S3_Mime_Type_Checker( $filename, $mime_type );
		return $mime_type_checker->get_mime_type();
	}
}
