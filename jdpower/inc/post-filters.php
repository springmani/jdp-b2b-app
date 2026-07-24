<?php
/**
 * Shared filter query, AJAX, and helpers for Insight Center and Product Finder.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is the Insight Center: blog index at the dedicated Posts page.
 *
 * Requires Settings → Reading → Posts page to be set. When the front page shows latest posts
 * (no Posts page), this is false so index.php uses the default main loop.
 *
 * @return bool
 */
function jdpower_post_filters_is_insight_center_view() {
	return (int) get_option( 'page_for_posts' ) > 0 && is_home();
}

/**
 * Card data for Insight Center (same rules as featured insights block when that file is not loaded).
 *
 * @param int $post_id Post ID.
 * @return array{image_id: int, heading: string, sub_heading: string, copy_html: string, url: string, segment_label?: string, post_type_label?: string, date_display?: string, date_iso?: string}|null
 */
function jdpower_post_filters_insight_card_data( $post_id ) {
	if ( function_exists( 'jdpower_featured_insights_card_from_post' ) ) {
		return jdpower_featured_insights_card_from_post( $post_id );
	}

	$post_id = (int) $post_id;
	if ( $post_id <= 0 || 'publish' !== get_post_status( $post_id ) ) {
		return null;
	}

	$image_id = function_exists( 'jdpower_insight_card_image_id' )
		? jdpower_insight_card_image_id( $post_id )
		: (int) get_post_thumbnail_id( $post_id );

	$heading_override_clean = function_exists( 'get_field' ) ? jdpower_sanitize_inline_html( get_field( 'page_card_heading_override', $post_id ) ) : '';
	$heading                = '' !== $heading_override_clean
		? $heading_override_clean
		: jdpower_sanitize_inline_html( get_the_title( $post_id ) );

	$sub_heading = function_exists( 'get_field' ) ? jdpower_sanitize_inline_html( get_field( 'page_card_sub_heading', $post_id ) ) : '';

	$copy_html = '';
	$card_short = function_exists( 'get_field' ) ? get_field( 'page_card_short_description', $post_id ) : '';
	if ( is_string( $card_short ) && '' !== trim( $card_short ) && '' !== trim( wp_strip_all_tags( $card_short ) ) ) {
		$copy_html = jdpower_sanitize_inline_html( $card_short, true, array( 'br' ) );
	}
	if ( '' === $copy_html ) {
		$excerpt = get_post_field( 'post_excerpt', $post_id );
		if ( '' === trim( (string) $excerpt ) ) {
			$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		}
		if ( '' !== trim( wp_strip_all_tags( (string) $excerpt ) ) ) {
			$copy_html = jdpower_sanitize_inline_html( $excerpt, true, array( 'br' ) );
		}
	}

	$date_fields = jdpower_post_card_date_fields( $post_id );

	return array(
		'image_id'      => $image_id,
		'heading'       => $heading,
		'sub_heading'   => $sub_heading,
		'copy_html'     => $copy_html,
		'url'           => get_permalink( $post_id ),
		'segment_label' => function_exists( 'jdpower_insight_card_pill_label' )
			? jdpower_insight_card_pill_label( $post_id )
			: jdpower_get_post_primary_taxonomy_term_name( $post_id, 'post_topic' ),
		'date_display'  => $date_fields['date_display'],
		'date_iso'      => $date_fields['date_iso'],
	);
}

/**
 * Product segment line for card display (Card Details text override or assigned terms).
 *
 * @param int $post_id Product post ID.
 * @return string[] Display strings for the segment line (one custom label, or term names).
 */
function jdpower_product_card_segment_names( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return array();
	}

	if ( function_exists( 'get_field' ) ) {
		$override = get_field( 'product_card_segments', $post_id );
		if ( is_string( $override ) && '' !== trim( $override ) ) {
			return array( jdpower_decode_text_for_display( trim( $override ) ) );
		}

		// Legacy taxonomy checkbox override (term IDs saved before this field became text).
		if ( ! empty( $override ) && is_array( $override ) ) {
			$names = array();
			foreach ( array_values( array_filter( array_map( 'intval', $override ) ) ) as $term_id ) {
				$term = get_term( $term_id, 'product_segment' );
				if ( $term && ! is_wp_error( $term ) && is_string( $term->name ) && '' !== trim( $term->name ) ) {
					$names[] = jdpower_decode_text_for_display( $term->name );
				}
			}
			if ( ! empty( $names ) ) {
				return $names;
			}
		}
	}

	$segment_terms = wp_get_post_terms(
		$post_id,
		'product_segment',
		array(
			'orderby' => 'name',
			'order'   => 'ASC',
		)
	);
	if ( is_wp_error( $segment_terms ) || empty( $segment_terms ) ) {
		return array();
	}

	return array_map( 'jdpower_decode_text_for_display', wp_list_pluck( $segment_terms, 'name' ) );
}

/**
 * Product card heading, copy, and segment line from ACF Card Details + product_segment terms.
 *
 * @param int $post_id Product post ID.
 * @return array{heading: string, copy_html: string, segment_label: string}
 */
function jdpower_post_filters_product_card_data( $post_id ) {
	$post_id = (int) $post_id;

	$heading_override_clean = function_exists( 'get_field' ) ? jdpower_sanitize_inline_html( get_field( 'product_card_heading_override', $post_id ) ) : '';
	$heading                = '' !== $heading_override_clean
		? $heading_override_clean
		: jdpower_sanitize_inline_html( get_the_title( $post_id ) );

	$copy_html = '';
	$card_short = function_exists( 'get_field' ) ? get_field( 'product_card_short_description', $post_id ) : '';
	if ( is_string( $card_short ) && '' !== trim( $card_short ) && '' !== trim( wp_strip_all_tags( $card_short ) ) ) {
		$copy_html = jdpower_sanitize_inline_html( $card_short, true, array( 'br' ) );
	}
	if ( '' === $copy_html ) {
		$excerpt = get_post_field( 'post_excerpt', $post_id );
		if ( '' === trim( (string) $excerpt ) ) {
			$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		}
		if ( '' !== trim( wp_strip_all_tags( (string) $excerpt ) ) ) {
			$copy_html = jdpower_sanitize_inline_html( $excerpt, true, array( 'br' ) );
		}
	}

	$segment_names = jdpower_product_card_segment_names( $post_id );
	$segment_label = ! empty( $segment_names ) ? strtoupper( implode( ', ', $segment_names ) ) : '';

	return array(
		'heading'        => $heading,
		'copy_html'      => $copy_html,
		'segment_label'  => $segment_label,
	);
}

/** Nonce action for AJAX and localized script. */
define( 'JDPOWER_POST_FILTERS_NONCE_ACTION', 'jdpower_post_filters' );

/** Context: blog / Insight Center. */
define( 'JDPOWER_POST_FILTERS_CONTEXT_INSIGHT', 'insight_center' );

/** Context: Product Finder page template. */
define( 'JDPOWER_POST_FILTERS_CONTEXT_PRODUCT', 'product_finder' );

/**
 * Insight Center: post types included in the grid and Post Type filter (single source of truth).
 *
 * @return string[]
 */
function jdpower_post_filters_insight_post_type_slugs() {
	return array( 'post', 'press_release', 'podcast', 'event_webinar', 'resource' );
}

/**
 * Post type slugs for an insight config (WP_Query and filters).
 *
 * @param array<string, mixed> $config Config.
 * @return string[]
 */
function jdpower_post_filters_get_insight_post_types( $config ) {
	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT !== $config['context'] ) {
		return array();
	}
	$pt = $config['post_type'] ?? 'post';
	if ( is_array( $pt ) ) {
		return $pt;
	}
	return array( (string) $pt );
}

