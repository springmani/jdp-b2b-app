<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Rest;

use ReflectionException;
use WPE\AITK\Core\Reflection\Property_Mapper;
use WPE\AITK\Core\Reflection\Property_Parser;
use WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception;

/**
 * REST Args Builder.
 *
 * Builds WordPress REST API argument definitions from a request data class.
 * Uses Property_Parser to reflect class properties and Property_Mapper
 * to produce args ready for register_rest_route().
 */
final class Rest_Args_Builder {
	/**
	 * @var \WPE\AITK\Core\Reflection\Property_Mapper
	 */
	private Property_Mapper $property_mapper;

	/**
	 * @var \WPE\AITK\Core\Reflection\Property_Parser
	 */
	private Property_Parser $property_parser;

	/**
	 * @param \WPE\AITK\Core\Reflection\Property_Mapper $property_mapper Property mapper.
	 * @param \WPE\AITK\Core\Reflection\Property_Parser $property_parser Property parser.
	 */
	public function __construct( Property_Mapper $property_mapper, Property_Parser $property_parser ) {
		$this->property_mapper = $property_mapper;
		$this->property_parser = $property_parser;
	}

	/**
	 * Builds WordPress REST API argument definitions from a data class.
	 *
	 * Each entry includes type, constraints, and required flag
	 * derived from the class properties.
	 *
	 * @param string $class_name Fully qualified class name.
	 *
	 * @return array<string, array<string, bool|int|string|array>> WordPress REST argument definitions.
	 *
	 * @throws \WPE\AITK\WP\Schema\Exceptions\Schema_Generation_Exception If reflection fails.
	 */
	public function build( string $class_name ): array {
		try {
			$properties = $this->property_parser->parse_class( $class_name );
		} catch ( ReflectionException $e ) {
			throw Schema_Generation_Exception::reflection_failed( $class_name, $e->getMessage() );
		}

		$args = [];

		foreach ( $properties as $name => $property ) {
			$arg             = $this->property_mapper->map( $property );
			$arg['required'] = $property->get_required();
			$args[ $name ]   = $arg;
		}

		return $args;
	}
}
