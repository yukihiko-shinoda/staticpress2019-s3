<?php
/**
 * Class Static_Press_S3_Mime_Type_Checker
 *
 * @package static_press\includes
 */

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
	 */
	public function get_mime_type() {
		if ( 'text/plain' != $this->mime_type ) {
			return $this->mime_type;
		}

		return $this->get_mime_type_for_text( $this->path_to_file, $this->mime_type );
	}

	/**
	 * Gets mime type for text.
	 * 
	 * @return string Extension.
	 */
	private function get_mime_type_for_text() {
		switch ( $this->get_extension() ) {
			case 'css':
				return 'text/css';
			case 'js':
				return 'application/x-javascript';
			case 'html':
				return 'text/html';
			case 'xml':
				return 'application/xml';
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
		return array_key_exists( 'extension', $path_parts ) ? $path_parts['extension'] : '';
	}
}