/**
 * Parse pf_pt (allowed insight post type slug or empty).
 *
 * @param array<string, mixed> $config Config.
 * @param array<string, mixed> $source GET/POST.
 * @return string
 */
function jdpower_post_filters_parse_pf_pt( $config, $source ) {
	$allowed = jdpower_post_filters_get_insight_post_types( $config );
	if ( empty( $allowed ) ) {
		return '';
	}
	$raw = isset( $source['pf_pt'] ) ? sanitize_key( wp_unslash( (string) $source['pf_pt'] ) ) : '';
	if ( '' === $raw || ! in_array( $raw, $allowed, true ) ) {
		return '';
	}
	return $raw;
}

/**
 * Toggle insight post type filter (single slug; same again clears).
 *
 * @param array<string, mixed> $config Config.
 * @param array<string, mixed> $request Request.
 * @param string               $slug    Post type slug.
 * @return array<string, mixed>
 */
function jdpower_post_filters_request_toggle_insight_post_type( $request, $slug, $config ) {
	$allowed = jdpower_post_filters_get_insight_post_types( $config );
	$slug    = sanitize_key( (string) $slug );
	if ( '' === $slug || ! in_array( $slug, $allowed, true ) ) {
		return $request;
	}
	$cur = isset( $request['pf_pt'] ) ? sanitize_key( (string) $request['pf_pt'] ) : '';
	if ( $slug === $cur ) {
		$request['pf_pt'] = '';
	} else {
		$request['pf_pt'] = $slug;
	}
	$request['paged'] = 1;
	return $request;
}

/**
 * Registered configurations for filter contexts.
 *
 * @return array<string, array<string, mixed>>
 */
function jdpower_post_filters_registered_configs() {
	$per_page = (int) get_option( 'posts_per_page' );
	if ( $per_page < 1 ) {
		$per_page = 10;
	}

	return array(
		JDPOWER_POST_FILTERS_CONTEXT_INSIGHT => array(
			'context'            => JDPOWER_POST_FILTERS_CONTEXT_INSIGHT,
			'post_type'          => jdpower_post_filters_insight_post_type_slugs(),
			'grid_columns'       => 4,
			'posts_per_page'     => $per_page,
			'orderby'            => 'date',
			'order'              => 'DESC',
			'sidebar_taxonomies' => array(
				array(
					'taxonomy' => 'post_industry',
					'label'    => __( 'Industry', 'jdpower' ),
				),
				array(
					'taxonomy' => 'post_topic',
					'label'    => __( 'Topic', 'jdpower' ),
				),
				array(
					'taxonomy' => 'post_segment',
					'label'    => __( 'Segment', 'jdpower' ),
				),
			),
			'global_filter'      => array(
				'type'  => 'post_type',
				'param' => 'pf_pt',
				'label' => __( 'All Content Types', 'jdpower' ),
			),
			'card_part'          => 'template-parts/post-filters/card-post',
			'search_placeholder' => __( 'Search Insights', 'jdpower' ),
			'results_noun'       => __( 'Insights', 'jdpower' ),
		),
		JDPOWER_POST_FILTERS_CONTEXT_PRODUCT => array(
			'context'            => JDPOWER_POST_FILTERS_CONTEXT_PRODUCT,
			'post_type'          => 'product',
			'grid_columns'       => 3,
			'posts_per_page'     => $per_page,
			'orderby'            => 'date',
			'order'              => 'DESC',
			'sidebar_taxonomies' => array(
				array(
					'taxonomy' => 'product_industry',
					'label'    => __( 'Industry', 'jdpower' ),
				),
				array(
					'taxonomy' => 'product_segment',
					'label'    => __( 'Segment', 'jdpower' ),
				),
			),
			'global_filter'      => array(
				'type'     => 'taxonomy',
				'taxonomy' => 'product_region',
				'param'    => 'region',
				'label'    => __( 'All Regions', 'jdpower' ),
			),
			'card_part'          => 'template-parts/post-filters/card-product',
			'search_placeholder' => __( 'Search Products', 'jdpower' ),
			'results_noun'       => __( 'Products', 'jdpower' ),
		),
	);
}

/**
 * @param string $context insight_center|product_finder
 * @return array<string, mixed>
 */
function jdpower_post_filters_get_config( $context ) {
	$all = jdpower_post_filters_registered_configs();
	return isset( $all[ $context ] ) ? $all[ $context ] : $all[ JDPOWER_POST_FILTERS_CONTEXT_INSIGHT ];
}

/**
 * Parse slug list from request (comma-separated or array).
 *
 * @param array<string, mixed> $source GET/POST.
 * @param string               $key    Parameter name.
 * @return string[]
 */
function jdpower_post_filters_parse_slug_list( $source, $key ) {
	if ( empty( $source[ $key ] ) ) {
		return array();
	}
	$raw = $source[ $key ];
	if ( is_array( $raw ) ) {
		$parts = $raw;
	} else {
		$parts = explode( ',', (string) $raw );
	}
	$slugs = array();
	foreach ( $parts as $p ) {
		$p = sanitize_title( trim( (string) $p ) );
		if ( '' !== $p ) {
			$slugs[] = $p;
		}
	}
	return array_values( array_unique( $slugs ) );
}

/**
 * Parse product_region slug from request.
 *
 * @param array<string, mixed> $source GET/POST.
 * @return string Region slug, or empty when unset / invalid.
 */
function jdpower_post_filters_parse_region_slug( $source ) {
	if ( empty( $source['region'] ) ) {
		return '';
	}
	$slug = sanitize_title( wp_unslash( (string) $source['region'] ) );
	if ( '' === $slug ) {
		return '';
	}
	$term = get_term_by( 'slug', $slug, 'product_region' );
	if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
		return $term->slug;
	}
	return '';
}

/**
 * Default product_region term (North America, slug: na).
 *
 * @return WP_Term|null
 */
function jdpower_product_region_default_term() {
	static $cached    = null;
	static $resolved = false;

	if ( $resolved ) {
		return $cached;
	}

	$resolved = true;
	$term     = get_term_by( 'slug', 'na', 'product_region' );
	if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
		$cached = $term;
	}

	return $cached;
}

/**
 * Default product_region slug (North America) for Product Finder and Featured Solutions.
 *
 * @return string Empty when the term does not exist.
 */
function jdpower_product_region_default_slug() {
	$term = jdpower_product_region_default_term();

	return ( $term instanceof WP_Term ) ? $term->slug : '';
}

/**
 * Allowed sort keys for URL/AJAX (pf_sort).
 *
 * @return array<string, string> key => label
 */
function jdpower_post_filters_sort_choices() {
	return array(
		'relevancy' => __( 'Relevancy', 'jdpower' ),
		'newest'    => __( 'Newest first', 'jdpower' ),
		'oldest'    => __( 'Oldest first', 'jdpower' ),
		'title_az'  => __( 'Title A–Z', 'jdpower' ),
		'title_za'  => __( 'Title Z–A', 'jdpower' ),
	);
}

/**
 * Sort options shown in the Insight Center UI.
 *
 * Relevancy only applies to keyword search (Smart Search / WP relevance). Browse hides it.
 *
 * @param bool $has_search Whether a keyword search is active.
 * @return array<string, string>
 */
function jdpower_post_filters_display_sort_choices( $has_search = false ) {
	$choices = jdpower_post_filters_sort_choices();
	if ( ! $has_search ) {
		unset( $choices['relevancy'] );
	}
	return $choices;
}

/**
 * @param array<string, mixed> $source     GET/POST.
 * @param bool                 $has_search Whether a keyword is present for this request.
 * @return string One of the sort choice keys.
 */
