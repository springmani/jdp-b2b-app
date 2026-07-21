<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Schema;

use ReflectionException;
use WPE\AITK\Core\Reflection\Property_Mapper;
use WPE\AITK\Core\Reflection\Property_Parser;
use WPE\AITK\WP\Schema\Contracts\Schema_Builder_Interface;
use WPE\AITK\WP\Schema\Contracts\Schema_Provider_Interface;
use WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception;

/**
 * Generates WordPress-compatible JSON schemas from PHP class definitions using reflection.
 *
 * phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
 */
final class Schema_Builder implements Schema_Builder_Interface {
	private const SCHEMA_DRAFT_04 = 'http://json-schema.org/draft-04/schema#';

	private Property_Mapper $property_mapper;

	private Property_Parser $property_parser;

	public function __construct( Property_Mapper $property_mapper, Property_Parser $property_parser ) {
		$this->property_mapper = $property_mapper;
		$this->property_parser = $property_parser;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception If schema generation fails.
	 */
	public function build_rest_schema( string $class_name, string $title, ?string $description = null ): array {
		if ( ! class_exists( $class_name ) ) {
			throw Schema_Generation_Exception::invalid_class( $class_name );
		}

		if ( '' === trim( $title ) ) {
			throw Schema_Generation_Exception::invalid_title( $title );
		}

		try {
			$parsed_properties = $this->property_parser->parse_class( $class_name );
			$schema            = $this->build_base_schema( $class_name, $parsed_properties );

			// Add REST API specific fields.
			$schema['$schema']              = self::SCHEMA_DRAFT_04;
			$schema['title']                = $title;
			$schema['additionalProperties'] = false;

			if ( null !== $description ) {
				$schema['description'] = $description;
			}

			return $schema;
		} catch ( ReflectionException $e ) {
			throw Schema_Generation_Exception::reflection_failed( $class_name, $e->getMessage() );
		}
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception If schema generation fails.
	 */
	public function build_settings_schema( string $class_name ): array {
		if ( ! class_exists( $class_name ) ) {
			throw Schema_Generation_Exception::invalid_class( $class_name );
		}

		try {
			$parsed_properties = $this->property_parser->parse_class( $class_name );

			return $this->build_base_schema( $class_name, $parsed_properties );
		} catch ( ReflectionException $e ) {
			throw Schema_Generation_Exception::reflection_failed( $class_name, $e->getMessage() );
		}
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception If schema generation fails.
	 */
	public function build_properties_schema( string $class_name ): array {
		if ( ! class_exists( $class_name ) ) {
			throw Schema_Generation_Exception::invalid_class( $class_name );
		}

		try {
			$parsed_properties = $this->property_parser->parse_class( $class_name );

			return array_map( fn ( $property ) => $this->property_mapper->map( $property ), $parsed_properties );
		} catch ( ReflectionException $e ) {
			throw Schema_Generation_Exception::reflection_failed( $class_name, $e->getMessage() );
		}
	}

	/**
	 * Builds common schema structure used by both REST and settings schemas.
	 *
	 * @param string $class_name The class name to build schema for.
	 * @param array<string, \WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface> $parsed_properties Parsed class properties.
	 *
	 * @return array<array-key, scalar|array> The base schema.
	 */
	private function build_base_schema( string $class_name, array $parsed_properties ): array {
		// Build schemas from reflection for scalar properties.
		$properties_schema = array_map(
			fn ( $property ) => $this->property_mapper->map( $property ),
			$parsed_properties
		);

		// Merge custom schemas if the class provides them.
		$custom_schemas = $this->get_custom_property_schemas( $class_name );

		if ( ! empty( $custom_schemas ) ) {
			$properties_schema = array_merge( $properties_schema, $custom_schemas );
		}

		$required_fields = $this->extract_required_fields_from_properties( $parsed_properties );

		$schema = [
			'properties' => $properties_schema,
			'type'       => 'object',
		];

		if ( ! empty( $required_fields ) ) {
			$schema['required'] = $required_fields;
		}

		return $schema;
	}

	/**
	 * Get custom property schemas if the class implements Schema_Provider_Interface.
	 *
	 * @param string $class_name The class name to check.
	 *
	 * @return array<string, array<string, scalar|array>> Custom property schemas.
	 */
	private function get_custom_property_schemas( string $class_name ): array {
		$interfaces = class_implements( $class_name );

		if ( false === $interfaces || ! in_array( Schema_Provider_Interface::class, $interfaces, true ) ) {
			return [];
		}

		return $class_name::get_property_schemas();
	}

	/**
	 * @param array<string, \WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface> $parsed_properties Parsed properties.
	 *
	 * @return array<int, string> Array of required property names.
	 */
	private function extract_required_fields_from_properties( array $parsed_properties ): array {
		return array_values(
			array_filter(
				array_keys( $parsed_properties ),
				static fn ( $property_name ) => $parsed_properties[ $property_name ]->get_required()
			)
		);
	}
}
