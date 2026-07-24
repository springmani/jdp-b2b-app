<?php
/**
 * Tiered insight queries — shared by singular Related Insights and Featured Insights (Dynamic By Type).
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'jdpower_insights_acf_taxonomy_term_ids' ) ) :
	/**
	 * Normalize an ACF taxonomy field value to valid term IDs.
	 *
	 * @param mixed  $value    Raw ACF value.
	 * @param string $taxonomy Taxonomy slug.
	 * @return int[]
	 */
	function jdpower_insights_acf_taxonomy_term_ids( $value, $taxonomy ) {
		$taxonomy = sanitize_key( (string) $taxonomy );
		if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$raw = array();
		if ( null === $value || false === $value || '' === $value ) {
			$raw = array();
		} elseif ( is_numeric( $value ) ) {
			$raw[] = (int) $value;
		} elseif ( is_array( $value ) ) {
			foreach ( $value as $item ) {
				if ( is_numeric( $item ) ) {
					$raw[] = (int) $item;
				} elseif ( is_object( $item ) && isset( $item->term_id ) ) {
					$raw[] = (int) $item->term_id;
				} elseif ( is_array( $item ) && isset( $item['term_id'] ) && is_numeric( $item['term_id'] ) ) {
					$raw[] = (int) $item['term_id'];
				}
			}
		}

		$raw = array_values( array_unique( array_filter( array_map( 'intval', $raw ) ) ) );
		$out = array();
		foreach ( $raw as $tid ) {
			if ( $tid <= 0 ) {
				continue;
			}
			$term = get_term( $tid, $taxonomy );
			if ( $term instanceof WP_Term && ! is_wp_error( $term ) ) {
				$out[] = $tid;
			}
		}

		return array_values( array_unique( $out ) );
	}
endif;

if ( ! function_exists( 'jdpower_insights_acf_taxonomy_term_map' ) ) :
	/**
	 * Build a terms-by-taxonomy map from ACF industry / segment / topic fields.
	 *
	 * @param mixed $industry_field ACF post_industry value.
	 * @param mixed $segment_field  ACF post_segment value.
	 * @param mixed $topic_field    ACF post_topic value.
	 * @return array<string, int[]>
	 */
	function jdpower_insights_acf_taxonomy_term_map( $industry_field, $segment_field, $topic_field ) {
		$map = array();

		$industry_ids = jdpower_insights_acf_taxonomy_term_ids( $industry_field, 'post_industry' );
		if ( ! empty( $industry_ids ) ) {
			$map['post_industry'] = $industry_ids;
		}

		$segment_ids = jdpower_insights_acf_taxonomy_term_ids( $segment_field, 'post_segment' );
		if ( ! empty( $segment_ids ) ) {
			$map['post_segment'] = $segment_ids;
		}

		$topic_ids = jdpower_insights_acf_taxonomy_term_ids( $topic_field, 'post_topic' );
		if ( ! empty( $topic_ids ) ) {
			$map['post_topic'] = $topic_ids;
		}

		return $map;
	}
endif;

if ( ! function_exists( 'jdpower_related_insights_assigned_terms' ) ) :
	/**
	 * Insight taxonomy term IDs assigned to a post (non-empty taxonomies only).
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $post_type Post type slug.
	 * @return array<string, int[]> Keys: post_industry, post_segment, post_topic.
	 */
	function jdpower_related_insights_assigned_terms( $post_id, $post_type ) {
		$post_id   = (int) $post_id;
		$post_type = (string) $post_type;
		$out       = array();

		foreach ( array( 'post_industry', 'post_segment', 'post_topic' ) as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) || ! is_object_in_taxonomy( $post_type, $taxonomy ) ) {
				continue;
			}

			$term_ids = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
				continue;
			}

			$term_ids = array_values( array_filter( array_map( 'intval', $term_ids ) ) );
			if ( ! empty( $term_ids ) ) {
				$out[ $taxonomy ] = $term_ids;
			}
		}

		return $out;
	}
endif;