function jdpower_post_filters_parse_sort( $source, $has_search = false ) {
	$allowed = array_keys( jdpower_post_filters_sort_choices() );
	$raw     = isset( $source['pf_sort'] ) ? sanitize_key( wp_unslash( (string) $source['pf_sort'] ) ) : '';
	$default = $has_search ? 'relevancy' : 'newest';

	if ( '' === $raw || ! in_array( $raw, $allowed, true ) ) {
		return $default;
	}

	// Relevancy is meaningless without a keyword — fall back to newest for browse.
	if ( ! $has_search && 'relevancy' === $raw ) {
		return 'newest';
	}

	return $raw;
}

/**
 * Grid vs list result layout (pf_view). Does not affect WP_Query.
 *
 * @param array<string, mixed> $source GET/POST.
 * @return string grid|list
 */
function jdpower_post_filters_parse_view( $source ) {
	$raw = isset( $source['pf_view'] ) ? sanitize_key( wp_unslash( (string) $source['pf_view'] ) ) : '';
	return ( 'list' === $raw ) ? 'list' : 'grid';
}

/**
 * @param array<string, mixed> $args  WP_Query args (modified by reference).
 * @param string               $sort  Parsed sort key.
 */
function jdpower_post_filters_apply_sort_to_query_args( array &$args, $sort ) {
	switch ( $sort ) {
		case 'relevancy':
			// Leave order to WP / Smart Search relevance scoring (same idea as Search hub).
			$args['orderby'] = 'relevance';
			$args['order']   = 'DESC';
			break;
		case 'oldest':
			$args['orderby'] = 'date';
			$args['order']   = 'ASC';
			break;
		case 'title_az':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;
		case 'title_za':
			$args['orderby'] = 'title';
			$args['order']   = 'DESC';
			break;
		case 'newest':
		default:
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
	}
}

/**
 * product_industry slug for Automotive (Product Finder default industry lead).
 *
 * @return string Term slug, or "automotive" when the term is missing.
 */
function jdpower_post_filters_product_automotive_industry_slug() {
	static $slug = null;

	if ( null !== $slug ) {
		return $slug;
	}

	$slug = 'automotive';
	if ( ! taxonomy_exists( 'product_industry' ) ) {
		return $slug;
	}

	$term = get_term_by( 'slug', 'automotive', 'product_industry' );
	if ( ! ( $term instanceof WP_Term ) || is_wp_error( $term ) ) {
		$term = get_term_by( 'name', 'Automotive', 'product_industry' );
	}
	if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
		$slug = $term->slug;
	}

	return $slug;
}

/**
 * product_industry term IDs for Automotive and its descendants (hierarchical assignees).
 *
 * @return int[]
 */
function jdpower_post_filters_product_automotive_industry_term_ids() {
	static $ids = null;

	if ( null !== $ids ) {
		return $ids;
	}

	$ids = array();
	if ( ! taxonomy_exists( 'product_industry' ) ) {
		return $ids;
	}

	$term = get_term_by( 'slug', jdpower_post_filters_product_automotive_industry_slug(), 'product_industry' );
	if ( ! ( $term instanceof WP_Term ) || is_wp_error( $term ) ) {
		$term = get_term_by( 'name', 'Automotive', 'product_industry' );
	}
	if ( ! ( $term instanceof WP_Term ) || is_wp_error( $term ) ) {
		return $ids;
	}

	$ids[] = (int) $term->term_id;
	$child = get_term_children( (int) $term->term_id, 'product_industry' );
	if ( ! is_wp_error( $child ) && ! empty( $child ) ) {
		foreach ( $child as $child_id ) {
			$ids[] = (int) $child_id;
		}
	}

	$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );

	return $ids;
}

/**
 * Put Automotive first, then preserve existing term order (typically name ASC).
 *
 * @param WP_Term[] $terms    Terms list.
 * @param string    $taxonomy Taxonomy slug.
 * @return WP_Term[]
 */
function jdpower_post_filters_sort_terms_automotive_first( array $terms, $taxonomy ) {
	if ( 'product_industry' !== sanitize_key( $taxonomy ) || empty( $terms ) ) {
		return $terms;
	}

	$auto_slug = jdpower_post_filters_product_automotive_industry_slug();
	$auto      = array();
	$rest      = array();

	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term && $term->slug === $auto_slug ) {
			$auto[] = $term;
		} else {
			$rest[] = $term;
		}
	}

	return array_merge( $auto, $rest );
}

/**
 * Secondary ORDER BY SQL for Product Finder (after featured / industry boost).
 *
 * @param string $sort Parsed sort key.
 * @param wpdb   $wpdb Database object.
 * @return string SQL fragment without leading ORDER BY.
 */
function jdpower_post_filters_sort_sql_orderby( $sort, $wpdb ) {
	switch ( $sort ) {
		case 'oldest':
			return "{$wpdb->posts}.post_date ASC";
		case 'title_az':
			return "{$wpdb->posts}.post_title ASC";
		case 'title_za':
			return "{$wpdb->posts}.post_title DESC";
		case 'newest':
		default:
			return "{$wpdb->posts}.post_date DESC";
	}
}

/**
 * Product Finder ordering: Featured products A–Z, then remaining products A–Z.
 *
 * @param string[] $clauses Query clauses.
 * @param WP_Query $query   Query object.
 * @return string[]
 */
function jdpower_post_filters_product_finder_posts_clauses( $clauses, $query ) {
	if ( ! $query instanceof WP_Query ) {
		return $clauses;
	}

	$mode = (string) $query->get( 'jdpower_product_finder_order' );
	if ( '' === $mode || ! in_array( $mode, array( 'featured', 'industry', 'alpha' ), true ) ) {
		return $clauses;
	}

	global $wpdb;

	$order_parts = array();

	if ( 'alpha' === $mode ) {
		$order_parts[] = "{$wpdb->posts}.post_title ASC";
	} elseif ( 'featured' === $mode ) {
		if ( false === strpos( $clauses['join'], 'jdp_pf_feat' ) ) {
			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS jdp_pf_feat ON ({$wpdb->posts}.ID = jdp_pf_feat.post_id AND jdp_pf_feat.meta_key = 'jdpower_featured') ";
		}
		$order_parts[] = "(CASE WHEN jdp_pf_feat.meta_value = '1' THEN 1 ELSE 0 END) DESC";
		$order_parts[] = "{$wpdb->posts}.post_title ASC";
	} else {
		$auto_ids = jdpower_post_filters_product_automotive_industry_term_ids();
		if ( ! empty( $auto_ids ) ) {
			$id_list = implode( ',', array_map( 'intval', $auto_ids ) );
			$rank    = "MIN( CASE WHEN t.term_id IN ({$id_list}) THEN 0 ELSE 1 END )";
		} else {
			$auto_slug = esc_sql( jdpower_post_filters_product_automotive_industry_slug() );
			$rank      = "MIN( CASE WHEN t.slug = '{$auto_slug}' THEN 0 ELSE 1 END )";
		}

		if ( false === strpos( $clauses['join'], 'jdp_pf_ind' ) ) {
			$clauses['join'] .= " LEFT JOIN (
				SELECT tr.object_id AS post_id,
					{$rank} AS industry_rank
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'product_industry'
				INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				GROUP BY tr.object_id
			) AS jdp_pf_ind ON {$wpdb->posts}.ID = jdp_pf_ind.post_id ";
		}

		// Automotive products A–Z, then all other products A–Z (flat, not grouped by industry name).
		$order_parts[] = 'COALESCE( jdp_pf_ind.industry_rank, 999 ) ASC';
		$order_parts[] = "{$wpdb->posts}.post_title ASC";
	}

	$clauses['orderby'] = implode( ', ', $order_parts );

	return $clauses;
}
add_filter( 'posts_clauses', 'jdpower_post_filters_product_finder_posts_clauses', 10, 2 );

/**
 * Skip WPE Smart Search when Product Finder sets use_native_search on the query.
 *
 * @param bool     $enabled Whether Smart Search should handle the query.
 * @param WP_Query $query   Current query.
 * @return bool
 */
