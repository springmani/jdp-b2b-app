<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Multisite\Contracts;

interface Site_Context_Interface {
	/**
	 * Switch to a specific site's context.
	 *
	 * When not network-activated, this is a no-op.
	 * Must be paired with a corresponding restore() call, ideally in a try/finally block.
	 *
	 * @param int $site_id The blog ID to switch to.
	 *
	 * @throws \WPE\AITK\WP\Multisite\Exceptions\Site_Context_Exception If the site context switch fails.
	 */
	public function switch_to_site( int $site_id ): void;

	/**
	 * Restore the previous site context.
	 *
	 * When not network-activated, this is a no-op.
	 */
	public function restore(): void;
}
