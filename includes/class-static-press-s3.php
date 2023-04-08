<?php
/**
 * Class Static_Press_S3
 *
 * @package static_press_s3\includes
 */

namespace static_press_s3\includes;

/**
 * StaticPress S3.
 */
class Static_Press_S3 {
	static $debug_mode  = false;
	static $instance;

	/**
	 * S3 batch PutObject.
	 * 
	 * @var Static_Press_S3_Batch_Put_Object|null
	 */
	private $s3_batch_put_object;
	/**
	 * S3 Helper.
	 * 
	 * @var Static_Press_S3_Helper|null
	 */
	private $s3_helper;
	/**
	 * This plugin options.
	 * 
	 * @var string[]
	 */
	private $options = array();

	/**
	 * Inits.
	 * 
	 * @param string[] $options Options set in Admin page.
	 * @throws \InvalidArgumentException In case when $option is empty.
	 */
	public function __construct( $options ) {
		self::$instance = $this;
		if ( ! $options ) {
			throw new \InvalidArgumentException( '$options can\'t be empty.' );
		}
		$this->options = $options;
		add_action( 'StaticPress::file_put', array( $this, 's3_upload' ), 10, 2 );
	}

	/** 
	 * Get S3 object (singleton).
	 * 
	 * @return Static_Press_S3_Batch_Put_Object
	 */
	private function get_s3_batch_put_object() {
		if ( isset( $this->s3_batch_put_object ) ) {
			return $this->s3_batch_put_object;
		}
		$s3_helper = new Static_Press_S3_Helper(
			$this->options['access_key'],
			$this->options['secret_key'],
			isset( $this->options['region'] ) ? $this->options['region'] : null,
			isset( $this->options['endpoint'] ) ? $this->options['endpoint'] : null
		);
		return new Static_Press_S3_Batch_Put_Object( $s3_helper, $this->options['bucket'] );
	}

	/**
	 * Returns plugin base name.
	 * 
	 * @return string
	 */
	public static function plugin_basename() {
		return plugin_basename( dirname( dirname( __FILE__ ) ) . '/plugin.php' );
	}

	/** 
	 * Uploads file to S3.
	 * 
	 * @param string $file_name File name.
	 * @param string $url       S3 key.
	 */
	public function s3_upload( $file_name, $url ) {
		$this->s3_batch_put_object = $this->get_s3_batch_put_object();
		$upload_path               = preg_replace( '#^(https?://[^/]+/|/)#i', '', urldecode( $url ) );
		return $this->s3_batch_put_object->upload( $file_name, $upload_path );
	}

	// Download file to S3
	private function s3_download($filename, $S3_bucket, $S3_key){
		if (!$s3_helper->object_exists($S3_key))
			return false;
		$download_result = $s3_helper->download($S3_key, $filename);
		if (self::$debug_mode && function_exists('dbgx_trace_var')) {
			dbgx_trace_var($download_result);
		}
		return $download_result;
	}

	// Delete S3 object
	private function s3_delete($S3_bucket, $S3_key){
		$delete_result =
			$s3_helper->object_exists($S3_key)
			? $s3_helper->delete($S3_key)
			: true;
		if (self::$debug_mode && function_exists('dbgx_trace_var')) {
			dbgx_trace_var($delete_result);
		}
		return $delete_result;
	}
}
