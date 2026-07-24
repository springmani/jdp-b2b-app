<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings\Contracts;

/**
 * Handles WordPress registration of individual settings (register_setting / REST API exposure).
 */
interface Settings_Registrar_Interface {
	/**
	 * Registers a single setting with WordPress via register_setting().
	 *
	 * @param string $option_name The WordPress settings option name.
	 * @param class-string<\WPE\AITK\WP\Settings\Contracts\Setting_Interface> $data_class The correspondent data class.
	 *
	 * @throws \WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception If schema generation fails.
	 */
	public function register( string $option_name, string $data_class ): void;
}
