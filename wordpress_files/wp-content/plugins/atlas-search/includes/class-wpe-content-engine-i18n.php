<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/includes
 */
class Wpe_Content_Engine_I18n {
	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'smart-search',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
