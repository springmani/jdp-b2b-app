<?php
/**
 * Block Name: Featured Insights Columns
 *
 * Repeater rows = insight columns on the page. “Columns per row” (ACF) applies as a modifier on
 * `.featured-insights-columns-block__row` (CSS Grid: 2/3/4 columns at lg+). Repeater items are
 * plain grid cells—no Bootstrap `col-*` on each item. Cards render directly in each cell (no
 * inner `featured-insights-block__grid` / `cols-*` wrapper).
 *
 * @package jdpower
 */

require_once get_template_directory() . '/template-parts/partials/featured-insights-card.php';

if ( ! function_exists( 'jdpower_featured_insights_acf_insight_post_type_slugs' ) ) :
	/**
	 * Normalize ACF post type field (checkbox or single slug string) to allowed Insight Center slugs.
	 *
	 * @param mixed $value Raw value from get_field.
	 * @return string[]
	 */
	function jdpower_featured_insights_acf_insight_post_type_slugs( $value ) {
		$allowed = function_exists( 'jdpower_post_filters_insight_post_type_slugs' )
			? jdpower_post_filters_insight_post_type_slugs()
			: array( 'post' );

		$raw = array();
		if ( null === $value || false === $value || '' === $value ) {
			$raw = array();
		} elseif ( is_string( $value ) ) {
			$raw[] = $value;
		} elseif ( is_array( $value ) ) {
			$raw = $value;
		}

		$out = array();
		foreach ( $raw as $item ) {
			if ( ! is_string( $item ) || '' === $item ) {
				continue;
			}
			$slug = sanitize_key( $item );
			if ( '' === $slug || ! in_array( $slug, $allowed, true ) ) {
				continue;
			}
			if ( ! in_array( $slug, $out, true ) ) {
				$out[] = $slug;
			}
		}

		return $out;
	}
endif;

if ( ! function_exists( 'jdpower_featured_insights_columns_cards_from_row' ) ) {
	/**
	 * Build card list for one repeater row (one card per column).
	 *
	 * @param array<string, mixed> $row Repeater row from get_field.
	 * @return array{0: string, 1: array<int, array<string, mixed>>} Tuple: source slug, card list (0–1 cards).
	 */
	function jdpower_featured_insights_columns_cards_from_row( $row ) {
		$cards  = array();
		$source = isset( $row['featured_insights_columns_item_source'] ) ? $row['featured_insights_columns_item_source'] : '';
		if ( empty( $source ) || ! in_array( $source, array( 'dynamic_by_type', 'manual_selection', 'custom' ), true ) ) {
			$source = 'manual_selection';
		}

		if ( 'dynamic_by_type' === $source ) {
			$type_raw         = isset( $row['featured_insights_columns_item_type'] ) ? $row['featured_insights_columns_item_type'] : null;
			$post_types       = jdpower_featured_insights_acf_insight_post_type_slugs( $type_raw );
			$include_featured = ! empty( $row['featured_insights_columns_item_include_featured'] );
			$terms_by_tax     = function_exists( 'jdpower_insights_acf_taxonomy_term_map' )
				? jdpower_insights_acf_taxonomy_term_map(
					isset( $row['featured_insights_columns_item_industry'] ) ? $row['featured_insights_columns_item_industry'] : null,
					isset( $row['featured_insights_columns_item_segment'] ) ? $row['featured_insights_columns_item_segment'] : null,
					isset( $row['featured_insights_columns_item_topic'] ) ? $row['featured_insights_columns_item_topic'] : null
				)
				: array();
			if ( ! empty( $post_types ) ) {
				$cards = jdpower_featured_insights_dynamic_query_cards( $post_types, 1, $include_featured, $terms_by_tax );
			}
		} elseif ( 'manual_selection' === $source ) {
			$selected = isset( $row['featured_insights_columns_item_selected_posts'] ) ? $row['featured_insights_columns_item_selected_posts'] : array();
			if ( ! is_array( $selected ) ) {
				$selected = array();
			}
			$selected = array_map( 'intval', $selected );
			$selected = array_values( array_filter( $selected ) );
			$selected = array_slice( $selected, 0, 1 );
			foreach ( $selected as $post_id ) {
				$built = jdpower_featured_insights_card_from_post( $post_id );
				if ( null !== $built ) {
					$cards[] = $built;
				}
			}
		} elseif ( 'custom' === $source ) {
			$custom_rows = isset( $row['featured_insights_columns_item_cards'] ) ? $row['featured_insights_columns_item_cards'] : array();
			if ( ! is_array( $custom_rows ) ) {
				$custom_rows = array();
			}
			foreach ( array_slice( $custom_rows, 0, 1 ) as $card_row ) {
				$image   = isset( $card_row['featured_insights_columns_item_card_image'] ) ? $card_row['featured_insights_columns_item_card_image'] : null;
				$heading = isset( $card_row['featured_insights_columns_item_card_heading'] ) ? $card_row['featured_insights_columns_item_card_heading'] : '';
				$sub     = isset( $card_row['featured_insights_columns_item_card_sub_heading'] ) ? $card_row['featured_insights_columns_item_card_sub_heading'] : '';
				$copy_f  = isset( $card_row['featured_insights_columns_item_card_copy'] ) ? $card_row['featured_insights_columns_item_card_copy'] : '';

				$image_id  = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
				$heading   = is_string( $heading ) ? trim( $heading ) : '';
				$sub       = is_string( $sub ) ? trim( $sub ) : '';
				$copy_html = is_string( $copy_f ) ? wp_kses_post( $copy_f ) : '';

				if ( '' === $heading && '' === $sub && '' === trim( wp_strip_all_tags( $copy_html ) ) && $image_id <= 0 ) {
					continue;
				}

				$cards[] = array(
					'image_id'    => $image_id,
					'heading'     => $heading,
					'sub_heading' => $sub,
					'copy_html'   => $copy_html,
					'url'         => '',
				);
			}
		}

		return array( $source, $cards );
	}
}

