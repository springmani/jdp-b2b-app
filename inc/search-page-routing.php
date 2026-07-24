<?php
/**
 * Search page: public ?keyword= on the hub page (template page-search.php).
 *
 * The main query stays a normal Page query. We intentionally do **not** set `s` on the
 * main request: WordPress would combine page ID constraints with search SQL and can 404.
 * Results use a secondary {@see WP_Query} with `s` set so search plugins (including WP Engine
 * Smart Search, when it hooks `posts_search` / `pre_get_posts` on non-main queries) can run.
 *
 * `get_search_query` is filtered on the hub page so code that reads the global search string
 * still sees the `keyword` value.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cached ID of the published page assigned template "Search" (page-search.php).
 *
 * @return int Page ID or 0 if none.
 */
function jdpower_get_search_page_id() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$ids = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_key'               => '_wp_page_template',
			'meta_value'             => 'page-search.php',
		)
	);
	$cached = ! empty( $ids ) ? (int) $ids[0] : 0;
	return $cached;
}

/**
 * Whether the request already targets the given page (by page_id or pagename path).
 *
 * @param array $query_vars Public query variables.
 * @param int   $page_id    Page ID.
 * @return bool
 */
function jdpower_search_request_targets_page_id( $query_vars, $page_id ) {
	if ( $page_id <= 0 ) {
		return false;
	}
	if ( ! empty( $query_vars['page_id'] ) && (int) $query_vars['page_id'] === $page_id ) {
		return true;
	}
	if ( ! empty( $query_vars['pagename'] ) ) {
		$page = get_page_by_path( $query_vars['pagename'] );
		if ( $page instanceof WP_Post && 'page' === $page->post_type && (int) $page->ID === $page_id ) {
			return true;
		}
	}
	return false;
}

/**
 * Insight-style post type slugs (Insight Center parity).
 *
 * @return string[]
 */
function jdpower_search_page_insight_post_type_slugs() {
	if ( function_exists( 'jdpower_post_filters_insight_post_type_slugs' ) ) {
		return jdpower_post_filters_insight_post_type_slugs();
	}
	return array( 'post' );
}

/**
 * Content type filters shown on the Search hub sidebar.
 *
 * Keys are `pf_pt` query values. News (`post`) searches both News and Resource posts.
 *
 * @return array<string, string> Filter slug => label.
 */
function jdpower_search_page_content_filter_choices() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$choices = array(
		'post' => __( 'News', 'jdpower' ),
	);

	$press_release = get_post_type_object( 'press_release' );
	if ( $press_release && ! empty( $press_release->labels->name ) ) {
		$choices['press_release'] = $press_release->labels->name;
	}

	$product = get_post_type_object( 'product' );
	if ( $product && ! empty( $product->labels->name ) ) {
		$choices['product'] = $product->labels->name;
	}

	/**
	 * Filter Search hub content type filter labels.
	 *
	 * @param array<string, string> $choices Filter slug => label.
	 */
	$cached = apply_filters( 'jdpower_search_page_content_filter_choices', $choices );

	return $cached;
}

/**
 * Allowed `pf_pt` values for Search hub content filters.
 *
 * @return string[]
 */
function jdpower_search_page_content_filter_slugs() {
	return array_keys( jdpower_search_page_content_filter_choices() );
}

/**
 * Map a Search hub content filter to {@see WP_Query} post type slugs.
 *
 * @param string $filter_slug `pf_pt` value or '' for all Search hub types.
 * @return string[]
 */
function jdpower_search_page_filter_post_types( $filter_slug = '' ) {
	$filter_slug = is_string( $filter_slug ) ? sanitize_key( $filter_slug ) : '';

	switch ( $filter_slug ) {
		case 'post':
			return array( 'post', 'resource' );
		case 'press_release':
			return array( 'press_release' );
		case 'product':
			return array( 'product' );
		default:
			return array( 'post', 'resource', 'press_release', 'product' );
	}
}

