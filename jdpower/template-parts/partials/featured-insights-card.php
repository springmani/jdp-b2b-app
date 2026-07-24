<?php
/**
 * Featured Insights: card data + markup (shared partial).
 *
 * Loaded via require from `functions.php` and the Featured Insights block template.
 * Path: `template-parts/partials/featured-insights-card.php`.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'jdpower_featured_insights_card_from_post' ) ) :
	/**
	 * Build card data from a post (manual selection).
	 *
	 * @param int  $post_id               Post ID.
	 * @param bool $copy_card_short_only When true, copy uses only `page_card_short_description` (no excerpt/content fallback).
	 * @return array{image_id: int, heading: string, sub_heading: string, copy_html: string, url: string, segment_label?: string, post_type_label?: string, date_display?: string, date_iso?: string}|null Card `segment_label` is the configured insight pill taxonomy term.
	 */
	function jdpower_featured_insights_card_from_post( $post_id, $copy_card_short_only = false ) {
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
		if ( '' === $copy_html && ! $copy_card_short_only ) {
			$excerpt = get_post_field( 'post_excerpt', $post_id );
			if ( '' === trim( (string) $excerpt ) ) {
				$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
			}
			if ( '' !== trim( wp_strip_all_tags( (string) $excerpt ) ) ) {
				$copy_html = jdpower_sanitize_inline_html( $excerpt, true, array( 'br' ) );
			}
		}

		$segment_label = function_exists( 'jdpower_insight_card_pill_label' )
			? jdpower_insight_card_pill_label( $post_id )
			: jdpower_get_post_primary_taxonomy_term_name( $post_id, 'post_topic' );

		$post_type_label = function_exists( 'jdpower_insight_card_post_type_label' )
			? jdpower_insight_card_post_type_label( $post_id )
			: '';

		$date_fields = jdpower_post_card_date_fields( $post_id );

		return array(
			'image_id'        => $image_id,
			'heading'         => $heading,
			'sub_heading'     => $sub_heading,
			'copy_html'       => $copy_html,
			'url'             => get_permalink( $post_id ),
			'segment_label'   => $segment_label,
			'post_type_label' => $post_type_label,
			'date_display'    => $date_fields['date_display'],
			'date_iso'        => $date_fields['date_iso'],
		);
	}
endif;

if ( ! function_exists( 'jdpower_featured_insights_render_card_inner' ) ) :
	/**
	 * Echo media + body for one featured insights card.
	 *
	 * @param array $card                       Card data from theme builders (`heading` / `sub_heading` / `copy_html` already sanitized).
	 * @param bool  $compact_card_typography     Unused for card title (always h2.xxsmall); reserved for other card styles.
	 */
	function jdpower_featured_insights_render_card_inner( $card, $compact_card_typography = false ) {
		$image_id    = isset( $card['image_id'] ) ? (int) $card['image_id'] : 0;
		$heading     = isset( $card['heading'] ) ? $card['heading'] : '';
		$sub_heading = isset( $card['sub_heading'] ) ? $card['sub_heading'] : '';
		$copy_html = isset( $card['copy_html'] ) && is_string( $card['copy_html'] ) ? $card['copy_html'] : '';

		$heading     = is_string( $heading ) ? trim( $heading ) : '';
		$sub_heading = is_string( $sub_heading ) ? trim( $sub_heading ) : '';

		$segment_label = isset( $card['segment_label'] ) && is_string( $card['segment_label'] ) ? trim( $card['segment_label'] ) : '';

		$post_type_label = isset( $card['post_type_label'] ) && is_string( $card['post_type_label'] ) ? trim( $card['post_type_label'] ) : '';
		$date_display    = isset( $card['date_display'] ) && is_string( $card['date_display'] ) ? trim( $card['date_display'] ) : '';
		$date_iso        = isset( $card['date_iso'] ) && is_string( $card['date_iso'] ) ? trim( $card['date_iso'] ) : '';

		$title_size_class = 'xxsmall';
		?>
		<span class="featured-insights-block__media">
			<?php if ( $image_id > 0 ) : ?>
				<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'featured-insights-block__img', 'draggable' => 'false' ) ); ?>
			<?php else : ?>
				<span class="featured-insights-block__placeholder" aria-hidden="true">
					<?php if ( '' !== $post_type_label ) : ?>
						<span class="featured-insights-block__placeholder-label"><?php echo esc_html( $post_type_label ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
			<?php if ( '' !== $segment_label ) : ?>
				<span class="featured-insights-block__card-segment"><?php echo esc_html( $segment_label ); ?></span>
			<?php endif; ?>
		</span>
		<span class="featured-insights-block__body">
			<?php if ( '' !== $post_type_label ) : ?>
				<p class="featured-insights-block__card-type"><?php echo esc_html( $post_type_label ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $heading ) : ?>
				<h2 class="<?php echo esc_attr( $title_size_class . ' featured-insights-block__card-title' ); ?>"><?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- jdpower_sanitize_inline_html() / title when building $card ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $copy_html ) : ?>
				<div class="featured-insights-block__card-copy"><?php echo $copy_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe HTML from card builders ?></div>
			<?php endif; ?>
			<?php if ( '' !== $date_display ) : ?>
				<time class="featured-insights-block__card-date"<?php echo '' !== $date_iso ? ' datetime="' . esc_attr( $date_iso ) . '"' : ''; ?>><?php echo esc_html( $date_display ); ?></time>
			<?php endif; ?>
		</span>
		<?php
	}
