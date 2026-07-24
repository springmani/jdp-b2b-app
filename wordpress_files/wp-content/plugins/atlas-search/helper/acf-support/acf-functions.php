<?php

namespace Wpe_Content_Engine\Helper\Acf_Support;

use Wpe_Content_Engine\Helper\String_Transformation;

function is_acf_loaded(): bool {
	return class_exists( 'ACF' );
}

/**
 * Get ACF fields from specified post types, filtering by unsupported and nested types.
 *
 * @param array $post_types        List of post types to include, or null for all.
 * @param array $excluded_field_types Field types to exclude entirely.
 * @param array $included_field_types Field types to include only.
 * @param array $nested_types      Field types to recurse into (if empty, no recursion).
 *
 * @return array                        Flat list of valid ACF fields.
 */
function get_acf_fields( array $post_types = [], array $excluded_field_types = [], array $included_field_types = [], array $nested_types = [] ): array {
	$result = [];

	if ( ! function_exists( 'acf_get_field_groups' ) ) {
		return $result;
	}

	$acf_field_group_filters = array_reduce(
		$post_types,
		static function ( $acc, $item ) {
			$acc[] = [ 'post_type' => $item ];

			return $acc;
		},
		[]
	);

	$field_groups = acf_get_field_groups( $acf_field_group_filters );

	foreach ( $field_groups as $group ) {
		if ( empty( $group ) || ! $group['active'] ) {
			continue;
		}

		$fields = acf_get_fields( $group['key'] );

		if ( ! $fields ) {
			continue;
		}

		foreach ( $fields as $field ) {
			collect_flat_acf_fields(
				$field,
				$result,
				String_Transformation::camel_case( $group['title'] ),
				$excluded_field_types,
				$included_field_types,
				$nested_types
			);
		}
	}

	return $result;
}

/**
 * Collect ACF fields flatly, respecting unsupported and nested type rules.
 *
 * @param array  $field ACF field.
 * @param array  &$store Where to collect valid fields.
 * @param string $path_name Path name for the field.
 * @param array  $excluded_field_types Field types to completely skip.
 * @param array  $included_field_types Field types to include only.
 * @param array  $nested_types Field types to recurse into.
 */
function collect_flat_acf_fields( array $field, array &$store, string $path_name, array $excluded_field_types, array $included_field_types, array $nested_types ): void {
	$type = $field['type'];

	$is_nested = in_array( $type, $nested_types, true );

	// Include current field only if it's not a container-type you're choosing to traverse.
	if ( ! $is_nested ) {
		if ( is_excluded_field( $type, $excluded_field_types, $included_field_types ) ) {
			return;
		}

		$store[] = [
			'name'      => $field['name'],
			'label'     => $path_name . ' - ' . $field['label'],
			'path_name' => $path_name . '.' . $field['name'],
			'key'       => $field['key'],
			'type'      => $field['type'],
		];
	}

	if ( ! $is_nested ) {
		return;
	}

	if ( ! empty( $field['sub_fields'] ) ) {
		$path_name .= '.' . $field['name'];

		foreach ( $field['sub_fields'] as $sub_field ) {
			collect_flat_acf_fields(
				$sub_field,
				$store,
				$path_name,
				$excluded_field_types,
				$included_field_types,
				$nested_types
			);
		}
	}
	// TODO later can test/ support more nested types if needed ( like Acf_Factory::FLEXIBLE_CONTENT).
}

function is_excluded_field( $type, array $excluded_field_types, array $included_field_types ): bool {
	if ( ! empty( $excluded_field_types ) && in_array( $type, $excluded_field_types, true ) ) {
		return true;
	}

	return ! empty( $included_field_types ) && ! in_array( $type, $included_field_types, true );
}
