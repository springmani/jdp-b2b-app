<?php
/**
 * Block Name: Featured Insights
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

if ( ! function_exists( 'jdpower_featured_insights_carousel_nav' ) ) :
	/**
	 * Echo prev/next controls for the featured insights carousel track.
	 *
	 * @param string $carousel_id DOM id of the carousel element (aria-controls target).
	 */
	function jdpower_featured_insights_carousel_nav( $carousel_id ) {
		$carousel_id = is_string( $carousel_id ) ? $carousel_id : '';
		?>
		<div class="featured-insights-block__carousel-nav" role="group" aria-label="<?php esc_attr_e( 'Featured insights carousel', 'jdpower' ); ?>">
			<button type="button" class="featured-insights-block__nav-btn featured-insights-block__nav-btn--prev" aria-controls="<?php echo esc_attr( $carousel_id ); ?>" aria-label="<?php esc_attr_e( 'Previous insights', 'jdpower' ); ?>">
				<span class="featured-insights-block__nav-icon" aria-hidden="true">
					<svg class="featured-insights-block__chevron" width="20" height="34" viewBox="0 0 20 34" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
						<path d="M16.7366 2.50008L2.5 16.7364L16.7366 30.9727" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			</button>
			<button type="button" class="featured-insights-block__nav-btn featured-insights-block__nav-btn--next" aria-controls="<?php echo esc_attr( $carousel_id ); ?>" aria-label="<?php esc_attr_e( 'Next insights', 'jdpower' ); ?>">
				<span class="featured-insights-block__nav-icon" aria-hidden="true">
					<svg class="featured-insights-block__chevron" width="20" height="34" viewBox="0 0 20 34" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
						<path d="M2.49976 30.9726L16.7363 16.7363L2.49976 2.5" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			</button>
		</div>
		<?php
	}
endif;

$classes = 'featured-insights-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

// ACF block fields: pass block ID so taxonomy and conditional fields resolve on front end and in editor.
$acf_block_id = false;
if ( isset( $block['id'] ) && '' !== $block['id'] && 0 !== $block['id'] ) {
	$acf_block_id = $block['id'];
}
$acf_get      = static function ( $name ) use ( $acf_block_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	return $acf_block_id ? get_field( $name, $acf_block_id ) : get_field( $name );
};

$padding_top     = $acf_get( 'featured_insights_padding_top' );
$padding_bottom  = $acf_get( 'featured_insights_padding_bottom' );
$background_tone = $acf_get( 'featured_insights_background_tone' );
$animate_cards   = function_exists( 'jdpower_acf_flag_on' ) ? jdpower_acf_flag_on( $acf_get( 'featured_insights_animate_cards' ) ) : true;
$source          = $acf_get( 'featured_insights_source' );

if ( empty( $source ) || ! in_array( $source, array( 'dynamic_by_type', 'manual_selection', 'custom' ), true ) ) {
	$source = 'manual_selection';
}

$dynamic_limit = 3;
if ( 'dynamic_by_type' === $source ) {
	$raw_limit     = $acf_get( 'featured_insights_count' );
	$dynamic_limit = is_numeric( $raw_limit ) ? (int) $raw_limit : 3;
	$dynamic_limit = min( 12, max( 1, $dynamic_limit ) );
}

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

$classes .= ' featured-insights-block--source-' . $source;

$intro_column_ratio = $acf_get( 'featured_insights_intro_column_ratio' );
$valid_intro_ratios = array( '50_50', '60_40', '70_30', '80_20' );
if ( empty( $intro_column_ratio ) || ! in_array( $intro_column_ratio, $valid_intro_ratios, true ) ) {
	$intro_column_ratio = '50_50';
}
$classes .= ' featured-insights-block--intro-ratio-' . str_replace( '_', '-', $intro_column_ratio );

$featured_insights_intro_col_classes = array(
	'50_50' => array( 'heading' => 'col-lg-6', 'copy' => 'col-lg-6' ),
	'60_40' => array( 'heading' => 'col-lg-7', 'copy' => 'col-lg-5' ),
	'70_30' => array( 'heading' => 'col-lg-8', 'copy' => 'col-lg-4' ),
	'80_20' => array( 'heading' => 'col-lg-10', 'copy' => 'col-lg-2' ),
);
$intro_split_cols = $featured_insights_intro_col_classes[ $intro_column_ratio ];

$pre_heading = $acf_get( 'featured_insights_pre_heading' );
$heading     = $acf_get( 'featured_insights_heading' );
$heading_size = $acf_get( 'featured_insights_heading_size' );
$copy        = $acf_get( 'featured_insights_copy' );
$cta_link    = $acf_get( 'featured_insights_cta' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'default';
}