function jdpower_post_filters_disable_smart_search_for_native( $enabled, $query ) {
	if ( ! $query instanceof WP_Query ) {
		return $enabled;
	}
	if ( ! empty( $query->get( 'use_native_search' ) ) ) {
		return false;
	}
	return $enabled;
}
add_filter( 'wpe_aitk/enable_search', 'jdpower_post_filters_disable_smart_search_for_native', 10, 2 );

/**
 * Resolve grid page from request + WordPress query vars.
 *
 * Avoid using core `paged` in shareable URLs: `redirect_canonical` often rewrites
 * `?paged=N` to `/page/N/` on Pages, which maps to the `page` query var (content
 * pagination), not the blog `paged` var — so `get_query_var( 'paged' )` stays 0.
 * We emit `pf_paged` in URLs/AJAX instead; still accept legacy `paged` and `page`.
 *
 * @param array<string, mixed> $config Config (reserved for parity with callers).
 * @param array<string, mixed> $source Typically $_GET or wp_unslash( $_POST ).
 * @return int >= 1
 */
function jdpower_post_filters_resolve_paged( $config, array $source ) {
	if ( isset( $source['pf_paged'] ) ) {
		return max( 1, (int) $source['pf_paged'] );
	}
	if ( isset( $source['paged'] ) ) {
		return max( 1, (int) $source['paged'] );
	}

	$pf = (int) get_query_var( 'pf_paged' );
	if ( $pf > 0 ) {
		return max( 1, $pf );
	}

	$paged_qv = (int) get_query_var( 'paged' );
	if ( $paged_qv > 0 ) {
		return max( 1, $paged_qv );
	}

	// `/page/N/` on a Page or some posts-page permutations set `page`, not `paged`.
	$page_qv = (int) get_query_var( 'page' );
	if ( $page_qv > 1 ) {
		return $page_qv;
	}

	return 1;
}

/**
 * Search term from request. Prefer public `keyword` (same as the Search hub) —
 * core `?s=` on a Page makes WordPress treat the main query as a search and can 404.
 * Still accept legacy `pf_s` / `s` / AJAX `s`.
 *
 * @param array<string, mixed> $source Typically $_GET or wp_unslash( $_POST ).
 * @return string
 */
function jdpower_post_filters_parse_search_term( array $source ) {
	if ( isset( $source['keyword'] ) && '' !== trim( (string) $source['keyword'] ) ) {
		return sanitize_text_field( wp_unslash( (string) $source['keyword'] ) );
	}
	if ( isset( $source['pf_s'] ) && '' !== trim( (string) $source['pf_s'] ) ) {
		return sanitize_text_field( wp_unslash( (string) $source['pf_s'] ) );
	}
	if ( isset( $source['s'] ) && '' !== trim( (string) $source['s'] ) ) {
		return sanitize_text_field( wp_unslash( (string) $source['s'] ) );
	}
	return '';
}

/**
 * @param array<string, mixed> $config  From jdpower_post_filters_get_config.
 * @param array<string, mixed> $source  Typically $_GET or wp_unslash( $_POST ).
 * @return array<string, mixed>
 */
function jdpower_post_filters_parse_request( $config, $source ) {
	$src = is_array( $source ) ? $source : array();

	$search_term = jdpower_post_filters_parse_search_term( $src );

	$out = array(
		's'     => $search_term,
		'paged' => jdpower_post_filters_resolve_paged( $config, $src ),
		'sort'  => JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context']
			? jdpower_post_filters_parse_sort( $src, '' !== $search_term )
			: 'newest',
		'view'  => jdpower_post_filters_parse_view( $src ),
		'lang'  => isset( $src['lang'] ) ? sanitize_key( wp_unslash( (string) $src['lang'] ) ) : '',
	);

	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context'] ) {
		$out['pf_pt']           = jdpower_post_filters_parse_pf_pt( $config, $src );
		$out['post_industry']  = jdpower_post_filters_parse_slug_list( $src, 'post_industry' );
		$out['post_topic']     = jdpower_post_filters_parse_slug_list( $src, 'post_topic' );
		$out['post_segment']   = jdpower_post_filters_parse_slug_list( $src, 'post_segment' );
	} else {
		$out['region'] = jdpower_post_filters_parse_region_slug( $src );
		if ( '' === $out['region'] && ! array_key_exists( 'region', $src ) ) {
			$out['region'] = jdpower_product_region_visitor_slug();
		}
		$out['product_industry']  = jdpower_post_filters_parse_slug_list( $src, 'product_industry' );
		$out['product_segment']   = jdpower_post_filters_parse_slug_list( $src, 'product_segment' );
	}

	return $out;
}

/**
 * Resolve Polylang language for post-filters queries (page load + AJAX).
 *
 * Prefer an explicit request lang, then the Product Finder page language, then current language.
 *
 * @param array<string, mixed> $config  Config.
 * @param array<string, mixed> $request Parsed request.
 * @return string Language slug or empty.
 */
function jdpower_post_filters_resolve_query_lang( $config, $request ) {
	if ( ! function_exists( 'jdpower_pll_is_active' ) || ! jdpower_pll_is_active() ) {
		return '';
	}

	$lang = '';
	if ( ! empty( $request['lang'] ) ) {
		$lang = sanitize_key( (string) $request['lang'] );
	}

	if ( '' === $lang && ! empty( $config['finder_page_id'] ) && function_exists( 'jdpower_pll_get_post_language_slug' ) ) {
		$lang = jdpower_pll_get_post_language_slug( (int) $config['finder_page_id'] );
	}

	if ( '' === $lang && function_exists( 'jdpower_pll_current_language_slug' ) ) {
		$lang = jdpower_pll_current_language_slug();
	}

	if ( '' === $lang || ! function_exists( 'pll_languages_list' ) ) {
		return $lang;
	}

	$allowed = pll_languages_list( array( 'fields' => 'slug' ) );
	if ( ! is_array( $allowed ) || ! in_array( $lang, $allowed, true ) ) {
		return '';
	}

	return $lang;
}

/**
 * Append one taxonomy clause when slugs are non-empty (OR within that taxonomy).
 * Does nothing if the taxonomy is not registered — never limits the grid by taxonomy implicitly.
 *
 * @param array<int, array<string, mixed>> $clauses  Numeric list of tax_query clauses.
 * @param string                           $taxonomy Taxonomy slug.
 * @param string[]                         $slugs    Term slugs.
 */
function jdpower_post_filters_append_tax_clause( &$clauses, $taxonomy, $slugs ) {
	if ( empty( $slugs ) || ! taxonomy_exists( $taxonomy ) ) {
		return;
	}
	$clauses[] = array(
		'taxonomy'         => $taxonomy,
		'field'            => 'slug',
		'terms'            => $slugs,
		'include_children' => true,
		'operator'         => 'IN',
	);
}

/**
 * @param array<string, mixed> $config  Config.
 * @param array<string, mixed> $request Parsed request.
 * @return array<string, mixed> WP_Query args.
 */
