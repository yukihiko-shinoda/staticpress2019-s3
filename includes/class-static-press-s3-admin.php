<?php
/**
 * Class Static_Press_S3_Admin
 *
 * @package static_press_s3\includes
 */

namespace static_press_s3\includes;

require_once STATIC_PRESS_S3_PLUGIN_DIR . 'includes/class-static-press-s3-helper.php';

use InputValidator;
use static_press_s3\includes\Static_Press_S3_Helper;

/**
 * StaticPress S3 Admin page.
 */
class Static_Press_S3_Admin {
	const OPTION_KEY   = 'staticpress_s3';
	const OPTION_PAGE  = 'staticpress_s3';
	const TEXT_DOMAIN  = 'staticpress_s3';

	const NONCE_ACTION = 's3_update_options';
	const NONCE_NAME   = '_wpnonce_s3_update_options';

    static $debug_mode = false;
    static $instance;

	private $options = array();
	private $admin_action;

	/**
	 * Constructor.
	 */
	public function __construct() {
		self::$instance = $this;
		$this->options  = $this->get_option();
		add_action( 'StaticPress::options_save', array( $this, 'options_save' ) );
		add_action( 'StaticPress::options_page', array( $this, 'options_page' ) );
	}

	/**
	 * Gets option array.
	 * 
	 * @return array Option array.
	 */
	public static function option_keys() {
		return array(
			'access_key'     => __( 'AWS Access Key', 'staticpress_s3' ),
			'secret_key'     => __( 'AWS Secret Key', 'staticpress_s3' ),
			'region'         => __( 'AWS Region', 'staticpress_s3' ),
			'endpoint'       => __( 'S3 Endpoint', 'staticpress_s3' ),
			'bucket'         => __( 'S3 Bucket', 'staticpress_s3' ),
			'put_object_acl' => __( 'Put Object ACL', 'staticpress_s3' ),
		);
	}

	/**
	 * Gets option.
	 * 
	 * @return array Option.
	 */
	public static function get_option() {
		$options = get_option( self::OPTION_KEY );
		foreach ( array_keys( self::option_keys() ) as $key ) {
			if ( ! isset( $options[ $key ] ) || is_wp_error( $options[ $key ] ) ) {
				$options[ $key ] = '';
			}
		}
		return $options;
	}

	/**
	 * Saves options.
	 */
	public function options_save() {
		$option_keys   = $this->option_keys();
		$this->options = $this->get_option();

		$iv = new InputValidator( 'POST' );
		$iv->set_rules( self::NONCE_NAME, 'required' );

		if ( is_wp_error( $iv->input( self::NONCE_NAME ) ) || ! check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME ) ) {
			return;
		}
		// Get posted options.
		$fields = array_keys( $option_keys );
		foreach ( $fields as $field ) {
			switch ( $field ) {
				case 'access_key':
				case 'secret_key':
					$iv->set_rules( $field, array( 'trim', 'esc_html', 'required' ) );
					break;
				default:
					$iv->set_rules( $field, array( 'trim', 'esc_html' ) );
					break;
			}
		}
		$options     = $iv->input( $fields );
		$err_message = '';
		foreach ( $option_keys as $key => $field ) {
			if ( is_wp_error( $options[ $key ] ) ) {
				$error_data = $options[ $key ];
				$err        = '';
				foreach ( $error_data->errors as $errors ) {
					foreach ( $errors as $error ) {
						$err .= ( ! empty( $err ) ? '<br />' : '' ) . __( 'Error! : ', 'staticpress_s3' );
						$err .= sprintf(
							str_replace( $key, '%s', $error ),
							$field
						);
					}
				}
				$err_message .= ( ! empty( $err_message ) ? '<br />' : '' ) . $err;
			}
			if ( ! isset( $options[ $key ] ) || is_wp_error( $options[ $key ] ) ) {
				$options[ $key ] = '';
			}
		}
		if ( self::$debug_mode && function_exists( 'dbgx_trace_var' ) ) {
			dbgx_trace_var( $options );
		}

