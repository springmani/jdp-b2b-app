<?php

namespace Wpe_Content_Engine\Helper\Search;

use WP_Query;
use Wpe_Content_Engine\Helper\Client_Interface;
use Wpe_Content_Engine\Helper\Exceptions\ClientQueryGraphqlErrorsException;
use Wpe_Content_Engine\Helper\Logging\Debug_Logger;
use Wpe_Content_Engine\Helper\Logging\Server_Log_Info;
use Wpe_Content_Engine\Settings_Interface;
use Wpe_Content_Engine\WPSettings;

use const Wpe_Content_Engine\Helper\Notifications\WPE_SMART_SEARCH_INDEX_READY;

const WPE_AITK_ENABLE_SEARCH = 'wpe_aitk/enable_search';

/**
 * @package    Wpe_Content_Engine
 *
 * @subpackage Wpe_Content_Engine/public
 */
class Search {
	public const NEW_NAMING_MAPPING = [
		'title'                => 'post_title',
		'post_title'           => 'title',
		'content'              => 'post_content',
		'post_content'         => 'content',
		'excerpt'              => 'post_excerpt',
		'post_excerpt'         => 'excerpt',
		'author.displayName'   => 'author.user_nicename',
		'author.user_nicename' => 'author.displayName',
	];
	public const ORDER_BY_MAPPING   = [
		// WP GraphQL supported params.
		'post_date'     => 'post_date_gmt',
		'post_modified' => 'post_modified_gmt',

		// WpQuery values https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters.
		'date'          => 'post_date_gmt',
		'modified'      => 'post_modified_gmt',
		'ID'            => 'ID',
	];
	public const DEFAULT_ORDER_BY   =
	[
		[ 'field' => '_score' ],
		[
			'field'     => 'ID',
			'direction' => 'desc',
		],
	];

	/**
	 * The client of this plugin.
	 *
	 * @var      \Wpe_Content_Engine\Helper\Client_Interface $client
	 */
	private Client_Interface $client;

	private Debug_Logger $debug_logger;

	/**
	 * The various settings needed for search.
	 *
	 * @var      \Wpe_Content_Engine\Settings_Interface $settings
	 */
	private Settings_Interface $settings;

	/**
	 * The search configuration set by the client.
	 *
	 * @var \Wpe_Content_Engine\Helper\Search\Search_Config $search_config
	 */
	private Search_Config $search_config;

	/**
	 * The promotions resolver.
	 *
	 * @var \Wpe_Content_Engine\Helper\Search\Promotions_Resolver $promotions_resolver
	 */
	private Promotions_Resolver $promotions_resolver;

	/**
	 * The custom search results resolver.
	 *
	 * @var \Wpe_Content_Engine\Helper\Search\Custom_Search_Results_Resolver $custom_search_results_resolver
	 */
	private Custom_Search_Results_Resolver $custom_search_results_resolver;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param \Wpe_Content_Engine\Helper\Client_Interface     $client Client Interface.
	 * @param \Wpe_Content_Engine\Settings_Interface          $settings Settings Interface.
	 * @param \Wpe_Content_Engine\Helper\Search\Search_Config $search_config Search Config.
	 * @param \Wpe_Content_Engine\Helper\Logging\Debug_Logger $debug_logger Debug Logger.
	 */
	public function __construct(
		Client_Interface $client,
		Settings_Interface $settings,
		Search_Config $search_config,
		Debug_Logger $debug_logger
	) {
		$this->client                         = $client;
		$this->settings                       = $settings;
		$this->search_config                  = $search_config;
		$this->debug_logger                   = $debug_logger;
		$this->promotions_resolver            = new Promotions_Resolver( $debug_logger );
		$this->custom_search_results_resolver = new Custom_Search_Results_Resolver( $debug_logger );
	}

