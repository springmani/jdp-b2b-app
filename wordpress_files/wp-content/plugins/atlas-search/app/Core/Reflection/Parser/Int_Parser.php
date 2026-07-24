<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Parser;

use InvalidArgumentException;
use WPE\AITK\Core\Reflection\Docblock\Int_Info;
use WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception;

/**
 * Parses integer type information with range constraints from reflection property docblocks.
 *
 * Extracts type information from docblock annotations in the format:
 * - `int<0, 100>` for integers between 0 and 100
 * - `int<min, 100>` for integers with maximum 100 (no minimum)
 * - `int<50, max>` for integers with minimum 50 (no maximum)
 * - `int<min, max>` for any integer (no constraints)
 * - `positive-int` for positive integers (min=1, no maximum)
 * - `non-negative-int` for non-negative integers (min=0, no maximum)
 *
 * The literal keywords "min" and "max" represent unbounded constraints.
 * Numeric values must be valid integers.
 */
final class Int_Parser {
	/**
	 * Keyword for positive integer type (min=1).
	 */
	private const POSITIVE_INT = 'positive-int';

	/**
	 * Keyword for non-negative integer type (min=0).
	 */
	private const NON_NEGATIVE_INT = 'non-negative-int';

	/**
	 * @var \WPE\AITK\Core\Reflection\Parser\Range_Parser
	 */
	private Range_Parser $range_parser;

	public function __construct( Range_Parser $range_parser ) {
		$this->range_parser = $range_parser;
	}

	/**
	 * @param non-empty-string $name Property name.
	 * @param non-empty-string $type_string The var part of the property docblock to parse data from.
	 *
	 * @return \WPE\AITK\Core\Reflection\Docblock\Int_Info|null Returns Int_Info if constraints are present, null otherwise.
	 */
	public function parse( string $name, string $type_string ): ?Int_Info {
		$trimmed = trim( $type_string );

		// If no type string, no constraints.
		if ( empty( $trimmed ) ) {
			return null;
		}

		// Check for simple int types first (positive-int, non-negative-int).
		$simple_type = $this->extract_simple_int_type( $trimmed );

		if ( null !== $simple_type ) {
			return $this->parse_simple_int_type( $simple_type, $name );
		}

		if ( 'int' === $trimmed ) {
			return null;
		}

		// If it doesn't start with 'int', it's not an int type annotation - return null.
		// This handles cases where unrelated docblock text is passed.
		if ( ! str_starts_with( $trimmed, 'int' ) ) {
			return null;
		}

		// Otherwise, parse as range declaration (will throw if format is invalid).
		[ $min_str, $max_str ] = $this->range_parser->extract_range( $trimmed, 'int', $name );
		[ $min, $max ]         = $this->convert_range_values( $min_str, $max_str, $name );

		return $this->create_int_info( $min, $max, $name );
	}

	/**
	 * Extracts a simple integer type from the var part.
	 *
	 * Checks for types like 'positive-int' or 'non-negative-int'.
	 *
	 * @param string $type_string The var part to extract from.
	 *
	 * @return string|null The simple type keyword, or null if not found.
	 */
	private function extract_simple_int_type( string $type_string ): ?string {
		// Pattern to match simple int types without @var prefix.
		$pattern = '/^(positive-int|non-negative-int)$/';

		if ( preg_match( $pattern, trim( $type_string ), $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Parses a simple integer type into min/max constraints.
	 *
	 * Converts type keywords to their equivalent range constraints:
	 * - 'positive-int' → min=1, max=null
	 * - 'non-negative-int' → min=0, max=null
	 *
	 * @param string $simple_type   The simple type keyword.
	 * @param string $property_name The property name for error messages.
	 *
	 * @return \WPE\AITK\Core\Reflection\Docblock\Int_Info The parsed info.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If the type is invalid.
	 */
	private function parse_simple_int_type( string $simple_type, string $property_name ): Int_Info {
		switch ( $simple_type ) {
			case self::POSITIVE_INT:
				// Positive integers: 1, 2, 3, ...
				return $this->create_int_info( 1, null, $property_name );
			case self::NON_NEGATIVE_INT:
				// Non-negative integers: 0, 1, 2, 3, ...
				return $this->create_int_info( 0, null, $property_name );
			default:
				throw new Invalid_Docblock_Exception(
					sprintf(
						'Unknown simple integer type "%s" for property $%s.',
						$simple_type,
						$property_name
					)
				);
		}
	}

	/**
	 * Converts string range values to integers or null.
	 *
	 * Handles both numeric values and the special "min"/"max" keywords.
	 *
	 * @param string $min_str The minimum value string (number or "min").
	 * @param string $max_str The maximum value string (number or "max").
	 * @param string $name    The property name for error messages.
	 *
	 * @return array{0: int|null, 1: int|null} Array containing [min_value, max_value].
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If values are invalid.
	 */
	private function convert_range_values( string $min_str, string $max_str, string $name ): array {
		$min = $this->range_parser->parse_int( $min_str, 'min', $name );
		$max = $this->range_parser->parse_int( $max_str, 'max', $name );

		return [ $min, $max ];
	}

	/**
	 * Creates an Int_Info instance with error handling.
	 *
	 * Wraps InvalidArgumentException from the constructor into a more specific
	 * Invalid_Docblock_Exception with additional context.
	 *
	 * @param int|null $min           The minimum value.
	 * @param int|null $max           The maximum value.
	 * @param string   $name The name of the property.
	 *
	 * @return \WPE\AITK\Core\Reflection\Docblock\Int_Info|null The created instance, or null if no constraints.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If validation fails.
	 */
	private function create_int_info( ?int $min, ?int $max, string $name ): ?Int_Info {
		if ( null === $min && null === $max ) {
			return null;
		}

		try {
			return new Int_Info( $min, $max );
		} catch ( InvalidArgumentException $e ) {
			throw Invalid_Docblock_Exception::constraint( $name, 'integer range', $e->getMessage(), $e );
		}
	}
}
