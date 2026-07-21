<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings\Contracts;

/**
 * Maintains a bidirectional registry between WordPress option names and setting data classes.
 */
interface Settings_Repository_Interface {
	/**
	 * Registers a setting with its corresponding class.
	 *
	 * Stores a bidirectional mapping between the option name and the data class,
	 * allowing retrieval by either key.
	 *
	 * @template T of \WPE\AITK\WP\Settings\Contracts\Setting_Interface
	 *
	 * @param string          $option_name The WordPress option name.
	 * @param class-string<T> $class_name  The class to instantiate when retrieving this setting.
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Registration_Exception If registration fails.
	 */
	public function set( string $option_name, string $class_name ): void;

	/**
	 * Resolves the WordPress option name for a registered data class.
	 *
	 * @param class-string<\WPE\AITK\WP\Settings\Contracts\Setting_Interface> $data_class The data class to look up.
	 *
	 * @return string The WordPress option name mapped to this class.
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception If the class is not registered.
	 */
	public function get_option_name( string $data_class ): string;

	/**
	 * Checks if a setting is registered by its option name.
	 */
	public function is_registered( string $option_name ): bool;

	/**
	 * Returns all registered settings.
	 *
	 * @return array<string, class-string<\WPE\AITK\WP\Settings\Contracts\Setting_Interface>> Option name => data class.
	 */
	public function get_all(): array;
}
