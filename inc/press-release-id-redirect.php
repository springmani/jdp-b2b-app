<?php
/**
 * Map /pr-id/{numeric-id}/ to the press release whose ACF meta `press_release_id` matches.
 *
 * After deploying or changing this rule, visit Settings → Permalinks → Save once per environment
 * to flush rewrite rules (see also `jdpower_flush_rewrite_rules_on_switch` in product-rewrites.php).
 */

/**
 * Register pretty URL → query var.
 */
function jdpower_press_release_id_rewrite_rule() {
	add_rewrite_rule(
		'^pr-id/([0-9]+)/?$',
		'index.php?pr_id=$matches[1]',
		'top'
	);
}
add_action( 'init', 'jdpower_press_release_id_rewrite_rule' );

/**
 * Allow `pr_id` in public query vars.
 *
 * @param array $vars Public query variables.
 * @return array
 */
function jdpower_press_release_id_query_vars( $vars ) {
	$vars[] = 'pr_id';
	return $vars;
}
add_filter( 'query_vars', 'jdpower_press_release_id_query_vars' );

/**
 * Resolve `pr_id` to a published press_release and redirect, or fall back to home.
 */
function jdpower_press_release_id_template_redirect() {
	if ( is_admin() ) {
		return;
	}

	$pr_id = get_query_var( 'pr_id' );
	if ( '' === $pr_id || '0' === $pr_id ) {
		return;
	}

	$pr_id = absint( $pr_id );
	if ( ! $pr_id ) {
		return;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'press_release',
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => 'press_release_id',
					'value' => (string) $pr_id,
				),
			),
		)
	);

	if ( $query->have_posts() ) {
		$permalink = get_permalink( (int) $query->posts[0]->ID );
		if ( $permalink ) {
			wp_safe_redirect( $permalink, 301 );
			exit;
		}
	}

	wp_safe_redirect( home_url( '/' ), 302 );
	exit;
}
add_action( 'template_redirect', 'jdpower_press_release_id_template_redirect', 0 );
