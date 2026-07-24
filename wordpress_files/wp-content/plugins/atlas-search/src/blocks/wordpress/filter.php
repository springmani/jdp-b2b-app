<?php

namespace AtlasSearch\Blocks\WordPress;

const RANGE_SLIDER_PREFIX = 'range_';
const ATTRIBUTES_PREFIX   = 'pa_';
const TAXONOMY_MAPPINGS   = [
	'category' => 'category_name',
	'post_tag' => 'tag',
];

function convert_url_filter_to_data_source( $url_filter ) {
	if ( empty( $url_filter ) ) {
		return $url_filter;
	}

	$reverse_taxonomy_mappings = array_flip( TAXONOMY_MAPPINGS );

	if ( isset( $reverse_taxonomy_mappings[ $url_filter ] ) ) {
		return $reverse_taxonomy_mappings[ $url_filter ];
	}

	if ( 0 === strpos( $url_filter, RANGE_SLIDER_PREFIX ) ) {
		return str_replace( RANGE_SLIDER_PREFIX, '', $url_filter );
	}

	return $url_filter;
}

function in_filter( $filter_value, $filter ): bool {
	if ( is_array( $filter ) ) {
		return in_array( $filter_value, $filter, true );
	}

	return $filter_value === $filter;
}

function extract_filters( ?array $query_params ) {
	return array_map(
		static function ( $value ) {
			if ( is_string( $value ) && false !== strpos( $value, ',' ) ) {
				return explode( ',', $value );
			}

			if ( is_string( $value ) && false !== strpos( $value, '+' ) ) {
				return explode( '+', $value );
			}

			if ( is_string( $value ) && false !== strpos( $value, ' ' ) ) {
				return explode( ' ', $value );
			}

			return $value;
		},
		$query_params ?? []
	);
}

function prefix_filter( $filter, $prefix ): string {
	if ( empty( $filter ) ) {
		return '';
	}

	if ( empty( $prefix ) || 0 === strpos( $filter, $prefix ) ) {
		return $filter;
	}

	return $prefix . $filter;
}

function filters_from_query_string( ?string $server_query_string ) {
	if ( ! empty( $server_query_string ) ) {
		$filters            = [];
		$query_string_parts = explode( '&', $server_query_string );

		foreach ( $query_string_parts as $part ) {
			$param_parts     = explode( '=', $part, 2 );
			$key             = urldecode( $param_parts[0] );
			$value           = isset( $param_parts[1] ) ? urldecode( $param_parts[1] ) : '';
			$sanitized_value = filter_var( $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$filters[ $key ] = $sanitized_value;
		}

		return $filters;
	}
}

function get_query_string(): ?string {
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	return isset( $_SERVER['QUERY_STRING'] ) ? wp_unslash( $_SERVER['QUERY_STRING'] ) : null;
}

/**
 * Get the filter mode for a taxonomy from query string.
 *
 * Auto-detects mode based on separator in the filter value:
 * - Comma (,) = OR mode
 * - Plus (+) or space = AND mode
 *
 * @param string $taxonomy The taxonomy name (e.g., 'category_name', 'tag').
 *
 * @return string 'and' or 'or'
 */
function get_taxonomy_filter_mode( string $taxonomy ): string {
	// Default.
	$mode = 'or';

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET[ $taxonomy ] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$value = wp_unslash( $_GET[ $taxonomy ] );

		if ( is_string( $value ) ) {
			// Check for separators to determine mode.
			if ( false !== strpos( $value, '+' ) || false !== strpos( $value, ' ' ) ) {
				$mode = 'and';
			} elseif ( false !== strpos( $value, ',' ) ) {
				$mode = 'or';
			}
		}
	}

	return $mode;
}
