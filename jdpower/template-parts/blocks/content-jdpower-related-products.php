<?php
/**
 * Block Name: Related Products
 *
 * @package jdpower
 */

if ( ! function_exists( 'jdpower_related_products_solution_term_ids' ) ) {
	/**
	 * Normalize ACF Product Solution field (checkbox or legacy single ID) to valid product_solution term IDs.
	 *
	 * @param mixed $value Raw value from get_field.
	 * @return int[]
	 */
	function jdpower_related_products_solution_term_ids( $value ) {
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
			$term = get_term( $tid, 'product_solution' );
			if ( $term && ! is_wp_error( $term ) ) {
				$out[] = $tid;
			}
		}

		return $out;
	}
}

if ( ! function_exists( 'jdpower_related_products_post_has_solution_terms' ) ) {
	/**
	 * Whether a product is assigned at least one of the given product_solution term IDs.
	 *
	 * @param int   $post_id  Product post ID.
	 * @param int[] $term_ids product_solution term IDs.
	 * @return bool
	 */
	function jdpower_related_products_post_has_solution_terms( $post_id, $term_ids ) {
		$post_id  = (int) $post_id;
		$term_ids = array_values( array_filter( array_map( 'intval', (array) $term_ids ) ) );
		if ( $post_id <= 0 || empty( $term_ids ) ) {
			return false;
		}

		$assigned = wp_get_post_terms( $post_id, 'product_solution', array( 'fields' => 'ids' ) );
		if ( is_wp_error( $assigned ) || empty( $assigned ) ) {
			return false;
		}

		$assigned = array_map( 'intval', $assigned );
		return ! empty( array_intersect( $term_ids, $assigned ) );
	}
}

if ( ! function_exists( 'jdpower_related_products_card_from_post' ) ) {
	/**
	 * Build card data from a product post (dynamic source).
	 *
	 * @param int $post_id Product post ID.
	 * @return array{image_id: int, tag: string, heading: string, copy_html: string, url: string, target: string, rel: string}
	 */
	function jdpower_related_products_card_from_post( $post_id ) {
		$post_id  = (int) $post_id;
		$image_id = (int) get_post_thumbnail_id( $post_id );

		$tag = '';
		$segment_names = function_exists( 'jdpower_product_card_segment_names' )
			? jdpower_product_card_segment_names( $post_id )
			: array();
		if ( ! empty( $segment_names ) ) {
			$tag = implode( ', ', $segment_names );
		} else {
			$solution_terms = get_the_terms( $post_id, 'product_solution' );
			if ( $solution_terms && ! is_wp_error( $solution_terms ) ) {
				$tag = jdpower_decode_text_for_display( $solution_terms[0]->name );
			} else {
				$ind = get_the_terms( $post_id, 'product_industry' );
				if ( $ind && ! is_wp_error( $ind ) ) {
					$tag = jdpower_decode_text_for_display( $ind[0]->name );
				}
			}
		}

		$detail    = function_exists( 'jdpower_post_filters_product_card_data' )
			? jdpower_post_filters_product_card_data( $post_id )
			: array(
				'heading'       => get_the_title( $post_id ),
				'copy_html'     => '',
				'segment_label' => '',
			);
		$heading   = isset( $detail['heading'] ) ? (string) $detail['heading'] : get_the_title( $post_id );
		$copy_html = isset( $detail['copy_html'] ) ? (string) $detail['copy_html'] : '';

		return array(
			'image_id'     => $image_id,
			'tag'          => $tag,
			'heading'      => $heading,
			'copy_html'    => $copy_html,
			'url'          => get_permalink( $post_id ),
			'target'       => '',
			'rel'          => '',
			'region_slugs' => function_exists( 'jdpower_post_region_slugs' )
				? jdpower_post_region_slugs( $post_id, 'product' )
				: array(),
		);
	}
}

