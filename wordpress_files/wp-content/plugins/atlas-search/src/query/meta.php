<?php

namespace AtlasSearch\Query\Meta;

use const AtlasSearch\Support\WooCommerce\WOO_META_KEY_TO_INDEX_FIELD_MAP;

/**
 * Normalizes a WordPress meta key to its corresponding indexed field name.
 *
 * Uses the WooCommerce meta key mapping (WOO_META_KEY_TO_INDEX_FIELD_MAP)
 * when available. Unmapped keys pass through unchanged.
 *
 * @param string $meta_key The WordPress meta key (e.g. '_sku', '_stock_status').
 *
 * @return string The indexed field name (e.g. 'sku', 'stock_status'), or the original key if no mapping exists.
 */
function normalize_meta_key( string $meta_key ): string {
	if ( defined( 'AtlasSearch\Support\WooCommerce\WOO_META_KEY_TO_INDEX_FIELD_MAP' ) ) {
		return WOO_META_KEY_TO_INDEX_FIELD_MAP[ $meta_key ] ?? $meta_key;
	}

	return $meta_key;
}

/**
 * This function returns a string filter that represents the meta_query.
 *
 * $meta_query_args = array(
 *   'relation' => 'OR', // Optional, defaults to "AND"
 *   array(
 *       'key'     => '_my_custom_key',
 *       'value'   => 'Value I am looking for',
 *       'compare' => '='
 *   ),
 *   array(
 *       'relation' => 'AND',
 *       array(
 *           'key'     => '_my_custom_key_2',
 *           'value'   => 'Value I am looking for 2',
 *           'compare' => '='
 *       ),
 *       array(
 *           'key'     => '_my_custom_key_3',
 *           'value'   => 'Value I am looking for 3',
 *           'compare' => '='
 *       )
 *   )
 * );
 * $meta_query = new WP_Meta_Query( $meta_query_args );
 *
 * The above meta_query should return the following string:
 * _my_custom_key:"Value I am looking for" OR (_my_custom_key_2:"Value I am looking for 2" AND _my_custom_key_3:"Value I am looking for 3")
 *
 * @param \WP_Query|null $wp_query WP Query.
 *
 * @return string|null
 */
function get_meta_query_filter( \WP_Query $wp_query = null ) {
	if ( ! isset( $wp_query ) ) {
		return null;
	}

	if ( ! isset( $wp_query->meta_query ) ) {
		return null;
	}

	if ( empty( $wp_query->meta_query->queries ) ) {
		return null;
	}

	return meta_query_filter_recursive( $wp_query->meta_query->queries );
}

function meta_query_filter_recursive( array $wp_meta_queries ) {
	$queries  = [];
	$relation = $wp_meta_queries['relation'] ?? 'AND';

	foreach ( $wp_meta_queries as $key => $query ) {
		if ( 'relation' === $key ) {
			continue;
		}

		$compare = $query['compare'] ?? null;
		$key     = $query['key'] ?? null;

		if (
			isset( $key )
			&& ! isset( $query['value'] )
			&& ! in_array( $compare, [ 'EXISTS', 'NOT EXISTS', 'IN', 'NOT IN' ] )
		) {
			continue;
		}

		$queries[] = isset( $key ) ? generate_simple_query( $query ) : meta_query_filter_recursive( $query );
	}

	return implode(
		' ' . $relation . ' ',
		array_map(
			static fn ( $v ) => '(' . $v . ')',
			$queries
		)
	);
}

function generate_simple_query( $query ) {
	$compare = $query['compare'] ?? null;
	$type    = $query['type'] ?? 'CHAR';
	$key     = normalize_meta_key( $query['key'] ?? '' );

	switch ( $compare ) {
		case 'EXISTS':
		case 'NOT EXISTS':
			$inner_query = '_exists_' . inner_operator( $compare, $type ) . $key;

			break;
		default:
			$inner_query = $key . inner_operator( $compare, $type ) . wrap_value( $query );
	}

	return outer_operator( $inner_query, $compare );
}

function outer_operator( string $query_string, ?string $operator = '=' ) {
	switch ( $operator ) {
		case '!=':
		case 'NOT LIKE':
		case 'NOT EXISTS':
		case 'NOT IN':
		case 'NOT BETWEEN':
			return ' NOT (' . $query_string . ')';
		case '=':
		default:
			return $query_string;
	}
}

function inner_operator( ?string $operator = '=', ?string $type = 'CHAR' ) {
	if ( 'DATETIME' === $type ) {
		return ':';
	}

	switch ( $operator ) {
		case '>':
			return ':>';
		case '>=':
			return ':>=';
		case '<':
			return ':<';
		case '<=':
			return ':<=';
		case '=':
		default:
			return ':';
	}
}

function wrap_value( $query ) {
	$value   = $query['value'] ?? '';
	$type    = $query['type'] ?? 'CHAR';
	$compare = $query['compare'] ?? null;

	if ( 'LIKE' === $compare || 'NOT LIKE' === $compare ) {
		switch ( str_word_count( $value ) ) {
			case 1:
				return '*' . $value . '*';
			default:
				return '"*' . $value . '*"';
		}
	}

	if ( 'IN' === $compare || 'NOT IN' === $compare ) {
		if ( ! is_array( $value ) ) {
			return '""';
		}

		if ( count( $value ) <= 1 ) {
			return '"' . ( $value[0] ?? '' ) . '"';
		}

		return '(' . implode(
			' OR ',
			array_map(
				static fn ( $v ) => '"' . $v . '"',
				$value
			)
		) . ')';
	}

	if ( 'BETWEEN' === $compare || 'NOT BETWEEN' === $compare ) {
		switch ( $type ) {
			case 'DATETIME':
				return '[' . es_datetime( $value[0] ) . ' TO ' . es_datetime( $value[1] ) . ']';
			default:
				return '[' . $value[0] . ' TO ' . $value[1] . ']';
		}
	}

	if ( in_array( $type, [ 'NUMERIC', 'DATE' ] ) ) {
		return $value;
	}

	if ( 'DATETIME' === $type ) {
		$formatted_value = es_datetime( $value );

		switch ( $compare ) {
			case '>':
				return '{' . $formatted_value . ' TO *]';
			case '>=':
				return '[' . $formatted_value . ' TO *]';
			case '<':
				return '[* TO ' . $formatted_value . '}';
			case '<=':
				return '[* TO ' . $formatted_value . ']';
			case '=':
			case '!=':
			default:
				return '"' . $formatted_value . '"';
		}
	}

	return '"' . $value . '"';
}

function es_datetime( $datetime ) {
	$time_obj     = new \DateTime( $datetime );
	$has_timezone = 0 !== $time_obj->getOffset() || 'UTC' !== $time_obj->getTimezone()->getName();

	if ( $has_timezone ) {
		return $time_obj->format( 'c' );
	}

	return $time_obj->format( 'Y-m-d\TH:i:s' );
}
