<?php

namespace AtlasSearch\Support\WordPress;

/**
 * Use the correct update option function
 *
 * @param string $option The option name.
 * @param mixed  $value The option value.
 * @param ?bool  $autoload Optional. Whether to load the option when WordPress starts up. For existing options, $autoload can only be updated using update_option() if $value is also changed. Accepts true to enable or false to disable. For non-existent options, the default value is 'yes'. Default null.
 *
 * @return bool True if the value was updated, false otherwise.
 */
function update_option( $option, $value, $autoload = null ) {
	return is_wpe_smart_search_network_activated() ? \update_site_option( $option, $value ) : \update_option(
		$option,
		$value,
		$autoload
	);
}

/**
 * Use the correct delete option function
 *
 * @param string $option The option name.
 *
 * @return bool
 */
function delete_option( $option ) {
	return is_wpe_smart_search_network_activated() ? \delete_site_option( $option ) : \delete_option( $option );
}

/**
 * Use the correct get option function
 *
 * @param string $option The option name.
 * @param mixed  $default Optional. Default value to return if the option does not exist. Default false.
 *
 * @return mixed Value set for the option.
 */
function get_option( $option, $default = false ) {
	return is_wpe_smart_search_network_activated() ? \get_site_option( $option, $default ) : \get_option(
		$option,
		$default
	);
}