function jdpower_post_filters_build_query_args( $config, $request ) {
	$sort = isset( $request['sort'] ) ? (string) $request['sort'] : 'newest';

	$insight_pts = jdpower_post_filters_get_insight_post_types( $config );
	$pf_pt       = isset( $request['pf_pt'] ) ? sanitize_key( (string) $request['pf_pt'] ) : '';
	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context'] && ! empty( $insight_pts ) ) {
		$post_type_for_query = ( '' !== $pf_pt && in_array( $pf_pt, $insight_pts, true ) ) ? $pf_pt : $insight_pts;
	} else {
		$post_type_for_query = $config['post_type'];
	}

	$per_page_cfg = max( 1, (int) $config['posts_per_page'] );
	$paged_req    = max( 1, (int) $request['paged'] );

	$args = array(
		'post_type'           => $post_type_for_query,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
	);

	/*
	 * Full page load with pf_paged>1: fetch posts 1…(paged×per_page) in one query so the grid
	 * matches “load more” accumulation. AJAX always requests a single page slice for replace/append.
	 */
	if ( ! wp_doing_ajax() && $paged_req > 1 ) {
		$args['posts_per_page'] = $per_page_cfg * $paged_req;
		$args['paged']          = 1;
	} else {
		$args['posts_per_page'] = $per_page_cfg;
		$args['paged']          = $paged_req;
	}

	if ( ! empty( $request['s'] ) ) {
		/*
		 * Lowercase for the query only (UI/URL keep user casing). Smart Search can return
		 * empty results for some title-case keywords when combined with tax_query filters
		 * (e.g. "Automotive" + North America), while the lowercase form works.
		 */
		$search = (string) $request['s'];
		$args['s'] = function_exists( 'mb_strtolower' )
			? mb_strtolower( $search, 'UTF-8' )
			: strtolower( $search );

		if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $config['context'] ) {
			// Product Finder has no sort UI — native WP relevance while Smart Search is bypassed.
			$args['orderby'] = 'relevance';
			$args['order']   = 'DESC';
		} else {
			// Insights: relevancy by default; newest/oldest/title only when user picks them.
			jdpower_post_filters_apply_sort_to_query_args( $args, $sort );
		}
	} else {
		jdpower_post_filters_apply_sort_to_query_args( $args, $sort );
	}

	// Only add tax_query when the user has chosen filters. Unfiltered queries show all published
	// posts/products regardless of industry/topic/segment assignments.
	$clauses           = array();
	$product_industry  = array();

	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context'] ) {
		$post_industry = isset( $request['post_industry'] ) && is_array( $request['post_industry'] ) ? $request['post_industry'] : array();
		$post_topic    = isset( $request['post_topic'] ) && is_array( $request['post_topic'] ) ? $request['post_topic'] : array();
		$post_segment  = isset( $request['post_segment'] ) && is_array( $request['post_segment'] ) ? $request['post_segment'] : array();

		jdpower_post_filters_append_tax_clause( $clauses, 'post_industry', $post_industry );
		jdpower_post_filters_append_tax_clause( $clauses, 'post_topic', $post_topic );
		jdpower_post_filters_append_tax_clause( $clauses, 'post_segment', $post_segment );
	} else {
		$region_slug      = isset( $request['region'] ) ? (string) $request['region'] : '';
		$product_industry = isset( $request['product_industry'] ) && is_array( $request['product_industry'] ) ? $request['product_industry'] : array();
		$product_segment  = isset( $request['product_segment'] ) && is_array( $request['product_segment'] ) ? $request['product_segment'] : array();

		if ( '' !== $region_slug ) {
			$clauses[] = array(
				'taxonomy'         => 'product_region',
				'field'            => 'slug',
				'terms'            => array( $region_slug ),
				'include_children' => true,
			);
		}
		jdpower_post_filters_append_tax_clause( $clauses, 'product_industry', $product_industry );
		jdpower_post_filters_append_tax_clause( $clauses, 'product_segment', $product_segment );
	}

	if ( count( $clauses ) > 1 ) {
		$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $clauses );
	} elseif ( count( $clauses ) === 1 ) {
		$args['tax_query'] = array( $clauses[0] );
	}

	if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $config['context'] ) {
		// Browse-only: featured + title order. Keyword search: native WP_Query (see use_native_search).
		if ( empty( $request['s'] ) ) {
			$args['jdpower_product_finder_order'] = 'featured';
		} else {
			/*
			 * Temporary: WPE Smart Search returns empty for logged-out Product Finder queries
			 * when s is combined with product_* tax_query. Insights / main search keep Smart Search.
			 */
			$args['use_native_search'] = true;
			$args['ep_integrate']      = false;
		}
		$args['jdpower_product_finder_sort'] = $sort;
	}

	if ( function_exists( 'jdpower_pll_merge_query_lang' ) ) {
		$args = jdpower_pll_merge_query_lang(
			$args,
			jdpower_post_filters_resolve_query_lang( $config, $request )
		);
	}

	return apply_filters( 'jdpower_post_filters_query_args', $args, $config, $request );
}

/**
 * Fix max_num_pages after a cumulative initial query (posts_per_page = per_page × paged).
 *
 * @param WP_Query             $query   Query object.
 * @param array<string, mixed> $config  Config.
 * @param array<string, mixed> $request Parsed request.
 */
function jdpower_post_filters_normalize_grid_query_pagination( $query, array $config, array $request ) {
	if ( ! $query instanceof WP_Query || wp_doing_ajax() ) {
		return;
	}
	$paged_req = max( 1, (int) $request['paged'] );
	if ( $paged_req <= 1 ) {
		return;
	}
	$per = max( 1, (int) $config['posts_per_page'] );
	$query->max_num_pages = max( 1, (int) ceil( (int) $query->found_posts / $per ) );
}

/**
 * Sets query var for card title utility class (h2.xxsmall).
 *
 * @param array<string, mixed> $config Filter config (reserved for call-site consistency).
 */
function jdpower_post_filters_set_card_heading_query_var( $config ) {
	set_query_var( 'jdpower_pf_card_heading_class', 'xxsmall' );
}

/**
 * Build query string args for URL (for History API and links).
 *
 * @param array<string, mixed> $config  Config.
 * @param array<string, mixed> $request Parsed request.
 * @return array<string, string|int>
 */
function jdpower_post_filters_query_args_for_url( $config, $request ) {
	$q = array();

	if ( ! empty( $request['s'] ) ) {
		// Never put core `s` in shareable URLs — it breaks Page main queries (404).
		$q['keyword'] = $request['s'];
	}

	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context'] ) {
		if ( ! empty( $request['pf_pt'] ) ) {
			$q['pf_pt'] = (string) $request['pf_pt'];
		}
		if ( ! empty( $request['post_industry'] ) ) {
			$q['post_industry'] = implode( ',', $request['post_industry'] );
		}
		if ( ! empty( $request['post_topic'] ) ) {
			$q['post_topic'] = implode( ',', $request['post_topic'] );
		}
		if ( ! empty( $request['post_segment'] ) ) {
			$q['post_segment'] = implode( ',', $request['post_segment'] );
		}
	} else {
		if ( ! empty( $request['region'] ) ) {
			$q['region'] = (string) $request['region'];
		}
		if ( ! empty( $request['product_industry'] ) ) {
			$q['product_industry'] = implode( ',', $request['product_industry'] );
		}
		if ( ! empty( $request['product_segment'] ) ) {
			$q['product_segment'] = implode( ',', $request['product_segment'] );
		}
	}

	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context'] && ! empty( $request['sort'] ) ) {
		$default_sort = ! empty( $request['s'] ) ? 'relevancy' : 'newest';
		if ( (string) $request['sort'] !== $default_sort ) {
			$q['pf_sort'] = (string) $request['sort'];
		}
	}

	if ( ! empty( $request['view'] ) && 'list' === $request['view'] ) {
		$q['pf_view'] = 'list';
	}

	if ( ! empty( $request['paged'] ) && (int) $request['paged'] > 1 ) {
		$q['pf_paged'] = (int) $request['paged'];
	}

	return $q;
}

/**
 * Base URL for a filter context (posts page or Product Finder page).
 *
 * @param array<string, mixed> $config Config.
 * @return string
 */
