<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Reflection;

use WPE\AITK\Core\Reflection\Docblock\Base_Range_Info;
use WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface;
use WPE\AITK\Core\Reflection\Docblock\Float_Info;
use WPE\AITK\Core\Reflection\Docblock\Int_Info;
use WPE\AITK\Core\Reflection\Docblock\String_Info;
use WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface;

/**
 * Property Mapper.
 *
 * Maps a parsed Property_Interface into a type + constraints array.
 * Used by both Schema_Builder (JSON Schema) and Rest_Args_Builder (WP args),
 * since both formats share the same key names (type, minimum, maximum,
 * minLength, enum).
 */
final class Property_Mapper {
	private const TYPE_MAP = [
		'bool'   => 'boolean',
		'float'  => 'number',
		'int'    => 'integer',
		'string' => 'string',
	];

	private const FALLBACK_TYPE = 'string';

	/**
	 * Maps a property to a type + constraints array.
	 *
	 * @param \WPE\AITK\Core\Reflection\Type\Contracts\Property_Interface $property The parsed property.
	 *
	 * @return array<string, int|float|string|list<string>> The mapped property definition.
	 */
	public function map( Property_Interface $property ): array {
		$result = [ 'type' => self::TYPE_MAP[ $property->get_type() ] ?? self::FALLBACK_TYPE ];
		$info   = $property->get_info();

		if ( ! $info->has_constraints() ) {
			return $result;
		}

		return $this->apply_constraints( $result, $info );
	}

	/**
	 * Applies type-specific constraints to the result array.
	 *
	 * @param array<string, int|float|string|list<string>> $result The base result.
	 * @param \WPE\AITK\Core\Reflection\Docblock\Contracts\Info_Interface $info The constraint info.
	 *
	 * @return array<string, int|float|string|list<string>> The result with constraints applied.
	 */
	private function apply_constraints( array $result, Info_Interface $info ): array {
		if ( $info instanceof Int_Info || $info instanceof Float_Info ) {
			return $this->add_range_constraints( $result, $info );
		}

		if ( $info instanceof String_Info ) {
			return $this->add_string_constraints( $result, $info );
		}

		// @codeCoverageIgnore
		return $result;
	}

	/**
	 * @param array<string, int|float|string|list<string>> $result The array to add constraints to.
	 *
	 * @return array<string, int|float|string|list<string>> The array with range constraints.
	 */
	private function add_range_constraints( array $result, Base_Range_Info $info ): array {
		if ( $info->has_min() ) {
			$result['minimum'] = $info->get_min();
		}

		if ( $info->has_max() ) {
			$result['maximum'] = $info->get_max();
		}

		return $result;
	}

	/**
	 * @param array<string, int|float|string|list<string>> $result The array to add constraints to.
	 *
	 * @return array<string, int|float|string|list<string>> The array with string constraints.
	 */
	private function add_string_constraints( array $result, String_Info $info ): array {
		if ( $info->get_is_non_empty() ) {
			$result['minLength'] = 1;
		}

		if ( $info->is_enum() ) {
			$result['enum'] = $info->get_allowed_values();
		}

		return $result;
	}
}