if ( ! function_exists( 'jdpower_related_products_render_card' ) ) {
	/**
	 * Echo one related product card (grid or carousel).
	 *
	 * @param array $card Card data from dynamic or manual source.
	 * @param bool        $compact_card_typography When true (four-column grid), card title uses h2.xsmall (three-column uses h2.small).
	 * @param bool        $animate_cards           When true, scroll-reveal class on the card.
	 * @param bool $region_filter_enabled   When true, output region filter data attributes and initial visibility.
	 */
	function jdpower_related_products_render_card( $card, $compact_card_typography = false, $animate_cards = true, $region_filter_enabled = false ) {
		$url    = isset( $card['url'] ) ? $card['url'] : '';
		$target = isset( $card['target'] ) ? $card['target'] : '';
		$rel    = isset( $card['rel'] ) ? $card['rel'] : '';
		if ( '' === $url ) {
			return;
		}
		$image_id  = isset( $card['image_id'] ) ? (int) $card['image_id'] : 0;
		$tag       = isset( $card['tag'] ) ? $card['tag'] : '';
		$heading   = isset( $card['heading'] ) ? $card['heading'] : '';
		$copy_html = isset( $card['copy_html'] ) ? $card['copy_html'] : '';
		$title_size_class = 'small-min';
		$reveal_class     = $animate_cards ? ' jdp-animate-view-fade-in-up' : '';

		$region_attrs = '';
		if ( $region_filter_enabled ) {
			$default_region_slug = function_exists( 'jdpower_product_region_visitor_slug' )
				? jdpower_product_region_visitor_slug()
				: '';
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
			$data_all   = empty( $region_slugs ) ? '1' : '0';
			$data_slugs = empty( $region_slugs ) ? '' : implode( ',', $region_slugs );
			$region_attrs .= ' data-featured-solutions-card';
			$region_attrs .= ' data-region-all="' . esc_attr( $data_all ) . '"';
			if ( '' !== $data_slugs ) {
				$region_attrs .= ' data-region-slugs="' . esc_attr( $data_slugs ) . '"';
			}
			if ( '' !== $default_region_slug && '0' === $data_all && ! in_array( $default_region_slug, $region_slugs, true ) ) {
				$region_attrs .= ' hidden';
			}
		}
		?>
		<a class="related-products-block__card<?php echo esc_attr( $reveal_class ); ?>" href="<?php echo esc_url( $url ); ?>" draggable="false"<?php echo $region_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above ?>
			<?php
			if ( '_blank' === $target ) {
				echo ' target="_blank"';
				if ( $rel ) {
					echo ' rel="' . esc_attr( $rel ) . '"';
				} else {
					echo ' rel="noopener noreferrer"';
				}
			}
			?>
		>
			<span class="related-products-block__media">
				<?php if ( $image_id > 0 ) : ?>
					<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'related-products-block__img', 'draggable' => 'false' ) ); ?>
				<?php else : ?>
					<span class="related-products-block__placeholder" aria-hidden="true"></span>
				<?php endif; ?>
			</span>
			<span class="related-products-block__body">
				<?php if ( is_string( $tag ) && '' !== $tag ) : ?>
					<span class="related-products-block__industry"><?php echo esc_html( $tag ); ?></span>
				<?php endif; ?>
				<?php if ( is_string( $heading ) && '' !== $heading ) : ?>
					<h2 class="related-products-block__title <?php echo esc_attr( $title_size_class ); ?>"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( is_string( $copy_html ) && '' !== trim( wp_strip_all_tags( $copy_html ) ) ) : ?>
					<span class="related-products-block__copy"><?php echo $copy_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses at build ?></span>
				<?php endif; ?>
				<span class="arrow-link related-products-block__card-learn-more"><?php echo esc_html( jdpower_pll__( 'Learn More' ) ); ?></span>
			</span>
		</a>
		<?php
	}
}