endif;

if ( ! function_exists( 'jdpower_featured_insights_render_card' ) ) :
	/**
	 * Echo one featured insights card (linked from post or static from custom repeater).
	 *
	 * @param array  $card                   Card data.
	 * @param bool   $as_link                Whether to wrap in a link.
	 * @param string $url                    URL when $as_link is true.
	 * @param bool   $compact_card_typography When true (four-column grid), tighter card heading and sub styles.
	 * @param bool   $animate_cards           When true, scroll-reveal class on the card wrapper.
	 */
	function jdpower_featured_insights_render_card( $card, $as_link = true, $url = '', $compact_card_typography = false, $animate_cards = true ) {
		$url = is_string( $url ) ? $url : '';
		if ( $as_link && '' === $url ) {
			return;
		}

		$reveal_class = $animate_cards ? ' jdp-animate-view-fade-in-up' : '';

		if ( $as_link ) {
			?>
			<a class="featured-insights-block__card<?php echo esc_attr( $reveal_class ); ?>" href="<?php echo esc_url( $url ); ?>" draggable="false">
				<?php jdpower_featured_insights_render_card_inner( $card, $compact_card_typography ); ?>
			</a>
			<?php
		} else {
			?>
			<div class="featured-insights-block__card featured-insights-block__card--static<?php echo esc_attr( $reveal_class ); ?>">
				<?php jdpower_featured_insights_render_card_inner( $card, $compact_card_typography ); ?>
			</div>
			<?php
		}
	}
endif;

