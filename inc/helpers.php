<?php
/**
 * PowerPack for MemberMouse LITE - Helpers class
 * @since 1.0.0
 * @author Mintun Media
 */

if (!defined('ABSPATH')) {
	exit;
}

class PowerPack_Lite_Helpers {

	private static $instance;

	public static function instance() {

		// Check instance
		if (!isset(self::$instance))
			self::$instance = new self;

		// Done
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {}

  /**
   * Outputs settings checkboxes
   */
	public function ppfml_output_setting_checkbox( $class, $option_key, $value, $data_div, $text, $options ) {
		if ( empty( $option_key ) ) {
			return;
		}
		?><input
		type="checkbox"
		class="<?php echo $class ?>"
		name="powerpack-plugin-options[<?php echo $option_key ?>]"
		value="<?php echo $value ?>" <?php isset( $options[ $option_key ] ) ? checked( $options[ $option_key ], 1 ) : ''; ?>
		data-div="<?php echo $data_div ?>"
		/>
		<?php echo $text;
	}

  /**
   * Outputs settings Radio Boxes
   */
	public function ppfml_output_setting_radio( $option_key, $value, $text, $options ) {
		if ( empty( $option_key ) ) {
			return;
		}
		?>
		<input type="radio"
			name="powerpack-plugin-options[<?php echo $option_key ?>]"
			value="<?php echo $value ?>" <?php isset( $options[ $option_key ] ) ? checked( $options[ $option_key ], $value ) : ''; ?> >
		<?php echo $text;
	}

} // End Class

PowerPack_Lite_Helpers::instance();