<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Parser;

use InvalidArgumentException;
use WPE\AITK\Core\Reflection\Docblock\String_Info;
use WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception;

/**
 * Parses string type information with constraints from reflection property docblocks.
 *
 * Extracts type information from docblock annotations in the format:
 * - `string` for any string
 * - `non-empty-string` for non-empty strings
 * - `'foo'|'bar'|'baz'` for enum of specific string values
 */
final class String_Parser {
	/**
	 * Keyword for non-empty string type.
	 */
	private const NON_EMPTY_STRING = 'non-empty-string';

	/**
	 * Keyword for regular string type.
	 */
	private const STRING_TYPE = 'string';

	/**
	 * @param non-empty-string $name Property name.
	 * @param non-empty-string $type_string The var part of the property docblock to parse data from.
	 *
	 * @return \WPE\AITK\Core\Reflection\Docblock\String_Info|null Returns String_Info if constraints are present, null otherwise.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If type declaration is empty.
	 */
	public function parse( string $name, string $type_string ): ?String_Info {
		$type_declaration = trim( $type_string );

		// If no type string, no constraints.
		if ( empty( $type_declaration ) ) {
			return null;
		}

		// Determine if it's non-empty-string, enum, or regular string.
		if ( self::NON_EMPTY_STRING === $type_declaration ) {
			return $this->create_string_info( true, null, $name );
		}

		if ( self::STRING_TYPE === $type_declaration ) {
			return null;
		}

		// Try to parse as enum.
		$allowed_values = $this->parse_enum_values( $type_declaration, $name );

		return $this->create_string_info( false, $allowed_values, $name );
	}

	/**
	 * Parses enum values from a type declaration.
	 *
	 * Extracts individual string values from patterns like 'foo'|'bar'|'baz'.
	 *
	 * @param string $type_declaration The type declaration string.
	 * @param string $name The property name for error messages.
	 *
	 * @return array<int, string> Array of allowed string values.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If the enum format is invalid.
	 */
	private function parse_enum_values( string $type_declaration, string $name ): array {
		// Split by pipe to get individual values.
		$parts = array_map( 'trim', explode( '|', $type_declaration ) );

		$allowed_values = [];

		foreach ( $parts as $part ) {
			// Each part should be a single-quoted string.
			if ( ! preg_match( "/^'([^']*)'$/", $part, $matches ) ) {
				throw new Invalid_Docblock_Exception(
					sprintf(
						'Invalid enum value "%s" in docblock for property $%s.',
						$part,
						$name
					) . 'Enum values must be single-quoted strings like \'value\'.'
				);
			}

			$allowed_values[] = $matches[1];
		}

		if ( empty( $allowed_values ) ) {
			throw new Invalid_Docblock_Exception(
				sprintf(
					'No enum values found in docblock for property $%s.',
					$name
				)
			);
		}

		return $allowed_values;
	}

	/**
	 * Creates a String_Info instance with error handling.
	 *
	 * Wraps InvalidArgumentException from the constructor into a more specific
	 * Invalid_Docblock_Exception with additional context.
	 *
	 * @param bool $is_non_empty Whether the string must be non-empty.
	 * @param array<int, string>|null $allowed The allowed string values (enum).
	 * @param string $name The name of the property.
	 *
	 * @return \WPE\AITK\Core\Reflection\Docblock\String_Info|null The created instance, or null if no constraints.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If validation fails.
	 */
	private function create_string_info( bool $is_non_empty, ?array $allowed, string $name ): ?String_Info {
		try {
			if ( ! $is_non_empty && null === $allowed ) {
				return null;
			}

			return new String_Info( $is_non_empty, $allowed );
		} catch ( InvalidArgumentException $e ) {
			throw Invalid_Docblock_Exception::constraint( $name, 'string type', $e->getMessage(), $e );
		}
	}
}
