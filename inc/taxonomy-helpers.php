<?php
/**
 * Shared taxonomy helpers (primary term resolution).
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Primary taxonomy term for a post (SEO plugin primary when set), else first assigned term.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy slug.
 * @return WP_Term|null
 */
function jdpower_get_post_primary_taxonomy_term( $post_id, $taxonomy ) {
	$post_id  = (int) $post_id;
	$taxonomy = sanitize_key( (string) $taxonomy );

	if ( $post_id <= 0 || '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
		return null;
	}

	$post_type = get_post_type( $post_id );
	if ( ! $post_type || ! is_object_in_taxonomy( $post_type, $taxonomy ) ) {
		return null;
	}

	$is_assigned = static function ( $term_id ) use ( $post_id, $taxonomy ) {
		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) {
			return false;
		}
		return is_object_in_term( $post_id, $taxonomy, $term_id );
	};

	if ( class_exists( 'WPSEO_Primary_Term' ) ) {
		$yoast_primary = new WPSEO_Primary_Term( $taxonomy, $post_id );
		$term_id       = (int) $yoast_primary->get_primary_term();
		if ( $is_assigned( $term_id ) ) {
			$term = get_term( $term_id, $taxonomy );
			if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
				return $term;
			}
		}
	}

	if ( function_exists( 'rank_math_get_primary_term' ) ) {
		$term = rank_math_get_primary_term( $taxonomy, $post_id );
		if ( $term instanceof WP_Term && ! is_wp_error( $term ) && $is_assigned( $term->term_id ) ) {
			return $term;
		}
	}

	$terms = wp_get_post_terms(
		$post_id,
		$taxonomy,
		array(
			'orderby' => 'name',
			'order'   => 'ASC',
		)
	);
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) && $terms[0] instanceof WP_Term ) {
		return $terms[0];
	}

	return null;
}

/**
 * Display name for the primary (or first) term on a post taxonomy.
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy slug.
 * @return string
 */
function jdpower_get_post_primary_taxonomy_term_name( $post_id, $taxonomy ) {
	$term = jdpower_get_post_primary_taxonomy_term( $post_id, $taxonomy );
	return ( $term instanceof WP_Term && isset( $term->name ) )
		? jdpower_decode_text_for_display( (string) $term->name )
		: '';
}

/**
 * Insight card pill taxonomy from Theme Settings → Insights.
 *
 * @return string post_topic|post_industry|post_segment
 */
function jdpower_insight_card_pill_taxonomy() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$allowed = array( 'post_topic', 'post_industry', 'post_segment' );
	$raw     = function_exists( 'get_field' ) ? get_field( 'insight_card_pill_taxonomy', 'option' ) : '';
	$cached  = ( is_string( $raw ) && in_array( $raw, $allowed, true ) ) ? $raw : 'post_topic';

	return $cached;
}

/**
 * Term IDs excluded from insight card pills for a taxonomy (Theme Settings → Insights).
 *
 * @param string|null $taxonomy Optional taxonomy slug; defaults to the configured pill taxonomy.
 * @return int[]
 */
function jdpower_insight_card_pill_excluded_term_ids( $taxonomy = null ) {
	$field_map = array(
		'post_topic'    => 'insight_card_pill_exclude_post_topic',
		'post_industry' => 'insight_card_pill_exclude_post_industry',
		'post_segment'  => 'insight_card_pill_exclude_post_segment',
	);

	if ( null === $taxonomy ) {
		$taxonomy = jdpower_insight_card_pill_taxonomy();
	}
	$taxonomy = sanitize_key( (string) $taxonomy );

	if ( ! isset( $field_map[ $taxonomy ] ) || ! function_exists( 'get_field' ) ) {
		return array();
	}

	$raw = get_field( $field_map[ $taxonomy ], 'option' );
	if ( ! is_array( $raw ) ) {
		if ( is_numeric( $raw ) ) {
			$raw = array( (int) $raw );
		} elseif ( is_object( $raw ) && isset( $raw->term_id ) ) {
			$raw = array( (int) $raw->term_id );
		} else {
			$raw = array();
		}
	}

	$ids = array();
	foreach ( $raw as $item ) {
		if ( is_numeric( $item ) ) {
			$ids[] = (int) $item;
		} elseif ( is_object( $item ) && isset( $item->term_id ) ) {
			$ids[] = (int) $item->term_id;
		} elseif ( is_array( $item ) && isset( $item['term_id'] ) && is_numeric( $item['term_id'] ) ) {
			$ids[] = (int) $item['term_id'];
		}
	}

	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Label for the insight card media pill (primary term, skipping excluded terms).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function jdpower_insight_card_pill_label( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return '';
	}

	$taxonomy = jdpower_insight_card_pill_taxonomy();
	$excluded = array_flip( jdpower_insight_card_pill_excluded_term_ids( $taxonomy ) );

	$primary = jdpower_get_post_primary_taxonomy_term( $post_id, $taxonomy );
	if ( $primary instanceof WP_Term && ! isset( $excluded[ (int) $primary->term_id ] ) ) {
		return jdpower_decode_text_for_display( (string) $primary->name );
	}

	$terms = wp_get_post_terms(
		$post_id,
		$taxonomy,
		array(
			'orderby' => 'name',
			'order'   => 'ASC',
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		if ( isset( $excluded[ (int) $term->term_id ] ) ) {
			continue;
		}
		return jdpower_decode_text_for_display( (string) $term->name );
	}

	return '';
}
