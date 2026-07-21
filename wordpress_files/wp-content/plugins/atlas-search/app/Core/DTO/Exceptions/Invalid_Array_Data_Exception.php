<?php

declare(strict_types=1);

namespace WPE\AITK\Core\DTO\Exceptions;

/**
 * Thrown when array data is invalid, malformed, or doesn't match the expected structure for DTO construction.
 */
final class Invalid_Array_Data_Exception extends DTO_Exception {
	/**
	 * @param class-string $class_name The name of the class being constructed.
	 */
	public static function missing_required_field( string $class_name, string $field_name ): self {
		return new self(
			sprintf( 'Missing required field "%s" when constructing %s from array.', $field_name, $class_name )
		);
	}
}
