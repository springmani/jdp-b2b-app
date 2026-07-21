<?php

namespace AtlasSearch\Support\WordPress;

function is_network_activated( $plugin ) {
	$plugins = get_site_option( 'active_sitewide_plugins' );

	return is_multisite() && isset( $plugins[ $plugin ] );
}
