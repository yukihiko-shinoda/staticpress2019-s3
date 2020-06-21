<?php
/**
 * Class Static_Press_S3_Mime_Type_Checker
 *
 * @package static_press_s3\includes
 */

namespace static_press_s3\includes;

/**
 * Myme type checker.
 */
class Static_Press_S3_Mime_Type_Checker {
	/**
	 * Path to file.
	 * 
	 * @var string
	 */
	private $path_to_file;

	/**
	 * Mime type.
	 * 
	 * @var string|false
	 */
	private $mime_type;

	/**
	 * Constructor.
	 * 
	 * @param string       $path_to_file Path to file.
	 * @param string|false $mime_type    Mime type.
	 */
	public function __construct( $path_to_file, $mime_type ) {
		$this->path_to_file = $path_to_file;
		$this->mime_type    = $mime_type;
	}

	/**
	 * Gets mime type.
	 * 
	 * @return string Mime type.
	 */
	public function get_mime_type() {
		switch ( $this->mime_type ) {
			case 'text/plain':
				return $this->get_mime_type_for_text();
			case 'application/octet-stream':
				return $this->get_mime_type_for_media();
			default:
				return $this->mime_type;
		}
	}

	/**
	 * Gets mime type for text.
	 * 
	 * @return string Mime type.
	 */
	private function get_mime_type_for_text() {
		switch ( $this->get_extension() ) {
			case 'css':
				return 'text/css';
			case 'js':
				return 'application/javascript';
			case 'htm':
			case 'html':
				return 'text/html';
			case 'svg':
				return 'image/svg+xml';
			case 'xml':
				return 'application/xml';
			default:
				return $this->mime_type;
		}
	}

	/**
	 * Gets mime type for media.
	 * 
	 * @return string Mime type.
	 */
	private function get_mime_type_for_media() {
		switch ( $this->get_extension() ) {
			case 'aac':
				return 'audio/aac';
			case 'flac':
				return 'audio/flac';
			case 'gif':
				return 'image/gif';
			case 'jpe':
			case 'jpeg':
			case 'jpg':
				return 'image/jpeg';
			case 'm4a':
				return 'audio/m4a';
			case 'mp3':
				return 'audio/mpeg';
			case 'png':
				return 'image/png';
			case 'wav':
				return 'audio/wav';
			default:
				return $this->mime_type;
		}
	}

	/**
	 * Gets extension.
	 * 
	 * @return string Extension.
	 */
	private function get_extension() {
		$path_parts = pathinfo( $this->path_to_file );
		return array_key_exists( 'extension', $path_parts ) ? strtolower( $path_parts['extension'] ) : '';
	}
}
