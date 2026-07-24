<?php
/**
 * Admin list columns for Segments and Solutions CPTs (relationships via ACF post object meta).
 *
 * Uses raw post meta keys — same values ACF stores for post_object fields with return format "id".
 *
 * @package jdpower
 */

/** ACF post_object meta — return format "id" (same as field name). */
if ( ! defined( 'JDPOWER_META_ASSOCIATED_INDUSTRY' ) ) {
	define( 'JDPOWER_META_ASSOCIATED_INDUSTRY', 'associated_industry' );
}
if ( ! defined( 'JDPOWER_META_ASSOCIATED_SEGMENT' ) ) {
	define( 'JDPOWER_META_ASSOCIATED_SEGMENT', 'associated_segment' );
}

/**
 * Output an admin list cell: edit link to a post or escaped title.
 *
 * @param int $target_post_id Post ID (industries, segments, etc.).
 */
function jdpower_admin_column_linked_post_title( $target_post_id ) {
	$target_post_id = (int) $target_post_id;
	if ( ! $target_post_id ) {
		echo '&mdash;';
		return;
	}

	$title = get_the_title( $target_post_id );
	if ( '' === $title ) {
		echo '&mdash;';
		return;
	}

	$edit_link = get_edit_post_link( $target_post_id, 'display' );
	if ( $edit_link ) {
		printf(
			'<a href="%s">%s</a>',
			esc_url( $edit_link ),
			esc_html( $title )
		);
	} else {
		echo esc_html( $title );
	}
}

/**
 * Segments: add Industry column after title.
 *
 * @param string[] $columns Column slug => label.
 * @return string[]
 */
function jdpower_segments_posts_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['jdpower_industry'] = __( 'Industry', 'jdpower' );
		}
	}
	return $new;
}
add_filter( 'manage_segments_posts_columns', 'jdpower_segments_posts_columns' );

/**
 * Segments: render Industry column (industry post title from meta; no ACF field object load).
 *
 * @param string $column Column slug.
 * @param int    $post_id Post ID.
 */
function jdpower_segments_posts_custom_column( $column, $post_id ) {
	if ( 'jdpower_industry' !== $column ) {
		return;
	}

	$industry_id = (int) get_post_meta( $post_id, JDPOWER_META_ASSOCIATED_INDUSTRY, true );
	jdpower_admin_column_linked_post_title( $industry_id );
}
add_action( 'manage_segments_posts_custom_column', 'jdpower_segments_posts_custom_column', 10, 2 );

/**
 * Segments: make Industry sortable (by related industry post_title via SQL join).
 *
 * @param string[] $columns Column => orderby key.
 * @return string[]
 */
function jdpower_segments_sortable_columns( $columns ) {
	$columns['jdpower_industry'] = 'jdpower_industry';
	return $columns;
}
add_filter( 'manage_edit-segments_sortable_columns', 'jdpower_segments_sortable_columns' );

/**
 * Solution: add Industry and Segment columns after title (path order: industry, segment).
 *
 * @param string[] $columns Column slug => label.
 * @return string[]
 */
function jdpower_solution_posts_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['jdpower_industry'] = __( 'Industry', 'jdpower' );
			$new['jdpower_segment']  = __( 'Segment', 'jdpower' );
		}
	}
	return $new;
}
add_filter( 'manage_solution_posts_columns', 'jdpower_solution_posts_columns' );

/**
 * Solution: render Industry and Segment columns (meta only; industry via segment’s associated_industry).
 *
 * @param string $column Column slug.
 * @param int    $post_id Post ID.
 */
function jdpower_solution_posts_custom_column( $column, $post_id ) {
	if ( 'jdpower_industry' === $column ) {
		$segment_id = (int) get_post_meta( $post_id, JDPOWER_META_ASSOCIATED_SEGMENT, true );
		if ( ! $segment_id ) {
			echo '&mdash;';
			return;
		}
		$industry_id = (int) get_post_meta( $segment_id, JDPOWER_META_ASSOCIATED_INDUSTRY, true );
		jdpower_admin_column_linked_post_title( $industry_id );
		return;
	}

	if ( 'jdpower_segment' === $column ) {
		$segment_id = (int) get_post_meta( $post_id, JDPOWER_META_ASSOCIATED_SEGMENT, true );
		jdpower_admin_column_linked_post_title( $segment_id );
		return;
	}
}
add_action( 'manage_solution_posts_custom_column', 'jdpower_solution_posts_custom_column', 10, 2 );

/**
 * Solution: make Industry and Segment columns sortable (distinct orderby keys so SQL stays correct).
 *
 * @param string[] $columns Column => orderby key.
 * @return string[]
 */
