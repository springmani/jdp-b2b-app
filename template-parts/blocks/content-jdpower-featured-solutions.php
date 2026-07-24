<?php
/**
 * Block Name: Featured Solutions
 *
 * @package jdpower
 */

if ( ! function_exists( 'jdpower_featured_pages_card_from_page' ) ) {
	/**
	 * Build card data from a post (page, segment, or solution) using Card Details fields when set.
	 * Defined here so this block works when Featured Pages is not on the page.
	 *
	 * @param int $post_id Post ID.
	 * @return array{image_id: int, heading: string, sub_heading: string, copy_html: string, url: string, explore_target: string}|null
	 */
	function jdpower_featured_pages_card_from_page( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 || 'publish' !== get_post_status( $post_id ) ) {
			return null;
		}

		$image_id = (int) get_post_thumbnail_id( $post_id );

		$post_type = get_post_type( $post_id );
		if ( 'segments' === $post_type ) {
			$f_heading = 'segment_card_heading_override';
			$f_sub     = 'segment_card_sub_heading';
			$f_short   = 'segment_card_short_description';
			$f_cta     = 'segment_card_cta_text_override';
		} elseif ( 'solution' === $post_type ) {
			$f_heading = 'solution_card_heading_override';
			$f_sub     = 'solution_card_sub_heading';
			$f_short   = 'solution_card_short_description';
			$f_cta     = 'solution_card_cta_text_override';
		} else {
			$f_heading = 'page_card_heading_override';
			$f_sub     = 'page_card_sub_heading';
			$f_short   = 'page_card_short_description';
			$f_cta     = 'page_card_cta_text_override';
		}

		$heading_override = function_exists( 'get_field' ) ? get_field( $f_heading, $post_id ) : '';
		$heading          = ( is_string( $heading_override ) && '' !== trim( $heading_override ) )
			? trim( $heading_override )
			: get_the_title( $post_id );

		$sub_raw     = function_exists( 'get_field' ) ? get_field( $f_sub, $post_id ) : '';
		$sub_heading = is_string( $sub_raw ) ? trim( $sub_raw ) : '';

		$card_short = function_exists( 'get_field' ) ? get_field( $f_short, $post_id ) : '';
		if ( is_string( $card_short ) && '' !== trim( $card_short ) ) {
			$copy_html = wp_kses_post( wpautop( $card_short ) );
		} else {
			$excerpt = get_post_field( 'post_excerpt', $post_id );
			if ( '' === trim( (string) $excerpt ) ) {
				$excerpt = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 40, '…' );
			}
			$copy_html = wp_kses_post( wpautop( $excerpt ) );
		}

		$cta_override_raw = function_exists( 'get_field' ) ? get_field( $f_cta, $post_id ) : '';
		$explore_target   = ( is_string( $cta_override_raw ) && '' !== trim( $cta_override_raw ) )
			? trim( $cta_override_raw )
			: get_the_title( $post_id );

		return array(
			'image_id'       => $image_id,
			'heading'        => $heading,
			'sub_heading'    => $sub_heading,
			'copy_html'      => $copy_html,
			'url'            => get_permalink( $post_id ),
			'explore_target' => $explore_target,
		);
	}
}

$classes = 'featured-solutions-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'featured_solutions_padding_top' );
$padding_bottom  = get_field( 'featured_solutions_padding_bottom' );
$background_tone = get_field( 'featured_solutions_background_tone' );
$animate_cards   = function_exists( 'jdpower_acf_flag_on' ) ? jdpower_acf_flag_on( get_field( 'featured_solutions_animate_cards' ) ) : true;
$source          = get_field( 'featured_solutions_source' );

if ( empty( $source ) || ! in_array( $source, array( 'manual_selection', 'segment_solutions', 'custom' ), true ) ) {
	$source = 'manual_selection';
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
	$classes .= ' featured-solutions-block--tone-dark';
} else {
	$classes .= ' featured-solutions-block--tone-light';
}