if ( ! function_exists( 'jdpower_related_products_carousel_nav' ) ) {
	/**
	 * Echo prev/next controls for the related products carousel track.
	 *
	 * @param string $carousel_id DOM id of the carousel element (aria-controls target).
	 */
	function jdpower_related_products_carousel_nav( $carousel_id ) {
		$carousel_id = is_string( $carousel_id ) ? $carousel_id : '';
		?>
		<div class="related-products-block__nav" role="group" aria-label="<?php esc_attr_e( 'Related products carousel', 'jdpower' ); ?>">
			<button type="button" class="related-products-block__nav-btn related-products-block__nav-btn--prev" aria-controls="<?php echo esc_attr( $carousel_id ); ?>" aria-label="<?php esc_attr_e( 'Previous products', 'jdpower' ); ?>">
				<span class="related-products-block__nav-icon" aria-hidden="true">
					<svg class="related-products-block__chevron" width="20" height="34" viewBox="0 0 20 34" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
						<path d="M16.7366 2.50008L2.5 16.7364L16.7366 30.9727" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			</button>
			<button type="button" class="related-products-block__nav-btn related-products-block__nav-btn--next" aria-controls="<?php echo esc_attr( $carousel_id ); ?>" aria-label="<?php esc_attr_e( 'Next products', 'jdpower' ); ?>">
				<span class="related-products-block__nav-icon" aria-hidden="true">
					<svg class="related-products-block__chevron" width="20" height="34" viewBox="0 0 20 34" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
						<path d="M2.49976 30.9726L16.7363 16.7363L2.49976 2.5" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			</button>
		</div>
		<?php
	}
}

