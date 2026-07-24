<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Multisite\Contracts;

interface Site_Repository_Interface {
	/**
	 * Get all site IDs in the network.
	 *
	 * When not network-activated, returns only the current blog ID.
	 *
	 * @return array<int>
	 */
	public function get_all_site_ids(): array;

	/**
	 * Get the display name of a site by its ID.
	 *
	 * @param int $site_id The blog ID.
	 */
	public function get_site_name( int $site_id ): string;
}
