<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Capability\Contracts;

interface Capability_Service_Interface {
	/**
	 * Checks whether a specific capability is available.
	 *
	 * @param string $capability One of the Capability constants.
	 */
	public function has_capability( string $capability ): bool;

	/**
	 * Returns all available capabilities.
	 *
	 * @return array<int, string>
	 */
	public function get_capabilities(): array;
}