	/**
	 * @param string      $search_query Search Query.
	 * @param array       $order_by Search order definition.
	 * @param int         $limit Page limit.
	 * @param int         $offset Page offset.
	 * @param array|null  $search_fields_by_type Fields to scope the search in on, grouped by type.
	 * @param bool        $fuzzy for fuzzy search toggle.
	 * @param int         $fuzzy_distance for fuzzy typos allowable per word.
	 * @param string|null $filter for adding additional filters to query.
	 * @param array|null  $semantic_search for adding semantic search to query.
	 * @param array|null  $search_after for cursor pagination.
	 * @param array|null  $geo_constraints for geographic search constraints.
	 * @param array|null  $recency_config for recency time decay configuration.
	 * @param array|null  $query_vars for query variables.
	 *
	 * @return array[]
	 *
	 * @throws \ErrorException Throws exception.
	 */
	public function search_content_engine(
		string $search_query,
		array $order_by,
		int $limit = 10,
		int $offset = 0,
		?array $search_fields_by_type = null,
		bool $fuzzy = true,
		int $fuzzy_distance = 1,
		?string $filter = null,
		?array $semantic_search = null,
		?array $search_after = null,
		?array $geo_constraints = null,
		?array $recency_config = null,
		?array $query_vars = null
	) {
		$graphql_query = <<<'GRAPHQL'
			query Search(
			$query: String!
			$filter: String
			$orderBy: [OrderBy!]
			$searchAfter: [String!]
			$offset: Int
			$limit: Int
			$tolerance: SearchOption
			$meta: MetaInput
			$semanticSearch: SemanticSearchInput
			$geoConstraints: GeoConstraintsInput
			$options: OptionsInput
				) {
				find(
					query: $query
					filter: $filter
					orderBy: $orderBy
					searchAfter: $searchAfter
					offset: $offset
					limit: $limit
					tolerance: $tolerance
					meta: $meta
					semanticSearch: $semanticSearch
					geoConstraints: $geoConstraints
					options: $options
				) {
					total
					documents {
						id
						sort
					}
				}
			}
		GRAPHQL;

		$tolerance = $fuzzy ?
			[
				'name'          => 'fuzzy',
				'fuzzyDistance' => $fuzzy_distance,
			] : [ 'name' => 'stemming' ];

		$graphql_vars = [
			'query'     => $search_query,
			'orderBy'   => $order_by,
			'offset'    => $offset,
			'limit'     => $limit,
			'options'   => [ 'fields' => [ 'types' => $search_fields_by_type ] ],
			'tolerance' => $tolerance,
			'meta'      => [
				'system' => \AtlasSearch\Meta\get_system_version(),
				'action' => 'find',
				'source' => \AtlasSearch\Meta\get_domain_name(),
			],
		];

		if ( ! empty( $filter ) ) {
			$graphql_vars['filter'] = $filter;
		}

		if ( ! empty( $semantic_search ) ) {
			$graphql_vars['semanticSearch'] = $semantic_search;
		}

		if ( ! empty( $search_after ) ) {
			$graphql_vars['searchAfter'] = $search_after;
		}

		if ( ! empty( $geo_constraints ) ) {
			$graphql_vars['geoConstraints'] = $geo_constraints;
		}

		// Prepare default time decay configuration.
		$default_time_decay = null;

		// If recency is enabled in UI, use those settings as defaults.
		if ( ! empty( $recency_config ) && isset( $recency_config['enabled'] ) && $recency_config['enabled'] ) {
			$default_time_decay = [
				'field'     => 'post_date_gmt',
				'scale'     => $recency_config['days'] . 'd',
				'decayRate' => 0.5,
				'offset'    => '0d',
			];
		}

		/**
		 * Filter to customize recency time decay configuration.
		 *
		 * This filter allows developers to override recency settings programmatically,
		 * even when recency is disabled in the UI settings.
		 *
		 * @param array|null $time_decay_config The time decay configuration (null if recency disabled in UI).
		 * @param array|null $recency_config    The original recency config from settings.
		 * @param array      $query_vars        The query variables.
		 *
		 * @return array|null Modified time decay configuration. Return null to disable recency.
		 */
		$time_decay_config = apply_filters(
			'wpe_smartsearch/recency_time_decay_config',
			$default_time_decay,
			$recency_config,
			$query_vars
		);

		// Apply time decay if configuration is available (either from UI or filter).
		if ( ! empty( $time_decay_config ) && is_array( $time_decay_config ) ) {
			$graphql_vars['options']['timeDecay'] = [ $time_decay_config ];
		}

		// Add promotions to the query if available.
		$promotions = $this->promotions_resolver->resolve_option();

		if ( ! empty( $promotions ) ) {
			$graphql_vars['options']['promotions'] = [
				'documents' => $promotions,
			];
		}

		// Add custom search results to the query if available.
		$custom_results = $this->custom_search_results_resolver->resolve_option();

		if ( ! empty( $custom_results ) ) {
			$graphql_vars['options']['customResults'] = $custom_results;
		}

		// Array of All Options.
		$wpe_content_engine_options = $this->settings->get( WPSettings::WPE_CONTENT_ENGINE_OPTION_NAME );

		$response = $this->client->query(
			str_replace( 'sync', 'sites', $wpe_content_engine_options['url'] ),
			$graphql_query,
			$graphql_vars,
			$wpe_content_engine_options['access_token'],
			( new Server_Log_Info() )->get_data()
		);

		return [ 'result' => $response['data']['find'] ?? [] ];
	}

