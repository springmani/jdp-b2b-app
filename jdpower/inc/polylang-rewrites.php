<?php
/**
 * Shared Polylang helpers for custom rewrite rules and permalinks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Polylang is available.
 */
function jdpower_pll_is_active() {
	return function_exists( 'pll_languages_list' );
}

/**
 * Query arg to fetch posts from all languages.
 *
 * @return array<string, string>
 */
function jdpower_pll_all_languages_query_arg() {
	return jdpower_pll_is_active() ? array( 'lang' => '' ) : array();
}

/**
 * Default Polylang language slug.
 */
function jdpower_pll_default_language() {
	return jdpower_pll_is_active() ? (string) pll_default_language( 'slug' ) : '';
}

/**
 * Whether the default language omits its URL prefix.
 */
function jdpower_pll_hide_default() {
	return jdpower_pll_is_active() && ! empty( PLL()->options['hide_default'] );
}

/**
 * Polylang language slug for a post.
 *
 * @param int $post_id Post ID.
 */
function jdpower_pll_get_post_language_slug( $post_id ) {
	if ( ! jdpower_pll_is_active() ) {
		return '';
	}

	return (string) pll_get_post_language( (int) $post_id, 'slug' );
}

/**
 * Current Polylang language slug on the frontend.
 */
function jdpower_pll_current_language_slug() {
	if ( ! jdpower_pll_is_active() || ! function_exists( 'pll_current_language' ) ) {
		return '';
	}

	return (string) pll_current_language( 'slug' );
}

/**
 * Whether insight queries may fall back to default-language posts on translated pages.
 */
function jdpower_pll_should_fallback_to_default() {
	if ( ! jdpower_pll_is_active() ) {
		return false;
	}

	$current = jdpower_pll_current_language_slug();
	$default = jdpower_pll_default_language();

	return '' !== $current && '' !== $default && $current !== $default;
}

/**
 * Merge an explicit Polylang language into WP_Query args.
 *
 * When $lang is empty, Polylang uses the current frontend language.
 *
 * @param array<string, mixed> $args WP_Query arguments.
 * @param string|null          $lang Language slug, or null/empty for current language.
 * @return array<string, mixed>
 */
function jdpower_pll_merge_query_lang( array $args, $lang = null ) {
	if ( ! jdpower_pll_is_active() || null === $lang || '' === $lang ) {
		return $args;
	}

	$args['lang'] = (string) $lang;

	return $args;
}

/**
 * Map taxonomy term IDs to their equivalents in a Polylang language.
 *
 * @param int[]  $term_ids Term IDs.
 * @param string $lang     Target language slug.
 * @return int[]
 */
function jdpower_pll_map_term_ids_to_language( array $term_ids, $lang ) {
	$lang = sanitize_key( (string) $lang );
	if ( '' === $lang || ! jdpower_pll_is_active() || ! function_exists( 'pll_get_term' ) ) {
		return array_values( array_unique( array_filter( array_map( 'intval', $term_ids ) ) ) );
	}

	$out = array();
	foreach ( $term_ids as $term_id ) {
		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) {
			continue;
		}
		$mapped = (int) pll_get_term( $term_id, $lang );
		$out[]  = $mapped > 0 ? $mapped : $term_id;
	}

	return array_values( array_unique( array_filter( $out ) ) );
}

/**
 * Map insight block taxonomy filters to a Polylang language.
 *
 * @param array<string, int[]> $terms_by_tax Term IDs keyed by taxonomy.
 * @param string               $lang         Target language slug.
 * @return array<string, int[]>
 */
function jdpower_pll_map_insight_terms_by_tax_to_language( array $terms_by_tax, $lang ) {
	$out = array();

	foreach ( $terms_by_tax as $taxonomy => $term_ids ) {
		if ( ! is_array( $term_ids ) || empty( $term_ids ) ) {
			continue;
		}
		$mapped = jdpower_pll_map_term_ids_to_language( $term_ids, $lang );
		if ( ! empty( $mapped ) ) {
			$out[ sanitize_key( (string) $taxonomy ) ] = $mapped;
		}
	}

	return $out;
}

/**
 * Pick one post ID from candidates, preferring a Polylang language slug.
 *
 * @param int[]       $post_ids       Candidate post IDs.
 * @param string|null $preferred_lang Language slug to prefer; defaults to Polylang default.
 * @return int Post ID, or 0 if none.
 */
function jdpower_isp_pick_post_by_language( array $post_ids, $preferred_lang = null ) {
	if ( empty( $post_ids ) ) {
		return 0;
	}

	if ( 1 === count( $post_ids ) ) {
		return (int) $post_ids[0];
	}

	if ( null === $preferred_lang || '' === $preferred_lang ) {
		$preferred_lang = jdpower_pll_default_language();
	}

	foreach ( $post_ids as $post_id ) {
		if ( jdpower_pll_get_post_language_slug( (int) $post_id ) === $preferred_lang ) {
			return (int) $post_id;
		}
	}

	return (int) $post_ids[0];
}

/**
 * Find a hierarchy post ID by slug and parent path segments (industry / segment slugs).
 *
 * @param string      $post_type      Post type: industries, segments, or solution.
 * @param string      $slug           Post slug (name).
 * @param string|null $ind_path       Industry slug from the URL.
 * @param string|null $seg_path       Segment slug from the URL.
 * @param string|null $preferred_lang Polylang language slug to prefer when slugs collide.
 * @return int Post ID, or 0 if not found.
 */
