<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Schema\Contracts;

/**
 * Interface for classes that need to provide custom JSON schemas for complex properties.
 *
 * This interface allows classes to define JSON Schema specifications for properties
 * that cannot be automatically detected through reflection (e.g., arrays of objects,
 * nested structures, or properties with complex validation rules).
 *
 * When a class implements this interface, the Schema_Builder will use the provided
 * schemas instead of attempting automatic detection for those properties.
 *
 * The schemas should follow JSON Schema Draft-04 specification for WordPress REST API compatibility.
 *
 * @see https://json-schema.org/specification-links.html#draft-4
 */
interface Schema_Provider_Interface {
	/**
	 * Get custom JSON schemas for complex properties.
	 *
	 * Returns a map of property names to their complete JSON Schema definitions.
	 * Each schema should be a valid JSON Schema object according to Draft-04 specification.
	 *
	 * @return array<string, array<string, scalar|array>> Map of property name to JSON Schema definition.
	 */
	public static function get_property_schemas(): array;
}
