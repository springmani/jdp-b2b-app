<?php

namespace AtlasSearch\Index;

use WP_Post;
use Wpe_Content_Engine\Helper\Acf_Support\Acf_Factory;
use Wpe_Content_Engine\Helper\Admin_Notice;
use Wpe_Content_Engine\Helper\Constants\Post_Status;
use Wpe_Content_Engine\Helper\Constants\Post_Type;
use Wpe_Content_Engine\Helper\Exceptions\MissingSettingsException;
use Wpe_Content_Engine\Helper\Sync\GraphQL\Client;
use Wpe_Content_Engine\WPSettings;

use const AtlasSearch\Hooks\SMART_SEARCH_FILTER_ALLOW_POST_CONTENT_FILTERING;

const INDEX_GRAPHQL = <<<'GRAPHQL'
mutation IndexRecord($input: DocumentInput!) {
	index(input: $input) {
		code
		document {
			data
			id
		}
		message
		success
	}
}
GRAPHQL;

const DELETE_GRAPHQL = <<<'GRAPHQL'
mutation DeleteRecord($id: ID!, $meta: MetaInput) {
  delete(id: $id, meta: $meta) {
    code
    message
    success
  }
}
GRAPHQL;

const DELETE_ALL_GRAPHQL = <<<'GRAPHQL'
mutation DeleteAllRecords($meta: MetaInput) {
  deleteAll(meta: $meta){
    code
    message
    success
  }
}
GRAPHQL;

const BULK_INDEX_GRAPHQL = <<<'GRAPHQL'
mutation BulkIndex($docs: [DocumentInput!], $meta: MetaInput) {
  bulkIndex(input: {
    documents: $docs
    meta: $meta
  }) {
    code
  }
}
GRAPHQL;

function format_date( $date ) {
	return wp_date( 'Y-m-d\TH:i:s', strtotime( $date ) );
}

const MANUAL_INDEX         = 'MANUAL';
const AUTOMATIC_INDEX      = 'AUTOMATIC';
const ID_PREFIX_FIELD_NAME = '__id_prefix';

/**
 * Indexes a WP_Post
 *
 * @param \WP_Post $post The post.
 * @param int      $post_id The post id.
 * @param string   $index_type The index operation type.
 */
function index_post( WP_Post $post, int $post_id, string $index_type = AUTOMATIC_INDEX ) {
	if ( ! should_be_indexed( $post ) || ! is_allowed( $post ) ) {
		return false;
	}

	$data = prepare_post( $post, $post_id, $index_type );

	return graphql( INDEX_GRAPHQL, $data );
}

/**
 * Indexes a list of WP_Posts
 *
 * @param \WP_Post[] $posts The posts.
 *
 * @return mixed
 */
function bulk_index_posts( $posts ) {
	$posts_to_index = [];

	foreach ( $posts as $post ) {
		if ( should_be_indexed( $post ) && is_allowed( $post ) ) {
			$posts_to_index[] = prepare_post( $post, $post->ID, MANUAL_INDEX )['input'];
		}
	}

	if ( empty( $posts_to_index ) ) {
		return false;
	}

	// Index the posts.
	$variables = [
		'docs' => $posts_to_index,
		'meta' => [
			'system' => \AtlasSearch\Meta\get_system_version(),
			'action' => MANUAL_INDEX . ':bulk-index:' . $post->post_type . ':count=' . count( $posts_to_index ),
			'source' => \AtlasSearch\Meta\get_domain_name(),
		],
	];

	return graphql( BULK_INDEX_GRAPHQL, $variables );
}

/**
 * Prepare a post for indexing.
 *
 * @param \WP_Post $post The post.
 * @param int      $post_id The post id.
 * @param string   $index_type The index operation type.
 *
 * @return array
 */
function prepare_post( WP_Post $post, int $post_id, string $index_type = AUTOMATIC_INDEX ) {
	$post_array                      = filter_wp_object_to_array( $post );
	$post_array['post_date_gmt']     = format_date( $post->post_date_gmt );
	$post_array['post_date']         = format_date( $post->post_date );
	$post_array['post_modified_gmt'] = format_date( $post->post_modified_gmt );
	$post_array['post_modified']     = format_date( $post->post_modified );
	$allow_post_content_filtering    = apply_filters( SMART_SEARCH_FILTER_ALLOW_POST_CONTENT_FILTERING, true );

	if ( $allow_post_content_filtering ) {
		try {
			$post_array['post_content'] = handle_tags( $post_array['post_content'] );
		} catch ( \Throwable $e ) {
			if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( '\\WP_CLI' ) ) {
				\WP_CLI::warning(
					sprintf(
						'Post %d ("%s"): content rendering failed — %s. Indexed with raw content.',
						$post_id,
						wp_strip_all_tags( $post->post_title ),
						str_replace( [ "\r", "\n" ], ' ', $e->getMessage() )
					)
				);
			}
		}
	}

	// Get taxonomies.
	$terms = terms_for_post( $post );

	// Get author.
	$author_array = author_for_post( $post );

	$post_array             = array_merge( $post_array, $terms, $author_array );
	$post_array['post_url'] = get_permalink( $post );

	// Get ACF content.
	$acf_info    = Acf_Factory::build_acf_helper_for_type( $post_id, $post->post_type );
	$coordinates = [];

	if ( $acf_info ) {
		$post_array  = array_merge( $post_array, $acf_info->get_data() );
		$coordinates = $acf_info->get_coordinates();
	}

	/**
	 * Apply filters
	 */

	$id_prefix = \AtlasSearch\Hooks\filter_id_prefix();

	if ( $id_prefix ) {
		$post_array = array_merge( $post_array, [ \AtlasSearch\Index\ID_PREFIX_FIELD_NAME => $id_prefix ] );
	}

	$post_array = \AtlasSearch\Hooks\filter_extra_fields( $post_array, $post );

	$input = [
		'id'   => \AtlasSearch\Hooks\filter_index_id( $post->post_type, $post_id ),
		'data' => $post_array,
		'meta' => [
			'system' => \AtlasSearch\Meta\get_system_version(),
			'action' => $index_type . ':index:' . $post->post_type,
			'source' => \AtlasSearch\Meta\get_domain_name(),
		],
	];

	// Add coordinates if any were found.
	if ( ! empty( $coordinates ) ) {
		$input['coordinates'] = $coordinates;
	}

	return [
		'input' => $input,
	];
}

