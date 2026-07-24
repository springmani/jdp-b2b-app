<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Schema\Exceptions;

final class Schema_Generation_Exception extends Schema_Exception {
	public static function invalid_class( string $name ): self {
		return new self(
			sprintf( 'Cannot generate schema for class "%s". Class does not exist or is not accessible.', $name )
		);
	}

	/**
	 * @param class-string $class_name The class name.
	 */
	public static function reflection_failed( string $class_name, string $reason ): self {
		return new self(
			sprintf( 'Failed to generate schema for class "%s": %s', $class_name, $reason )
		);
	}

	/**
	 * @param class-string $class_name The class name.
	 */
	public static function unsupported_type( string $class_name, string $property_name, string $type ): self {
		return new self(
			sprintf(
				'Cannot generate schema for property "%s::$%s" of type "%s".',
				$class_name,
				$property_name,
				$type
			) . ' Only scalar types (int, string, float, bool) are supported.'
		);
	}

	public static function invalid_title( string $title ): self {
		return new self( sprintf( 'Schema title cannot be empty. Received: "%s"', $title ) );
	}
}