if ( ! function_exists( 'jdpower_related_insights_tax_query_for_keys' ) ) :
	/**
	 * Build a tax_query AND group for the given taxonomy keys (skips keys with no assigned terms).
	 *
	 * @param array<string, int[]> $terms_by_tax Assigned term IDs keyed by taxonomy.
	 * @param string[]             $taxonomy_keys Taxonomies to require.
	 * @return array<int|string, mixed>|null Null when no clauses would be added.
	 */
	function jdpower_related_insights_tax_query_for_keys( array $terms_by_tax, array $taxonomy_keys ) {
		$clauses = array();

		foreach ( $taxonomy_keys as $taxonomy ) {
			if ( empty( $terms_by_tax[ $taxonomy ] ) ) {
				continue;
			}

			$clauses[] = array(
				'taxonomy'         => $taxonomy,
				'field'            => 'term_id',
				'terms'            => $terms_by_tax[ $taxonomy ],
				'operator'         => 'IN',
				'include_children' => true,
			);
		}

		if ( empty( $clauses ) ) {
			return null;
		}

		if ( count( $clauses ) > 1 ) {
			return array_merge( array( 'relation' => 'AND' ), $clauses );
		}

		return $clauses;
	}
endif;

if ( ! function_exists( 'jdpower_related_insights_match_tiers' ) ) :
	/**
	 * Taxonomy match tiers from strictest to broadest for filling insight slots.
	 *
	 * @param array<string, int[]> $terms_by_tax Assigned term IDs keyed by taxonomy.
	 * @return array<int, string[]>
	 */
	function jdpower_related_insights_match_tiers( array $terms_by_tax ) {
		$assigned = array();
		foreach ( array( 'post_industry', 'post_segment', 'post_topic' ) as $taxonomy ) {
			if ( ! empty( $terms_by_tax[ $taxonomy ] ) ) {
				$assigned[] = $taxonomy;
			}
		}

		if ( empty( $assigned ) ) {
			return array();
		}

		$candidates = array();

		if ( count( $assigned ) > 1 ) {
			$candidates[] = $assigned;
		}

		if ( ! empty( $terms_by_tax['post_industry'] ) && ! empty( $terms_by_tax['post_segment'] ) ) {
			$candidates[] = array( 'post_industry', 'post_segment' );
		}
		if ( ! empty( $terms_by_tax['post_industry'] ) && ! empty( $terms_by_tax['post_topic'] ) ) {
			$candidates[] = array( 'post_industry', 'post_topic' );
		}
		if ( ! empty( $terms_by_tax['post_segment'] ) ) {
			$candidates[] = array( 'post_segment' );
		}
		if ( ! empty( $terms_by_tax['post_industry'] ) ) {
			$candidates[] = array( 'post_industry' );
		}

		if ( 1 === count( $assigned ) ) {
			$candidates[] = $assigned;
		}

		$tiers = array();
		$seen  = array();
		foreach ( $candidates as $keys ) {
			$keys = array_values( array_unique( $keys ) );
			sort( $keys );
			$signature = implode( '|', $keys );
			if ( isset( $seen[ $signature ] ) ) {
				continue;
			}
			$seen[ $signature ] = true;
			$tiers[]            = $keys;
		}

		return $tiers;
	}
endif;

if ( ! function_exists( 'jdpower_insights_query_latest_ids' ) ) :
	/**
	 * Newest published insight post IDs (no taxonomy filter).
	 *
	 * @param string[] $post_types  Post type slugs.
	 * @param int[]    $exclude_ids Post IDs to omit.
	 * @param int      $limit       Max IDs.
	 * @param string|null $query_lang Polylang language slug, or null for current frontend language.
	 * @return int[]
	 */
	function jdpower_insights_query_latest_ids( array $post_types, array $exclude_ids, $limit, $query_lang = null ) {
		$limit = max( 1, (int) $limit );
		$post_types = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );
		if ( empty( $post_types ) ) {
			return array();
		}

		$exclude_ids = array_values( array_unique( array_filter( array_map( 'intval', $exclude_ids ) ) ) );

		$query = new WP_Query(
			function_exists( 'jdpower_pll_merge_query_lang' )
				? jdpower_pll_merge_query_lang(
					array(
						'post_type'              => $post_types,
						'post_status'            => 'publish',
						'posts_per_page'         => $limit,
						'post__not_in'           => $exclude_ids,
						'ignore_sticky_posts'    => true,
						'orderby'                => 'date',
						'order'                  => 'DESC',
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
					),
					$query_lang
				)
				: array(
					'post_type'              => $post_types,
					'post_status'            => 'publish',
					'posts_per_page'         => $limit,
					'post__not_in'           => $exclude_ids,
					'ignore_sticky_posts'    => true,
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
		);

		if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'intval', $query->posts ) ) );
	}
endif;