if ( ! function_exists( 'jdpower_related_products_dynamic_solution_cards' ) ) {
	/**
	 * Load cards for Dynamic By Solution: newest first, or featured products first when $include_featured is true.
	 *
	 * @param int[]    $term_ids           product_solution term IDs.
	 * @param int|null $limit              Max cards (1–12), or null for all matching products.
	 * @param bool     $include_featured   When true, products with jdpower_featured appear first (newest within each group).
	 * @param int[]    $exclude_post_ids   Post IDs to exclude (e.g. current product on single product).
	 * @param string[] $region_slugs       product_region slugs from the host post; empty skips region filter.
	 * @return array<int, array<string, mixed>>
	 */
	function jdpower_related_products_dynamic_solution_cards( $term_ids, $limit, $include_featured, $exclude_post_ids = array(), $region_slugs = array() ) {
		$cards       = array();
		$term_ids    = array_values( array_filter( array_map( 'intval', (array) $term_ids ) ) );
		$unlimited   = null === $limit;
		if ( ! $unlimited ) {
			$limit = max( 1, min( 12, (int) $limit ) );
		}
		if ( empty( $term_ids ) ) {
			return $cards;
		}

		$exclude_post_ids = array_values( array_unique( array_filter( array_map( 'intval', (array) $exclude_post_ids ) ) ) );

		$tax_query = array(
			array(
				'taxonomy'         => 'product_solution',
				'field'            => 'term_id',
				'terms'            => $term_ids,
				'operator'         => 'IN',
				'include_children' => false,
			),
		);

		$region_slugs = array_values(
			array_unique(
				array_filter(
					array_map(
						static function ( $slug ) {
							return sanitize_title( (string) $slug );
						},
						(array) $region_slugs
					)
				)
			)
		);
		if ( ! empty( $region_slugs ) ) {
			$tax_query[] = array(
				'taxonomy'         => 'product_region',
				'field'            => 'slug',
				'terms'            => $region_slugs,
				'operator'         => 'IN',
				'include_children' => true,
			);
		}
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		$base = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'tax_query'           => $tax_query,
		);
		if ( ! empty( $exclude_post_ids ) ) {
			$base['post__not_in'] = $exclude_post_ids;
		}

		$posts_per = $unlimited ? -1 : $limit;

		if ( ! $include_featured ) {
			$q = new WP_Query(
				array_merge(
					$base,
					array(
						'posts_per_page' => $posts_per,
					)
				)
			);
			if ( $q->have_posts() ) {
				while ( $q->have_posts() ) {
					$q->the_post();
					$pid = get_the_ID();
					if ( ! jdpower_related_products_post_has_solution_terms( $pid, $term_ids ) ) {
						continue;
					}
					$cards[] = jdpower_related_products_card_from_post( $pid );
				}
				wp_reset_postdata();
			}
			return $cards;
		}

		$q_feat = new WP_Query(
			array_merge(
				$base,
				array(
					'posts_per_page' => $posts_per,
					'meta_query'     => array(
						array(
							'key'     => 'jdpower_featured',
							'value'   => '1',
							'compare' => '=',
						),
					),
				)
			)
		);

		$seen_ids = array();
		if ( $q_feat->have_posts() ) {
			while ( $q_feat->have_posts() ) {
				$q_feat->the_post();
				$pid = get_the_ID();
				if ( ! jdpower_related_products_post_has_solution_terms( $pid, $term_ids ) ) {
					continue;
				}
				$seen_ids[] = $pid;
				$cards[]    = jdpower_related_products_card_from_post( $pid );
			}
			wp_reset_postdata();
		}

		if ( $unlimited ) {
			$not_in = array_merge( $exclude_post_ids, $seen_ids );
			$not_in = array_values( array_unique( array_filter( $not_in ) ) );

			$q_rest = new WP_Query(
				array_merge(
					$base,
					array(
						'posts_per_page' => -1,
						'post__not_in'   => $not_in,
						'meta_query'     => array(
							'relation' => 'OR',
							array(
								'key'     => 'jdpower_featured',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key'     => 'jdpower_featured',
								'value'   => '0',
								'compare' => '=',
							),
							array(
								'key'     => 'jdpower_featured',
								'value'   => '',
								'compare' => '=',
							),
						),
					)
				)
			);

			if ( $q_rest->have_posts() ) {
				while ( $q_rest->have_posts() ) {
					$q_rest->the_post();
					$pid = get_the_ID();
					if ( ! jdpower_related_products_post_has_solution_terms( $pid, $term_ids ) ) {
						continue;
					}
					$cards[] = jdpower_related_products_card_from_post( $pid );
				}
				wp_reset_postdata();
			}

			return $cards;
		}

		$remaining = $limit - count( $cards );
		if ( $remaining <= 0 ) {
			return $cards;
		}

		$not_in = array_merge( $exclude_post_ids, $seen_ids );
		$not_in = array_values( array_unique( array_filter( $not_in ) ) );

		$q_rest = new WP_Query(
			array_merge(
				$base,
				array(
					'posts_per_page' => $remaining,
					'post__not_in'   => $not_in,
					'meta_query'     => array(
						'relation' => 'OR',
						array(
							'key'     => 'jdpower_featured',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'jdpower_featured',
							'value'   => '0',
							'compare' => '=',
						),
						array(
							'key'     => 'jdpower_featured',
							'value'   => '',
							'compare' => '=',
						),
					),
				)
			)
		);

		if ( $q_rest->have_posts() ) {
			while ( $q_rest->have_posts() ) {
				$q_rest->the_post();
				$pid = get_the_ID();
				if ( ! jdpower_related_products_post_has_solution_terms( $pid, $term_ids ) ) {
					continue;
				}
				$cards[] = jdpower_related_products_card_from_post( $pid );
			}
			wp_reset_postdata();
		}

		return $cards;
	}
}

$classes = 'related-products-block';

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

$padding_top     = $acf_get( 'related_products_padding_top' );
$padding_bottom  = $acf_get( 'related_products_padding_bottom' );
$background_tone = $acf_get( 'related_products_background_tone' );
$animate_cards   = function_exists( 'jdpower_acf_flag_on' ) ? jdpower_acf_flag_on( $acf_get( 'related_products_animate_cards' ) ) : true;
$source          = $acf_get( 'related_products_source' );
// Blocks saved before three source options used "manual" for the custom repeater.
if ( 'manual' === $source ) {
	$source = 'custom';
}