	public function is_html_search( $post_types ): bool {
		return empty( $post_types ) && ! $this->is_graphql_request();
	}

	public function add_debug_message( string $message ) {
		if ( function_exists( 'graphql_debug' ) ) {
			graphql_debug(
				$message,
				[
					'version' => WPE_SMART_SEARCH_VERSION,
					'type'    => 'WPE_SMART_SEARCH_DEBUG',
				]
			);
		}

		// Query monitor log for HTML search.
		do_action( 'qm/info', $message );
	}

	/**
	 * Extracts post types from passed in $query_vars['post_type']
	 *
	 * @param string|array $post_type The post type(s) to extract.
	 *
	 * @return array An array of post types.
	 */
	public function extract_post_types( $post_type ) {
		return array_values( ! is_array( $post_type ) ? [ $post_type ] : $post_type );
	}

	/**
	 * Get posts from Content Engine using the search method
	 *
	 * @param array|null $posts Array of posts.
	 * @param \WP_Query  $query WP_Query instance.
	 *
	 * @return array|null
	 */
	public function get_ce_posts( ?array $posts, WP_Query $query ) {
		// This overrides the post list behavior on WP Admin
		// This is not directly tied to search
		// this affects WP_Query.

		$query->content_engine_search_success = false;

		// Check if we should turn off Smart Search.
		if (
			! ( $query->is_search() || ( $query->is_woocommerce_product_query ?? false ) ) ||
			$this->is_admin_search_request()
		) {
			return $posts;
		}

		/**
		 * Filter to control whether Smart Search is enabled for a query.
		 *
		 * By default, Smart Search is enabled for all search queries and WooCommerce
		 * product queries (when WooCommerce support is active). Use this filter to
		 * disable Smart Search for specific queries if needed.
		 *
		 * @param bool      $enabled Whether Smart Search is enabled. Default true.
		 * @param \WP_Query $query   The current WP_Query object.
		 *
		 * @return bool True to enable Smart Search, false to disable it.
		 */
		if ( false === (bool) apply_filters( WPE_AITK_ENABLE_SEARCH, true, $query ) ) {
			return $posts;
		}

		if ( ! $this->is_index_ready() ) {
			$this->add_debug_message(
				'WP Engine Smart Search: Search has been disabled until a full index operation has been completed.'
			);

			return $posts;
		}

		$query_vars = $query->query;

		if ( empty( $query_vars['post_type'] ) || 'any' === $query_vars['post_type'] ) {
			$query_vars['post_type'] = [];
		}

		$order_by = $this->extract_order_by( $query );

		if ( empty( $order_by ) ) {
			$order_by = self::DEFAULT_ORDER_BY;
		}

		$post_types                      = $this->extract_post_types( $query_vars['post_type'] );
		$config                          = $this->search_config->get_config( true );
		$fuzzy_config                    = $config['fuzzy'];
		$recency_config                  = $config['recency'] ?? null;
		$full_text_search_fields_by_type = $this->get_full_text_search_fields( $post_types, $config );
		$order_by                        = apply_filters(
			'wpe_smartsearch/get_order_by',
			$order_by,
			$query_vars,
			$query
		);
		$search_after                    = $this->get_search_after( $query_vars );
		$geo_constraints                 = $this->get_geo_constraints( $query_vars );
		$excluded_post_types             = $this->get_excluded_post_types( $config );
		$semantic_search_config          = $this->get_semantic_search_config( $config['searchType'] ?? 'fullText' );

		if ( $semantic_search_config ) {
			$semantic_search_config = $this->update_search_bias( $semantic_search_config, $config );
		}

		$semantic_search_bias = $semantic_search_config['searchBias'] ?? 0;

		if (
			0 === count( $full_text_search_fields_by_type )
			&& (
				! isset( $semantic_search_config )
				|| 0 === $semantic_search_bias
			)
		) {
			return [];
		}

		$page_number    = $query->get( 'paged', 0 );
		$posts_per_page = $query->get( 'posts_per_page', 10 );
		/**
		 * Sometimes WP plugin developers calling with negative number expect to get all posts
		 */
		$posts_per_page = ( $posts_per_page < 0 || $posts_per_page > 1000 ) ? 1000 : $posts_per_page;
		$query->set( 'posts_per_page', $posts_per_page );
		$query_offset = $query->get( 'offset', false );
		$offset       = false !== $query_offset ? $query_offset : self::get_offset( (int) $page_number, $posts_per_page );
		$filter       = $this->generate_filters( $post_types, $query, $excluded_post_types );

		if ( 0 === (int) $posts_per_page ) {
			return [];
		}

		try {
			// Reach out to content engine.
			$search_term = $query->get( 's' );
			$ce_results  = $this->search_content_engine(
				( null === $search_term || '' === trim( $search_term ) ) ? '*' : $search_term,
				$order_by,
				$posts_per_page,
				$offset,
				$full_text_search_fields_by_type,
				$fuzzy_config['enabled'],
				$fuzzy_config['distance'],
				$filter,
				$semantic_search_config,
				$search_after,
				$geo_constraints,
				$recency_config,
				$query_vars
			);
		} catch ( \Throwable $e ) {
			$message = 'Falling back to default search. WP Engine Smart Search was not successful. ' . $e->getMessage();
			$this->debug_logger->log( $message . " {$e->__toString()}" );
			$this->add_debug_message( $message );

			return $posts;
		}

		$this->add_debug_message( 'Search Provided by WP Engine Smart Search.' );

		// Pick out the hits.
		$hits = apply_filters( 'wpe_smartsearch/search_hits', $ce_results['result']['documents'], $query_vars );

		// setup for found documents.
		$found_documents = $ce_results['result']['total'];
		// set the amount of records found for this page.
		$query->found_posts = $found_documents;
		// set num_posts for found_posts hook.
		$query->num_posts = $found_documents;
		// setup pagination.
		$query->max_num_pages = (int) ceil( $found_documents / $posts_per_page );
		// set that the search was a success to be used later.
		$query->content_engine_search_success = true;

		// format strategy.
		$fields = $query->get( 'fields' );

		switch ( $fields ) {
			case 'id=>parent':
				$result_posts = $this->format_hits_as_id_parents( $hits );

				break;
			default:
				$result_posts = $this->format_hits_as_ids( $hits );

				break;
		}

		do_action( 'wpe_smartsearch/search_operation_completed', $hits, $query_vars );

		return $result_posts;
	}

