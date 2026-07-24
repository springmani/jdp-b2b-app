<?php

namespace AtlasSearch\Query\Taxonomy;

use function AtlasSearch\Blocks\WordPress\get_taxonomy_filter_mode;

use const AtlasSearch\Blocks\WordPress\TAXONOMY_MAPPINGS;

/**
 * Converts WP_Query tax_query to Elasticsearch filter string.
 * Supports nested taxonomy queries with unlimited depth.
 *
 * @param \WP_Query $wp_query Tax query.
 *
 * @return string|null
 */
function get_taxonomy_filter( \WP_Query $wp_query = null ) {
	if ( ! isset( $wp_query->tax_query ) ) {
		return null;
	}

	if ( empty( $wp_query->tax_query->queries ) ) {
		return null;
	}

	$result = taxonomy_query_filter_recursive( $wp_query->tax_query->queries, $wp_query->tax_query->relation ?? 'AND' );

	return '' === $result ? null : $result;
}

/**
 * Recursively processes taxonomy queries, supporting nested groups.
 *
 * @param array  $tax_queries   The taxonomy queries array.
 * @param string $top_relation  The relation to use (AND/OR).
 *
 * @return string The filter string, or empty string if no valid queries.
 */
function taxonomy_query_filter_recursive( array $tax_queries, string $top_relation = 'AND' ) {
	$queries  = [];
	$relation = $tax_queries['relation'] ?? $top_relation;

	foreach ( $tax_queries as $key => $query ) {
		if ( 'relation' === $key || ! is_numeric( $key ) ) {
			continue;
		}

		if ( isset( $query['taxonomy'] ) ) {
			$result = generate_simple_taxonomy_query( $query );

			if ( '' !== $result ) {
				$queries[] = $result;
			}
		} elseif ( is_array( $query ) ) {
			$result = taxonomy_query_filter_recursive( $query, $relation );

			if ( '' !== $result ) {
				$queries[] = '(' . $result . ')';
			}
		}
	}

	$non_empty_queries = [];

	foreach ( $queries as $q_str ) {
		if ( is_string( $q_str ) && '' !== $q_str ) {
			$non_empty_queries[] = $q_str;
		}
	}

	if ( empty( $non_empty_queries ) ) {
		return '';
	}

	return implode( ' ' . $relation . ' ', $non_empty_queries );
}

/**
 * Generates filter string for a single taxonomy query.
 *
 * @param array $query The taxonomy query array.
 *
 * @return string The filter string, or empty string if invalid.
 */
function generate_simple_taxonomy_query( array $query ) {
	if ( ! isset( $query['taxonomy'] ) || ! isset( $query['terms'] ) || empty( $query['terms'] ) ) {
		return '';
	}

	$tax_name      = get_taxonomy_name( $query['taxonomy'] );
	$operator      = $query['operator'] ?? 'IN';
	$full_tax_path = calculate_full_tax_path( $tax_name, $query['field'] );
	$quote         = in_array( $operator, [ 'NOT IN', 'IN' ], true ) ? '"' : '';

	switch ( $operator ) {
		case 'NOT IN':
		case 'NOT EXISTS':
			$template = 'NOT ' . $full_tax_path;
			$terms    = array_map(
				static fn ( $term ) => $template . $quote . $term . $quote,
				$query['terms']
			);

			return implode( ' ', $terms );
		case 'IN':
		case 'EXISTS':
		default:
			$taxonomy       = $query['taxonomy'] ?? '';
			$query_var_name = TAXONOMY_MAPPINGS[ $taxonomy ] ?? $taxonomy;
			$mode           = get_taxonomy_filter_mode( $query_var_name );

			if ( 'and' === $mode && count( $query['terms'] ) > 1 ) {
				$term_conditions = array_map(
					static fn ( $term ) => '(' . $full_tax_path . $quote . $term . $quote . ')',
					$query['terms']
				);

				return '(' . implode( ' AND ', $term_conditions ) . ')';
			}

			if ( 1 === count( $query['terms'] ) ) {
				return '(' . $full_tax_path . $quote . $query['terms'][0] . $quote . ')';
			}

			$term_strings = array_map(
				static fn ( $term ) => $quote . $term . $quote,
				$query['terms']
			);

			return '(' . $full_tax_path . '(' . implode( ' OR ', $term_strings ) . '))';
	}
}

function calculate_full_tax_path( string $tax_name, string $tax_field ): string {
	$base_path            = $tax_name . '.' . $tax_field;
	$requires_keyword     = in_array( $tax_field, [ 'slug', 'name' ], true );
	$base_path_no_keyword = $base_path . ':';

	return $requires_keyword
		? $base_path . '.keyword:'
		: $base_path_no_keyword;
}

/**
 * This function is used to map the taxonomy name to the fields in the index.
 *
 * @param string $taxonomy Taxonomy name.
 *
 * @return string
 */
function get_taxonomy_name( $taxonomy ) {
	if ( 'category' === $taxonomy ) {
		return 'categories';
	}

	if ( 'post_tag' === $taxonomy ) {
		return 'tags';
	}

	return $taxonomy;
}
