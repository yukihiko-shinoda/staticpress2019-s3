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
	 * S3 Helper.
	 * 
	 * @var Static_Press_S3_Helper
	 */
	private $s3;
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
	 */
	public function __construct( $options ) {
		self::$instance = $this;
		$this->options  = $options;
		add_action( 'StaticPress::file_put', array( $this, 's3_upload' ), 10, 2 );
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
	 * @param string $url S3 key.
	 */
	public function s3_upload( $file_name, $url ) {
		$this->s3 = $this->s3();
		if ( ! $this->s3 ) {
			return false;
		}
		$s3_bucket = isset( $this->options['bucket'] ) ? $this->options['bucket'] : false;
		if ( isset( $s3_bucket ) && $this->s3->current_bucket() !== $s3_bucket ) {
			$this->s3->set_current_bucket( $s3_bucket );
		}
		$s3_key = preg_replace( '#^(https?://[^/]+/|/)#i', '', urldecode( $url ) );
		if ( ! file_exists( $file_name ) ) {
			return false;
		}
		return $this->s3->upload( $file_name, $s3_key );
	}

	/** 
	 * Initializing S3 object.
	 * 
	 * @return Static_Press_S3_Helper|false
	 */
	private function s3() {
		if ( isset( $this->s3 ) ) {
			return $this->s3;
		}
		if ( ! $this->options ) {
			return false;
		}
		return new Static_Press_S3_Helper(
			isset( $this->options['access_key'] ) ? $this->options['access_key'] : null,
			isset( $this->options['secret_key'] ) ? $this->options['secret_key'] : null,
			isset( $this->options['region'] ) ? $this->options['region'] : null,
			isset( $this->options['endpoint'] ) ? $this->options['endpoint'] : null
		);
	}

	// Download file to S3
	private function s3_download($filename, $S3_bucket, $S3_key){
		$download_result = false;
		if ($s3 = $this->s3($S3_bucket)) {
			if (!$s3->object_exists($S3_key))
				return false;
			$download_result = $s3->download($S3_key, $filename);
			if (self::$debug_mode && function_exists('dbgx_trace_var')) {
				dbgx_trace_var($download_result);
			}
		}
		return $download_result;
	}

	// Delete S3 object
	private function s3_delete($S3_bucket, $S3_key){
		$delete_result = false;
		if ($s3 = $this->s3($S3_bucket)) {
			$delete_result =
				$s3->object_exists($S3_key)
				? $s3->delete($S3_key)
				: true;
			if (self::$debug_mode && function_exists('dbgx_trace_var')) {
				dbgx_trace_var($delete_result);
			}
		}
		return $delete_result;
	}
}