if ( ! function_exists( 'jdpower_related_insights_query_ids' ) ) :
	/**
	 * Query published insight post IDs for one match tier.
	 *
	 * @param string|string[]      $post_types   Post type slug or list.
	 * @param array<string, int[]> $terms_by_tax Assigned terms.
	 * @param string[]             $tier_keys    Taxonomies that must match for this tier.
	 * @param int[]                  $exclude_ids  Post IDs to omit.
	 * @param int                    $limit        Max IDs to return.
	 * @param string|null            $query_lang   Polylang language slug, or null for current frontend language.
	 * @return int[]
	 */
	function jdpower_related_insights_query_ids( $post_types, array $terms_by_tax, array $tier_keys, array $exclude_ids, $limit, $query_lang = null ) {
		$limit = max( 1, (int) $limit );
		if ( ! is_array( $post_types ) ) {
			$post_types = array( (string) $post_types );
		}
		$post_types = array_values( array_filter( array_map( 'sanitize_key', $post_types ) ) );
		if ( empty( $post_types ) ) {
			return array();
		}

		$tax_query = jdpower_related_insights_tax_query_for_keys( $terms_by_tax, $tier_keys );
		if ( null === $tax_query ) {
			return array();
		}

		$exclude_ids = array_values( array_unique( array_filter( array_map( 'intval', $exclude_ids ) ) ) );

		$query = new WP_Query(
			function_exists( 'jdpower_pll_merge_query_lang' )
				? jdpower_pll_merge_query_lang(
					array(
						'post_type'              => $post_types,
						'post_status'            => 'publish',
						'posts_per_page'         => $limit,
						'post__not_in'           => $exclude_ids,
						'ignore_sticky_posts'    => true,
						'orderby'                => 'date',
						'order'                  => 'DESC',
						'tax_query'              => $tax_query,
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
					),
					$query_lang
				)
				: array(
					'post_type'              => $post_types,
					'post_status'            => 'publish',
					'posts_per_page'         => $limit,
					'post__not_in'           => $exclude_ids,
					'ignore_sticky_posts'    => true,
					'orderby'                => 'date',
					'order'                  => 'DESC',
					'tax_query'              => $tax_query,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
		);

		if ( empty( $query->posts ) || ! is_array( $query->posts ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'intval', $query->posts ) ) );
	}
endif;

if ( ! function_exists( 'jdpower_insights_tiered_collect_ids' ) ) :
	/**
	 * Fill insight post IDs using tiered taxonomy matches, then optional latest fallback.
	 *
	 * @param array<int, string[]> $post_type_phases Ordered post-type slug lists to query (e.g. same type, then all types).
	 * @param array<string, int[]> $terms_by_tax     Term IDs keyed by taxonomy; empty skips tiers.
	 * @param int[]                  $exclude_ids      Post IDs to omit.
	 * @param int                    $limit            Target count.
	 * @param bool                   $latest_fallback  When true, fill remainder with newest posts (no taxonomy filter).
	 * @param string[]               $latest_types     Post types for latest fallback; defaults to last phase.
	 * @param string|null            $query_lang       Polylang language slug, or null for current frontend language.
	 * @return int[]
	 */
	function jdpower_insights_tiered_collect_ids( array $post_type_phases, array $terms_by_tax, array $exclude_ids, $limit, $latest_fallback = true, array $latest_types = array(), $query_lang = null ) {
		$limit = max( 1, (int) $limit );
		$collected   = array();
		$exclude_ids = array_values( array_unique( array_filter( array_map( 'intval', $exclude_ids ) ) ) );
		$tiers       = jdpower_related_insights_match_tiers( $terms_by_tax );

		$merge_ids = static function ( array $found ) use ( &$collected, &$exclude_ids, $limit ) {
			foreach ( $found as $found_id ) {
				if ( count( $collected ) >= $limit ) {
					break;
				}
				$found_id = (int) $found_id;
				if ( $found_id <= 0 || in_array( $found_id, $exclude_ids, true ) ) {
					continue;
				}
				$collected[]   = $found_id;
				$exclude_ids[] = $found_id;
			}
		};

		if ( ! empty( $tiers ) ) {
			foreach ( $post_type_phases as $phase_types ) {
				if ( count( $collected ) >= $limit ) {
					break;
				}
				$phase_types = array_values( array_filter( array_map( 'sanitize_key', (array) $phase_types ) ) );
				if ( empty( $phase_types ) ) {
					continue;
				}
				foreach ( $tiers as $tier_keys ) {
					if ( count( $collected ) >= $limit ) {
						break;
					}
					$remaining = $limit - count( $collected );
					$merge_ids(
						jdpower_related_insights_query_ids(
							$phase_types,
							$terms_by_tax,
							$tier_keys,
							$exclude_ids,
							$remaining,
							$query_lang
						)
					);
				}
			}
		}

		if ( $latest_fallback && count( $collected ) < $limit ) {
			$fallback_types = ! empty( $latest_types )
				? $latest_types
				: ( ! empty( $post_type_phases ) ? (array) end( $post_type_phases ) : array() );
			$fallback_types = array_values( array_filter( array_map( 'sanitize_key', $fallback_types ) ) );
			if ( ! empty( $fallback_types ) ) {
				$merge_ids(
					jdpower_insights_query_latest_ids(
						$fallback_types,
						$exclude_ids,
						$limit - count( $collected ),
						$query_lang
					)
				);
			}
		}

		return $collected;
	}
