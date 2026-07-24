<?php
/**
 * This file updates the query on the front end based on custom query attributes.
 *
 * @package Wpe_Content_Engine
 */

namespace AtlasSearch\Blocks\WordPress\RecommendationsBlock;

const RECOMMENDATIONS_BLOCK_NAMESPACE = 'wpengine-smart-search/recommendations';

add_filter( 'pre_render_block', __NAMESPACE__ . '\pre_render_block', 10, 2 );

function pre_render_block( $pre_render, $block ) {
	if ( RECOMMENDATIONS_BLOCK_NAMESPACE === ( $block['attrs']['namespace'] ?? null ) ) {
		$GLOBALS['rec_block'] = $block;

		if ( isset( $block['attrs']['query'] ) ) {
			$block['attrs']['query']['postsPerPage'] = (int) ( $block['attrs']['postLimit'] ?? 5 );
		}

		add_filter( 'query_loop_block_query_vars', __NAMESPACE__ . '\modify_query_vars', 10, 1 );
	}

	return $pre_render;
}

function modify_query_vars( $query ) {
	$block = $GLOBALS['rec_block'];

    // phpcs:ignore
    do_action( 'qm/debug', $block['attrs'] );

	$post_limit           = (int) ( $block['attrs']['postLimit'] ?? 5 );
	$recommendations_type = (string) ( $block['attrs']['recommendationsType'] ?? 'trending' );

	if ( 'trending' === $recommendations_type ) {
		$popular_ids = get_trending_posts( $post_limit );
	} else {
		// TODO: handle cases where the global $post is not set
		// consider using is singular to check if we are on a single post page.
		global $post;

		if ( ! $post ) {
			return $query;
		}

		$post_type   = get_post_type( $post );
		$post_id     = $post->ID;
		$popular_ids = get_related_posts( $post_type, $post_id, $post_limit );
	}

	if ( empty( $popular_ids ) ) {
		return $query;
	}

	$query['post__in'] = $popular_ids;

	$query['orderby']        = 'post__in';
	$query['posts_per_page'] = $post_limit;

	remove_filter( 'query_loop_block_query_vars', __NAMESPACE__ . '\modify_query_vars' );

	return $query;
}

const TRENDING_GRAPHQL = <<<'GRAPHQL'
query Trending($count: Int!, $site: String) {
  recommendations(count: $count, site: $site) {
	trendingDocuments {
	  docID
	  count
	}
  }
}
GRAPHQL;

const RELATED_GRAPHQL = <<<'GRAPHQL'
query Recommend($count: Int!, $doc_id: String!, $site: String) {
	recommendations(count: $count, site: $site) {
		relatedDocuments(docID: $doc_id) {
			docID
			score
			source
		}
	}
}

GRAPHQL;

function get_trending_posts( $count ) {
	$vars = [ 'count' => $count ];

	if ( is_multisite() ) {
		$vars['site'] = (string) get_current_blog_id();
	}

	try {
		$data = \AtlasSearch\Index\graphql( TRENDING_GRAPHQL, $vars );

		if ( isset( $data['errors'] ) ) {
			return [];
		}

		$docs = $data['data']['recommendations']['trendingDocuments'] ?? [];

		return format_docs_as_ids( $docs );
	} catch ( \Throwable $e ) {
		return [];
	}
}

function format_docs_as_ids( $docs ) {
	if ( empty( $docs ) ) {
		return [];
	}

	$ids = [];

	foreach ( $docs as $doc ) {
		$doc_parts = explode( ':', $doc['docID'] );
		// Extract the post ID which is always the last part.
		// Format: post_type:post_id or site_id:post_type:post_id (multisite).
		$ids[] = end( $doc_parts );
	}

	return array_unique( $ids );
}

function get_related_posts( $post_type, $post_id, $count ) {
	$doc_id = \AtlasSearch\Hooks\filter_index_id( $post_type, $post_id );

	$vars = [
		'count'  => $count,
		'doc_id' => $doc_id,
	];

	if ( is_multisite() ) {
		$vars['site'] = (string) get_current_blog_id();
	}

	try {
		$data = \AtlasSearch\Index\graphql( RELATED_GRAPHQL, $vars );

		$docs = $data['data']['recommendations']['relatedDocuments'] ?? [];

		if ( isset( $data['errors'] ) ) {
			return [];
		}

		return format_docs_as_ids( $docs );
	} catch ( \Throwable $e ) {
		return [];
	}
}
