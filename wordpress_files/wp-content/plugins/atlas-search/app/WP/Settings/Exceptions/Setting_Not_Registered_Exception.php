<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings\Exceptions;

/**
 * Thrown when attempting to access a setting that hasn't been registered with the settings repository.
 */
final class Setting_Not_Registered_Exception extends Settings_Exception {
	public static function for_option( string $option_name ): self {
		return new self(
			sprintf(
				'Setting "%s" is not registered. Call register() first to register the setting class.',
				$option_name
			)
		);
	}

	/**
	 * @param class-string $data_class The data class that was not found in the registry.
	 */
	public static function for_class( string $data_class ): self {
		return new self(
			sprintf(
				'No setting registered for class "%s". Call register() first to register the setting class.',
				$data_class
			)
		);
	}
}