$classes .= ' featured-solutions-block--source-' . $source;

$pre_heading = get_field( 'featured_solutions_pre_heading' );
$heading     = get_field( 'featured_solutions_heading' );
$heading_size = get_field( 'featured_solutions_heading_size' );
$copy        = get_field( 'featured_solutions_copy' );
$cta_link    = get_field( 'featured_solutions_cta' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'default';
}

$heading_classes = array( 'featured-solutions-block__heading' );
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

$show_region_selector = get_field( 'featured_solutions_show_region_selector' );
if ( null === $show_region_selector || '' === $show_region_selector ) {
	$show_region_selector = true;
}
$show_region_selector = (bool) (int) $show_region_selector;

$region_terms = get_terms(
	array(
		'taxonomy'   => 'product_region',
		'hide_empty' => false,
	)
);
if ( is_wp_error( $region_terms ) ) {
	$region_terms = array();
}

$region_id_to_slug = array();
foreach ( $region_terms as $fs_region_term ) {
	if ( $fs_region_term instanceof WP_Term ) {
		$region_id_to_slug[ (int) $fs_region_term->term_id ] = $fs_region_term->slug;
	}
}

$fs_default_region_slug  = jdpower_product_region_visitor_slug();
$fs_default_region_label = '';
if ( '' !== $fs_default_region_slug ) {
	$fs_visitor_term = get_term_by( 'slug', $fs_default_region_slug, 'product_region' );
	if ( $fs_visitor_term instanceof WP_Term && ! is_wp_error( $fs_visitor_term ) ) {
		$fs_default_region_label = $fs_visitor_term->name;
	}
}

$show_region_dropdown = $show_region_selector && ! empty( $region_terms );
$show_region_bar      = $show_region_dropdown || $has_copy;
// Heading + region dropdown, no body copy: single row with region on the right.
$intro_inline_region  = $has_heading && $show_region_dropdown && ! $has_copy;

if ( ! $show_region_selector ) {
	$classes .= ' featured-solutions-block--no-region-select';
}

$cards = array();

