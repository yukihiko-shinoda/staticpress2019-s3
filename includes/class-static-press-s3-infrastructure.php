<?php
/**
 * Class Static_Press_S3_Infrastructure
 *
 * @package static_press_s3\includes
 */

namespace static_press_s3\includes;

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-finfo-factory.php';
require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-mime-type-checker.php';
use static_press_s3\includes\Static_Press_S3_Finfo_Factory;
use static_press_s3\includes\Static_Press_S3_Mime_Type_Checker;

/**
 * S3 Infrastructure.
 */
class Static_Press_S3_Infrastructure {
	/**
	 * Gets mime type.
	 * 
	 * @param string $filename Path to file.
	 * @return string Mime type.
	 */
	public static function mime_type( $filename ) {
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
