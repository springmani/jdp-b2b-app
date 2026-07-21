<?php

namespace AtlasSearch\Support\WordPress;

/**
 * Use the correct set transient function
 *
 * @param string $transient The transient name.
 * @param mixed  $value The transient value.
 * @param int    $expiration Time until expiration in seconds.
 *
 * @return bool True if the transient was set, false otherwise.
 */
function set_transient( $transient, $value, $expiration = 0 ) {
	return is_wpe_smart_search_network_activated() ? \set_site_transient(
		$transient,
		$value,
		$expiration
	) : \set_transient( $transient, $value, $expiration );
}

/**
 * Use the correct delete transient function
 *
 * @param string $transient The transient name.
 *
 * @return bool
 */
function delete_transient( $transient ) {
	return is_wpe_smart_search_network_activated() ? \delete_site_transient( $transient ) : \delete_transient(
		$transient
	);
}

/**
 * Use the correct get transient function
 *
 * @param string $transient The transient name.
 *
 * @return mixed Value of transient.
 */
function get_transient( $transient ) {
	return is_wpe_smart_search_network_activated() ? \get_site_transient( $transient ) : \get_transient( $transient );
}
