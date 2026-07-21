<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection\Contracts;

/**
 * Implementations of this interface analyze all properties of a class and extract
 * complete type information, including nullability detection and type constraints.
 */
interface Property_Parser_Interface {
	/**
	 * Parses all properties of a class.
	 *
	 * Analyzes each property and extracts complete type information, including:
	 * - Whether the property is required (non-nullable) or optional (nullable)
	 * - The base type (int, string, float, bool)
	 * - Type-specific constraints (ranges, enums, etc.)
	 *
	 * @param class-string $class_name The class name to parse.
	 *
	 * @return array<string, \WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface> Array of parsed properties, keyed by property name.
	 *
	 * @throws \ReflectionException If the class doesn't exist.
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If a docblock format is invalid or property has no type.
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Unsupported_Property_Type_Exception If a property has an unsupported type.
	 */
	public function parse_class( string $class_name ): array;
}