/**
 * Post types included in site search (browse-all and unfiltered queries).
 *
 * @return string[]
 */
function jdpower_search_page_post_type_slugs() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$types = array_values(
		array_filter(
			jdpower_search_page_filter_post_types( '' ),
			'post_type_exists'
		)
	);

	/**
	 * Filter searchable post types on the Search hub.
	 *
	 * @param string[] $types Post type slugs.
	 */
	$cached = apply_filters( 'jdpower_search_page_post_type_slugs', $types );

	return $cached;
}

/**
 * Sort modes for the site search hub (GET `search_sort`).
 *
 * @return array<string, string> Slug => label.
 */
function jdpower_search_page_sort_choices() {
	return array(
		'relevancy'     => __( 'Relevancy', 'jdpower' ),
		'date'          => __( 'Date', 'jdpower' ),
		'alphabetical'  => __( 'Alphabetical', 'jdpower' ),
	);
}

/**
 * Active `search_sort` slug (default: relevancy).
 *
 * @return string
 */
function jdpower_search_page_get_search_sort() {
	$allowed = array_keys( jdpower_search_page_sort_choices() );
	$raw     = get_query_var( 'search_sort' );
	$key     = is_string( $raw ) ? sanitize_key( $raw ) : '';
	if ( '' === $key || ! in_array( $key, $allowed, true ) ) {
		return 'relevancy';
	}
	return $key;
}

/**
 * Map sort slug to {@see WP_Query} order arguments (Smart Search can adjust via filter on full args).
 *
 * @param string $sort Slug from {@see jdpower_search_page_sort_choices()}.
 * @return array{orderby: string, order: string}
 */
function jdpower_search_page_sort_to_query_order( $sort ) {
	switch ( $sort ) {
		case 'date':
			return array(
				'orderby' => 'date',
				'order'   => 'DESC',
			);
		case 'alphabetical':
			return array(
				'orderby' => 'title',
				'order'   => 'ASC',
			);
		case 'relevancy':
		default:
			return array(
				'orderby' => 'relevance',
				'order'   => 'DESC',
			);
	}
}

/**
 * Sanitized `pf_pt` query var when present and allowed.
 *
 * @return string Post type slug or ''.
 */
function jdpower_search_page_get_pf_pt() {
	$raw = get_query_var( 'pf_pt' );
	if ( ! is_string( $raw ) || '' === $raw ) {
		return '';
	}
	$pt = sanitize_key( $raw );
	return in_array( $pt, jdpower_search_page_content_filter_slugs(), true ) ? $pt : '';
}

/**
 * {@see WP_Query} arguments for Search hub results (keyword search or browse-all when empty).
 *
 * @param string $search_term Keyword or '' for all published searchable content.
 * @param int    $paged       Results page number.
 * @param string $pf_pt       Active post type filter or ''.
 * @param string $sort        Sort slug from {@see jdpower_search_page_sort_choices()}.
 * @return array
 */
function jdpower_search_page_results_query_args( $search_term, $paged, $pf_pt = '', $sort = 'relevancy' ) {
	$search_term = is_string( $search_term ) ? sanitize_text_field( $search_term ) : '';
	$paged       = max( 1, (int) $paged );
	$pf_pt       = is_string( $pf_pt ) ? sanitize_key( $pf_pt ) : '';
	$sort        = is_string( $sort ) ? sanitize_key( $sort ) : 'relevancy';

	$post_types = array_values(
		array_filter(
			jdpower_search_page_filter_post_types( $pf_pt ),
			'post_type_exists'
		)
	);
	$post_type_arg = ! empty( $post_types ) ? $post_types : jdpower_search_page_post_type_slugs();

	$effective_sort = $sort;
	if ( '' === $search_term && 'relevancy' === $effective_sort ) {
		$effective_sort = 'date';
	}

	$results_args = array_merge(
		array(
			'paged'                       => $paged,
			'post_status'                 => 'publish',
			'posts_per_page'              => (int) get_option( 'posts_per_page', 10 ),
			'post_type'                   => $post_type_arg,
			'jdpower_site_search_results' => true,
		),
		jdpower_search_page_sort_to_query_order( $effective_sort )
	);

	if ( '' !== $search_term ) {
		$results_args['s'] = $search_term;
	}

	/**
	 * Filter secondary search results query (Smart Search / Elasticsearch plugins often hook here).
	 *
	 * @param array  $results_args  {@see WP_Query} arguments.
	 * @param string $search_term   Keyword or ''.
	 * @param string $pf_pt         Active `pf_pt` slug or ''.
	 * @param string $sort          Active `search_sort` slug.
	 */
	return apply_filters( 'jdpower_search_results_wp_query_args', $results_args, $search_term, $pf_pt, $sort );
}

