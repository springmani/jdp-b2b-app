<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Multisite;

use WPE\AITK\WP\Multisite\Contracts\Multisite_Environment_Interface;

final class WP_Multisite_Environment implements Multisite_Environment_Interface {
	private string $plugin_basename;

	public function __construct( string $plugin_basename ) {
		$this->plugin_basename = $plugin_basename;
	}

	public function is_multisite(): bool {
		// phpcs:ignore Generic.PHP.ForbiddenFunctions.FoundWithAlternative
		return is_multisite();
	}

	public function is_network_activated(): bool {
		if ( ! $this->is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );

		return isset( $plugins[ $this->plugin_basename ] );
	}

	public function get_current_blog_id(): int {
		// phpcs:ignore Generic.PHP.ForbiddenFunctions.FoundWithAlternative
		return get_current_blog_id();
	}
}
