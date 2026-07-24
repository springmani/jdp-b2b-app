<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ReflectionType;
use WPE\AITK\Core\Reflection\Contracts\Property_Parser_Interface;
use WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception;
use WPE\AITK\Core\Reflection\Exceptions\Unsupported_Property_Type_Exception;
use WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface;
use WPE\AITK\WP\Schema\Contracts\Schema_Provider_Interface;

/**
 * High-level parser for analyzing all properties of a class.
 *
 * This parser automatically examines all properties of a class and extracts complete
 * type information, including:
 * - Whether each property is required (non-nullable) or optional (nullable)
 * - The base type (int, string, float, bool, array, object)
 * - Type-specific constraints (ranges for ints, enums for strings, etc.)
 * - Default values from constructor parameters or property declarations
 *
 * ## Supported Property Types
 *
 * **Auto-detected (via reflection):**
 * - int, string, float, bool (with optional constraints)
 * - Nullable versions: int|null, ?string, etc.
 *
 * **Schema-required (manual JSON schema needed):**
 * - array, object
 * - Classes with these types MUST implement Schema_Provider_Interface
 * - Provide schemas via get_property_schemas() method
 *
 * **Unsupported (will throw exception):**
 * - Resource types
 * - Mixed types (except nullables)
 * - Callable types
 * - Specific class types (e.g., DateTime, MyClass)
 *
 * ## Nullability Detection
 *
 * Properties are considered optional (nullable) if:
 * - Type declaration includes 'null' (e.g., int|null, ?int)
 *
 * Properties are considered required (non-nullable) otherwise.
 *
 * ## Default Value Resolution
 *
 * Default values are resolved from two sources (in priority order):
 * 1. Constructor parameter default values (high priority)
 * 2. Property default values (lower priority)
 * Null values are not considered real defaults.
 */
final class Property_Parser implements Property_Parser_Interface {
	/**
	 * Types that are automatically supported via reflection and type constraints.
	 */
	private const SUPPORTED_TYPES = [ 'int', 'string', 'float', 'bool' ];

	/**
	 * Types that are supported but require manual JSON schema definition.
	 * Classes with these property types must implement Schema_Provider_Interface
	 * and provide schemas via get_property_schemas().
	 */
	private const SCHEMA_REQUIRED_TYPES = [ 'array', 'object' ];

	/**
	 * Cache for parsed class properties.
	 *
	 * @var array<string, array<\WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface>>
	 */
	private static array $cache = [];

	/**
	 * Factory for creating type objects.
	 *
	 * @var \WPE\AITK\Core\Reflection\Type_Factory
	 */
	private Type_Factory $type_factory;

