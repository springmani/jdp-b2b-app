<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Settings\Exceptions;

/**
 * Thrown when setting registration fails due to invalid class, missing interface implementation, or WordPress registration errors.
 */
final class Setting_Registration_Exception extends Settings_Exception {
	public static function class_not_found( string $class_name ): self {
		return new self(
			sprintf( 'Cannot register setting: class "%s" does not exist.', $class_name )
		);
	}

	public static function missing_interface( string $class_name, string $interface_name ): self {
		return new self(
			sprintf( 'Cannot register setting: class "%s" must implement %s.', $class_name, $interface_name )
		);
	}

	public static function invalid_option_name( string $option_name ): self {
		return new self(
			sprintf( 'Cannot register setting: option name "%s" is invalid or empty.', $option_name )
		);
	}

	public static function already_registered( string $option_name ): self {
		return new self(
			sprintf( 'Cannot register setting: option name "%s" is already registered.', $option_name )
		);
	}

	public static function duplicate_class( string $class_name, string $existing_option ): self {
		return new self(
			sprintf(
				'Cannot register setting: class "%s" is already registered under option "%s".',
				$class_name,
				$existing_option
			)
		);
	}

	public static function registration_failed( string $option_name, string $reason ): self {
		return new self(
			sprintf( 'WordPress failed to register setting "%s": %s', $option_name, $reason )
		);
	}
}