if ( empty( $source ) || ! in_array( $source, array( 'dynamic', 'manual_selection', 'custom' ), true ) ) {
	$source = 'dynamic';
}

$limit = null;
if ( 'dynamic' === $source && ! empty( $acf_get( 'related_products_limit_count' ) ) ) {
	$raw_limit = $acf_get( 'related_products_count' );
	$limit     = is_numeric( $raw_limit ) ? (int) $raw_limit : 3;
	$limit     = min( 12, max( 1, $limit ) );
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
	$classes .= ' related-products-block--tone-dark';
} else {
	$classes .= ' related-products-block--tone-light';
}

$classes .= ' related-products-block--source-' . $source;

$intro_column_ratio = $acf_get( 'related_products_intro_column_ratio' );
$valid_intro_ratios = array( '50_50', '60_40', '70_30', '80_20' );
if ( empty( $intro_column_ratio ) || ! in_array( $intro_column_ratio, $valid_intro_ratios, true ) ) {
	$intro_column_ratio = '50_50';
}
$classes .= ' related-products-block--intro-ratio-' . str_replace( '_', '-', $intro_column_ratio );

$related_products_intro_col_classes = array(
	'50_50' => array( 'heading' => 'col-lg-6', 'copy' => 'col-lg-6' ),
	'60_40' => array( 'heading' => 'col-lg-7', 'copy' => 'col-lg-5' ),
	'70_30' => array( 'heading' => 'col-lg-8', 'copy' => 'col-lg-4' ),
	'80_20' => array( 'heading' => 'col-lg-10', 'copy' => 'col-lg-2' ),
);
$intro_split_cols = $related_products_intro_col_classes[ $intro_column_ratio ];

$pre_heading      = $acf_get( 'related_products_pre_heading' );
$section_heading  = $acf_get( 'related_products_section_heading' );
$heading_size     = $acf_get( 'related_products_heading_size' );
$section_copy     = $acf_get( 'related_products_section_copy' );
$cta_link         = $acf_get( 'related_products_cta' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'default';
}

$heading_classes = array( 'related-products-block__heading' );
if ( 'default' !== $heading_size ) {
	$heading_classes[] = $heading_size;
}
$heading_class_attr = esc_attr( implode( ' ', $heading_classes ) );

$has_pre          = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_section_heading = is_string( $section_heading ) && '' !== trim( wp_strip_all_tags( (string) $section_heading ) );
$has_section_copy    = is_string( $section_copy ) && '' !== trim( wp_strip_all_tags( (string) $section_copy ) );

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

$show_region_selector_raw = $acf_get( 'related_products_show_region_selector' );
// Explicit opt-in only — unset/null on legacy blocks must not show the selector (unlike jdpower_acf_flag_on).
$show_region_selector     = in_array( $show_region_selector_raw, array( true, 1, '1' ), true );
$region_filter_client     = $show_region_selector && in_array( $source, array( 'dynamic', 'manual_selection' ), true );

$region_terms = get_terms(
	array(
		'taxonomy'   => 'product_region',
		'hide_empty' => false,
	)
);
if ( is_wp_error( $region_terms ) ) {
	$region_terms = array();
}

$show_region_dropdown = $region_filter_client && ! empty( $region_terms );

$intro_split = $has_section_heading && $has_section_copy;

$cards = array();

