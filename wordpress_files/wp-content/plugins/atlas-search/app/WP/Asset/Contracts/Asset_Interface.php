<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Contracts;

interface Asset_Interface {
	public function get_handle(): string;

	public function get_src(): string;

	/**
	 * An array of other script/style handles this script/style depends.
	 *
	 * @return array<string> $deps
	 */
	public function get_deps(): array;
}