if ( ! function_exists( 'jdpower_featured_insights_columns_render_card' ) ) :
	/**
	 * Featured Insights Columns block: card without image; date below heading; decorative Read More at bottom.
	 *
	 * @param array  $card    Card data (same shape as `jdpower_featured_insights_render_card`).
	 * @param bool   $as_link Wrap in anchor when true (dynamic/manual).
	 * @param string $url     Destination URL when `$as_link` is true.
	 * @param bool   $animate_cards When true, scroll-reveal class on the card wrapper.
	 */
	function jdpower_featured_insights_columns_render_card( $card, $as_link = true, $url = '', $animate_cards = true ) {
		$url = is_string( $url ) ? $url : '';
		if ( $as_link && '' === $url ) {
			return;
		}

		$reveal_class = $animate_cards ? ' jdp-animate-view-fade-in-up' : '';

		$heading = isset( $card['heading'] ) ? $card['heading'] : '';
		$heading = is_string( $heading ) ? trim( $heading ) : '';

		$post_type_label = isset( $card['post_type_label'] ) && is_string( $card['post_type_label'] ) ? trim( $card['post_type_label'] ) : '';
		$copy_html       = isset( $card['copy_html'] ) && is_string( $card['copy_html'] ) ? $card['copy_html'] : '';
		$date_display    = isset( $card['date_display'] ) && is_string( $card['date_display'] ) ? trim( $card['date_display'] ) : '';
		$date_iso        = isset( $card['date_iso'] ) && is_string( $card['date_iso'] ) ? trim( $card['date_iso'] ) : '';

		$read_more = function_exists( 'jdpower_pll__' )
			? jdpower_pll__( 'Read More' )
			: __( 'Read More', 'jdpower' );

		if ( $as_link ) :
			?>
			<a class="featured-insights-block__card featured-insights-block__card--insights-columns<?php echo esc_attr( $reveal_class ); ?>" href="<?php echo esc_url( $url ); ?>" draggable="false">
				<span class="featured-insights-block__body featured-insights-block__body--insights-columns">
					<?php if ( '' !== $post_type_label ) : ?>
						<p class="featured-insights-block__card-type"><?php echo esc_html( $post_type_label ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $heading ) : ?>
						<h2 class="xxsmall featured-insights-block__card-title"><?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized when building $card ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $date_display ) : ?>
						<time class="featured-insights-block__card-date"<?php echo '' !== $date_iso ? ' datetime="' . esc_attr( $date_iso ) . '"' : ''; ?>><?php echo esc_html( $date_display ); ?></time>
					<?php endif; ?>
					<?php if ( '' !== $copy_html ) : ?>
						<div class="featured-insights-block__card-copy"><?php echo $copy_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>
					<span class="arrow-link featured-insights-columns-block__card-read-more"><?php echo esc_html( $read_more ); ?></span>
				</span>
			</a>
			<?php
		else :
			?>
			<div class="featured-insights-block__card featured-insights-block__card--insights-columns featured-insights-block__card--static<?php echo esc_attr( $reveal_class ); ?>">
				<span class="featured-insights-block__body featured-insights-block__body--insights-columns">
					<?php if ( '' !== $post_type_label ) : ?>
						<p class="featured-insights-block__card-type"><?php echo esc_html( $post_type_label ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $heading ) : ?>
						<h2 class="xxsmall featured-insights-block__card-title"><?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $date_display ) : ?>
						<time class="featured-insights-block__card-date"<?php echo '' !== $date_iso ? ' datetime="' . esc_attr( $date_iso ) . '"' : ''; ?>><?php echo esc_html( $date_display ); ?></time>
					<?php endif; ?>
					<?php if ( '' !== $copy_html ) : ?>
						<div class="featured-insights-block__card-copy"><?php echo $copy_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>
					<span class="arrow-link featured-insights-columns-block__card-read-more"><?php echo esc_html( $read_more ); ?></span>
				</span>
			</div>
			<?php
		endif;
	}
endif;

if ( ! function_exists( 'jdpower_mega_menu_featured_insights_allowed_post_types' ) ) :
	/**
	 * Insight post types available for mega menu Featured Insights.
	 *
	 * @return string[]
	 */
	function jdpower_mega_menu_featured_insights_allowed_post_types() {
		if ( function_exists( 'jdpower_post_filters_insight_post_type_slugs' ) ) {
			return jdpower_post_filters_insight_post_type_slugs();
		}
		return array( 'post', 'press_release', 'podcast', 'event_webinar', 'resource' );
	}
endif;

if ( ! function_exists( 'jdpower_mega_menu_featured_insights_normalize_post_types' ) ) :
	/**
	 * Sanitize selected post type slugs against allowed insight types.
	 *
	 * @param mixed $value Raw ACF checkbox value.
	 * @return string[]
	 */
	function jdpower_mega_menu_featured_insights_normalize_post_types( $value ) {
		$allowed = jdpower_mega_menu_featured_insights_allowed_post_types();
		$raw     = array();
		if ( is_array( $value ) ) {
			$raw = $value;
		} elseif ( is_string( $value ) && '' !== $value ) {
			$raw = array( $value );
		}
		$out = array();
		foreach ( $raw as $slug ) {
			$slug = sanitize_key( (string) $slug );
			if ( '' !== $slug && in_array( $slug, $allowed, true ) ) {
				$out[] = $slug;
			}
		}
		return array_values( array_unique( $out ) );
	}
endif;

