<?php

namespace AtlasSearch\Blocks\WordPress\RangeFacet;

use function AtlasSearch\Blocks\WordPress\filters_from_query_string;
use function AtlasSearch\Blocks\WordPress\get_query_string;
use function AtlasSearch\Support\WooCommerce\is_woo_enabled;
use function Wpe_Content_Engine\Helper\Acf_Support\is_acf_loaded;

use const AtlasSearch\Blocks\WordPress\RANGE_SLIDER_PREFIX;

/**
 * Returns true when the range slider meta query should be applied to $query.
 *
 * @param \WP_Query $query The WP_Query object.
 */
function should_apply_range_filter( \WP_Query $query ): bool {
	if ( ! is_woo_enabled() && ! is_acf_loaded() ) {
		return false;
	}

	if ( ! $query->is_main_query() || is_admin() ) {
		return false;
	}

	// Allow WooCommerce product archive pages when Atlas Search is intercepting
	// the query (flag set by enable_search() on woocommerce_product_query hook).
	// Using this flag rather than is_product_archive() ensures the meta_query is
	// only added when Atlas Search will actually translate it — the raw meta key
	// 'price' does not match WooCommerce's '_price' in SQL.
	$is_woo_product_query = $query->is_woocommerce_product_query ?? false;

	return $query->is_search() || $is_woo_product_query;
}

/**
 * Parses a single range filter string into a WP_Query meta_query entry.
 * Returns null when the value is missing or empty.
 *
 * @param string      $filter       The filter key (e.g. range_price).
 * @param string|null $filter_value The raw comma-separated "min,max" string.
 */
function build_range_query_for_filter( string $filter, ?string $filter_value ): ?array {
	if ( ! isset( $filter_value ) || '' === trim( $filter_value ) ) {
		return null;
	}

	$field_name = str_replace( RANGE_SLIDER_PREFIX, '', $filter );
	$value_arr  = explode( ',', $filter_value );
	$from       = (int) $value_arr[0];
	$to         = ( isset( $value_arr[1] ) && '' !== $value_arr[1] ) ? $value_arr[1] : null;

	if ( isset( $to ) && $to < $from ) {
		$to = null;
	}

	return [
		'relation' => 'AND',
		[
			'key'     => $field_name,
			'type'    => 'NUMERIC',
			'value'   => isset( $to ) ? [ $from, $to ] : $from,
			'compare' => isset( $to ) ? 'BETWEEN' : '>=',
		],
	];
}

/**
 * Iterates query string filters and builds a combined meta_query array
 * for all range_* entries.
 *
 * @param array $filters Parsed query-string filters.
 */
function build_range_meta_queries( array $filters ): array {
	$meta_query = [];

	foreach ( $filters as $filter => $filter_value ) {
		if ( empty( $filter ) || 0 !== strpos( $filter, RANGE_SLIDER_PREFIX ) ) {
			continue;
		}

		$entry = build_range_query_for_filter( $filter, $filter_value );

		if ( null !== $entry ) {
			$meta_query = array_merge( $meta_query, $entry );
		}
	}

	return $meta_query;
}

/**
 * Creates meta queries for the range slider based on URL params.
 * The filter value is a comma-separated string of two values, min and max.
 * A meta query is created for each filter.
 * The meta query is added to the existing meta queries.
 *
 * @param \WP_Query $query The WP_Query object.
 */
function filter_range_slider( \WP_Query $query ) {
	if ( ! should_apply_range_filter( $query ) ) {
		return;
	}

	$new_meta_query = build_range_meta_queries(
		filters_from_query_string( get_query_string() ) ?? []
	);

	if ( empty( $new_meta_query ) ) {
		return;
	}

	$existing_meta_query = $query->get( 'meta_query' );
	$query->set(
		'meta_query',
		empty( $existing_meta_query )
			? $new_meta_query
			: array_merge( $new_meta_query, [ $existing_meta_query ] )
	);
}
