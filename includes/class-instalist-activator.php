<?php
/**
 * Fired during plugin activation
 *
 * @link       https://codingfix.com
 * @since      1.0.0
 *
 * @package    Instalist
 * @subpackage Instalist/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Instalist
 * @subpackage Instalist/includes
 * @author     Marco Gasi <info@codingfix.com>
 */
class Instalist_Activator {


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$defaults         = array();
		$options          = get_option( 'inslst_options', array() );
		$options_to_store = array_merge( $defaults, $options );
		update_option( 'inslst_options', $options_to_store );
	}
}
