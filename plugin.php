<?php
/**
 * StaticPress2019-S3
 * 
 * @package     static_press_s3
 * Plugin Name: StaticPress2019-S3
 * Author: wokamoto
 * Plugin URI: https://github.com/megumiteam/staticpress-s3
 * Description: StaticPress -> S3.
 * Version: 0.1.1
 * Author URI: http://www.digitalcube.jp/
 * Text Domain: static-press-S3
 * Domain Path: /languages
 * 
 * License:
 *  Released under the GPL license
 *   http://www.gnu.org/copyleft/gpl.html
 * 
 *   Copyright 2013 (email : wokamoto1973@gmail.com)
 * 
 *     This program is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with this program; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'STATIC_PRESS_S3_PLUGIN_DIR' ) ) {
	/**
	 * Plugin Directory.
	 *
	 * @var string $STATIC_PRESS_S3_PLUGIN_DIR Plugin folder directory path. Eg. `/var/www/html/web/app/plugins/staticpress2019-s3/`
	 */
	define( 'STATIC_PRESS_S3_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Since this file includes multiple times when unit test.
if ( ! function_exists( 'static_press_s3_run' ) ) {
	/**
	 * Runs StaticPress S3.
	 */
	function static_press_s3_run() {
		global $staticpress;
		if ( ! isset( $staticpress ) ) {
			return;
		}
		require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-S3_helper.php';
		require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-staticpress_s3_admin.php';
		require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-staticpress_s3.php';
		new staticpress_s3( staticpress_s3_admin::get_option() );
		if ( is_admin() ) {
			new staticpress_s3_admin();
		}
	};
}

add_action( 'plugins_loaded', 'static_press_s3_run' );
