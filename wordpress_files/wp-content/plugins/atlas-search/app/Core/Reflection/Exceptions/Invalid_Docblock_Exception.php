<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a docblock has invalid or malformed syntax.
 *
 * This exception is raised when:
 * - The docblock exists but doesn't contain the expected `@var array<...>` format
 * - The array type annotation has incorrect syntax (e.g., too many type parameters)
 * - Type names contain invalid characters or don't match PHP identifier rules
 * - The specified types are not supported (e.g., non-scalar value types, invalid key types)
 */
final class Invalid_Docblock_Exception extends RuntimeException {
	/**
	 * Creates exception for invalid type constraint in docblock.
	 *
	 * @param string $property_name The property name.
	 * @param string $type_description Description of the type (e.g., "integer range", "float range", "string type").
	 * @param string $reason The validation error message.
	 * @param \Throwable|null $previous Previous exception.
	 */
	public static function constraint(
		string $property_name,
		string $type_description,
		string $reason,
		?Throwable $previous = null
	): self {
		return new self(
			sprintf( 'Invalid %s in docblock for property $%s: %s', $type_description, $property_name, $reason ),
			0,
			$previous
		);
	}
}
