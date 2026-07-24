<?php

declare(strict_types=1);

namespace AtlasSearch\Blocks\WordPress\RecommendationsBlock;

function get_recommendations_post_ids( $post_limit = 5, $recommendations_type = 'trending' ) {
	global $post;

	if ( 'trending' === $recommendations_type ) {
		$popular_ids = get_trending_posts( $post_limit );
	} elseif ( 'related' === $recommendations_type && $post ) {
		$post_type   = get_post_type( $post );
		$post_id     = $post->ID;
		$popular_ids = get_related_posts( $post_type, $post_id, $post_limit );
	} else {
		return [];
	}

	if ( empty( $popular_ids ) ) {
		return [];
	}

	return $popular_ids;
}
