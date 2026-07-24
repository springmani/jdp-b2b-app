<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Parser;

use WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception;

/**
 * Parses range declarations from PHPDoc type annotations.
 *
 * Handles patterns like:
 * - `int<min, max>`
 * - `float<min, max>`
 *
 * Extracts the min and max values as strings for further processing by type-specific parsers.
 */
final class Range_Parser {
	/**
	 * Literal keyword for unbounded minimum.
	 */
	private const MIN_KEYWORD = 'min';

	/**
	 * Literal keyword for unbounded maximum.
	 */
	private const MAX_KEYWORD = 'max';

	/**
	 * Extracts range declaration from a var part.
	 *
	 * @param string $type_string The var part to extract from.
	 * @param string $type_name The expected type name (e.g., 'int', 'float').
	 * @param string $prop_name The property name for error messages.
	 *
	 * @return array{0: string, 1: string} Array containing [min_string, max_string] (trimmed).
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If the format is invalid.
	 */
	public function extract_range( string $type_string, string $type_name, string $prop_name ): array {
		// Pattern to match range declarations: type<min, max>.
		$pattern = sprintf( '/^%s\s*<\s*([^,]+)\s*,\s*([^>]+)\s*>$/', preg_quote( $type_name, '/' ) );

		if ( ! preg_match( $pattern, $type_string, $matches ) ) {
			throw new Invalid_Docblock_Exception(
				sprintf(
					'Missing or invalid %s<...> format for property $%s.',
					$type_name,
					$prop_name
				) . sprintf(
					'Expected format: %s<min, max> where min/max are numbers or the literals "min"/"max"',
					$type_name
				)
			);
		}

		// Return trimmed min and max strings.
		return [
			trim( $matches[1] ),
			trim( $matches[2] ),
		];
	}

	/**
	 * Gets the keyword for the given bound type.
	 *
	 * @param string $bound_type The bound type ('min' or 'max').
	 *
	 * @return string The corresponding keyword.
	 */
	public function get_keyword_for_bound_type( string $bound_type ): string {
		return 'min' === $bound_type ? self::MIN_KEYWORD : self::MAX_KEYWORD;
	}

	/**
	 * Parses an integer bound value.
	 *
	 * @param string $value The value string to parse.
	 * @param string $bound_type The bound type ('min' or 'max').
	 * @param string $property_name The property name for error messages.
	 *
	 * @return int|null The parsed integer, or null if unbounded.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If the value is invalid.
	 */
	public function parse_int( string $value, string $bound_type, string $property_name ): ?int {
		if ( $this->is_unbounded_keyword( $value, $bound_type ) ) {
			return null;
		}

		$validated = $this->validate_int_value( $value, $bound_type, $property_name );

		return (int) $validated;
	}

	/**
	 * Parses a float bound value.
	 *
	 * @param string $value The value string to parse.
	 * @param string $bound_type The bound type ('min' or 'max').
	 * @param string $property_name The property name for error messages.
	 *
	 * @return float|null The parsed float, or null if unbounded.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If the value is invalid.
	 */
	public function parse_float( string $value, string $bound_type, string $property_name ): ?float {
		if ( $this->is_unbounded_keyword( $value, $bound_type ) ) {
			return null;
		}

		$validated = $this->validate_float_value( $value, $bound_type, $property_name );

		return (float) $validated;
	}

	/**
	 * Checks if the value is an unbounded keyword.
	 *
	 * @param string $value The value to check.
	 * @param string $bound_type The bound type ('min' or 'max').
	 *
	 * @return bool True if the value matches the keyword.
	 */
	private function is_unbounded_keyword( string $value, string $bound_type ): bool {
		return $value === $this->get_keyword_for_bound_type( $bound_type );
	}

	/**
	 * Validates an integer value string.
	 *
	 * @param string $value The value string to validate.
	 * @param string $bound_type The bound type ('min' or 'max').
	 * @param string $property_name The property name for error messages.
	 *
	 * @return numeric-string The validated integer string.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If the value is invalid.
	 */
	private function validate_int_value( string $value, string $bound_type, string $property_name ): string {
		if ( $this->is_valid_integer( $value ) ) {
			return $value;
		}

		throw $this->create_validation_error( $value, $bound_type, $property_name, 'int', 'an integer' );
	}

	/**
	 * Validates a float value string.
	 *
	 * @param string $value The value string to validate.
	 * @param string $bound_type The bound type ('min' or 'max').
	 * @param string $property_name The property name for error messages.
	 *
	 * @return numeric-string The validated float string.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If the value is invalid.
	 */
	private function validate_float_value( string $value, string $bound_type, string $property_name ): string {
		if ( is_numeric( $value ) ) {
			return $value;
		}

		throw $this->create_validation_error( $value, $bound_type, $property_name, 'float', 'a number' );
	}

	/**
	 * Checks if a value is a valid integer string.
	 *
	 * @param string $value The value to check.
	 *
	 * @return bool True if valid integer.
	 */
	private function is_valid_integer( string $value ): bool {
		// Check for negative integer.
		if ( str_starts_with( $value, '-' ) ) {
			return ctype_digit( substr( $value, 1 ) );
		}

		// Check for positive integer.
		return ctype_digit( $value );
	}

	/**
	 * Creates a validation error exception.
	 *
	 * @param string $value The invalid value.
	 * @param string $bound_type The bound type ('min' or 'max').
	 * @param string $property_name The property name.
	 * @param string $type_name The type name ('int' or 'float').
	 * @param string $expected_desc Description of expected value.
	 *
	 * @return \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception The exception.
	 */
	private function create_validation_error(
		string $value,
		string $bound_type,
		string $property_name,
		string $type_name,
		string $expected_desc
	): Invalid_Docblock_Exception {
		$keyword = $this->get_keyword_for_bound_type( $bound_type );

		return new Invalid_Docblock_Exception(
			sprintf(
				'Invalid %s value "%s" in %s range for property $%s. Expected %s or "%s".',
				$bound_type,
				$value,
				$type_name,
				$property_name,
				$expected_desc,
				$keyword
			)
		);
	}
}