if ( 'manual_selection' === $source ) {
	$selected = get_field( 'featured_solutions_selected_pages' );
	if ( ! is_array( $selected ) ) {
		$selected = array();
	}
	$selected = array_map( 'intval', $selected );
	$selected = array_values( array_filter( $selected ) );

	foreach ( $selected as $post_id ) {
		$built = jdpower_featured_pages_card_from_page( $post_id );
		if ( null === $built ) {
			continue;
		}
		$regions      = get_field( 'solution_available_regions', $post_id );
		$region_slugs = array();
		if ( is_array( $regions ) ) {
			foreach ( $regions as $rid ) {
				$rid = (int) $rid;
				if ( $rid > 0 && isset( $region_id_to_slug[ $rid ] ) ) {
					$region_slugs[] = $region_id_to_slug[ $rid ];
				}
			}
			$region_slugs = array_values( array_unique( $region_slugs ) );
		}
		$built['region_slugs'] = $region_slugs;
		$cards[]               = $built;
	}
} elseif ( 'segment_solutions' === $source ) {
	$segment_id = (int) get_field( 'featured_solutions_segment' );
	if ( $segment_id > 0 ) {
		$raw_solution_limit = get_field( 'featured_solutions_count' );
		// No value (legacy blocks before this field): show all matching solutions. Otherwise clamp 1–12 like Featured Segments.
		if ( null === $raw_solution_limit || false === $raw_solution_limit || '' === $raw_solution_limit ) {
			$solution_limit = -1;
		} else {
			$solution_limit = min( 12, max( 1, (int) $raw_solution_limit ) );
		}

		$solution_ids = get_posts(
			array(
				'post_type'              => 'solution',
				'post_status'            => 'publish',
				'posts_per_page'         => $solution_limit,
				'orderby'                => 'title',
				'order'                  => 'ASC',
				'fields'                 => 'ids',
				'suppress_filters'       => false,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_query'             => array(
					array(
						'key'   => 'associated_segment',
						'value' => (string) $segment_id,
					),
				),
			)
		);

		$fallback_solutions = get_field( 'featured_solutions_fallback_solutions' );
		if ( ! is_array( $fallback_solutions ) ) {
			$fallback_solutions = is_numeric( $fallback_solutions ) ? array( (int) $fallback_solutions ) : array();
		}
		$fallback_solutions = array_values(
			array_filter(
				array_map( 'intval', $fallback_solutions )
			)
		);

		if ( ! empty( $fallback_solutions ) ) {
			$seen_solution_ids = array_fill_keys( array_map( 'intval', $solution_ids ), true );
			foreach ( $fallback_solutions as $fallback_id ) {
				if ( ! isset( $seen_solution_ids[ $fallback_id ] ) ) {
					$seen_solution_ids[ $fallback_id ] = true;
					$solution_ids[]                    = $fallback_id;
				}
			}
		}

		$all_region_slugs = array();
		if ( ! empty( $region_terms ) && ! is_wp_error( $region_terms ) ) {
			foreach ( $region_terms as $t ) {
				if ( $t instanceof WP_Term ) {
					$all_region_slugs[] = $t->slug;
				}
			}
		}

		foreach ( $solution_ids as $post_id ) {
			$post_id = (int) $post_id;
			$built   = jdpower_featured_pages_card_from_page( $post_id );
			if ( null === $built ) {
				continue;
			}

			$regions      = get_field( 'solution_available_regions', $post_id );
			$region_slugs = array();
			if ( is_array( $regions ) ) {
				foreach ( $regions as $rid ) {
					$rid = (int) $rid;
					if ( $rid > 0 && isset( $region_id_to_slug[ $rid ] ) ) {
						$region_slugs[] = $region_id_to_slug[ $rid ];
					}
				}
				$region_slugs = array_values( array_unique( $region_slugs ) );
			}
			if ( empty( $region_slugs ) && ! empty( $all_region_slugs ) ) {
				$region_slugs = $all_region_slugs;
			}

			$built['region_slugs'] = $region_slugs;
			$cards[]             = $built;
		}
	}
} elseif ( 'custom' === $source && have_rows( 'featured_solutions_items' ) ) {
	while ( have_rows( 'featured_solutions_items' ) ) {
		the_row();
		$image   = get_sub_field( 'featured_solutions_card_image' );
		$ch      = get_sub_field( 'featured_solutions_card_heading' );
		$sub     = get_sub_field( 'featured_solutions_card_sub_heading' );
		$copy_f  = get_sub_field( 'featured_solutions_card_copy' );

		$image_id  = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
		$ch        = is_string( $ch ) ? trim( $ch ) : '';
		$sub       = is_string( $sub ) ? trim( $sub ) : '';
		$copy_html = is_string( $copy_f ) ? wp_kses_post( $copy_f ) : '';

		if ( '' === $ch && '' === $sub && '' === trim( wp_strip_all_tags( $copy_html ) ) && $image_id <= 0 ) {
			continue;
		}

		$cards[] = array(
			'image_id'       => $image_id,
			'heading'        => $ch,
			'sub_heading'    => $sub,
			'copy_html'      => $copy_html,
			'url'            => '',
			'explore_target' => '',
			'region_slugs'   => array(),
		);
	}
}

$has_cards = ! empty( $cards );
$has_intro = $has_pre || $has_heading || $has_copy;

if ( ! $has_intro && ! $has_cards && ! $has_cta && ! $show_region_bar ) {
	return;
}

$column_size_raw         = get_field( 'featured_solutions_column_size' );
$grid_cols               = ( 4 === (int) $column_size_raw ) ? 4 : 3;
$compact_card_typography = ( 4 === $grid_cols );

$grid_dom_id = 'featured-solutions-grid-' . preg_replace( '/[^a-zA-Z0-9_-]/', '-', (string) ( $block['id'] ?? 'block' ) );
if ( '' === $grid_dom_id || 'featured-solutions-grid-' === $grid_dom_id ) {
	$grid_dom_id = 'featured-solutions-grid-' . uniqid();
}

