<?php

namespace AtlasSearch\Support\WooCommerce;

const WOO_ORDER_BY_MAPPING = [
	'popularity' => 'total_sales',
	'popular'    => 'total_sales',
	'rating'     => 'average_rating',
	'title'      => 'post_title.keyword',
	'price'      => 'price',
];

function get_order_by( $order_by, $query_vars, ?\WP_Query $query = null ) {
	if ( ! is_array( $query_vars ) ) {
		return $order_by;
	}

	$is_product_post_type = 'product' === ( $query_vars['post_type'] ?? '' );
	$is_wc_product_query  = null !== $query && ( $query->is_search() || ( $query->is_woocommerce_product_query ?? false ) );

	if ( ! $is_product_post_type && ! $is_wc_product_query ) {
		return $order_by;
	}

	$raw_orderby = $query_vars['orderby'] ?? null;

	if ( null === $raw_orderby || '' === $raw_orderby ) {
		return $order_by;
	}

	// Normalize type safely.
	if ( is_int( $raw_orderby ) ) {
		$raw_orderby = (string) $raw_orderby;
	}

	if ( ! is_string( $raw_orderby ) ) {
		return $order_by;
	}

	$parts = preg_split( '/[-_]/', $raw_orderby, 2 );

	$key = $parts[0] ?? '';

	if ( '' === $key || ! array_key_exists( $key, WOO_ORDER_BY_MAPPING ) ) {
		return $order_by;
	}

	$direction = $parts[1] ?? '';

	if ( '' === $direction ) {
		$direction = get_direction( $query_vars );
	}

	$direction = strtolower( (string) $direction );

	if ( 'asc' !== $direction && 'desc' !== $direction ) {
		$direction = 'asc';
	}

	return [
		'field'     => WOO_ORDER_BY_MAPPING[ $key ],
		'direction' => $direction,
	];
}

function get_direction( $query_vars ) {
	if (
		! isset( $query_vars['order'] )
		&& isset( $query_vars['orderby'] )
		&& in_array( $query_vars['orderby'], [ 'price', 'title' ] )
	) {
		return 'asc';
	}

	return $query_vars['order'] ?? 'desc';
}

function price_filter( $query ) {
	// Ensure we only modify the main query, and avoid unintended modification of admin queries.
	if ( $query->is_main_query() && ! is_admin() ) {
		if ( ! isset( $query->query_vars['min_price'] ) && ! isset( $query->query_vars['max_price'] ) ) {
			return;
		}

		$meta_query_args = [];

		if ( isset( $query->query_vars['min_price'] ) && isset( $query->query_vars['max_price'] ) ) {
			$meta_query_args = [
				[
					'key'     => 'price',
					'value'   => [ $query->query_vars['min_price'], $query->query_vars['max_price'] ],
					'type'    => 'NUMERIC',
					'compare' => 'BETWEEN',
				],
			];
		} elseif ( isset( $query->query_vars['min_price'] ) ) {
			$meta_query_args = [
				[
					'key'     => 'price',
					'value'   => $query->query_vars['min_price'],
					'compare' => '>=',
					'type'    => 'NUMERIC',
				],
			];
		} elseif ( isset( $query->query_vars['max_price'] ) ) {
			$meta_query_args = [
				[
					'key'     => 'price',
					'value'   => $query->query_vars['max_price'],
					'compare' => '<=',
					'type'    => 'NUMERIC',
				],
			];
		}

		// Add the meta query to the existing query.
		$meta_query = $query->get( 'meta_query' );

		if ( ! empty( $meta_query ) ) {
			$meta_query_args = array_merge( (array) $meta_query, $meta_query_args );
		}

		$query->set( 'meta_query', $meta_query_args );
	}
}

function enable_search( $query ) {
	if ( $query->is_main_query() && ! is_admin() ) {
		$query->is_woocommerce_product_query = true;
	}
}

function is_woocommerce_search_query( $query ) {
	return $query->is_main_query()
		&& ! is_admin()
		&& ( $query->is_search() || ( $query->is_woocommerce_product_query ?? false ) );
}

/**
 * Get catalog visibility filter for search queries.
 *
 * Returns a search filter string to filter products by catalog visibility.
 * This is applied directly to the search query filter, not as a meta_query,
 * because catalog_visibility only exists in the search index, not in WordPress postmeta.
 *
 * @param \WP_Query $query The WP_Query object.
 *
 * @return string|null The filter string or null if not applicable.
 */
function get_catalog_visibility_filter( $query ) {
	if ( ! ( $query->is_woocommerce_product_query ?? false ) ) {
		return null;
	}

	$visibility_filter = '(catalog_visibility:"search" OR catalog_visibility:"visible")';

	$hide_out_of_stock = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' );

	if ( $hide_out_of_stock ) {
		$visibility_filter .= ' AND (NOT stock_status:"outofstock")';
	}

	return $visibility_filter;
}