if ( 'dynamic' === $source ) {
	$term_ids = jdpower_related_products_solution_term_ids( $acf_get( 'related_products_solution' ) );
	// Legacy: single solution or old related_products_industry only.
	if ( empty( $term_ids ) ) {
		$term_ids = jdpower_related_products_solution_term_ids( $acf_get( 'related_products_industry' ) );
	}
	if ( ! empty( $term_ids ) ) {
		$host_post_id = 0;
		if ( isset( $jdpower_acf_block_host_post_id ) && (int) $jdpower_acf_block_host_post_id > 0 ) {
			$host_post_id = (int) $jdpower_acf_block_host_post_id;
		} elseif ( is_singular() ) {
			$host_post_id = (int) get_queried_object_id();
		}
		if ( $host_post_id <= 0 ) {
			$host_post_id = (int) get_the_ID();
		}

		$exclude_ids = array();
		if ( $host_post_id > 0 && 'product' === get_post_type( $host_post_id ) ) {
			$exclude_ids[] = $host_post_id;
		}
		$include_featured = ! empty( $acf_get( 'related_products_include_featured' ) );
		// Default off: only skip region filter when explicitly enabled (not jdpower_acf_flag_on — unset must stay filtered).
		$disable_region = in_array( $acf_get( 'related_products_disable_region_filter' ), array( true, 1, '1' ), true );
		$region_slugs   = array();
		if ( ! $region_filter_client && ! $disable_region && $host_post_id > 0 && function_exists( 'jdpower_regional_popup_post_types' ) && function_exists( 'jdpower_post_region_slugs' ) ) {
			$host_post_type = get_post_type( $host_post_id );
			if ( is_string( $host_post_type ) && in_array( $host_post_type, jdpower_regional_popup_post_types(), true ) ) {
				$region_slugs = jdpower_post_region_slugs( $host_post_id, $host_post_type );
			}
		}
		$cards = jdpower_related_products_dynamic_solution_cards( $term_ids, $limit, $include_featured, $exclude_ids, $region_slugs );
	}
} elseif ( 'manual_selection' === $source ) {
	$selected = $acf_get( 'related_products_selected' );
	if ( ! is_array( $selected ) ) {
		$selected = array();
	}
	$selected = array_map( 'intval', $selected );
	$selected = array_values( array_filter( $selected ) );

	if ( is_singular( 'product' ) ) {
		$current_product_id = (int) get_queried_object_id();
		if ( $current_product_id > 0 ) {
			$selected = array_values(
				array_filter(
					$selected,
					function ( $id ) use ( $current_product_id ) {
						return (int) $id !== $current_product_id;
					}
				)
			);
		}
	}

	foreach ( $selected as $post_id ) {
		if ( 'publish' !== get_post_status( $post_id ) ) {
			continue;
		}
		$cards[] = jdpower_related_products_card_from_post( $post_id );
	}
} elseif ( 'custom' === $source ) {
	$rp_repeater_args = $acf_block_id
		? array( 'related_products_items', $acf_block_id )
		: array( 'related_products_items' );
	if ( call_user_func_array( 'have_rows', $rp_repeater_args ) ) {
		while ( call_user_func_array( 'have_rows', $rp_repeater_args ) ) {
			the_row();
			$link = get_sub_field( 'related_products_link' );
			$url  = is_array( $link ) ? (string) ( $link['url'] ?? '' ) : '';
			if ( '' === $url ) {
				continue;
			}
			$target = is_array( $link ) ? (string) ( $link['target'] ?? '' ) : '';
			$rel    = '';
			if ( '_blank' === $target ) {
				$rel = 'noopener noreferrer';
			}

			$image    = get_sub_field( 'related_products_image' );
			$image_id = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
			$tag      = get_sub_field( 'related_products_tag' );
			$heading  = get_sub_field( 'related_products_heading' );
			$copy     = get_sub_field( 'related_products_copy' );

			$tag       = is_string( $tag ) ? trim( $tag ) : '';
			$heading   = is_string( $heading ) ? trim( $heading ) : '';
			$copy_html = is_string( $copy ) ? wp_kses_post( $copy ) : '';

			$cards[] = array(
				'image_id'  => $image_id,
				'tag'       => $tag,
				'heading'   => $heading,
				'copy_html' => $copy_html,
				'url'       => $url,
				'target'    => $target,
				'rel'       => $rel,
			);
		}
	}
}