	public function __construct( Type_Factory $type_factory ) {
		$this->type_factory = $type_factory;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \ReflectionException If the class doesn't exist.
	 */
	public function parse_class( string $class_name ): array {
		// Return cached result if available.
		if ( isset( self::$cache[ $class_name ] ) ) {
			return self::$cache[ $class_name ];
		}

		if ( ! class_exists( $class_name ) ) {
			throw new ReflectionException(
				sprintf( 'Class "%s" does not exist.', $class_name )
			);
		}

		$reflection        = new ReflectionClass( $class_name );
		$parsed_properties = $this->parse_properties( $class_name, $reflection->getProperties() );

		// Cache the result before returning.
		self::$cache[ $class_name ] = $parsed_properties;

		return $parsed_properties;
	}

	/**
	 * Parses all properties from a reflection properties array.
	 *
	 * Properties are validated against SUPPORTED_TYPES (auto-detected) and SCHEMA_REQUIRED_TYPES.
	 * For schema-required types, the class must implement Schema_Provider_Interface and provide
	 * the schema for that property.
	 *
	 * @param string $class_name The class name being parsed.
	 * @param array<\ReflectionProperty> $properties The properties to parse.
	 *
	 * @return array<\WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface> Parsed properties.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If parsing fails for supported types.
	 */
	private function parse_properties( string $class_name, array $properties ): array {
		$parsed_properties = [];
		$provided_schemas  = $this->get_provided_schemas( $class_name );

		foreach ( $properties as $property ) {
			$parsed_property = $this->parse_single_property( $class_name, $property, $provided_schemas );

			if ( null !== $parsed_property ) {
				$parsed_properties[ $property->getName() ] = $parsed_property;
			}
		}

		return $parsed_properties;
	}

	/**
	 * Parses a single property and returns its type object, or null if it should be skipped.
	 *
	 * @param string $class_name The class name being parsed.
	 * @param \ReflectionProperty $property The property to parse.
	 * @param array<string, array<string, scalar|array>> $provided_schemas Schemas provided by the class.
	 *
	 * @return \WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface|null Parsed property or null if skipped.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Invalid_Docblock_Exception If parsing fails.
	 */
	private function parse_single_property(
		string $class_name,
		ReflectionProperty $property,
		array $provided_schemas
	): ?Property_Interface {
		$property_name = $property->getName();
		$type          = $property->getType();

		if ( ! $type instanceof ReflectionType ) {
			throw new Invalid_Docblock_Exception(
				sprintf(
					'Property $%s must have a native PHP type declaration. Docblock @var alone is not sufficient.',
					$property_name
				)
			);
		}

		$docblock  = $property->getDocComment();
		$docblock  = false === $docblock ? '' : $docblock;
		$type_name = $type->__toString();

		// Strip nullable prefix for validation.
		$base_type = ltrim( $type_name, '?' );

		// Validate the type and check if schema is required.
		$this->validate_type_with_schema( $class_name, $base_type, $property_name, $provided_schemas );

		// Only auto-detect scalar types - skip schema-required types.
		if ( ! in_array( $base_type, self::SUPPORTED_TYPES, true ) ) {
			return null;
		}

		return $this->type_factory->create_from_native_type(
			$property_name,
			$base_type,
			! $type->allowsNull(),
			$docblock
		);
	}

	/**
	 * Get provided schemas from a class if it implements Schema_Provider_Interface.
	 *
	 * @param class-string $class_name The class name to check.
	 *
	 * @return array<string, array<string, scalar|array>> Map of property names to their schemas.
	 */
	private function get_provided_schemas( string $class_name ): array {
		$interfaces = class_implements( $class_name );

		if ( false === $interfaces || ! in_array( Schema_Provider_Interface::class, $interfaces, true ) ) {
			return [];
		}

		return $class_name::get_property_schemas();
	}

	/**
	 * Validates property type and ensures schema-required types have schemas provided.
	 *
	 * @param string $class_name The class name being parsed.
	 * @param string $type_name The property type name.
	 * @param string $property_name The property name.
	 * @param array<string, array<string, scalar|array>> $provided_schemas Schemas provided by the class.
	 *
	 * @throws \WPE\AITK\Core\Reflection\Exceptions\Unsupported_Property_Type_Exception If type is unsupported or lacks required schema.
	 */
	private function validate_type_with_schema(
		string $class_name,
		string $type_name,
		string $property_name,
		array $provided_schemas
	): void {
		// Check if it's an auto-supported scalar type.
		if ( in_array( $type_name, self::SUPPORTED_TYPES, true ) ) {
			return;
		}

		// Check if it's a schema-required type.
		if ( in_array( $type_name, self::SCHEMA_REQUIRED_TYPES, true ) ) {
			// Verify that a schema is provided for this property.
			if ( ! isset( $provided_schemas[ $property_name ] ) ) {
				throw new Unsupported_Property_Type_Exception(
					sprintf(
						'Property $%s in class "%s" has type "%s" which requires a JSON schema. ' .
						'Please provide a schema for this property via get_property_schemas().',
						$property_name,
						$class_name,
						$type_name
					)
				);
			}

			return;
		}

		// Type is neither auto-supported nor schema-supported - throw exception.
		throw new Unsupported_Property_Type_Exception(
			sprintf(
				'Property $%s has unsupported type "%s". ' .
				'Supported types: %s (auto-detected), %s (requires JSON schema via Schema_Provider_Interface).',
				$property_name,
				$type_name,
				implode( ', ', self::SUPPORTED_TYPES ),
				implode( ', ', self::SCHEMA_REQUIRED_TYPES )
			)
		);
	}
}