function jdpower_isp_find_hierarchy_post_id( $post_type, $slug, $ind_path = null, $seg_path = null, $preferred_lang = null ) {
	$post_ids = get_posts(
		array_merge(
			array(
				'post_type'              => $post_type,
				'name'                   => $slug,
				'post_status'            => 'publish',
				'numberposts'            => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			),
			jdpower_pll_all_languages_query_arg()
		)
	);

	if ( empty( $post_ids ) ) {
		return 0;
	}

	if ( 'industries' === $post_type ) {
		return jdpower_isp_pick_post_by_language( $post_ids, $preferred_lang );
	}

	$matches = array();

	foreach ( $post_ids as $post_id ) {
		$post_id = (int) $post_id;

		if ( 'segments' === $post_type ) {
			$ind_slug = jdpower_get_slug( (int) get_post_meta( $post_id, 'associated_industry', true ) );
			if ( $ind_path && $ind_slug === $ind_path ) {
				$matches[] = $post_id;
			}
			continue;
		}

		if ( 'solution' === $post_type ) {
			$seg_id   = (int) get_post_meta( $post_id, 'associated_segment', true );
			$seg_slug = jdpower_get_slug( $seg_id );
			$ind_slug = jdpower_get_slug( (int) get_post_meta( $seg_id, 'associated_industry', true ) );

			if ( $seg_path && $seg_slug === $seg_path && $ind_path && $ind_slug === $ind_path ) {
				$matches[] = $post_id;
			}
		}
	}

	return jdpower_isp_pick_post_by_language( $matches, $preferred_lang );
}

/**
 * Resolve hierarchy URL to a specific post ID and Polylang language.
 *
 * When industry/segment slugs match across translations, prefers the language from
 * the URL prefix (query var `lang`) or the site default for unprefixed URLs.
 *
 * @param array<string, mixed> $query_vars Request query vars.
 * @param string               $ind_path   Industry slug from URL.
 * @param string               $seg_path   Segment slug from URL.
 * @param string               $sol_path   Solution slug from URL.
 * @return array<string, mixed>
 */
function jdpower_isp_set_lang_from_hierarchy( $query_vars, $ind_path, $seg_path, $sol_path ) {
	if ( ! jdpower_pll_is_active() ) {
		return $query_vars;
	}

	$preferred_lang = ! empty( $query_vars['lang'] )
		? (string) $query_vars['lang']
		: jdpower_pll_default_language();

	$post_id = 0;

	if ( $sol_path ) {
		$post_id = jdpower_isp_find_hierarchy_post_id( 'solution', $sol_path, $ind_path, $seg_path, $preferred_lang );
	} elseif ( $seg_path ) {
		$post_id = jdpower_isp_find_hierarchy_post_id( 'segments', $seg_path, $ind_path, null, $preferred_lang );
	} elseif ( $ind_path ) {
		$post_id = jdpower_isp_find_hierarchy_post_id( 'industries', $ind_path, null, null, $preferred_lang );
	}

	if ( $post_id > 0 ) {
		$query_vars['p'] = $post_id;

		$lang = jdpower_pll_get_post_language_slug( $post_id );
		if ( '' !== $lang ) {
			$query_vars['lang'] = $lang;
		}
	}

	return $query_vars;
}

/**
 * Resolve flat permalink requests (e.g. products) to a specific post and language.
 *
 * When the same slug exists in multiple translations, prefers the URL language prefix
 * or the site default for unprefixed URLs.
 *
 * @param array<string, mixed> $query_vars Request query vars.
 * @param string               $post_type  Post type slug.
 * @param string               $name       Post slug (name).
 * @return array<string, mixed>
 */
function jdpower_pll_resolve_flat_post_request( $query_vars, $post_type, $name ) {
	if ( ! jdpower_pll_is_active() ) {
		return $query_vars;
	}

	$preferred_lang = ! empty( $query_vars['lang'] )
		? (string) $query_vars['lang']
		: jdpower_pll_default_language();

	$post_ids = get_posts(
		array_merge(
			array(
				'post_type'              => $post_type,
				'name'                   => $name,
				'post_status'            => 'publish',
				'numberposts'            => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			),
			jdpower_pll_all_languages_query_arg()
		)
	);

	$post_id = jdpower_isp_pick_post_by_language( $post_ids, $preferred_lang );
	if ( $post_id <= 0 ) {
		return $query_vars;
	}

	$query_vars['p'] = $post_id;

	$lang = jdpower_pll_get_post_language_slug( $post_id );
	if ( '' !== $lang ) {
		$query_vars['lang'] = $lang;
	}

	return $query_vars;
}

/**
 * When no translation exists, keep the current (default-language) page URL.
 *
 * Avoids 404s from inventing /{lang}/same-slug/ links for pages that were never
 * translated. Polylang's nav-menu switcher uses pll_the_language_link (raw=1).
 *
 * @param string|null $url    Translation URL; empty when none exists.
 * @param string      $slug   Target language slug.
 * @param string      $locale Target language locale.
 * @return string|null
 */
function jdpower_pll_the_language_link_if_untranslated( $url, $slug, $locale ) {
	unset( $locale );

	if ( ! jdpower_pll_is_active() || ! empty( $url ) || ! is_singular() ) {
		return $url;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return $url;
	}

	$slug = sanitize_key( (string) $slug );
	if ( '' === $slug ) {
		return $url;
	}

	$translation_id = (int) pll_get_post( $post->ID, $slug );
	if ( $translation_id > 0 ) {
		return $url;
	}

	$current_url = get_permalink( $post );
	if ( ! is_string( $current_url ) || '' === $current_url ) {
		return $url;
	}

	// No translation: stay on this page's real URL (usually the default language).
	return $current_url;
}
add_filter( 'pll_the_language_link', 'jdpower_pll_the_language_link_if_untranslated', 10, 3 );