function jdpower_post_filters_base_url( $config ) {
	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context'] ) {
		$page_id = (int) get_option( 'page_for_posts' );
		if ( $page_id > 0 ) {
			return (string) get_permalink( $page_id );
		}
		return home_url( '/' );
	}

	if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $config['context'] ) {
		if ( ! empty( $config['finder_page_id'] ) ) {
			return (string) get_permalink( (int) $config['finder_page_id'] );
		}
		$page_id = (int) apply_filters( 'jdpower_post_filters_product_finder_page_id', 0 );
		if ( $page_id > 0 ) {
			return (string) get_permalink( $page_id );
		}
	}

	return home_url( '/' );
}

/**
 * Full URL with filters.
 *
 * @param array<string, mixed> $config  Config.
 * @param array<string, mixed> $request Parsed request.
 * @return string
 */
function jdpower_post_filters_build_url( $config, $request ) {
	$base = jdpower_post_filters_base_url( $config );
	$args = jdpower_post_filters_query_args_for_url( $config, $request );
	if ( empty( $args ) ) {
		return $base;
	}
	return add_query_arg( $args, $base );
}

/**
 * Insight Center URL filtered by a single post_industry slug.
 *
 * @param string $industry_slug Term slug.
 * @return string
 */
function jdpower_insight_center_industry_filter_url( $industry_slug ) {
	$config = jdpower_post_filters_get_config( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT );
	$slug   = sanitize_key( (string) $industry_slug );
	if ( '' === $slug ) {
		return jdpower_post_filters_base_url( $config );
	}

	return jdpower_post_filters_build_url(
		$config,
		array(
			'post_industry' => array( $slug ),
		)
	);
}

/**
 * Post types used when deciding whether sidebar taxonomy terms are non-empty.
 *
 * @param array<string, mixed> $config Config.
 * @return string[]
 */
function jdpower_post_filters_sidebar_term_post_types( $config ) {
	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === ( $config['context'] ?? '' ) ) {
		return jdpower_post_filters_get_insight_post_types( $config );
	}

	if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === ( $config['context'] ?? '' ) ) {
		$pt = $config['post_type'] ?? 'product';
		return is_array( $pt ) ? array_map( 'strval', $pt ) : array( (string) $pt );
	}

	return array();
}

/**
 * Term IDs that have at least one published post in the given post types.
 *
 * @param string   $taxonomy   Taxonomy slug.
 * @param string[] $post_types Post type slugs.
 * @return int[]
 */
