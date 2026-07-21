<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings\Contracts;

interface Settings_Manager_Interface {
	/**
	 * Retrieves a setting by its data class.
	 *
	 * The data class acts as a type-safe identifier — the repository resolves
	 * the corresponding WordPress option name internally. PHPStan narrows the
	 * return type to T via the @template annotation.
	 *
	 * @template T of \WPE\AITK\WP\Settings\Contracts\Setting_Interface
	 *
	 * @param class-string<T> $data_class The data class to retrieve.
	 *
	 * @return \WPE\AITK\WP\Settings\Contracts\Setting_Interface|null The setting instance, or null if not stored or if stored data is invalid.
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception If the class is not registered.
	 */
	public function get( string $data_class ): ?Setting_Interface;

	/**
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception If option_name is not registered.
	 */
	public function update( string $option_name, Setting_Interface $setting ): bool;

	/**
	 * Retrieves the raw option value for a registered data class without DTO construction.
	 *
	 * @param string $option_name The name of the option.
	 *
	 * @return array<array-key, scalar|array>|null The raw option value, or null if not stored.
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception If the class is not registered.
	 */
	public function get_raw( string $option_name ): ?array;

	/**
	 * Updates a setting with a raw array value, bypassing DTO construction.
	 *
	 * @param string $option_name The name of the option.
	 * @param array<array-key, scalar|array> $data The raw data to store.
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception If the class is not registered.
	 */
	public function update_raw( string $option_name, array $data ): bool;

	/**
	 * Deletes a setting from WordPress options.
	 */
	public function delete( string $option_name ): bool;

	/**
	 * Checks if a setting is registered by its option name.
	 */
	public function has( string $option_name ): bool;

	/**
	 * Declares a setting so the manager can resolve its option name and data class.
	 *
	 * This only adds the setting to the internal repository. WordPress registration
	 * (register_setting / REST API exposure) happens later via the Settings_Registrar
	 * on the `init` hook.
	 *
	 * @param string                                                        $option_name The WordPress option name.
	 * @param class-string<\WPE\AITK\WP\Settings\Contracts\Setting_Interface> $data_class  The setting data class.
	 *
	 * @throws \WPE\AITK\WP\Settings\Exceptions\Setting_Registration_Exception If the setting is already registered or invalid.
	 */
	public function add( string $option_name, string $data_class ): void;

	/**
	 * Returns all registered settings.
	 *
	 * @return array<string, class-string<\WPE\AITK\WP\Settings\Contracts\Setting_Interface>> Option name => data class.
	 */
	public function get_all(): array;
}