	public function is_index_ready() {
		return \AtlasSearch\Support\WordPress\get_option( WPE_SMART_SEARCH_INDEX_READY );
	}

	public function update_search_bias( $semantic_search_config, $config ) {
		if ( 'semantic' === (string) ( $config['searchType'] ?? '' ) ) {
			$semantic_search_config['searchBias'] = 10;
		}

		return $semantic_search_config;
	}

	/**
	 * @param array $config config.
	 *
	 * @return array excluded post types.
	 */
	public function get_excluded_post_types( $config ) {
		$excluded_post_types = $this->determine_excluded_post_types_from_config( $config['models'] );

		return array_unique( array_merge( $excluded_post_types, $config['disabledModelNames'] ?? [] ) );
	}

	/**
	 * @param array $model_config models from config.
	 *
	 * @return array excluded post types.
	 */
	public function determine_excluded_post_types_from_config( $model_config ) {
		$excluded_post_types = [];

		foreach ( $model_config as $post_type => $fields ) {
			$all_false = true;

			foreach ( $fields as $field ) {
				if ( isset( $field['searchable'] ) && true === $field['searchable'] ) {
					$all_false = false;

					break;
				}
			}

			if ( $all_false ) {
				$excluded_post_types[] = $post_type;
			}
		}

		return $excluded_post_types;
	}

	/**
	 * @param array $query_vars WP query vars.
	 *
	 * @return array search after params.
	 */
	public function get_search_after( $query_vars ) {
		return apply_filters( 'wpe_smartsearch/get_search_after', [], $query_vars );
	}

	/**
	 * Get geo constraints for geographic search from WP query vars.
	 *
	 * @param array $query_vars WP query vars.
	 *
	 * @return array|null geo constraints array or null.
	 */
	public function get_geo_constraints( $query_vars ): ?array {
		return apply_filters( 'wpe_smartsearch/get_geo_constraints', null, $query_vars );
	}