function jdpower_post_filters_term_ids_with_published_posts( $taxonomy, array $post_types ) {
	global $wpdb;

	$taxonomy = sanitize_key( $taxonomy );
	if ( '' === $taxonomy || empty( $post_types ) ) {
		return array();
	}

	$post_types = array_values(
		array_filter(
			array_map( 'sanitize_key', $post_types ),
			static function ( $slug ) {
				return '' !== $slug && post_type_exists( $slug );
			}
		)
	);
	if ( empty( $post_types ) ) {
		return array();
	}

	$type_placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
	$sql               = "
		SELECT DISTINCT tt.term_id
		FROM {$wpdb->term_taxonomy} tt
		INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		WHERE tt.taxonomy = %s
		AND p.post_status = 'publish'
		AND p.post_type IN ( {$type_placeholders} )
	";

	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders built safely above.
	$prepare_args = array_merge( array( $taxonomy ), $post_types );
	$prepared     = $wpdb->prepare( $sql, ...$prepare_args );
	$ids          = $wpdb->get_col( $prepared ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	if ( empty( $ids ) ) {
		return array();
	}

	return array_map( 'intval', $ids );
}

/**
 * Whether a taxonomy is registered for at least one sidebar post type.
 *
 * @param string               $taxonomy Taxonomy slug.
 * @param array<string, mixed> $config   Config.
 * @return bool
 */
function jdpower_post_filters_taxonomy_applies_to_config( $taxonomy, $config ) {
	$taxonomy   = sanitize_key( $taxonomy );
	$post_types = jdpower_post_filters_sidebar_term_post_types( $config );

	if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
		return false;
	}

	if ( empty( $post_types ) ) {
		return true;
	}

	foreach ( $post_types as $post_type ) {
		if ( is_object_in_taxonomy( $post_type, $taxonomy ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Sidebar taxonomy terms for filter accordions (non-empty, scoped to grid post types).
 *
 * @param string               $taxonomy Taxonomy slug.
 * @param array<string, mixed> $config   Config.
 * @return WP_Term[]
 */
function jdpower_post_filters_get_sidebar_terms( $taxonomy, $config ) {
	$taxonomy = sanitize_key( $taxonomy );
	if ( ! jdpower_post_filters_taxonomy_applies_to_config( $taxonomy, $config ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$post_types = jdpower_post_filters_sidebar_term_post_types( $config );
	if ( empty( $post_types ) ) {
		return $terms;
	}

	$term_ids_with_posts = jdpower_post_filters_term_ids_with_published_posts( $taxonomy, $post_types );
	if ( empty( $term_ids_with_posts ) ) {
		return array();
	}

	$filtered = array();
	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term && in_array( (int) $term->term_id, $term_ids_with_posts, true ) ) {
			$filtered[] = $term;
		}
	}

	if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $config['context'] && 'product_industry' === $taxonomy ) {
		$filtered = jdpower_post_filters_sort_terms_automotive_first( $filtered, $taxonomy );
	}

	return $filtered;
}

/**
 * Arguments for get_categories() in Insight Center filters.
 * Excludes the default post category (typically "Uncategorized"; Settings → Writing → Default Post Category).
 *
 * @return array<string, mixed>
 */
function jdpower_post_filters_get_category_args_for_filters() {
	$args = array(
		'hide_empty' => true,
	);
	$default_id = (int) get_option( 'default_category' );
	if ( $default_id > 0 ) {
		$args['exclude'] = array( $default_id );
	}
	return $args;
}

/**
 * Toggle URLs for every sidebar term link (same logic as template-parts/post-filters/layout.php).
 * Sent on AJAX so JS can refresh hrefs — otherwise links stay stuck to the initial server render.
 *
 * @param array<string, mixed> $config  Config.
 * @param array<string, mixed> $request Parsed request.
 * @return array<int, array{key: string, value: string, href: string}>
 */
function jdpower_post_filters_sidebar_term_hrefs( $config, $request ) {
	$out = array();
	if ( empty( $config['sidebar_taxonomies'] ) || ! is_array( $config['sidebar_taxonomies'] ) ) {
		return $out;
	}
	foreach ( $config['sidebar_taxonomies'] as $group ) {
		$tax = isset( $group['taxonomy'] ) ? (string) $group['taxonomy'] : '';
		if ( '' === $tax ) {
			continue;
		}
		foreach ( jdpower_post_filters_get_sidebar_terms( $tax, $config ) as $term ) {
			$r2       = jdpower_post_filters_request_toggle_slug( $request, $tax, $term->slug );
			$data_key = $tax;
			$data_val = (string) $term->slug;
			$out[]    = array(
				'key'   => $data_key,
				'value' => $data_val,
				'href'  => jdpower_post_filters_build_url( $config, $r2 ),
			);
		}
	}
	return $out;
}

/**
 * Render card loop HTML.
 *
 * @param WP_Query             $query  Query.
 * @param array<string, mixed> $config Config.
 * @return string
 */
function jdpower_post_filters_render_cards_html( $query, $config ) {
	if ( ! $query->have_posts() ) {
		return '';
	}

	jdpower_post_filters_set_card_heading_query_var( $config );

	$part = isset( $config['card_part'] ) ? $config['card_part'] : 'template-parts/post-filters/card-post';
	ob_start();
	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( $part );
	}
	wp_reset_postdata();
	return (string) ob_get_clean();
}

/**
 * Active filter pills for toolbar.
 *
 * @param array<string, mixed> $config  Config.
 * @param array<string, mixed> $request Request.
 * @return array<int, array{key: string, label: string, value: string, taxonomy?: string}>
 */
function jdpower_post_filters_request_has_active_filters( $config, $request ) {
	$s = isset( $request['s'] ) ? trim( (string) $request['s'] ) : '';
	if ( '' !== $s ) {
		return true;
	}
	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context'] ) {
		if ( ! empty( $request['pf_pt'] ) ) {
			return true;
		}
		if ( ! empty( $request['post_industry'] ) && is_array( $request['post_industry'] ) ) {
			return true;
		}
		if ( ! empty( $request['post_topic'] ) && is_array( $request['post_topic'] ) ) {
			return true;
		}
		if ( ! empty( $request['post_segment'] ) && is_array( $request['post_segment'] ) ) {
			return true;
		}
	} else {
		if ( ! empty( $request['region'] ) ) {
			return true;
		}
		if ( ! empty( $request['product_industry'] ) && is_array( $request['product_industry'] ) ) {
			return true;
		}
		if ( ! empty( $request['product_segment'] ) && is_array( $request['product_segment'] ) ) {
			return true;
		}
	}
	return false;
}

/**
 * HTML for `<li>` items inside `.post-filters__pills`.
 *
 * @param array<int, array{key?: string, label?: string, value?: string}> $pills From jdpower_post_filters_active_pills.
 * @return string
 */
function jdpower_post_filters_render_pills_list_items_html( $pills ) {
	if ( empty( $pills ) ) {
		return '';
	}
	ob_start();
	foreach ( $pills as $pill ) {
		$key   = isset( $pill['key'] ) ? (string) $pill['key'] : '';
		$val   = isset( $pill['value'] ) ? (string) $pill['value'] : '';
		$label = isset( $pill['label'] ) ? (string) $pill['label'] : '';
		?>
		<li>
			<button type="button" class="post-filters__pill" data-pill-key="<?php echo esc_attr( $key ); ?>" data-pill-value="<?php echo esc_attr( $val ); ?>">
				<?php echo esc_html( $label ); ?>
				<span class="post-filters__pill-remove" aria-hidden="true">×</span>
			</button>
		</li>
		<?php
	}
	return (string) ob_get_clean();
}

function jdpower_post_filters_active_pills( $config, $request ) {
	$pills = array();

	if ( ! empty( $request['s'] ) ) {
		$pills[] = array(
			'key'   => 's',
			'label' => (string) $request['s'],
			'value' => (string) $request['s'],
		);
	}

	if ( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT === $config['context'] ) {
		if ( ! empty( $request['pf_pt'] ) ) {
			$pto = get_post_type_object( (string) $request['pf_pt'] );
			if ( $pto && ! empty( $pto->labels->singular_name ) ) {
				$pills[] = array(
					'key'   => 'pf_pt',
					'label' => $pto->labels->singular_name,
					'value' => (string) $request['pf_pt'],
				);
			}
		}
		foreach ( (array) ( $request['post_industry'] ?? array() ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'post_industry' );
			if ( $term && ! is_wp_error( $term ) ) {
				$pills[] = array(
					'key'        => 'post_industry',
					'label'      => $term->name,
					'value'      => $slug,
					'taxonomy'   => 'post_industry',
				);
			}
		}
		foreach ( (array) ( $request['post_topic'] ?? array() ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'post_topic' );
			if ( $term && ! is_wp_error( $term ) ) {
				$pills[] = array(
					'key'        => 'post_topic',
					'label'      => $term->name,
					'value'      => $slug,
					'taxonomy'   => 'post_topic',
				);
			}
		}
		foreach ( (array) ( $request['post_segment'] ?? array() ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'post_segment' );
			if ( $term && ! is_wp_error( $term ) ) {
				$pills[] = array(
					'key'      => 'post_segment',
					'label'    => $term->name,
					'value'    => $slug,
					'taxonomy' => 'post_segment',
				);
			}
		}
	} else {
		if ( ! empty( $request['region'] ) ) {
			$t = get_term_by( 'slug', (string) $request['region'], 'product_region' );
			if ( $t && ! is_wp_error( $t ) ) {
				$pills[] = array(
					'key'   => 'region',
					'label' => $t->name,
					'value' => $t->slug,
				);
			}
		}
		foreach ( (array) ( $request['product_industry'] ?? array() ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_industry' );
			if ( $term && ! is_wp_error( $term ) ) {
				$pills[] = array(
					'key'      => 'product_industry',
					'label'    => $term->name,
					'value'    => $slug,
					'taxonomy' => 'product_industry',
				);
			}
		}
		foreach ( (array) ( $request['product_segment'] ?? array() ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_segment' );
			if ( $term && ! is_wp_error( $term ) ) {
				$pills[] = array(
					'key'      => 'product_segment',
					'label'    => $term->name,
					'value'    => $slug,
					'taxonomy' => 'product_segment',
				);
			}
		}
	}

	return $pills;
}

/**
 * Enqueue script only on Insight Center / Product Finder.
 */
function jdpower_post_filters_enqueue_scripts() {
	if ( ! jdpower_post_filters_should_enqueue() ) {
		return;
	}

	$config  = jdpower_post_filters_resolve_enqueue_config();
	$context = $config['context'];

	wp_enqueue_script(
		'jdpower-post-filters',
		get_template_directory_uri() . '/js/post-filters.js',
		array( 'jquery' ),
		THEME_VERSION,
		true
	);

	$per_page = (int) $config['posts_per_page'];
	if ( $per_page < 1 ) {
		$per_page = 10;
	}

	$finder_id = 0;
	if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $context && ! empty( $config['finder_page_id'] ) ) {
		$finder_id = (int) $config['finder_page_id'];
	}

	$lang = '';
	if ( function_exists( 'jdpower_post_filters_resolve_query_lang' ) ) {
		$lang = jdpower_post_filters_resolve_query_lang( $config, array() );
	}

	$pf_script_data = array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( JDPOWER_POST_FILTERS_NONCE_ACTION ),
			'context'       => $context,
			'postsPerPage'  => $per_page,
			'baseUrl'       => jdpower_post_filters_base_url( $config ),
			'finderPageId'  => $finder_id,
			'lang'          => $lang,
			'resultsNoun'   => isset( $config['results_noun'] ) ? $config['results_noun'] : __( 'Insights', 'jdpower' ),
			'i18n'          => array(
				/* translators: %d: batch size */
				'loadNext'     => sprintf( __( 'Load next %d', 'jdpower' ), $per_page ),
				'loading'      => jdpower_pll__( 'Loading…' ),
				/* translators: 1: shown count, 2: total, 3: noun e.g. Insights */
				'displaying'   => __( 'Displaying %1$d of %2$d %3$s', 'jdpower' ),
				'tagged'       => __( 'Tagged', 'jdpower' ),
				'clearFilters' => __( 'Clear filters', 'jdpower' ),
				'sortNewest'   => __( 'Newest first', 'jdpower' ),
				'noResults'    => __( 'No results match your filters.', 'jdpower' ),
				'switchToGridLayout' => __( 'Switch to grid layout', 'jdpower' ),
				'switchToListLayout' => __( 'Switch to list layout', 'jdpower' ),
			),
	);

	if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $context ) {
		$pf_script_data['defaultRegionSlug'] = jdpower_product_region_visitor_slug();
		$pf_script_data['geo']               = jdpower_geotarget_script_data();
	}

	wp_localize_script( 'jdpower-post-filters', 'jdpowerPostFilters', $pf_script_data );
}
add_action( 'wp_enqueue_scripts', 'jdpower_post_filters_enqueue_scripts', 20 );

/**
 * @return bool
 */
function jdpower_post_filters_should_enqueue() {
	if ( jdpower_post_filters_is_insight_center_view() ) {
		return true;
	}
	if ( is_page_template( 'page-templates/product-finder.php' ) ) {
		return true;
	}
	return false;
}