endif;

if ( ! function_exists( 'jdpower_related_insights_collect_ids' ) ) :
	/**
	 * Related insights on singular templates (same type first, then all insight types).
	 *
	 * @param int                  $post_id       Host post ID (excluded from results).
	 * @param string               $post_type     Host post type.
	 * @param string[]             $insight_types All insight post type slugs.
	 * @param array<string, int[]> $terms_by_tax  Assigned terms on the host post.
	 * @param int                  $limit         Target number of related posts.
	 * @return int[]
	 */
	function jdpower_related_insights_collect_ids( $post_id, $post_type, array $insight_types, array $terms_by_tax, $limit = 4 ) {
		$post_id   = (int) $post_id;
		$post_type = sanitize_key( (string) $post_type );
		$limit     = max( 1, (int) $limit );

		if ( empty( $terms_by_tax ) ) {
			return jdpower_insights_tiered_collect_ids(
				array(),
				array(),
				array( $post_id ),
				$limit,
				true,
				$insight_types
			);
		}

		return jdpower_insights_tiered_collect_ids(
			array( array( $post_type ), $insight_types ),
			$terms_by_tax,
			array( $post_id ),
			$limit,
			true,
			$insight_types
		);
	}
endif;

if ( ! function_exists( 'jdpower_insights_prioritize_featured_ids' ) ) :
	/**
	 * Move Featured (jdpower_featured) posts to the front while preserving relative order in each group.
	 *
	 * @param int[] $post_ids Post IDs.
	 * @return int[]
	 */
	function jdpower_insights_prioritize_featured_ids( array $post_ids ) {
		$featured = array();
		$rest     = array();

		foreach ( $post_ids as $post_id ) {
			$post_id = (int) $post_id;
			if ( $post_id <= 0 ) {
				continue;
			}
			if ( '1' === (string) get_post_meta( $post_id, 'jdpower_featured', true ) ) {
				$featured[] = $post_id;
			} else {
				$rest[] = $post_id;
			}
		}

		return array_merge( $featured, $rest );
	}
endif;

