<?php

namespace AtlasSearch\Hooks;

use WP_Post;

/**
 * Errors
 */
class InvalidExtraFieldsError extends \ErrorException {
}
class InvalidIdPrefixError extends \ErrorException {
}
class InvalidExtraSearchConfigFieldsError extends \ErrorException {
}
class InvalidExcludedPostTypesError extends \ErrorException {
}

/**
 * HOOK NAMES
 */
const SMART_SEARCH_HOOK_ID_PREFIX                      = 'wpe_smartsearch/id_prefix';
const SMART_SEARCH_HOOK_EXTRA_FIELDS                   = 'wpe_smartsearch/extra_fields';
const SMART_SEARCH_EXTRA_SEARCH_CONFIG_FIELDS          = 'wpe_smartsearch/extra_search_config_fields';
const SMART_SEARCH_EXCLUDED_POST_TYPES                 = 'wpe_smartsearch/excluded_post_types';
const SMART_SEARCH_SEARCH_FACET_BLOCKS_ENABLED         = 'wpe_smartsearch/search/facet_blocks_enabled';
const SMART_SEARCH_FILTER_ALLOW_POST_CONTENT_FILTERING = 'wpe_smartsearch/allow_post_content_filtering';

/**
 * Filters index fields before the indexing operation.
 *
 * @param array    $post_data The post array daya.
 * @param \WP_Post $post The post.
 *
 * @throws \AtlasSearch\Hooks\InvalidExtraFieldsError Throws if the returned data from the filter is invalid.
 */
function filter_extra_fields( array $post_data, WP_Post $post ) {
	$data = apply_filters( SMART_SEARCH_HOOK_EXTRA_FIELDS, $post_data, $post );

	if ( ! is_array( $data ) ) {
		$type = gettype( $data );

		throw new InvalidExtraFieldsError(
			wp_kses_data( "the filter '" . SMART_SEARCH_HOOK_EXTRA_FIELDS . "' returned invalid data of type: $type" )
		);
	}

	if ( empty( $data ) ) {
		throw new InvalidExtraFieldsError(
			wp_kses_data( "the filter '" . SMART_SEARCH_HOOK_EXTRA_FIELDS . "' returned invalid empty array" )
		);
	}

	return $data;
}

/**
 * Generates the index id for a given post type.
 *
 * @param string $post_type The post type.
 * @param int    $post_id The post ID.
 *
 * @throws \AtlasSearch\Hooks\InvalidIdPrefixError Throws if the returned data from the filter is invalid.
 */
function filter_index_id( string $post_type, int $post_id ) {
	$id = $post_type . ':' . $post_id;

	$prefix = filter_id_prefix();

	if ( ! $prefix ) {
		return $id;
	}

	return $prefix . ':' . $id;
}

/**
 * Filters search config fields.
 *
 * @param array       $fields List of search config fields.
 * @param string|null $post_type The post type.
 *
 * @return array
 *
 * @throws \AtlasSearch\Hooks\InvalidExtraSearchConfigFieldsError Throws if the returned data from the filter is invalid.
 */
function filter_extra_search_config_fields( array $fields, $post_type ) {
	$search_config = apply_filters( SMART_SEARCH_EXTRA_SEARCH_CONFIG_FIELDS, $fields, $post_type );

	if ( ! is_array( $search_config ) ) {
		$type = gettype( $search_config );

		throw new InvalidExtraSearchConfigFieldsError(
			wp_kses_data(
				"the filter '" . SMART_SEARCH_EXTRA_SEARCH_CONFIG_FIELDS .
				"' returned invalid data of type: $type for post_type:$post_type." .
				' Please amend the filter to return an array of field names.'
			)
		);
	}

	return $search_config;
}

/**
 * Filters a list of post types that should not be considered for search
 *
 * @throws \AtlasSearch\Hooks\InvalidExcludedPostTypesError Throws if the returned data from the filter is invalid.
 */
function filter_excluded_post_types() {
	$excluded_post_types = apply_filters( SMART_SEARCH_EXCLUDED_POST_TYPES, [] );

	if ( ! is_array( $excluded_post_types ) ) {
		$type = gettype( $excluded_post_types );

		throw new InvalidExcludedPostTypesError(
			wp_kses_data(
				"the filter '" . SMART_SEARCH_EXCLUDED_POST_TYPES .
				"' returned invalid data of type: $type." .
				' Please amend the filter to return an array of post types to exclude from WP Engine Smart Search.'
			)
		);
	}

	return $excluded_post_types;
}

/**
 * Filters the id prefix.
 *
 * @throws \AtlasSearch\Hooks\InvalidIdPrefixError Throws if the returned data from the filter is invalid.
 */
function filter_id_prefix() {
	$prefix = apply_filters( SMART_SEARCH_HOOK_ID_PREFIX, false );

	if ( ! $prefix ) {
		return false;
	}

	if ( preg_match( '/^[A-Za-z0-9]+$/', $prefix ) ) {
		return $prefix;
	}

	throw new InvalidIdPrefixError(
		wp_kses_data(
			"the filter '" . SMART_SEARCH_HOOK_ID_PREFIX . "' returned: '$prefix' which contains non-alphabet characters or invalid characters."
		)
	);
}

/**
 * Filters whether seearch facets are enabled.
 *
 * @return bool Whether WP Engine search facet blocks are enabled.
 */
function filter_search_facets_enabled(): bool {
	return (bool) apply_filters( SMART_SEARCH_SEARCH_FACET_BLOCKS_ENABLED, true );
}