/**
 * Config for current view (enqueue / body class).
 *
 * @return array<string, mixed>
 */
function jdpower_post_filters_resolve_enqueue_config() {
	if ( is_page_template( 'page-templates/product-finder.php' ) && is_singular() ) {
		$c                     = jdpower_post_filters_get_config( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT );
		$c['finder_page_id']   = get_queried_object_id();
		return $c;
	}
	return jdpower_post_filters_get_config( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT );
}

/**
 * AJAX: return grid HTML + meta.
 */
function jdpower_post_filters_ajax_fetch() {
	check_ajax_referer( JDPOWER_POST_FILTERS_NONCE_ACTION, 'nonce' );

	$context = isset( $_POST['context'] ) ? sanitize_key( wp_unslash( $_POST['context'] ) ) : '';
	if ( ! in_array( $context, array( JDPOWER_POST_FILTERS_CONTEXT_INSIGHT, JDPOWER_POST_FILTERS_CONTEXT_PRODUCT ), true ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid context.', 'jdpower' ) ), 400 );
	}

	$config = jdpower_post_filters_get_config( $context );
	if ( JDPOWER_POST_FILTERS_CONTEXT_PRODUCT === $context && ! empty( $_POST['finder_page_id'] ) ) {
		$config['finder_page_id'] = absint( wp_unslash( $_POST['finder_page_id'] ) );
	}

	$source  = wp_unslash( $_POST );
	$request = jdpower_post_filters_parse_request( $config, $source );

	$query = new WP_Query( jdpower_post_filters_build_query_args( $config, $request ) );

	$html = jdpower_post_filters_render_cards_html( $query, $config );

	$found      = (int) $query->found_posts;
	$max_pages  = (int) $query->max_num_pages;
	$current    = (int) $request['paged'];
	$per_page   = (int) $config['posts_per_page'];
	$showing_to   = min( $found, $current * $per_page );
	$showing_from = $found > 0 ? ( ( $current - 1 ) * $per_page + 1 ) : 0;

	$noun_lower = isset( $config['results_noun'] ) ? strtolower( (string) $config['results_noun'] ) : '';

	$pills      = jdpower_post_filters_active_pills( $config, $request );
	$pills_html = jdpower_post_filters_render_pills_list_items_html( $pills );

	wp_send_json_success(
		array(
			'html'                 => $html,
			'found_posts'          => $found,
			'max_num_pages'        => $max_pages,
			'current_page'         => $current,
			'showing_from'         => $showing_from,
			'showing_to'           => $showing_to,
			'has_more'             => $current < $max_pages,
			'url'                  => jdpower_post_filters_build_url( $config, $request ),
			'sidebar_term_hrefs'   => jdpower_post_filters_sidebar_term_hrefs( $config, $request ),
			'has_active_filters'   => jdpower_post_filters_request_has_active_filters( $config, $request ),
			'pills_html'           => $pills_html,
			'displaying_html'    => sprintf(
				/* translators: 1: cumulative number shown through this page, 2: total count, 3: noun like "insights" */
				esc_html__( 'Displaying %1$d of %2$d %3$s', 'jdpower' ),
				$showing_to,
				$found,
				esc_html( $noun_lower )
			),
		)
	);
}
add_action( 'wp_ajax_jdpower_post_filters_fetch', 'jdpower_post_filters_ajax_fetch' );
add_action( 'wp_ajax_nopriv_jdpower_post_filters_fetch', 'jdpower_post_filters_ajax_fetch' );

/**
 * Toggle a term slug in a request list field (Insight product taxonomies).
 *
 * @param array<string, mixed> $request Request.
 * @param string               $field   post_industry|post_topic|post_segment|product_industry|product_segment.
 * @param string               $slug    Term slug.
 * @return array<string, mixed>
 */
function jdpower_post_filters_request_toggle_slug( $request, $field, $slug ) {
	$slug = sanitize_title( $slug );
	$list = isset( $request[ $field ] ) && is_array( $request[ $field ] ) ? $request[ $field ] : array();
	if ( in_array( $slug, $list, true ) ) {
		$list = array_values( array_diff( $list, array( $slug ) ) );
	} else {
		$list[] = $slug;
	}
	$request[ $field ] = $list;
	$request['paged']  = 1;
	return $request;
}

/**
 * Toggle category (Insight) — single term id; click again clears.
 *
 * @param array<string, mixed> $request Request.
 * @param int                  $term_id Category term ID.
 * @return array<string, mixed>
 */
function jdpower_post_filters_request_toggle_category( $request, $term_id ) {
	$term_id = (int) $term_id;
	if ( $term_id === (int) ( $request['cat'] ?? 0 ) ) {
		$request['cat'] = 0;
	} else {
		$request['cat'] = $term_id;
	}
	$request['paged'] = 1;
	return $request;
}

/**
 * Toggle region term (Product Finder).
 *
 * @param array<string, mixed> $request     Request.
 * @param string               $region_slug product_region term slug.
 * @return array<string, mixed>
 */
function jdpower_post_filters_request_toggle_region( $request, $region_slug ) {
	$region_slug = sanitize_title( (string) $region_slug );
	if ( '' === $region_slug || $region_slug === (string) ( $request['region'] ?? '' ) ) {
		$request['region'] = '';
	} else {
		$request['region'] = $region_slug;
	}
	$request['paged'] = 1;
	return $request;
}

/**
 * Register public query var for filter grid pagination (see jdpower_post_filters_resolve_paged).
 *
 * @param string[] $vars Public query variables.
 * @return string[]
 */
function jdpower_post_filters_register_query_vars( $vars ) {
	$vars[] = 'pf_paged';
	return $vars;
}
add_filter( 'query_vars', 'jdpower_post_filters_register_query_vars' );

/**
 * Resolve a page ID from main-query vars (page_id or pagename).
 *
 * @param array<string, mixed> $query_vars Public query variables.
 * @return int
 */
function jdpower_post_filters_page_id_from_query_vars( $query_vars ) {
	if ( ! empty( $query_vars['page_id'] ) ) {
		return (int) $query_vars['page_id'];
	}
	if ( ! empty( $query_vars['pagename'] ) && function_exists( 'get_page_by_path' ) ) {
		$page = get_page_by_path( (string) $query_vars['pagename'] );
		if ( $page instanceof WP_Post && 'page' === $page->post_type ) {
			return (int) $page->ID;
		}
	}
	return 0;
}

/**
 * Whether this request is already a Product Finder or Insight Center page that owns `?keyword=`.
 * Used so the Search hub does not rewrite those URLs to the Search page.
 *
 * @param array<string, mixed> $query_vars Public query variables.
 * @return bool
 */
function jdpower_post_filters_request_owns_keyword( $query_vars ) {
	$page_id = jdpower_post_filters_page_id_from_query_vars( $query_vars );
	if ( $page_id > 0 && 'page-templates/product-finder.php' === get_page_template_slug( $page_id ) ) {
		return true;
	}

	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( $posts_page_id > 0 && $page_id === $posts_page_id ) {
		return true;
	}

	return false;
}

/**
 * Strip core `s` from the main query on Product Finder / Insight Center so legacy
 * `?s=` bookmarks do not 404. The secondary grid query still reads the term via
 * {@see jdpower_post_filters_parse_search_term()} from $_GET.
 *
 * @param array<string, mixed> $query_vars Public query variables.
 * @return array<string, mixed>
 */
function jdpower_post_filters_unset_main_query_s( $query_vars ) {
	if ( empty( $query_vars['s'] ) ) {
		return $query_vars;
	}

	if ( jdpower_post_filters_request_owns_keyword( $query_vars ) ) {
		unset( $query_vars['s'] );
	}

	return $query_vars;
}
add_filter( 'request', 'jdpower_post_filters_unset_main_query_s', 5 );