if ( ! function_exists( 'jdpower_featured_insights_dynamic_query_cards_for_language' ) ) :
	/**
	 * Load cards for Dynamic By Type in an explicit Polylang language.
	 *
	 * @param string[]             $post_types     Allowed post type slugs.
	 * @param int                  $limit          Max cards (1–12).
	 * @param bool                 $featured_first When true, Featured posts appear first; remaining slots use latest.
	 * @param array<string, int[]> $terms_by_tax   Optional industry / segment / topic term IDs from the block.
	 * @param bool                 $copy_card_short_only When true, card copy uses only `page_card_short_description`.
	 * @param string|null          $query_lang     Polylang language slug, or null for current frontend language.
	 * @return array<int, array<string, mixed>>
	 */
	function jdpower_featured_insights_dynamic_query_cards_for_language( $post_types, $limit, $featured_first, array $terms_by_tax = array(), $copy_card_short_only = false, $query_lang = null ) {
		$cards      = array();
		$post_types = array_values( array_filter( array_map( 'sanitize_key', (array) $post_types ) ) );
		$limit      = max( 1, min( 12, (int) $limit ) );
		if ( empty( $post_types ) || ! function_exists( 'jdpower_featured_insights_card_from_post' ) ) {
			return $cards;
		}

		$terms_by_tax = array_filter(
			(array) $terms_by_tax,
			static function ( $term_ids ) {
				return is_array( $term_ids ) && ! empty( $term_ids );
			}
		);

		if ( ! empty( $terms_by_tax ) ) {
			$post_ids = jdpower_insights_tiered_collect_ids(
				array( $post_types ),
				$terms_by_tax,
				array(),
				$limit,
				true,
				$post_types,
				$query_lang
			);
			if ( $featured_first && count( $post_ids ) > 1 ) {
				$post_ids = jdpower_insights_prioritize_featured_ids( $post_ids );
				$post_ids = array_slice( $post_ids, 0, $limit );
			}
		} elseif ( $featured_first ) {
			$post_ids = array();
			$exclude  = array();

			$q_feat = new WP_Query(
				function_exists( 'jdpower_pll_merge_query_lang' )
					? jdpower_pll_merge_query_lang(
						array(
							'post_type'              => $post_types,
							'post_status'            => 'publish',
							'posts_per_page'         => $limit,
							'ignore_sticky_posts'    => true,
							'orderby'                => 'date',
							'order'                  => 'DESC',
							'meta_query'             => array(
								array(
									'key'     => 'jdpower_featured',
									'value'   => '1',
									'compare' => '=',
								),
							),
							'fields'                 => 'ids',
							'no_found_rows'          => true,
							'update_post_meta_cache' => false,
							'update_post_term_cache' => false,
						),
						$query_lang
					)
					: array(
						'post_type'              => $post_types,
						'post_status'            => 'publish',
						'posts_per_page'         => $limit,
						'ignore_sticky_posts'    => true,
						'orderby'                => 'date',
						'order'                  => 'DESC',
						'meta_query'             => array(
							array(
								'key'     => 'jdpower_featured',
								'value'   => '1',
								'compare' => '=',
							),
						),
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
					)
			);
			if ( ! empty( $q_feat->posts ) && is_array( $q_feat->posts ) ) {
				foreach ( array_map( 'intval', $q_feat->posts ) as $found_id ) {
					if ( $found_id <= 0 ) {
						continue;
					}
					$post_ids[] = $found_id;
					$exclude[]  = $found_id;
					if ( count( $post_ids ) >= $limit ) {
						break;
					}
				}
			}

			if ( count( $post_ids ) < $limit ) {
				$rest = jdpower_insights_query_latest_ids( $post_types, $exclude, $limit - count( $post_ids ), $query_lang );
				foreach ( $rest as $found_id ) {
					if ( count( $post_ids ) >= $limit ) {
						break;
					}
					$post_ids[] = $found_id;
				}
			}
		} else {
			$post_ids = jdpower_insights_query_latest_ids( $post_types, array(), $limit, $query_lang );
		}

		foreach ( $post_ids as $post_id ) {
			$built = jdpower_featured_insights_card_from_post( (int) $post_id, $copy_card_short_only );
			if ( null !== $built ) {
				$cards[] = $built;
			}
		}

		return $cards;
	}
endif;

if ( ! function_exists( 'jdpower_featured_insights_dynamic_query_cards' ) ) :
	/**
	 * Load cards for Dynamic By Type: tiered taxonomy match when terms are set, else newest first.
	 *
	 * When the current language has no matches, falls back to default-language (English) posts.
	 *
	 * @param string[]             $post_types     Allowed post type slugs.
	 * @param int                  $limit          Max cards (1–12).
	 * @param bool                 $featured_first When true, Featured posts appear first; remaining slots use latest.
	 * @param array<string, int[]> $terms_by_tax   Optional industry / segment / topic term IDs from the block.
	 * @param bool                 $copy_card_short_only When true, card copy uses only `page_card_short_description`.
	 * @return array<int, array<string, mixed>>
	 */
	function jdpower_featured_insights_dynamic_query_cards( $post_types, $limit, $featured_first, array $terms_by_tax = array(), $copy_card_short_only = false ) {
		$cards = jdpower_featured_insights_dynamic_query_cards_for_language(
			$post_types,
			$limit,
			$featured_first,
			$terms_by_tax,
			$copy_card_short_only
		);

		if ( ! empty( $cards ) || ! function_exists( 'jdpower_pll_should_fallback_to_default' ) || ! jdpower_pll_should_fallback_to_default() ) {
			return $cards;
		}

		$default_lang   = function_exists( 'jdpower_pll_default_language' ) ? jdpower_pll_default_language() : '';
		$fallback_terms = function_exists( 'jdpower_pll_map_insight_terms_by_tax_to_language' )
			? jdpower_pll_map_insight_terms_by_tax_to_language( $terms_by_tax, $default_lang )
			: $terms_by_tax;

		return jdpower_featured_insights_dynamic_query_cards_for_language(
			$post_types,
			$limit,
			$featured_first,
			$fallback_terms,
			$copy_card_short_only,
			$default_lang
		);
	}
endif;