/**
 * Sort options shown in the Search hub UI (relevancy hidden when browsing all content).
 *
 * @param string $search_term Keyword or ''.
 * @return array<string, string>
 */
function jdpower_search_page_display_sort_choices( $search_term = '' ) {
	$choices = jdpower_search_page_sort_choices();
	if ( '' === sanitize_text_field( (string) $search_term ) ) {
		unset( $choices['relevancy'] );
	}
	return $choices;
}

/**
 * Sort slug selected in the Search hub UI.
 *
 * @param string $search_term Keyword or ''.
 * @param string $sort        Raw sort slug.
 * @return string
 */
function jdpower_search_page_display_sort( $search_term, $sort ) {
	$search_term = sanitize_text_field( (string) $search_term );
	$sort        = is_string( $sort ) ? sanitize_key( $sort ) : 'relevancy';

	if ( '' === $search_term && 'relevancy' === $sort ) {
		return 'date';
	}

	$allowed = array_keys( jdpower_search_page_display_sort_choices( $search_term ) );
	return in_array( $sort, $allowed, true ) ? $sort : ( '' === $search_term ? 'date' : 'relevancy' );
}

/**
 * Build the Search hub URL with query args (keyword, optional pf_pt, optional result_paged).
 *
 * @param string $keyword        Search keyword (empty allowed).
 * @param string $pf_pt          Allowed insight post type slug or ''.
 * @param int    $result_paged   Secondary loop page; 0 or 1 omits arg.
 * @param string $search_sort    Sort slug; `relevancy` or empty is omitted from the URL.
 * @return string
 */
function jdpower_search_page_url( $keyword, $pf_pt = '', $result_paged = 0, $search_sort = '' ) {
	$hub_id = jdpower_get_search_page_id();
	if ( $hub_id <= 0 ) {
		return home_url( '/' );
	}
	$base = get_permalink( $hub_id );
	$args = array();

	$keyword = is_string( $keyword ) ? sanitize_text_field( $keyword ) : '';
	if ( '' !== $keyword ) {
		$args['keyword'] = $keyword;
	}

	$pf_pt = is_string( $pf_pt ) ? sanitize_key( $pf_pt ) : '';
	if ( '' !== $pf_pt && in_array( $pf_pt, jdpower_search_page_content_filter_slugs(), true ) ) {
		$args['pf_pt'] = $pf_pt;
	}

	$p = (int) $result_paged;
	if ( $p > 1 ) {
		$args['result_paged'] = $p;
	}

	$sort = is_string( $search_sort ) ? sanitize_key( $search_sort ) : '';
	if ( '' !== $sort && 'relevancy' !== $sort && in_array( $sort, array_keys( jdpower_search_page_sort_choices() ), true ) ) {
		$args['search_sort'] = $sort;
	}

	return add_query_arg( $args, $base );
}

/**
 * Arguments for {@see paginate_links()} on the Search hub (preserves /business/ base + filters).
 *
 * @param int    $total       Total pages.
 * @param int    $current     Current page (1-based).
 * @param string $keyword     Search keyword or ''.
 * @param string $pf_pt       Active `pf_pt` slug or ''.
 * @param string $search_sort Active sort slug.
 * @return array<string, mixed>
 */
