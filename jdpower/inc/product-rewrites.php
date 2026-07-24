<?php
/**
 * Product routing — top-level permalinks (/{product-slug}/) without a /products/ prefix.
 *
 * After deploying or changing these rules, visit Settings → Permalinks → Save once per
 * environment to flush rewrite rules.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flush rewrite rules when the theme is activated.
 */
function jdpower_flush_rewrite_rules_on_switch() {
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'jdpower_flush_rewrite_rules_on_switch' );

/**
 * Published product IDs across all Polylang languages.
 *
 * @return int[]
 */
function jdpower_get_published_product_ids() {
	$args = array_merge(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'numberposts'            => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		),
		jdpower_pll_all_languages_query_arg()
	);

	return get_posts( $args );
}

/**
 * Register top-level rewrite rules for one product slug.
 *
 * @param string $slug Post slug.
 * @param string $lang Polylang language slug, or empty when Polylang is inactive.
 */
function jdpower_register_product_rewrite_rules( $slug, $lang = '' ) {
	if ( '' === $slug ) {
		return;
	}

	$quoted_slug  = preg_quote( $slug, '/' );
	$default_lang = jdpower_pll_default_language();
	$hide_default = jdpower_pll_hide_default();

	if ( ! $lang || ! jdpower_pll_is_active() ) {
		add_rewrite_rule(
			'^' . $quoted_slug . '/?$',
			'index.php?post_type=product&name=' . $slug,
			'top'
		);
		return;
	}

	$is_default = ( $lang === $default_lang );

	if ( $is_default && $hide_default ) {
		add_rewrite_rule(
			'^' . $quoted_slug . '/?$',
			'index.php?post_type=product&name=' . $slug,
			'top'
		);
		return;
	}

	add_rewrite_rule(
		'^' . preg_quote( $lang, '/' ) . '/' . $quoted_slug . '/?$',
		'index.php?lang=' . $lang . '&post_type=product&name=' . $slug,
		'top'
	);
}

/**
 * Register rewrite rules for published product slugs.
 *
 * Runs after industry rules (init priority 10) so industry hierarchy paths keep precedence
 * when the first URL segment is a valid industry slug.
 */
add_action(
	'init',
	function () {
		$product_ids = jdpower_get_published_product_ids();

		if ( empty( $product_ids ) ) {
			return;
		}

		foreach ( $product_ids as $product_id ) {
			$post = get_post( (int) $product_id );
			if ( ! ( $post instanceof WP_Post ) || '' === $post->post_name ) {
				continue;
			}

			$lang = jdpower_pll_get_post_language_slug( (int) $product_id );

			jdpower_register_product_rewrite_rules( $post->post_name, $lang );
		}
	},
	11
);

/**
 * Legacy /products/{slug}/ URLs → top-level product permalink.
 */
add_action(
	'init',
	function () {
		add_rewrite_rule(
			'^products/([^/]+)/?$',
			'index.php?jdpower_legacy_product=$matches[1]',
			'top'
		);
	},
	11
);

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'jdpower_legacy_product';
		return $vars;
	}
);

add_filter(
	'request',
	function ( $query_vars ) {
		if ( is_admin() || ! empty( $query_vars['p'] ) || ! empty( $query_vars['ind_path'] ) ) {
			return $query_vars;
		}

		$post_type = $query_vars['post_type'] ?? '';
		$name      = $query_vars['name'] ?? '';

		if ( 'product' !== $post_type || ! is_string( $name ) || '' === $name ) {
			return $query_vars;
		}

		return jdpower_pll_resolve_flat_post_request( $query_vars, 'product', $name );
	},
	98
);

add_action(
	'template_redirect',
	function () {
		if ( is_admin() ) {
			return;
		}

		$legacy_slug = get_query_var( 'jdpower_legacy_product' );
		if ( '' === $legacy_slug || '0' === $legacy_slug ) {
			return;
		}

		$lookup_args = array_merge(
			array(
				'post_type'              => 'product',
				'name'                   => sanitize_title( (string) $legacy_slug ),
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			),
			jdpower_pll_all_languages_query_arg()
		);

		$post_ids = get_posts(
			array_merge(
				$lookup_args,
				array(
					'fields'        => 'ids',
					'numberposts'   => -1,
					'posts_per_page' => -1,
				)
			)
		);

		if ( empty( $post_ids ) ) {
			return;
		}

		$product_id = jdpower_isp_pick_post_by_language( $post_ids );
		if ( $product_id <= 0 ) {
			return;
		}

		$permalink = get_permalink( $product_id );
		if ( $permalink ) {
			wp_safe_redirect( $permalink, 301 );
			exit;
		}
	},
	0
);

/**
 * Build top-level product permalinks.
 *
 * Priority 10 runs before Polylang (20) so language prefixes (/es/, etc.) are preserved.
 */
add_filter(
	'post_type_link',
	function ( $post_link, $post, $leavename = false, $sample = false ) {
		if ( ! ( $post instanceof WP_Post ) || 'product' !== $post->post_type ) {
			return $post_link;
		}

		$use_placeholder = ( $leavename || $sample );
		$slug            = $use_placeholder ? '%postname%' : $post->post_name;
		if ( ! $slug && ! $use_placeholder ) {
			return $post_link;
		}

		return jdpower_prefixed_home_url( $slug );
	},
	10,
	4
);

/**
 * Prevent canonical redirects from rewriting top-level product URLs.
 */
add_filter(
	'redirect_canonical',
	function ( $redirect_url, $requested_url ) {
		if ( is_singular( 'product' ) ) {
			return false;
		}

		return $redirect_url;
	},
	10,
	2
);
