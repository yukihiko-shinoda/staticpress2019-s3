<?php
/**
 * Class Static_Press_S3_Batch_Put_object
 *
 * @package static_press_s3\includes
 */

namespace static_press_s3\includes;

/**
 * S3 Batch Put Object to prevent multiple execution of S3 bucket validation in multiple execution of PutObject.
 */
class Static_Press_S3_Batch_Put_Object {
	/**
	 * S3 helper.
	 * 
	 * @var Static_Press_S3_Helper
	 */
	private $s3_helper;
	/**
	 * S3 bucket.
	 * 
	 * @var string
	 */
	private $bucket;
	/**
	 * Constructor.
	 * 
	 * @param Static_Press_S3_Helper $s3_helper S3 helper.
	 * @param string                 $bucket    S3 bucket.
	 * @throws \InvalidArgumentException S3 Bucket doesn't exist.
	 */
	public function __construct( $s3_helper, $bucket ) {
		$this->s3_helper = $s3_helper;
		if ( ! $this->s3_helper->bucket_exists( $bucket ) ) {
			throw new \InvalidArgumentException( "S3 Bucket doesn't exist." );
		};
		$this->bucket = $bucket;
	}

	/**
	 * Uploads.
	 * 
	 * @param string      $filename    Filename.
	 * @param string|null $upload_path Upload path.
	 */
	public function upload( $filename, $upload_path = null ) {
		$this->s3_helper->upload( $this->bucket, $filename, $upload_path );
	}
}