$classes = 'featured-insights-block featured-insights-columns-block featured-insights-block--insights-columns';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$acf_block_id = false;
if ( isset( $block['id'] ) && '' !== $block['id'] && 0 !== $block['id'] ) {
	$acf_block_id = $block['id'];
}
$acf_get = static function ( $name ) use ( $acf_block_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	return $acf_block_id ? get_field( $name, $acf_block_id ) : get_field( $name );
};

$padding_top     = $acf_get( 'featured_insights_columns_padding_top' );
$padding_bottom  = $acf_get( 'featured_insights_columns_padding_bottom' );
$background_tone = $acf_get( 'featured_insights_columns_background_tone' );
$animate_cards   = function_exists( 'jdpower_acf_flag_on' ) ? jdpower_acf_flag_on( $acf_get( 'featured_insights_columns_animate_cards' ) ) : true;

$valid_padding = array( 'default', 'small', 'medium', 'large', 'none' );
if ( ! empty( $padding_top ) && in_array( $padding_top, $valid_padding, true ) ) {
	$classes .= ' pt-' . $padding_top;
} else {
	$classes .= ' pt-default';
}

if ( ! empty( $padding_bottom ) && in_array( $padding_bottom, $valid_padding, true ) ) {
	$classes .= ' pb-' . $padding_bottom;
} else {
	$classes .= ' pb-default';
}

if ( empty( $background_tone ) || ! in_array( $background_tone, array( 'dark', 'medium', 'light' ), true ) ) {
	$background_tone = 'light';
}
$classes .= ' bg-' . $background_tone;

if ( 'dark' === $background_tone ) {
	$classes .= ' featured-insights-block--tone-dark';
} else {
	$classes .= ' featured-insights-block--tone-light';
}

// Outer layout: modifier class on `.featured-insights-columns-block__row` only (see SCSS grid).
$columns_per_row = (int) $acf_get( 'featured_insights_columns_grid_size' );
if ( ! in_array( $columns_per_row, array( 2, 3, 4 ), true ) ) {
	$columns_per_row = 3;
}
$row_cols_modifier = 'featured-insights-columns-block__row--cols-' . $columns_per_row;

$items = $acf_get( 'featured_insights_columns_items' );
if ( ! is_array( $items ) ) {
	$items = array();
}

$columns_out = array();
foreach ( $items as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	list( $source, $cards ) = jdpower_featured_insights_columns_cards_from_row( $row );

	$pre_raw   = isset( $row['featured_insights_columns_item_pre_heading'] ) ? $row['featured_insights_columns_item_pre_heading'] : '';
	$pre_text  = is_string( $pre_raw ) ? trim( $pre_raw ) : '';
	$has_pre   = '' !== $pre_text;
	$has_cards = ! empty( $cards );

	if ( ! $has_pre && ! $has_cards ) {
		continue;
	}

	$columns_out[] = array(
		'source'    => $source,
		'cards'     => $cards,
		'pre_text'  => $pre_text,
		'has_pre'   => $has_pre,
		'has_cards' => $has_cards,
	);
}

if ( empty( $columns_out ) ) {
	return;
}

?>


<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<div class="<?php echo esc_attr( 'gx-4 gy-5 featured-insights-columns-block__row ' . $row_cols_modifier ); ?>">
			<?php foreach ( $columns_out as $col ) : ?>
				<div class="featured-insights-columns-block__column">
					<?php if ( $col['has_pre'] ) : ?>
						<p class="featured-insights-block__pre preheading"><?php echo esc_html( $col['pre_text'] ); ?></p>
					<?php endif; ?>

					<?php if ( $col['has_cards'] ) : ?>
						<div class="featured-insights-columns-block__card-slot">
							<?php
							foreach ( $col['cards'] as $card ) {
								if ( 'manual_selection' === $col['source'] || 'dynamic_by_type' === $col['source'] ) {
									jdpower_featured_insights_columns_render_card( $card, true, isset( $card['url'] ) ? $card['url'] : '', $animate_cards );
								} else {
									jdpower_featured_insights_columns_render_card( $card, false, '', $animate_cards );
								}
							}
							?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