// Do not render intro/CTA alone when there are no product cards (e.g. Dynamic By Solution with no terms or matches).
if ( empty( $cards ) ) {
	return;
}

$column_size_raw = $acf_get( 'related_products_column_size' );
$grid_cols       = ( 4 === (int) $column_size_raw ) ? 4 : 3;
$compact_card_typography = ( 4 === $grid_cols );

$is_carousel = ! empty( $cards ) && count( $cards ) > 4;

if ( ! empty( $cards ) ) {
	$classes .= $is_carousel ? ' related-products-block--layout-carousel' : ' related-products-block--layout-grid';
}

$carousel_id = 'related-products-carousel-' . ( isset( $block['id'] ) ? sanitize_title( (string) $block['id'] ) : wp_unique_id( 'rp-' ) );
$products_grid_id = $is_carousel
	? $carousel_id
	: 'related-products-grid-' . ( isset( $block['id'] ) ? sanitize_title( (string) $block['id'] ) : wp_unique_id( 'rp-grid-' ) );
$carousel_nav_rendered = false;

$has_intro = $has_pre || $has_section_heading || $has_section_copy;

$region_in_carousel_intro = $is_carousel && $show_region_dropdown && ( $has_section_heading || $has_section_copy );

if ( $show_region_dropdown ) {
	$classes .= ' related-products-block--has-region-select';
}

$jdpower_related_products_render_region_bar = static function () use ( $products_grid_id ) {
	?>
	<div class="featured-solutions-block__region-bar related-products-block__region-bar">
		<?php
		get_template_part(
			'template-parts/partials/product-region',
			'combobox',
			array(
				'grid_dom_id' => $products_grid_id,
			)
		);
		?>
	</div>
	<?php
};