function jdpower_search_page_paginate_links_args( $total, $current, $keyword, $pf_pt = '', $search_sort = '' ) {
	$base_url = jdpower_search_page_url( $keyword, $pf_pt, 0, $search_sort );
	$parsed   = wp_parse_url( $base_url );
	$format   = empty( $parsed['query'] ) ? '?result_paged=%#%' : '&result_paged=%#%';

	return array(
		'base'      => add_query_arg( 'result_paged', '%_%', $base_url ),
		'format'    => $format,
		'total'     => max( 1, (int) $total ),
		'current'   => max( 1, (int) $current ),
		'add_args'  => false,
		'prev_text' => __( 'Previous', 'jdpower' ),
		'next_text' => __( 'Next', 'jdpower' ),
		'type'      => 'list',
	);
}

/**
 * Whether the Search hub has active keyword or content-type filters.
 *
 * @param string $search_term Keyword or ''.
 * @param string $pf_pt       Active `pf_pt` slug or ''.
 * @return bool
 */
function jdpower_search_page_has_active_filters( $search_term, $pf_pt = '' ) {
	if ( '' !== sanitize_text_field( (string) $search_term ) ) {
		return true;
	}

	$pf_pt = sanitize_key( (string) $pf_pt );

	return '' !== $pf_pt && in_array( $pf_pt, jdpower_search_page_content_filter_slugs(), true );
}

/**
 * Clear-filters URL for the Search hub (browse-all, no keyword).
 *
 * @return string
 */
function jdpower_search_page_clear_url() {
	return jdpower_search_page_url( '', '', 0, '' );
}

/**
 * Active filter pills for the Search hub toolbar.
 *
 * @param string $search_term Keyword or ''.
 * @param string $pf_pt       Active `pf_pt` slug or ''.
 * @param string $sort        Active sort slug.
 * @return array<int, array{key: string, label: string, url: string}>
 */
function jdpower_search_page_active_pills( $search_term, $pf_pt = '', $sort = 'relevancy' ) {
	$pills       = array();
	$search_term = sanitize_text_field( (string) $search_term );
	$pf_pt       = sanitize_key( (string) $pf_pt );
	$sort        = sanitize_key( (string) $sort );
	$choices     = jdpower_search_page_content_filter_choices();

	if ( '' !== $search_term ) {
		$pills[] = array(
			'key'   => 'keyword',
			'label' => $search_term,
			'url'   => jdpower_search_page_url( '', $pf_pt, 1, $sort ),
		);
	}

	if ( '' !== $pf_pt && isset( $choices[ $pf_pt ] ) ) {
		$pills[] = array(
			'key'   => 'pf_pt',
			'label' => $choices[ $pf_pt ],
			'url'   => jdpower_search_page_url( $search_term, '', 1, $sort ),
		);
	}

	return $pills;
}

/**
 * HTML for `<li>` items inside `.post-filters__pills` on the Search hub.
 *
 * @param array<int, array{key?: string, label?: string, url?: string}> $pills From {@see jdpower_search_page_active_pills()}.
 * @return string
 */
function jdpower_search_page_render_pills_list_items_html( $pills ) {
	if ( empty( $pills ) ) {
		return '';
	}

	ob_start();
	foreach ( $pills as $pill ) {
		if ( empty( $pill['url'] ) || empty( $pill['label'] ) ) {
			continue;
		}
		?>
		<li>
			<a class="post-filters__pill" href="<?php echo esc_url( (string) $pill['url'] ); ?>">
				<?php echo esc_html( (string) $pill['label'] ); ?>
				<span class="post-filters__pill-remove" aria-hidden="true">×</span>
			</a>
		</li>
		<?php
	}
	return (string) ob_get_clean();
}