		// Update options.
		if ( $this->options !== $options ) {
			update_option( self::OPTION_KEY, $options );
			printf(
				'<div id="message" class="updated fade"><p><strong>%s</strong></p></div>' . "\n",
				empty( $err_message ) ? __( 'Done!', 'staticpress_s3' ) : $err_message
			);
			$this->options = $options;
		}
		unset( $options );
	}

	/**
	 * Renders option page.
	 */
	public function options_page() {
		$option_keys   = $this->option_keys();
		$this->options = $this->get_option();

		// Get S3 Object.
		$s3      = new Static_Press_S3_Helper(
			! empty( $this->options['access_key'] ) ? $this->options['access_key'] : null,
			! empty( $this->options['secret_key'] ) ? $this->options['secret_key'] : null,
			! empty( $this->options['region'] ) ? $this->options['region'] : null,
			! empty( $this->options['endpoint'] ) ? $this->options['endpoint'] : null
		);
		$regions = $s3->get_regions();
		$buckets = $s3->list_buckets();
		$this->render_options_page( $option_keys, $regions, $buckets );
	}
	/**
	 * Renders options page.
	 * 
	 * @param string[]       $option_keys Option keys.
	 * @param string[]       $regions     Regions.
	 * @param string[]|false $buckets     S3 buckets.
	 */
	private function render_options_page( $option_keys, $regions, $buckets ) {
		?>
		<div class="wrap">
		<h2><?php echo esc_html( __( 'StaticPress S3 Option', 'staticpress_s3' ) ); ?></h2>
		<form method="post" action="<?php echo $this->admin_action; ?>">
		<?php echo wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME, true, false ) . "\n"; ?>
		<table class="wp-list-table fixed"><tbody>
		<?php
		$this->input_field( 'access_key', $option_keys['access_key'], $this->input_field_default( 'access_key' ) );
		$this->input_field( 'secret_key', $option_keys['secret_key'], $this->input_field_default( 'secret_key' ) );
		$this->input_field( 'region', $option_keys['region'], $this->input_field_region( 'region', $regions ) );
		if ( 'other' != $this->options['region'] ) {
			unset( $option_keys['endpoint'] );
		} else {
			$this->input_field( 'endpoint', $option_keys['endpoint'], $this->input_field_default( 'endpoint' ) );
		}
		if ( ! $buckets ) {
			unset( $option_keys['bucket'] );
		} else {
			$this->input_field( 'bucket', $option_keys['bucket'], $this->input_field_bucket( 'bucket', $buckets ) );
		}
		$this->input_field( 'put_object_acl', $option_keys['put_object_acl'], $this->input_field_checkbox( 'put_object_acl' ) );
		?>
		</tbody></table>
		<?php submit_button(); ?>
		</form>
		</div>
		<?php
	}

	/**
	 * Renders input fields.
	 * 
	 * @param string $field       Field.
	 * @param string $label       Label.
	 * @param string $input_field Input filed.
	 */
	private function input_field( $field, $label, $input_field ) {
		$label = sprintf( '<th><label for="%1$s">%2$s</label></th>' . "\n", $field, $label );
		echo "<tr>\n{$label}{$input_field}</tr>\n";
	}

	/**
	 * Renders input fields.
	 * 
	 * @param string $field Field.
	 * @return string
	 */
	private function input_field_default( $field ) {
		return sprintf( '<td><input type="text" name="%1$s" value="%2$s" id="%1$s" size=100 /></td>' . "\n", $field, esc_attr( $this->options[ $field ] ) );
	}
	/**
	 * Renders input fields.
	 * 
	 * @param string $field Field.
	 * @return string
	 */
	private function input_field_checkbox( $field ) {
		return sprintf( '<td><input type="checkbox" name="%1$s" id="%1$s"%2$s /></td>' . "\n", $field, $this->options[ $field ] ? ' checked' : '' );
	}
	/**
	 * Renders input fields.
	 * 
	 * @param string   $field   Field.
	 * @param string[] $regions Regions.
	 * @return string
	 */
	private function input_field_region( $field, $regions ) {
		if ( ! ( $regions && count( $regions ) > 0 ) ) {
			return '';
		}
		$input_field  = sprintf( '<td><select name="%1$s">', $field );
		$input_field .= '<option value=""></option>';
		foreach ( $regions as $region ) {
			$input_field .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $region ),
				$region == $this->options[ $field ] ? ' selected' : '',
				$region
			);
		}
		$input_field .= '</select></td>';
		return $input_field;
	}

	/**
	 * Renders input fields.
	 * 
	 * @param string         $field   Region.
	 * @param string[]|false $buckets Bucket.
	 * @return string
	 */
	private function input_field_bucket( $field, $buckets ) {
		if ( ! ( $buckets && count( $buckets ) > 0 ) ) {
			return '';
		}
		$input_field  = sprintf( '<td><select name="%1$s">', $field );
		$input_field .= '<option value=""></option>';
		foreach ( $buckets as $bucket ) {
			$input_field .= sprintf(
				'<option value="%1$s"%2$s>%1$s</option>',
				esc_attr( $bucket['Name'] ),
				$bucket['Name'] == $this->options[ $field ] ? ' selected' : ''
			);
		}
		$input_field .= '</select></td>';
		return $input_field;
	}
}
