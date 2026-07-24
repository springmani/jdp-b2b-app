<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection;

use WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception;
use WPE\AITK\Core\Reflection\Parser\Float_Parser;
use WPE\AITK\Core\Reflection\Parser\Int_Parser;
use WPE\AITK\Core\Reflection\Parser\String_Parser;
use WPE\AITK\Core\Reflection\Type\Bool_Property;
use WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface;
use WPE\AITK\Core\Reflection\Type\Float_Property;
use WPE\AITK\Core\Reflection\Type\Int_Property;
use WPE\AITK\Core\Reflection\Type\String_Property;

/**
 * Factory for creating Type objects from native PHP types and docblock information.
 *
 * Responsible for:
 * - Creating appropriate Type objects based on native type
 * - Parsing docblock constraints for scalar types
 *
 * ## Extending with New Types
 *
 * To add support for a new type (e.g., array, object):
 *
 * 1. **Create Parser** (if needed):
 *    - Implement a parser class (e.g., `Array_Parser`)
 *    - Parser should have a `parse(string $property_name, string $type_string)` method
 *    - Return appropriate Info object or null
 *
 * 2. **Create Info Class**:
 *    - Create info class implementing `Info_Interface` (e.g., `Array_Info`)
 *    - Add immutability traits: `Without_Clone`, `Without_Sleep`, `Without_Wakeup`
 *    - Implement `has_constraints(): bool` method
 *
 * 3. **Create Property Class**:
 *    - Create property class extending `Base_Property` (e.g., `Array_Property`)
 *    - Add immutability traits: `Without_Clone`, `Without_Sleep`, `Without_Wakeup`
 *    - Implement all `Property_Interface` methods
 *    - Add `validate_default_value()` private method for constraint validation
 *    - Use Null_Info when no constraints exist
 *
 * 4. **Update Type_Factory**:
 *    - Add parser dependency to constructor
 *    - Add new case in `create_property()` method
 *
 * 5. **Update Class_Property_Parser**:
 *    - Add type name to `SUPPORTED_TYPES` constant
 *
 * 6. **Update Definer**:
 *    - Add parser and dependencies to `Reflection_Definer`
 */
final class Type_Factory {
	/**
	 * Parser for integer constraints.
	 *
	 * @var \WPE\AITK\Core\Reflection\Parser\Int_Parser
	 */
	private Int_Parser $int_parser;

	/**
	 * Parser for string constraints.
	 *
	 * @var \WPE\AITK\Core\Reflection\Parser\String_Parser
	 */
	private String_Parser $string_parser;

	/**
	 * Parser for float constraints.
	 *
	 * @var \WPE\AITK\Core\Reflection\Parser\Float_Parser
	 */
	private Float_Parser $float_parser;

	/**
	 * Creates a new type factory.
	 *
	 * @param \WPE\AITK\Core\Reflection\Parser\Int_Parser $int_parser Parser for int constraints.
	 * @param \WPE\AITK\Core\Reflection\Parser\String_Parser $string_parser Parser for string constraints.
	 * @param \WPE\AITK\Core\Reflection\Parser\Float_Parser $float_parser Parser for float constraints.
	 */
	public function __construct( Int_Parser $int_parser, String_Parser $string_parser, Float_Parser $float_parser ) {
		$this->int_parser    = $int_parser;
		$this->string_parser = $string_parser;
		$this->float_parser  = $float_parser;
	}

	/**
	 * Creates a Type object from native type and optional docblock.
	 *
	 * Orchestrates the parsing of type constraints from docblock and creation of appropriate Type objects.
	 *
	 * @param string $property_name The property name for error messages.
	 * @param string $type_name The native type name (int, string, float, bool).
	 * @param bool $is_required Whether the property is required (non-nullable).
	 * @param string|null $docblock The docblock, or null if none.
	 *
	 * @return \WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface The created Type object.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If parsing fails.
	 */
	public function create_from_native_type(
		string $property_name,
		string $type_name,
		bool $is_required,
		?string $docblock
	): Property_Interface {

		$type_string = $this->extract_type_string_from_docblock( $docblock );

		// Strip nullable union types from docblock (e.g., "int|null" -> "int").
		// Nullability is determined by the PHP type, not the docblock.
		$type_string = $this->strip_nullable_union( $type_string );

		return $this->create_property( $property_name, $type_name, $is_required, $type_string );
	}

	/**
	 * Creates the appropriate property object for the given type.
	 *
	 * @param string $property_name The property name for error messages.
	 * @param string $type_name The native type name (int, string, float, bool).
	 * @param bool $is_required Whether the property is required (non-nullable).
	 * @param string $type_string The type string extracted from docblock.
	 *
	 * @return \WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface The created property object.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If type is unsupported.
	 */
	private function create_property(
		string $property_name,
		string $type_name,
		bool $is_required,
		string $type_string
	): Property_Interface {
		if ( 'bool' === $type_name ) {
			return new Bool_Property( $is_required );
		}

		if ( 'int' === $type_name ) {
			return new Int_Property(
				$is_required,
				$this->int_parser->parse( $property_name, $type_string )
			);
		}

		if ( 'float' === $type_name ) {
			return new Float_Property(
				$is_required,
				$this->float_parser->parse( $property_name, $type_string )
			);
		}

		if ( 'string' === $type_name ) {
			return new String_Property(
				$is_required,
				$this->string_parser->parse( $property_name, $type_string )
			);
		}

		throw new Invalid_Docblock_Exception(
			sprintf( 'Unsupported type "%s" for property $%s', $type_name, $property_name )
		);
	}

	/**
	 * Extracts the type string from a docblock "@var" annotation.
	 *
	 * @param string|null $docblock The docblock to parse.
	 *
	 * @return string The extracted type string, or empty string if not found.
	 */
	private function extract_type_string_from_docblock( ?string $docblock ): string {
		if ( empty( $docblock ) ) {
			return '';
		}

		// Match @var followed by the type annotation.
		// Captures everything until $ (variable name), newline, or */.
		if ( ! preg_match( '/@var\s+([^\$\n\r]+?)(?=\s*(?:\$|\*\/|[\n\r]|$))/', $docblock, $matches ) ) {
			return '';
		}

		return trim( $matches[1] );
	}

	/**
	 * Strips nullable union types from the type string.
	 *
	 * Removes "null" from union types like "int|null" or "null|int",
	 * since nullability is determined by the PHP type declaration, not the docblock.
	 *
	 * @param string $type_string The type string to clean.
	 *
	 * @return string The type string without nullable unions.
	 */
	private function strip_nullable_union( string $type_string ): string {
		// Remove |null or null| from the type string.
		$cleaned = preg_replace( '/\|null\b|\bnull\|/', '', $type_string );

		return trim( $cleaned );
	}
}
