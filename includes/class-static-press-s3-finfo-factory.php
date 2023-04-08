<?php
/**
 * Class Static_Press_S3_Finfo_Factory
 *
 * @package static_press_s3\includes
 */

namespace static_press_s3\includes;

use FInfo;
/**
 * Finfo factory.
 */
class Static_Press_S3_Finfo_Factory {
	/**
	 * Creates finfo.
	 * 
	 * @param string|null $magic_file Name of a magic database file,
	 *                           usually something like /path/to/magic.mime.
	 *                           If not specified, the MAGIC environment variable is used.
	 *                           If the environment variable isn't set,
	 *                           then PHP's bundled magic database will be used.
	 *                           Passing NULL or an empty string will be equivalent to the default value.
	 * @return FInfo finfo.
	 */
	public function create( $magic_file ) {
		switch ( true ) {
			case false !== getenv( 'MAGIC' ) && file_exists( getenv( 'MAGIC' ) ):
			case is_null( $magic_file ):
			case ! file_exists( $magic_file ):
			case is_dir( $magic_file ):
				return $this->create_without_file();
			default:
				return $this->create_with_file( $magic_file );
		}
	}

	/**
	 * Creates finfo.
	 * 
	 * @param string $magic_file Name of a magic database file,
	 *                           usually something like /path/to/magic.mime.
	 *                           If not specified, the MAGIC environment variable is used.
	 *                           If the environment variable isn't set,
	 *                           then PHP's bundled magic database will be used.
	 *                           Passing NULL or an empty string will be equivalent to the default value.
	 * @return FInfo finfo.
	 */
	public function create_with_file( $magic_file ) {
		return new FInfo( FILEINFO_MIME_TYPE, $magic_file );
	}

	/**
	 * Creates finfo.
	 * 
	 * @return FInfo finfo.
	 */
	public function create_without_file() {
		return new FInfo( FILEINFO_MIME_TYPE );
	}
}