function jdpower_solution_sortable_columns( $columns ) {
	$columns['jdpower_industry'] = 'jdpower_solution_industry';
	$columns['jdpower_segment']  = 'jdpower_solution_segment';
	return $columns;
}
add_filter( 'manage_edit-solution_sortable_columns', 'jdpower_solution_sortable_columns' );

/**
 * Sort Segments by related industry post_title; Solution Industry column by related industry title; Solution Segment by segment title.
 *
 * @param string[] $clauses Query clauses.
 * @param WP_Query $query   Query object.
 * @return string[]
 */
function jdpower_segment_solution_admin_posts_clauses( $clauses, $query ) {
	global $wpdb;

	if ( ! is_admin() || ! $query instanceof WP_Query ) {
		return $clauses;
	}

	$post_type = $query->get( 'post_type' );
	if ( is_array( $post_type ) ) {
		return $clauses;
	}

	$orderby = $query->get( 'orderby' );

	if ( 'segments' === $post_type && 'jdpower_industry' === $orderby ) {
		$order = 'ASC' === strtoupper( $query->get( 'order' ) ) ? 'ASC' : 'DESC';

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS jdp_si_m ON ({$wpdb->posts}.ID = jdp_si_m.post_id AND jdp_si_m.meta_key = '" . esc_sql( JDPOWER_META_ASSOCIATED_INDUSTRY ) . "') ";
		$clauses['join'] .= " LEFT JOIN {$wpdb->posts} AS jdp_si_p ON (jdp_si_p.ID = CAST(jdp_si_m.meta_value AS UNSIGNED) AND jdp_si_p.post_type = 'industries') ";

		$clauses['orderby'] = "COALESCE(jdp_si_p.post_title, '') {$order}, {$wpdb->posts}.ID {$order}";
		$clauses['groupby'] = "{$wpdb->posts}.ID";
		return $clauses;
	}

	if ( 'solution' === $post_type && 'jdpower_solution_industry' === $orderby ) {
		$order = 'ASC' === strtoupper( $query->get( 'order' ) ) ? 'ASC' : 'DESC';

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS jdp_su_seg_m ON ({$wpdb->posts}.ID = jdp_su_seg_m.post_id AND jdp_su_seg_m.meta_key = '" . esc_sql( JDPOWER_META_ASSOCIATED_SEGMENT ) . "') ";
		$clauses['join'] .= " LEFT JOIN {$wpdb->posts} AS jdp_su_seg_p ON (jdp_su_seg_p.ID = CAST(jdp_su_seg_m.meta_value AS UNSIGNED) AND jdp_su_seg_p.post_type = 'segments') ";
		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS jdp_su_ind_m ON (jdp_su_seg_p.ID = jdp_su_ind_m.post_id AND jdp_su_ind_m.meta_key = '" . esc_sql( JDPOWER_META_ASSOCIATED_INDUSTRY ) . "') ";
		$clauses['join'] .= " LEFT JOIN {$wpdb->posts} AS jdp_su_ind_p ON (jdp_su_ind_p.ID = CAST(jdp_su_ind_m.meta_value AS UNSIGNED) AND jdp_su_ind_p.post_type = 'industries') ";

		$clauses['orderby'] = "COALESCE(jdp_su_ind_p.post_title, '') {$order}, {$wpdb->posts}.ID {$order}";
		$clauses['groupby'] = "{$wpdb->posts}.ID";
		return $clauses;
	}

	if ( 'solution' === $post_type && 'jdpower_solution_segment' === $orderby ) {
		$order = 'ASC' === strtoupper( $query->get( 'order' ) ) ? 'ASC' : 'DESC';

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS jdp_su_seg2_m ON ({$wpdb->posts}.ID = jdp_su_seg2_m.post_id AND jdp_su_seg2_m.meta_key = '" . esc_sql( JDPOWER_META_ASSOCIATED_SEGMENT ) . "') ";
		$clauses['join'] .= " LEFT JOIN {$wpdb->posts} AS jdp_su_seg2_p ON (jdp_su_seg2_p.ID = CAST(jdp_su_seg2_m.meta_value AS UNSIGNED) AND jdp_su_seg2_p.post_type = 'segments') ";

		$clauses['orderby'] = "COALESCE(jdp_su_seg2_p.post_title, '') {$order}, {$wpdb->posts}.ID {$order}";
		$clauses['groupby'] = "{$wpdb->posts}.ID";
		return $clauses;
	}

	return $clauses;
}
add_filter( 'posts_clauses', 'jdpower_segment_solution_admin_posts_clauses', 10, 2 );
