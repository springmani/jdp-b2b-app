<?php

/**
 * Order Products list table columns: Title, then Product Regions, Industries, Segments, Solutions.
 */
function jdpower_product_posts_columns_order( $columns ) {
	$taxonomy_keys = array(
		'taxonomy-product_region',
		'taxonomy-product_industry',
		'taxonomy-product_segment',
		'taxonomy-product_solution',
	);

	$ordered = array();

	if ( isset( $columns['cb'] ) ) {
		$ordered['cb'] = $columns['cb'];
	}

	if ( isset( $columns['title'] ) ) {
		$ordered['title'] = $columns['title'];
	}

	foreach ( $taxonomy_keys as $key ) {
		if ( isset( $columns[ $key ] ) ) {
			$ordered[ $key ] = $columns[ $key ];
		}
	}

	foreach ( $columns as $key => $label ) {
		if ( 'cb' === $key || 'title' === $key ) {
			continue;
		}
		if ( in_array( $key, $taxonomy_keys, true ) ) {
			continue;
		}
		$ordered[ $key ] = $label;
	}

	return $ordered;
}
add_filter( 'manage_product_posts_columns', 'jdpower_product_posts_columns_order', 20 );