	/**
	 * @param \WP_Query $query wp_query->query array.
	 *
	 * @return array Order by if exists else null;
	 */
	public function extract_order_by( WP_Query $query ): array {
		$order_by = $query->get( 'orderby', null );

		if ( ! $order_by ) {
			return [];
		}

		if ( is_string( $order_by ) ) {
			$order_value = $query->get( 'order', 'desc' );
			$fields      = preg_split( '/\s+/', trim( $order_by ), -1, PREG_SPLIT_NO_EMPTY );
			$order_by    = array_fill_keys( $fields, $order_value );
		}

		if ( ! is_array( $order_by ) ) {
			$value = print_r( $order_by, true );
			$this->add_debug_message( "Order by field not in supported format: {$value}" );

			return [];
		}

		$order_by_arr       = [];
		$unsupported_fields = [];

		foreach ( $order_by as $field => $order ) {
			if ( array_key_exists( $field, self::ORDER_BY_MAPPING ) ) {
				$order_by_arr[] = [
					'field'     => self::ORDER_BY_MAPPING[ $field ],
					'direction' => strtolower( $order ),
				];
			} else {
				$unsupported_fields[] = $field;
			}
		}

		if ( ! empty( $unsupported_fields ) ) {
			$unsupported_fields_str = implode( ', ', $unsupported_fields );
			$this->add_debug_message( "The order by fields: [{$unsupported_fields_str}], are currently not supported." );
		}

		return $order_by_arr;
	}

	/**
	 * Generates filters.
	 *
	 * @param array     $post_types Post types - could be empty array.
	 * @param \WP_Query $query WP_Query object.
	 * @param array     $excluded_post_types excluded post types - could be empty array.
	 *
	 * @return string|null Filter string or null.
	 */
	public function generate_filters( array $post_types, WP_Query $query, array $excluded_post_types ): ?string {
		$filters = [
			$this->get_included_posts_filter( $query->query_vars ),
			$this->get_excluded_posts_filter( $query->query_vars ),
			$this->get_post_types_filter( $post_types ),
			$this->get_id_prefix_filter(),
			$this->get_excluded_post_types_filter( $excluded_post_types ),
			\AtlasSearch\Query\Taxonomy\get_taxonomy_filter( $query ),
			\AtlasSearch\Query\Meta\get_meta_query_filter( $query ),
			\AtlasSearch\Query\Date\get_date_query_filter( $query ),
			\AtlasSearch\Support\WooCommerce\get_catalog_visibility_filter( $query ),
		];

		$filter = array_reduce(
			$filters,
			static function ( $accum, $value ) {
				if ( ! $value ) {
					return $accum;
				}

				return '' === $accum ? "($value)" : "$accum AND ($value)";
			},
			''
		);

		if ( 0 === strlen( $filter ) ) {
			return null;
		}

		return $filter;
	}

	public function get_excluded_post_types_filter( array $excluded_post_types ) {
		if ( count( $excluded_post_types ) > 0 ) {
			$types_str = array_map(
				static fn ( $type ) => "NOT post_type:\"{$type}\" OR NOT postType:\"{$type}\"",
				$excluded_post_types
			);

			return implode( ' OR ', $types_str );
		}

		return null;
	}

