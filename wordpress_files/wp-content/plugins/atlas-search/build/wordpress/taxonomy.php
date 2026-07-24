<?php

namespace AtlasSearch\Blocks\WordPress;

function get_taxonomy_terms( ?string $source = 'category', array $excluded_slugs = [] ): array {
	$taxonomy_terms = get_terms(
		[
			'taxonomy'   => $source,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => 0,
		]
	);

	if ( ( $taxonomy_terms instanceof \WP_Error ) && 'invalid_taxonomy' === $taxonomy_terms->get_error_code() ) {
		// TODO consider throwing an exception here.
		return [];
	}

	// Display-only exclusion: Filter out excluded terms.
	if ( ! empty( $excluded_slugs ) ) {
		$taxonomy_terms = array_filter(
			$taxonomy_terms,
			static fn ( $term ) => ! in_array( $term->slug, $excluded_slugs, true )
		);
	}

	return $taxonomy_terms;
}
