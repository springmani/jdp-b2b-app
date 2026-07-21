<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Multisite\Contracts;

interface Multisite_Environment_Interface {
	/**
	 * Check if the WordPress installation is a multisite network.
	 */
	public function is_multisite(): bool;

	/**
	 * Check if the plugin is network-activated across all sites.
	 */
	public function is_network_activated(): bool;

	/**
	 * Get the current blog ID in the multisite network.
	 */
	public function get_current_blog_id(): int;
}
