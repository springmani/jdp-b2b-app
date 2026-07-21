<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Parser;

use InvalidArgumentException;
use WPE\AITK\Core\Reflection\Docblock\Float_Info;
use WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception;

/**
 * Parses float type information with range constraints from reflection property docblocks.
 *
 * Extracts type information from docblock annotations in the format:
 * - `float<0.0, 100.0>` for floats between 0.0 and 100.0
 * - `float<min, 100.0>` for floats with maximum 100.0 (no minimum)
 * - `float<50.0, max>` for floats with minimum 50.0 (no maximum)
 * - `float<min, max>` for any float (no constraints)
 *
 * The literal keywords "min" and "max" represent unbounded constraints.
 * Numeric values must be valid floats (including integers which will be converted).
 */
final class Float_Parser {
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
	 * @return \WPE\AITK\Core\Reflection\Docblock\Float_Info|null Returns Float_Info if constraints are present, null otherwise.
	 */
	public function parse( string $name, string $type_string ): ?Float_Info {
		$trimmed = trim( $type_string );

		// If no type string, no constraints.
		if ( empty( $trimmed ) ) {
			return null;
		}

		// Check if it's just 'float' with no constraints.
		if ( 'float' === $trimmed ) {
			return null;
		}

		// If it doesn't start with 'float', it's not a float type annotation - return null.
		// This handles cases where unrelated docblock text is passed.
		if ( ! str_starts_with( $trimmed, 'float' ) ) {
			return null;
		}

		// Parse as range declaration (will throw if format is invalid).
		[ $min_str, $max_str ] = $this->range_parser->extract_range( $trimmed, 'float', $name );
		[ $min, $max ]         = $this->convert_range_values( $min_str, $max_str, $name );

		return $this->create_float_info( $min, $max, $name );
	}

	/**
	 * Converts string range values to floats or null.
	 *
	 * Handles both numeric values and the special "min"/"max" keywords.
	 *
	 * @param string $min_str The minimum value string (number or "min").
	 * @param string $max_str The maximum value string (number or "max").
	 * @param string $name    The property name for error messages.
	 *
	 * @return array{0: float|null, 1: float|null} Array containing [min_value, max_value].
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If values are invalid.
	 */
	private function convert_range_values( string $min_str, string $max_str, string $name ): array {
		$min = $this->range_parser->parse_float( $min_str, 'min', $name );
		$max = $this->range_parser->parse_float( $max_str, 'max', $name );

		return [ $min, $max ];
	}

	/**
	 * Creates a Float_Info instance with error handling.
	 *
	 * Wraps InvalidArgumentException from the constructor into a more specific
	 * Invalid_Docblock_Exception with additional context.
	 *
	 * @param float|null $min           The minimum value.
	 * @param float|null $max           The maximum value.
	 * @param string     $name The name of the property.
	 *
	 * @return \WPE\AITK\Core\Reflection\Docblock\Float_Info|null The created instance, or null if no constraints.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If validation fails.
	 */
	private function create_float_info( ?float $min, ?float $max, string $name ): ?Float_Info {
		try {
			// Return null if no constraints.
			if ( null === $min && null === $max ) {
				return null;
			}

			return new Float_Info( $min, $max );
		} catch ( InvalidArgumentException $e ) {
			throw Invalid_Docblock_Exception::constraint( $name, 'float range', $e->getMessage(), $e );
		}
	}
}
