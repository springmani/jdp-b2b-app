<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Plugin\Contracts;

interface Plugin_Name_Resolver_Interface {
	/**
	 * Resolves the display name of the plugin based on platform context.
	 */
	public function resolve(): string;
}
