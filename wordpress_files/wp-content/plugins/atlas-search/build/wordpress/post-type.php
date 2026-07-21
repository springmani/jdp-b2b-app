<?php

namespace AtlasSearch\Blocks\WordPress;

function get_available_post_types( array $excluded_slugs = [] ): array {
	$post_types = get_post_types(
		[
			'public'  => true,
			'show_ui' => true,
		],
		'objects'
	);

	$formatted = [];

	foreach ( $post_types as $pt ) {
		if ( in_array( $pt->name, [ 'attachment', 'nav_menu_item' ], true ) ) {
			continue;
		}

		// Display-only exclusion: Filter out excluded post types.
		if ( ! empty( $excluded_slugs ) && in_array( $pt->name, $excluded_slugs, true ) ) {
			continue;
		}

		$count_obj = wp_count_posts( $pt->name );
		$count     = $count_obj->publish ?? 0;

		// Hide empty post types.
		if ( 0 === $count ) {
			continue;
		}

		$formatted[] = (object) [
			'name'  => $pt->labels->name,
			'slug'  => $pt->name,
			'count' => $count,
		];
	}

	return $formatted;
}