	/**
	 * @param array $post_types Post types.
	 * @param array $config Configuration array.
	 *
	 * @return array Array of fields organized by post type.
	 */
	public function get_full_text_search_fields( array $post_types, array $config ): array {
		$models_config        = $config['models'] ?? [];
		$fields_by_type       = [];
		$disabled_model_names = $config['disabledModelNames'] ?? [];

		// If there are no post types passed in assume all post types should be selected.
		if ( 0 === count( $post_types ) ) {
			foreach ( $models_config as $post_type => $post_type_config ) {
				$post_types[] = $post_type;
			}
		}

		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type, $disabled_model_names ) ) {
				continue;
			}

			$config_for_post_type = $models_config[ $post_type ];
			$fields_for_type      = [];

			foreach ( $config_for_post_type as $key => $value ) {
				$weight = $value['weight'];

				if ( true !== $value['searchable'] ) {
					continue;
				}

				$fields_for_type[] = [
					'name'   => $key,
					'weight' => $weight,
				];

				if ( $value['has_sub_fields'] ) {
					$fields_for_type[] = [
						'name'   => "$key.*",
						'weight' => $weight,
					];
				}

				if ( ! array_key_exists( $key, self::NEW_NAMING_MAPPING ) ) {
					continue;
				}

				$mapped_key        = self::NEW_NAMING_MAPPING[ $key ];
				$fields_for_type[] = [
					'name'   => $mapped_key,
					'weight' => $weight,
				];

				if ( ! $value['has_sub_fields'] ) {
					continue;
				}

				$fields_for_type[] = [
					'name'   => "$mapped_key.*",
					'weight' => $weight,
				];
			}

			$fields_by_type[] = [
				'type'   => $post_type,
				'fields' => $fields_for_type,
			];
		}

		return $fields_by_type;
	}

	public function format_hits_as_ids( $hits ): array {
		$result_posts = [];

		foreach ( $hits as $hit ) {
			if ( ! isset( $hit ) ) {
				continue;
			}

			// Support id formats in "post:1" | "1" | 1.
			$id_tuple = explode( ':', $hit['id'] );

			$result_posts[] = count( $id_tuple ) < 2 ? $hit['id'] : end( $id_tuple );
		}

		return $result_posts;
	}

	/**
	 * Set the found_posts variable on WP_Query.
	 *
	 * @param int       $found_posts Number of found posts.
	 * @param \WP_Query $query Query object.
	 *
	 * @return int
	 */
	public function found_posts( $found_posts, $query ) {
		if ( ( isset( $query->content_engine_search_success ) && false === $query->content_engine_search_success ) ) {
			return $found_posts;
		}

		return $query->num_posts;
	}

	/**
	 * @param string $search_type Search type.
	 *
	 * @return null|array
	 */
	public function get_semantic_search_config( $search_type = 'fullText' ) {
		try {
			$config = \AtlasSearch\Index\get_semantic_search_config( true );

			if ( ! isset( $config['enabled'] ) || ! $config['enabled'] ) {
				return null;
			}

			if ( empty( $config['fields'] ) ) {
				return null;
			}

			if ( 'fullText' === $search_type ) {
				return null;
			}

			$semantic_search_config = [
				'fields'     => $config['fields'],
				'searchBias' => $config['searchBias'],
			];
		} catch ( ClientQueryGraphqlErrorsException $e ) {
			return null;
		}

		return $semantic_search_config;
	}

	/**
	 * @param int $page_number Page number.
	 * @param int $posts_per_page Posts per page.
	 *
	 * @return int
	 */
	public static function get_offset( int $page_number, int $posts_per_page ): int {
		return $page_number > 0 ? ( $page_number - 1 ) * $posts_per_page : 0;
	}

	protected function format_hits_as_id_parents( $hits ): array {
		$result_posts = [];

		foreach ( $hits as $hit ) {
			$source = $hit['_source'];

			$post_data = (object) [
				'ID'                    => $source['wpId'],
				'post_parent'           => $source['slug'],
				'content_engine_search' => true,
			];

			$result_posts[] = $post_data;
		}

		return $result_posts;
	}

	private function is_graphql_request(): bool {
		return isset( $_REQUEST['graphql'] );
	}

	private function is_admin_search_request(): bool {
		$is_wp_admin = $this->is_referer_wp_admin() || is_admin();

		return is_user_logged_in() && $is_wp_admin && ! $this->is_graphql_request();
	}

	private function is_referer_wp_admin(): bool {
		$referer = wp_get_referer();

		return isset( $referer ) && false !== strpos( $referer, admin_url() );
	}

	private function get_excluded_posts_filter( array $query_vars ) {
		if ( empty( $query_vars['post__not_in'] ) ) {
			return null;
		}

		$excluded_post_ids_str = implode( ' NOT ID:', $query_vars['post__not_in'] );

		return "NOT ID:$excluded_post_ids_str";
	}

	private function get_included_posts_filter( array $query_vars ) {
		if ( empty( $query_vars['post__in'] ) ) {
			return null;
		}

		return 'ID: ( ' . implode( ' OR ', $query_vars['post__in'] ) . ' )';
	}

	private function get_post_types_filter( array $post_types ) {
		if ( count( $post_types ) > 0 ) {
			$types_str = implode( ',', $post_types );

			return "post_type:{$types_str} OR postType:{$types_str}";
		}

		return null;
	}

	private function get_id_prefix_filter() {
		$id_prefix = \AtlasSearch\Hooks\filter_id_prefix();

		if ( ! $id_prefix ) {
			return null;
		}

		return \AtlasSearch\Index\ID_PREFIX_FIELD_NAME . ":{$id_prefix}";
	}
}