function should_be_indexed( WP_Post $post ): bool {
	return Post_Status::WP_PUBLISH === $post->post_status && ! post_password_required( $post );
}

function author_for_post( \WP_Post $post ) {
	if ( ! post_type_supports( $post->post_type, 'author' ) ) {
		return [];
	}

	$author = get_userdata( $post->post_author );

	if ( ! $author ) {
		return [ 'author' => null ];
	}

	return [
		'author' => [
			'user_nicename' => $author->user_nicename,
		],
	];
}

function delete_post( WP_Post $post, int $post_id, $index_type = AUTOMATIC_INDEX ) {
	if ( false === is_allowed( $post ) ) {
		return false;
	}

	$variables = [
		'id'   => \AtlasSearch\Hooks\filter_index_id( $post->post_type, $post_id ),
		'meta' => [
			'system' => \AtlasSearch\Meta\get_system_version(),
			'action' => $index_type . ':delete:' . $post->post_type,
			'source' => \AtlasSearch\Meta\get_domain_name(),
		],
	];

	return graphql( DELETE_GRAPHQL, $variables );
}

function delete_all( $index_type = AUTOMATIC_INDEX ) {
	$variables = [
		'meta' => [
			'system' => \AtlasSearch\Meta\get_system_version(),
			'action' => $index_type . ':deleteAll',
			'source' => \AtlasSearch\Meta\get_domain_name(),
		],
	];

	return graphql( DELETE_ALL_GRAPHQL, $variables );
}

/**
 * @param string         $query GraphQL query string.
 * @param array| array[] $variables GraphQL variables.
 *
 * @return array
 *
 * @throws \Wpe_Content_Engine\Helper\Exceptions\ClientQueryException Throws error Exception.
 * * @throws \Wpe_Content_Engine\Helper\Exceptions\MissingSettingsException Throws error Exception.
 * * @throws \Wpe_Content_Engine\Helper\Exceptions\ClientQueryGraphqlErrorsException Throws error Exception.
 */
function graphql( $query, $variables ) {
	$client       = new Client( 'wpe-content-engine', WPE_SMART_SEARCH_VERSION );
	$opts         = \AtlasSearch\Support\WordPress\get_option( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME );
	$url          = $opts['url'] ?? '';
	$access_token = $opts['access_token'] ?? '';

	return $client->query(
		$url,
		$query,
		$variables,
		$access_token,
		[
			'domainName' => \AtlasSearch\Meta\get_domain_name(),
		]
	);
}

/**
 * Get unique terms including their parent terms recursively.
 *
 * @param \WP_Term[] $terms Array of WP_Term objects.
 * @param string     $taxonomy_name The taxonomy name.
 *
 * @return \WP_Term[] Array of unique WP_Term objects including all ancestors.
 */
function get_terms_with_parents( array $terms, string $taxonomy_name = '' ) {
	// All terms including ancestors.
	$all_terms = [];

	foreach ( $terms as $term ) {
		// Add the term itself.
		if ( array_key_exists( $term->term_id, $all_terms ) ) {
			continue;
		}

		$all_terms[ (int) $term->term_id ] = $term;

		// direct ancestors of this specific $term.
		$ancestors    = [];
		$current_term = $term;

		while (
			$current_term instanceof \WP_Term
			&& ! empty( $current_term->parent )
			&& ! array_key_exists( $current_term->parent, $all_terms )
		) {
			$current_term = get_term( $current_term->parent, $current_term->taxonomy );

			if ( ! $current_term instanceof \WP_Term ) {
				break;
			}

			$ancestors[ (int) $current_term->term_id ] = $current_term;
		}

		// Got this from get_ancestors WP function.
		$filtered_ancestors_ids = apply_filters(
			'get_ancestors',
			array_keys( $ancestors ),
			$term->term_id,
			$term->taxonomy,
			'taxonomy'
		);

		// filter ancestors based on filtered_ancestors_ids using array_intersect_key.
		$filtered_ancestors = array_intersect_key( $ancestors, array_flip( $filtered_ancestors_ids ) );
		$all_terms         += $filtered_ancestors;
	}

	return array_values( $all_terms );
}