if ( ! function_exists( 'jdpower_mega_menu_featured_insights_cards_for_language' ) ) :
	/**
	 * Mega menu only: featured-first insight cards for an explicit Polylang language.
	 *
	 * @param string[]    $post_types Allowed post type slugs.
	 * @param int         $limit      Max cards (1–12).
	 * @param string|null $query_lang Language slug, or null for current frontend language.
	 * @return array<int, array<string, mixed>>
	 */
	function jdpower_mega_menu_featured_insights_cards_for_language( $post_types, $limit, $query_lang = null ) {
		$cards      = array();
		$post_types = jdpower_mega_menu_featured_insights_normalize_post_types( $post_types );
		$limit      = max( 1, min( 12, (int) $limit ) );
		if ( empty( $post_types ) || ! function_exists( 'jdpower_featured_insights_card_from_post' ) ) {
			return $cards;
		}

		$post_ids = array();
		$exclude  = array();

		$q_feat = new WP_Query(
			jdpower_pll_merge_query_lang(
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
			$q_latest = new WP_Query(
				jdpower_pll_merge_query_lang(
					array(
						'post_type'              => $post_types,
						'post_status'            => 'publish',
						'posts_per_page'         => $limit - count( $post_ids ),
						'post__not_in'           => $exclude,
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
			);

			if ( ! empty( $q_latest->posts ) && is_array( $q_latest->posts ) ) {
				foreach ( array_map( 'intval', $q_latest->posts ) as $found_id ) {
					if ( $found_id <= 0 || count( $post_ids ) >= $limit ) {
						continue;
					}
					$post_ids[] = $found_id;
				}
			}
		}

		foreach ( $post_ids as $post_id ) {
			$built = jdpower_featured_insights_card_from_post( (int) $post_id, true );
			if ( null !== $built ) {
				$cards[] = $built;
			}
		}

		return $cards;
	}
endif;

if ( ! function_exists( 'jdpower_mega_menu_featured_insights_featured_only_cards' ) ) :
	/**
	 * Dynamic mega menu cards: Featured posts first, then latest from selected types.
	 *
	 * When the current language has no matches, falls back to default-language (English) posts.
	 *
	 * @param string[] $post_types Allowed post type slugs.
	 * @param int      $limit      Max cards (1–12).
	 * @return array<int, array<string, mixed>>
	 */
	function jdpower_mega_menu_featured_insights_featured_only_cards( $post_types, $limit ) {
		$post_types = jdpower_mega_menu_featured_insights_normalize_post_types( $post_types );
		if ( empty( $post_types ) ) {
			return array();
		}

		$cards = jdpower_mega_menu_featured_insights_cards_for_language( $post_types, $limit );

		if ( ! empty( $cards ) || ! function_exists( 'jdpower_pll_should_fallback_to_default' ) || ! jdpower_pll_should_fallback_to_default() ) {
			return $cards;
		}

		return jdpower_mega_menu_featured_insights_cards_for_language(
			$post_types,
			$limit,
			jdpower_pll_default_language()
		);
	}
endif;

if ( ! function_exists( 'jdpower_mega_menu_featured_insights_cards_from_ids' ) ) :
	/**
	 * Build cards from manually selected post IDs (preserves order).
	 *
	 * @param mixed $post_ids Relationship / ID list.
	 * @return array<int, array<string, mixed>>
	 */
	function jdpower_mega_menu_featured_insights_cards_from_ids( $post_ids ) {
		$cards   = array();
		$allowed = jdpower_mega_menu_featured_insights_allowed_post_types();
		if ( ! is_array( $post_ids ) ) {
			return $cards;
		}
		foreach ( $post_ids as $entry ) {
			if ( is_object( $entry ) && isset( $entry->ID ) ) {
				$post_id = (int) $entry->ID;
			} else {
				$post_id = (int) $entry;
			}
			if ( $post_id <= 0 || ! in_array( get_post_type( $post_id ), $allowed, true ) || 'publish' !== get_post_status( $post_id ) ) {
				continue;
			}
			$built = jdpower_featured_insights_card_from_post( $post_id, true );
			if ( null !== $built ) {
				$cards[] = $built;
			}
		}
		return $cards;
	}
endif;