?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_pre ) : ?>
			<p class="featured-solutions-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
		<?php endif; ?>

		<?php
		ob_start();
		if ( $show_region_bar ) :
			?>
			<div class="featured-solutions-block__region-bar">
				<?php if ( $show_region_dropdown ) : ?>
					<?php
					$fs_all_regions_label = jdpower_pll__( 'All regions' );
					$fs_region_label_id   = $grid_dom_id . '-region-label';
					$fs_region_list_id    = $grid_dom_id . '-region-list';
					$fs_region_trigger_id = $grid_dom_id . '-region-trigger';
					$fs_longest_region_label = $fs_all_regions_label;
					foreach ( $region_terms as $fs_term ) {
						if ( ! $fs_term instanceof WP_Term ) {
							continue;
						}
						$fs_name = $fs_term->name;
						if ( function_exists( 'mb_strlen' ) ) {
							if ( mb_strlen( $fs_name ) > mb_strlen( $fs_longest_region_label ) ) {
								$fs_longest_region_label = $fs_name;
							}
						} elseif ( strlen( $fs_name ) > strlen( $fs_longest_region_label ) ) {
							$fs_longest_region_label = $fs_name;
						}
					}
					?>
					<div
						class="featured-solutions-block__region-combobox"
						data-featured-solutions-grid="<?php echo esc_attr( $grid_dom_id ); ?>"
					>
						<label class="screen-reader-text" id="<?php echo esc_attr( $fs_region_label_id ); ?>" for="<?php echo esc_attr( $fs_region_trigger_id ); ?>">
							<?php esc_html_e( 'Product region', 'jdpower' ); ?>
						</label>
						<select
							class="featured-solutions-block__region-select-hidden"
							id="<?php echo esc_attr( $grid_dom_id . '-region' ); ?>"
							tabindex="-1"
							aria-hidden="true"
						>
							<option value=""<?php selected( '', $fs_default_region_slug ); ?>><?php echo esc_html( $fs_all_regions_label ); ?></option>
							<?php
							foreach ( $region_terms as $term ) {
								if ( ! $term instanceof WP_Term ) {
									continue;
								}
								?>
								<option value="<?php echo esc_attr( $term->slug ); ?>"<?php selected( $fs_default_region_slug, $term->slug ); ?>>
									<?php echo esc_html( $term->name ); ?>
								</option>
								<?php
							}
							?>
						</select>
						<div class="featured-solutions-block__region-dropdown">
							<div class="featured-solutions-block__region-trigger-wrap">
								<div class="featured-solutions-block__region-trigger featured-solutions-block__region-trigger--sizer" aria-hidden="true">
									<span class="featured-solutions-block__region-icon">
										<img
											src="<?php echo esc_url( get_template_directory_uri() . '/images/region.svg' ); ?>"
											alt=""
											width="16"
											height="22"
											decoding="async"
										/>
									</span>
									<span class="featured-solutions-block__region-value"><?php echo esc_html( $fs_longest_region_label ); ?></span>
									<span class="featured-solutions-block__region-chevron"></span>
								</div>
								<button
									type="button"
									class="featured-solutions-block__region-trigger"
									id="<?php echo esc_attr( $fs_region_trigger_id ); ?>"
									aria-haspopup="listbox"
									aria-expanded="false"
									aria-labelledby="<?php echo esc_attr( $fs_region_label_id ); ?>"
									aria-controls="<?php echo esc_attr( $fs_region_list_id ); ?>"
								>
									<span class="featured-solutions-block__region-icon" aria-hidden="true">
										<img
											src="<?php echo esc_url( get_template_directory_uri() . '/images/region.svg' ); ?>"
											alt=""
											width="16"
											height="22"
											loading="lazy"
											decoding="async"
										/>
									</span>
									<span class="featured-solutions-block__region-value" data-featured-solutions-region-label><?php echo esc_html( $fs_default_region_label ? $fs_default_region_label : $fs_all_regions_label ); ?></span>
									<span class="featured-solutions-block__region-chevron" aria-hidden="true"></span>
								</button>
							</div>
							<div
								class="featured-solutions-block__region-list"
								id="<?php echo esc_attr( $fs_region_list_id ); ?>"
								role="listbox"
								hidden
							>
								<button type="button" class="featured-solutions-block__region-option" role="option" data-value="" aria-selected="<?php echo $fs_default_region_slug ? 'false' : 'true'; ?>">
									<?php echo esc_html( $fs_all_regions_label ); ?>
								</button>
								<?php
								foreach ( $region_terms as $term ) {
									if ( ! $term instanceof WP_Term ) {
										continue;
									}
									?>
									<button type="button" class="featured-solutions-block__region-option" role="option" data-value="<?php echo esc_attr( $term->slug ); ?>" aria-selected="<?php echo $fs_default_region_slug === $term->slug ? 'true' : 'false'; ?>">
										<?php echo esc_html( $term->name ); ?>
									</button>
									<?php
								}
								?>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<?php if ( $has_copy ) : ?>
					<div class="featured-solutions-block__region-note featured-solutions-block__copy">
						<?php echo wp_kses_post( $copy ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		endif;
		$region_bar_html = ob_get_clean();
		?>

		<?php if ( $has_heading || $show_region_bar ) : ?>
			<?php
			$intro_classes = 'featured-solutions-block__intro featured-solutions-block__intro--stacked';
			if ( $intro_inline_region ) {
				$intro_classes .= ' featured-solutions-block__intro--inline-region';
			}
			?>
			<div class="<?php echo esc_attr( $intro_classes ); ?>">
				<?php if ( $has_heading ) : ?>
					<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>
				<?php echo $region_bar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above ?>
			</div>
		<?php endif; ?>

		<?php if ( $has_cards ) : ?>
			<div class="featured-solutions-block__grid featured-solutions-block__grid--cols-<?php echo (int) $grid_cols; ?>" id="<?php echo esc_attr( $grid_dom_id ); ?>">
				<?php
				foreach ( $cards as $card ) {
					$image_id    = isset( $card['image_id'] ) ? (int) $card['image_id'] : 0;
					$card_h      = isset( $card['heading'] ) ? trim( (string) $card['heading'] ) : '';
					$card_sub    = isset( $card['sub_heading'] ) ? trim( (string) $card['sub_heading'] ) : '';
					$copy_html   = isset( $card['copy_html'] ) ? $card['copy_html'] : '';
					$explore_raw = isset( $card['explore_target'] ) ? trim( (string) $card['explore_target'] ) : '';
					$card_url    = isset( $card['url'] ) ? (string) $card['url'] : '';

					$region_slugs = isset( $card['region_slugs'] ) && is_array( $card['region_slugs'] ) ? $card['region_slugs'] : array();
					$region_slugs = array_values(
						array_filter(
							array_unique(
								array_map(
									static function ( $slug ) {
										return sanitize_title( (string) $slug );
									},
									$region_slugs
								)
							)
						)
					);

					if ( 'custom' === $source ) {
						$data_all   = '1';
						$data_slugs = '';
					} else {
						$data_all   = '0';
						$data_slugs = implode( ',', $region_slugs );
					}

					$title_size_class = $compact_card_typography ? 'xsmall' : 'small';
					$display_explore = ( 'custom' !== $source ) && '' !== $card_url && '' !== $explore_raw;
					$explore_text    = '';
					if ( $display_explore ) {
						$explore_text = jdpower_format_explore_cta( $explore_raw );
					}

					$card_hidden_by_region = false;
					if ( '' !== $fs_default_region_slug && 'custom' !== $source ) {
						$card_hidden_by_region = ! in_array( $fs_default_region_slug, $region_slugs, true );
					}

					if ( 'custom' === $source ) {
						$reveal_class = $animate_cards ? ' jdp-animate-view-fade-in-up' : '';
						?>
						<div class="featured-solutions-block__card featured-solutions-block__card--static<?php echo esc_attr( $reveal_class ); ?>"
							data-featured-solutions-card
							data-region-all="<?php echo esc_attr( $data_all ); ?>"
							<?php if ( '' !== $data_slugs ) : ?>
								data-region-slugs="<?php echo esc_attr( $data_slugs ); ?>"
							<?php endif; ?>
							<?php if ( $card_hidden_by_region ) : ?>
								hidden
							<?php endif; ?>
						>
							<span class="featured-solutions-block__media">
								<?php if ( $image_id > 0 ) : ?>
									<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'featured-solutions-block__img' ) ); ?>
								<?php else : ?>
									<span class="featured-solutions-block__placeholder" aria-hidden="true"></span>
								<?php endif; ?>
							</span>
							<span class="featured-solutions-block__body">
								<?php if ( '' !== $card_h ) : ?>
									<h2 class="<?php echo esc_attr( $title_size_class . ' featured-solutions-block__card-title' ); ?>"><?php echo esc_html( $card_h ); ?></h2>
								<?php endif; ?>
								<?php if ( '' !== $card_sub ) : ?>
									<h3 class="small featured-solutions-block__card-sub"><?php echo esc_html( $card_sub ); ?></h3>
								<?php endif; ?>
								<?php if ( is_string( $copy_html ) && '' !== trim( wp_strip_all_tags( $copy_html ) ) ) : ?>
									<span class="featured-solutions-block__card-copy"><?php echo $copy_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php endif; ?>
							</span>
						</div>
						<?php
					} else {
						if ( '' === $card_url ) {
							$card_url = '#';
						}
						$reveal_class = $animate_cards ? ' jdp-animate-view-fade-in-up' : '';
						?>
						<a class="featured-solutions-block__card<?php echo esc_attr( $reveal_class ); ?>" href="<?php echo esc_url( $card_url ); ?>"
							data-featured-solutions-card
							data-region-all="<?php echo esc_attr( $data_all ); ?>"
							<?php if ( '' !== $data_slugs ) : ?>
								data-region-slugs="<?php echo esc_attr( $data_slugs ); ?>"
							<?php endif; ?>
							<?php if ( $card_hidden_by_region ) : ?>
								hidden
							<?php endif; ?>
						>
							<span class="featured-solutions-block__media">
								<?php if ( $image_id > 0 ) : ?>
									<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'featured-solutions-block__img' ) ); ?>
								<?php else : ?>
									<span class="featured-solutions-block__placeholder" aria-hidden="true"></span>
								<?php endif; ?>
							</span>
							<span class="featured-solutions-block__body">
								<?php if ( '' !== $card_h ) : ?>
									<h2 class="<?php echo esc_attr( $title_size_class . ' featured-solutions-block__card-title' ); ?>"><?php echo esc_html( $card_h ); ?></h2>
								<?php endif; ?>
								<?php if ( '' !== $card_sub ) : ?>
									<h3 class="small featured-solutions-block__card-sub"><?php echo esc_html( $card_sub ); ?></h3>
								<?php endif; ?>
								<?php if ( is_string( $copy_html ) && '' !== trim( wp_strip_all_tags( $copy_html ) ) ) : ?>
									<span class="featured-solutions-block__card-copy"><?php echo $copy_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php endif; ?>
								<?php if ( '' !== $explore_text ) : ?>
									<span class="arrow-link featured-solutions-block__card-explore"><?php echo esc_html( $explore_text ); ?></span>
								<?php endif; ?>
							</span>
						</a>
						<?php
					}
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( $has_cta ) : ?>
			<div class="featured-solutions-block__cta">
				<a class="arrow-link featured-solutions-block__cta-link" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $cta_target_attr . $cta_rel_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php echo esc_html( trim( $cta_title ) ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