$heading_classes = array( 'featured-insights-block__heading' );
if ( 'default' !== $heading_size ) {
	$heading_classes[] = $heading_size;
}
$heading_class_attr = esc_attr( implode( ' ', $heading_classes ) );

$cta_url    = is_array( $cta_link ) ? ( $cta_link['url'] ?? '' ) : '';
$cta_title  = is_array( $cta_link ) ? ( $cta_link['title'] ?? '' ) : '';
$cta_target = is_array( $cta_link ) ? ( $cta_link['target'] ?? '' ) : '';

$cta_target_attr = '';
$cta_rel_attr    = '';
if ( '_blank' === $cta_target ) {
	$cta_target_attr = ' target="_blank"';
	$cta_rel_attr    = ' rel="noopener noreferrer"';
}

$has_cta = is_string( $cta_url ) && '' !== $cta_url && is_string( $cta_title ) && '' !== trim( $cta_title );

$has_pre     = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_heading = is_string( $heading ) && '' !== trim( $heading );
$has_copy    = is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) );

$cards = array();

if ( 'dynamic_by_type' === $source ) {
	$type_raw         = $acf_get( 'featured_insights_type' );
	$post_types       = jdpower_featured_insights_acf_insight_post_type_slugs( $type_raw );
	$include_featured = ! empty( $acf_get( 'featured_insights_include_featured' ) );
	$terms_by_tax     = function_exists( 'jdpower_insights_acf_taxonomy_term_map' )
		? jdpower_insights_acf_taxonomy_term_map(
			$acf_get( 'featured_insights_industry' ),
			$acf_get( 'featured_insights_segment' ),
			$acf_get( 'featured_insights_topic' )
		)
		: array();
	if ( ! empty( $post_types ) ) {
		$cards = jdpower_featured_insights_dynamic_query_cards( $post_types, $dynamic_limit, $include_featured, $terms_by_tax );
	}
} elseif ( 'manual_selection' === $source ) {
	$selected = $acf_get( 'featured_insights_selected_posts' );
	if ( ! is_array( $selected ) ) {
		$selected = array();
	}
	$selected = array_map( 'intval', $selected );
	$selected = array_values( array_filter( $selected ) );

	foreach ( $selected as $post_id ) {
		$built = jdpower_featured_insights_card_from_post( $post_id );
		if ( null !== $built ) {
			$cards[] = $built;
		}
	}
} elseif ( 'custom' === $source ) {
	$fi_repeater_args = $acf_block_id
		? array( 'featured_insights_items', $acf_block_id )
		: array( 'featured_insights_items' );
	if ( call_user_func_array( 'have_rows', $fi_repeater_args ) ) {
		while ( call_user_func_array( 'have_rows', $fi_repeater_args ) ) {
			the_row();
			$image   = get_sub_field( 'featured_insights_card_image' );
			$heading = get_sub_field( 'featured_insights_card_heading' );
			$sub     = get_sub_field( 'featured_insights_card_sub_heading' );
			$copy_f  = get_sub_field( 'featured_insights_card_copy' );

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
}

$has_cards = ! empty( $cards );
$has_intro = $has_pre || $has_heading || $has_copy;

if ( ! $has_intro && ! $has_cards && ! $has_cta ) {
	return;
}

$intro_split = $has_heading && $has_copy;

$column_size_raw = $acf_get( 'featured_insights_column_size' );
$grid_cols       = ( 4 === (int) $column_size_raw ) ? 4 : 3;
$compact_card_typography = ( 4 === $grid_cols );

$card_count  = count( $cards );
$is_carousel = $has_cards && $card_count > 4;
if ( $has_cards ) {
	$classes .= $is_carousel ? ' featured-insights-block--layout-carousel' : ' featured-insights-block--layout-grid';
}

$carousel_id = 'featured-insights-carousel-' . ( isset( $block['id'] ) ? sanitize_title( (string) $block['id'] ) : wp_unique_id( 'fi-' ) );
$carousel_nav_rendered = false;

?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_cards && $is_carousel && $has_pre && ! $has_heading && ! $has_copy ) : ?>
			<div class="featured-insights-block__carousel-pre-nav-row">
				<p class="featured-insights-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
				<?php
				jdpower_featured_insights_carousel_nav( $carousel_id );
				$carousel_nav_rendered = true;
				?>
			</div>
		<?php elseif ( $has_cards && $is_carousel && $has_pre ) : ?>
			<p class="featured-insights-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
		<?php endif; ?>

		<?php if ( $has_intro ) : ?>
			<?php if ( ! $is_carousel && $has_pre ) : ?>
				<p class="featured-insights-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
			<?php endif; ?>

			<?php if ( $intro_split ) : ?>
				<?php if ( $is_carousel ) : ?>
					<div class="row featured-insights-block__carousel-intro-row align-items-stretch">
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['heading'] ); ?> featured-insights-block__carousel-intro-main">
							<div class="featured-insights-block__intro featured-insights-block__intro--stacked">
								<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
								<div class="featured-insights-block__copy">
									<?php echo wp_kses_post( $copy ); ?>
								</div>
							</div>
						</div>
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['copy'] ); ?> featured-insights-block__carousel-intro-aside">
							<?php
							jdpower_featured_insights_carousel_nav( $carousel_id );
							$carousel_nav_rendered = true;
							?>
						</div>
					</div>
				<?php else : ?>
					<div class="row featured-insights-block__intro-row align-items-start align-items-lg-end">
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['heading'] ); ?> featured-insights-block__intro-col featured-insights-block__intro-col--heading">
							<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
						</div>
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['copy'] ); ?> featured-insights-block__copy featured-insights-block__copy--aside">
							<?php echo wp_kses_post( $copy ); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php elseif ( $has_heading || $has_copy ) : ?>
				<?php if ( $is_carousel ) : ?>
					<?php
					$fi_partial_carousel_row_class = 'featured-insights-block__carousel-intro-row';
					$fi_partial_carousel_row_class .= $has_pre ? ' align-items-stretch' : ' featured-insights-block__carousel-intro-row--partial align-items-start';
					?>
					<div class="row <?php echo esc_attr( $fi_partial_carousel_row_class ); ?>">
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['heading'] ); ?> featured-insights-block__carousel-intro-main">
							<div class="featured-insights-block__intro featured-insights-block__intro--stacked">
								<?php if ( $has_heading ) : ?>
									<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
								<?php endif; ?>
								<?php if ( $has_copy ) : ?>
									<div class="featured-insights-block__copy">
										<?php echo wp_kses_post( $copy ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['copy'] ); ?> featured-insights-block__carousel-intro-aside">
							<?php
							jdpower_featured_insights_carousel_nav( $carousel_id );
							$carousel_nav_rendered = true;
							?>
						</div>
					</div>
				<?php else : ?>
					<div class="featured-insights-block__intro featured-insights-block__intro--stacked">
						<?php if ( $has_heading ) : ?>
							<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
						<?php endif; ?>
						<?php if ( $has_copy ) : ?>
							<div class="featured-insights-block__copy">
								<?php echo wp_kses_post( $copy ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $has_cards && ! $is_carousel ) : ?>
			<?php
			$fi_grid_classes = 'featured-insights-block__grid featured-insights-block__grid--cols-' . (int) $grid_cols;
			if ( 1 === $card_count ) {
				$fi_grid_classes .= ' featured-insights-block__grid--single';
			}
			?>
			<div class="<?php echo esc_attr( $fi_grid_classes ); ?>">
				<?php
				foreach ( $cards as $card ) {
					if ( 'manual_selection' === $source || 'dynamic_by_type' === $source ) {
						jdpower_featured_insights_render_card( $card, true, isset( $card['url'] ) ? $card['url'] : '', $compact_card_typography, $animate_cards );
					} else {
						jdpower_featured_insights_render_card( $card, false, '', $compact_card_typography, $animate_cards );
					}
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( $has_cta && ! $is_carousel ) : ?>
			<div class="featured-insights-block__cta">
				<a class="arrow-link featured-insights-block__cta-link" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $cta_target_attr . $cta_rel_attr; ?>>
					<?php echo esc_html( trim( $cta_title ) ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php if ( $has_cards && $is_carousel && ! $carousel_nav_rendered ) : ?>
			<div class="featured-insights-block__carousel-nav-row">
				<?php jdpower_featured_insights_carousel_nav( $carousel_id ); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $has_cards && $is_carousel ) : ?>
		<div class="featured-insights-block__carousel-shell">
			<div id="<?php echo esc_attr( $carousel_id ); ?>" class="featured-insights-block__carousel" data-jdpower-carousel tabindex="0">
				<?php
				foreach ( $cards as $card ) {
					if ( 'manual_selection' === $source || 'dynamic_by_type' === $source ) {
						jdpower_featured_insights_render_card( $card, true, isset( $card['url'] ) ? $card['url'] : '', $compact_card_typography, $animate_cards );
					} else {
						jdpower_featured_insights_render_card( $card, false, '', $compact_card_typography, $animate_cards );
					}
				}
				?>
			</div>
		</div>
		<?php if ( $has_cta ) : ?>
			<div class="container">
				<div class="featured-insights-block__cta">
					<a class="arrow-link featured-insights-block__cta-link" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $cta_target_attr . $cta_rel_attr; ?>>
						<?php echo esc_html( trim( $cta_title ) ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</section>