function terms_for_post( WP_Post $post ) {
	$terms_obj  = [];
	$taxonomies = get_object_taxonomies( $post, 'objects' );

	foreach ( $taxonomies as $taxonomy ) {
		if ( false === $taxonomy->publicly_queryable ) {
			continue;
		}

		$terms = get_the_terms( $post, $taxonomy->name );

		if ( ! $terms ) {
			continue;
		}

		// Get terms with their parents included.
		$terms_with_parents                                = get_terms_with_parents( $terms, $taxonomy->name );
		$terms_obj[ map_taxonomy_name( $taxonomy->name ) ] = [];

		foreach ( $terms_with_parents as $term ) {
			$terms_obj[ map_taxonomy_name( $taxonomy->name ) ][] = get_term_fields( $term );
		}
	}

	return $terms_obj;
}

function get_term_fields( \WP_Term $term ) {
	return [
		'name'             => $term->name,
		'slug'             => $term->slug,
		'term_taxonomy_id' => $term->term_taxonomy_id,
		'term_id'          => $term->term_id,
	];
}

function map_taxonomy_name( $name ) {
	$mapping = [
		'category' => 'categories',
		'post_tag' => 'tags',
	];

	if ( isset( $mapping[ $name ] ) ) {
		return $mapping[ $name ];
	}

	return $name;
}

function get_supported_custom_post_types(): array {
	return get_post_types(
		[
			'public'   => true,
			'_builtin' => false,
		]
	);
}

function get_supported_post_types(): array {
	$allowed_core_post_types = [ Post_Type::POST, Post_Type::PAGE ];
	$allow_list              = array_merge( $allowed_core_post_types, get_supported_custom_post_types() );
	$excluded_post_types     = \AtlasSearch\Hooks\filter_excluded_post_types();

	return array_diff( $allow_list, $excluded_post_types );
}

/**
 * @param \WP_Post $post Post.
 *
 * @return bool
 */
function is_allowed( WP_Post $post ): bool {
	$allowed_post_types = get_supported_post_types();

	if ( ! in_array( $post->post_type, $allowed_post_types, true ) ) {
		return false;
	}

	if ( Post_Status::WP_AUTO_DRAFT === $post->post_status ) {
		return false;
	}

	return '' !== $post->post_name;
}

/**
 * Gets the keys of a WP_Post.
 *
 * @param \WP_Post|\WP_Term|\WP_User $wp_object The WP Object.
 *
 * @return array Array of the post keys.
 */
function filter_wp_object_to_array( $wp_object ) {
	$wp_object_array = $wp_object->to_array();

	$keys_to_remove = [
		// WP Post fields.
		'post_author',
		'comment_status',
		'ping_status',
		'post_password',
		'to_ping',
		'pinged',
		'post_parent',
		'menu_order',
		'post_mime_type',
		'comment_count',
		'filter',
		'post_content_filtered',
		'guid',
		'post_modified',
		'post_date',
		'page_template',
		'post_category',
		'tags_input',
		'category',
		'ancestors',

		// WP Term fields.
		'count',
		'description',
		'filter',
		'parent',
		'term_group',
		'term_id',
		'term_taxonomy_id',

		// WP User fields.
		'user_avatar',
		'user_description',
		'user_nicename',
		'user_registered',
		'user_url',
		'user_login',
		'user_pass',
		'user_activation_key',
		'user_status',
	];

	foreach ( $keys_to_remove as $key ) {
		unset( $wp_object_array[ $key ] );
	}

	return $wp_object_array;
}

function handle_tags( $text ) {
	if ( empty( $text ) ) {
		return $text;
	}

	$text = apply_filters_the_content( $text );
	$text = strip_shortcodes( $text );
	$text = excerpt_remove_blocks( $text );
	$text = preg_replace(
		'/<\/(p|h[1-6]|li|td|th|div|section|article|blockquote|pre|figure|figcaption)>/i',
		'$0 ',
		$text
	);
	$text = wp_strip_all_tags( $text );
	$text = preg_replace( '/\s+/', ' ', $text );
	$text = trim( $text );

	return $text;
}

function apply_filters_the_content( $post_content ) {
	return apply_filters( 'the_content', $post_content );
}

/**
 * Handle admin notice for any errors during realtime indexing.
 *
 * @param callable $func Wrapped function.
 */
function admin_notice_handler( $func ) {
	try {
		$func();
	} catch ( MissingSettingsException $e ) {
		( new Admin_Notice() )->add_message( 'WP Engine Smart Search Settings Error: ' . $e->getMessage() );
	} catch ( \Throwable $e ) {
		( new Admin_Notice() )->add_message(
			'There was an error during WP Engine Smart Search indexing: ' . $e->getMessage()
		);
	}
}