?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $is_carousel && ! empty( $cards ) && $has_pre && ! $has_section_heading && ! $has_section_copy ) : ?>
			<div class="related-products-block__carousel-pre-nav-row">
				<p class="related-products-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
				<?php
				jdpower_related_products_carousel_nav( $carousel_id );
				$carousel_nav_rendered = true;
				?>
			</div>
		<?php elseif ( $is_carousel && $has_pre ) : ?>
			<p class="related-products-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
		<?php endif; ?>

		<?php if ( $has_intro ) : ?>
			<?php if ( ! $is_carousel && $has_pre ) : ?>
				<p class="related-products-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
			<?php endif; ?>

			<?php if ( $intro_split ) : ?>
				<?php if ( $is_carousel ) : ?>
					<div class="row related-products-block__carousel-intro-row align-items-stretch<?php echo $region_in_carousel_intro ? ' related-products-block__carousel-intro-row--with-region' : ''; ?>">
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['heading'] ); ?> related-products-block__carousel-intro-main">
							<div class="related-products-block__intro related-products-block__intro--stacked">
								<?php if ( $has_section_heading ) : ?>
									<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $section_heading ); ?></h2>
								<?php endif; ?>
								<?php if ( $has_section_copy ) : ?>
									<div class="related-products-block__intro-copy">
										<?php echo wp_kses_post( $section_copy ); ?>
									</div>
								<?php endif; ?>
								<?php if ( $region_in_carousel_intro ) : ?>
									<?php $jdpower_related_products_render_region_bar(); ?>
								<?php endif; ?>
							</div>
						</div>
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['copy'] ); ?> related-products-block__carousel-intro-aside">
							<?php
							jdpower_related_products_carousel_nav( $carousel_id );
							$carousel_nav_rendered = true;
							?>
						</div>
					</div>
				<?php else : ?>
					<div class="row related-products-block__intro-row align-items-start align-items-lg-end">
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['heading'] ); ?> related-products-block__intro-col related-products-block__intro-col--heading">
							<?php if ( $has_section_heading ) : ?>
								<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $section_heading ); ?></h2>
							<?php endif; ?>
						</div>
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['copy'] ); ?> related-products-block__intro-copy related-products-block__intro-copy--aside">
							<?php echo wp_kses_post( $section_copy ); ?>
						</div>
					</div>
				<?php endif; ?>
			<?php elseif ( $has_section_heading || $has_section_copy ) : ?>
				<?php if ( $is_carousel ) : ?>
					<?php
					$rp_partial_carousel_row_class = 'related-products-block__carousel-intro-row';
					$rp_partial_carousel_row_class .= $has_pre ? ' align-items-stretch' : ' related-products-block__carousel-intro-row--partial align-items-start';
					if ( $region_in_carousel_intro ) {
						$rp_partial_carousel_row_class .= ' related-products-block__carousel-intro-row--with-region';
					}
					?>
					<div class="row <?php echo esc_attr( $rp_partial_carousel_row_class ); ?>">
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['heading'] ); ?> related-products-block__carousel-intro-main">
							<div class="related-products-block__intro related-products-block__intro--stacked">
								<?php if ( $has_section_heading ) : ?>
									<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $section_heading ); ?></h2>
								<?php endif; ?>
								<?php if ( $has_section_copy ) : ?>
									<div class="related-products-block__intro-copy">
										<?php echo wp_kses_post( $section_copy ); ?>
									</div>
								<?php endif; ?>
								<?php if ( $region_in_carousel_intro ) : ?>
									<?php $jdpower_related_products_render_region_bar(); ?>
								<?php endif; ?>
							</div>
						</div>
						<div class="col-12 <?php echo esc_attr( $intro_split_cols['copy'] ); ?> related-products-block__carousel-intro-aside">
							<?php
							jdpower_related_products_carousel_nav( $carousel_id );
							$carousel_nav_rendered = true;
							?>
						</div>
					</div>
				<?php else : ?>
					<div class="related-products-block__intro">
						<?php if ( $has_section_heading ) : ?>
							<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $section_heading ); ?></h2>
						<?php endif; ?>
						<?php if ( $has_section_copy ) : ?>
							<div class="related-products-block__intro-copy">
								<?php echo wp_kses_post( $section_copy ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $is_carousel && ! empty( $cards ) && ! $carousel_nav_rendered ) : ?>
			<div class="related-products-block__carousel-nav-row">
				<?php jdpower_related_products_carousel_nav( $carousel_id ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $show_region_dropdown && ! $region_in_carousel_intro ) : ?>
			<?php $jdpower_related_products_render_region_bar(); ?>
		<?php endif; ?>

		<?php if ( ! $is_carousel && ! empty( $cards ) ) : ?>
			<div class="related-products-block__grid related-products-block__grid--cols-<?php echo (int) $grid_cols; ?>" id="<?php echo esc_attr( $products_grid_id ); ?>">
				<?php foreach ( $cards as $card ) : ?>
					<?php jdpower_related_products_render_card( $card, $compact_card_typography, $animate_cards, $region_filter_client ); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( $has_cta && ! $is_carousel ) : ?>
			<div class="related-products-block__cta">
				<a class="arrow-link related-products-block__cta-link" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $cta_target_attr . $cta_rel_attr; ?>>
					<?php echo esc_html( trim( $cta_title ) ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $is_carousel && ! empty( $cards ) ) : ?>
		<div class="related-products-block__carousel-shell">
			<div id="<?php echo esc_attr( $carousel_id ); ?>" class="related-products-block__carousel" data-jdpower-carousel tabindex="0">
				<?php foreach ( $cards as $card ) : ?>
					<?php jdpower_related_products_render_card( $card, $compact_card_typography, $animate_cards, $region_filter_client ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $has_cta ) : ?>
			<div class="container">
				<div class="related-products-block__cta">
					<a class="arrow-link related-products-block__cta-link" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $cta_target_attr . $cta_rel_attr; ?>>
						<?php echo esc_html( trim( $cta_title ) ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</section>