/**
 * Register public query vars ?keyword= & ?pf_pt= & ?result_paged= & ?search_sort=
 *
 * @param string[] $vars Query vars.
 * @return string[]
 */
function jdpower_search_register_query_vars( $vars ) {
	$vars[] = 'keyword';
	$vars[] = 'pf_pt';
	$vars[] = 'search_sort';
	// Pagination for the secondary search loop on the Search page (avoid `paged` conflicting with multipage Page content).
	$vars[] = 'result_paged';
	return $vars;
}
add_filter( 'query_vars', 'jdpower_search_register_query_vars' );

/**
 * On the Search hub page, expose `keyword` through {@see get_search_query()} for plugins/snippets
 * that read the global search string (without putting `s` on the main query).
 *
 * @param mixed $search Default search string from query var `s`.
 * @return mixed
 */
function jdpower_search_page_get_search_query( $search ) {
	$pid = jdpower_get_search_page_id();
	if ( $pid <= 0 || ! is_page( $pid ) ) {
		return $search;
	}
	if ( 'page-search.php' !== get_page_template_slug( $pid ) ) {
		return $search;
	}
	$kw = get_query_var( 'keyword' );
	if ( is_string( $kw ) && '' !== trim( $kw ) ) {
		return sanitize_text_field( wp_unslash( $kw ) );
	}
	return $search;
}
add_filter( 'get_search_query', 'jdpower_search_page_get_search_query', 5 );

/**
 * Fires on the secondary results {@see WP_Query} so hosting search integrations can adjust that query.
 *
 * @param WP_Query $query The results query.
 */
function jdpower_search_results_wp_query_pre_get_posts( $query ) {
	if ( ! $query instanceof WP_Query || ! $query->get( 'jdpower_site_search_results' ) ) {
		return;
	}
	do_action( 'jdpower_site_search_results_wp_query', $query );
}
add_action( 'pre_get_posts', 'jdpower_search_results_wp_query_pre_get_posts', 0 );

/**
 * Keep keyword on the main query; optionally rewrite other URLs to the Search page.
 * Never set `s` on the main query — combined with a singular page it can 404.
 *
 * @param array $query_vars Public query variables.
 * @return array
 */
function jdpower_search_keyword_request( $query_vars ) {
	if ( empty( $query_vars['keyword'] ) || ! is_string( $query_vars['keyword'] ) ) {
		return $query_vars;
	}

	$keyword = sanitize_text_field( wp_unslash( $query_vars['keyword'] ) );
	if ( '' === $keyword ) {
		unset( $query_vars['keyword'] );
		return $query_vars;
	}

	$search_page_id = jdpower_get_search_page_id();
	if ( $search_page_id <= 0 ) {
		return $query_vars;
	}

	// Only treat the configured page as the hub if it still uses the Search template.
	if ( 'page-search.php' !== get_page_template_slug( $search_page_id ) ) {
		return $query_vars;
	}

	if ( jdpower_search_request_targets_page_id( $query_vars, $search_page_id ) ) {
		// Already the Search page: leave pagename / page_id / keyword intact; do not set `s`.
		return $query_vars;
	}

	// Product Finder / Insight Center also use ?keyword= for their own grids.
	if ( function_exists( 'jdpower_post_filters_request_owns_keyword' ) && jdpower_post_filters_request_owns_keyword( $query_vars ) ) {
		return $query_vars;
	}

	// Keyword on another URL: resolve main query to the Search page; keep `keyword` for the template.
	$uri = get_page_uri( $search_page_id );
	if ( $uri ) {
		$query_vars['pagename'] = $uri;
		unset( $query_vars['page_id'] );
	} else {
		$query_vars['page_id'] = $search_page_id;
		unset( $query_vars['pagename'] );
	}

	unset(
		$query_vars['name'],
		$query_vars['attachment'],
		$query_vars['attachment_id'],
		$query_vars['error']
	);

	return $query_vars;
}
add_filter( 'request', 'jdpower_search_keyword_request', 5, 1 );
