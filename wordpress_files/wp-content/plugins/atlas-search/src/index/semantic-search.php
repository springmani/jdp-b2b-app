<?php

namespace AtlasSearch\Index;

use Wpe_Content_Engine\Helper\Exceptions\ClientQueryGraphqlErrorsException;

const CONFIGRE_SEMANTIC_SEARCH_GRAPHQL = <<<'GRAPHQL'
mutation ConfigureSemanticSearch($fields: [String!]!){
  config {
    semanticSearch(fields: $fields) {
      fields
	  fieldLimit
    }
  }
}
GRAPHQL;

const GET_SEMANTIC_SEARCH_CONFIG_GRAPHQL = <<<'GRAPHQL'
query GetSemanticSearchConfig{
  config {
    semanticSearch {
      fields
	  fieldLimit
    }
  }
}
GRAPHQL;

const SEMANTIC_SEARCH_CONFIG_KEY = 'wpe_smart_search_semantic_search_config';

/**
 * Configure semantic search
 *
 * @param array $fields The fields to configure.
 * @param int   $search_bias The search bias value.
 * @param bool  $enabled Whether semantic search is enabled or not.
 *
 * @return array
 */
function set_semantic_search_config( array $fields, int $search_bias, bool $enabled = false ) {
	$variables = [
		'fields'     => $fields,
		'searchBias' => $search_bias,
	];

	graphql( CONFIGRE_SEMANTIC_SEARCH_GRAPHQL, $variables );

	$config = [
		'fields'     => $fields,
		'searchBias' => $search_bias,
		'enabled'    => $enabled,
	];
	store_config( $config, 12 * HOUR_IN_SECONDS );

	return $config;
}

/**
 * Get semantic search config
 *
 * @param bool $use_cache Whether to use the cache or not.
 *
 * @return array
 *
 * @throws \Wpe_Content_Engine\Helper\Exceptions\ClientQueryGraphqlErrorsException Throws error Exception.
 */
function get_semantic_search_config( $use_cache = false ) {
	if ( $use_cache ) {
		$config = \AtlasSearch\Support\WordPress\get_transient( SEMANTIC_SEARCH_CONFIG_KEY );

		if ( false !== $config ) {
			return $config;
		}
	}

	// Fetch the config from the database.
	$config = \AtlasSearch\Support\WordPress\get_option(
		SEMANTIC_SEARCH_CONFIG_KEY,
		[
			'enabled'    => false,
			'searchBias' => 0,
			'fields'     => [],
		]
	);

	try {
		// Fetch the config from the API.
		$result           = graphql( GET_SEMANTIC_SEARCH_CONFIG_GRAPHQL, [] );
		$config['fields'] = $result['data']['config']['semanticSearch']['fields'];

		store_config( $config, 12 * HOUR_IN_SECONDS );
		// does not need to be stored in the cache.
		$config['fieldLimit'] = $result['data']['config']['semanticSearch']['fieldLimit'];

		return $config;
	} catch ( ClientQueryGraphqlErrorsException $e ) {
		$config['enabled'] = false;
		store_config( $config, MINUTE_IN_SECONDS );

		throw $e;
	}
}

function store_config( $config, $expiration ) {
	\AtlasSearch\Support\WordPress\update_option( SEMANTIC_SEARCH_CONFIG_KEY, $config );
	// Update the config in the cache.
	\AtlasSearch\Support\WordPress\set_transient( SEMANTIC_SEARCH_CONFIG_KEY, $config, $expiration );
}
