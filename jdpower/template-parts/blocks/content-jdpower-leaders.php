<?php
/**
 * Block Name: Leaders
 *
 * @package jdpower
 */

if ( ! function_exists( 'jdpower_leaders_display_name' ) ) {
	/**
	 * Full display name for a leader post (first + last, else post title).
	 *
	 * @param int $post_id Leader post ID.
	 * @return string
	 */
	function jdpower_leaders_display_name( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 || ! function_exists( 'get_field' ) ) {
			return '';
		}
		$fn = get_field( 'leader_first_name', $post_id );
		$ln = get_field( 'leader_last_name', $post_id );
		$fn = is_string( $fn ) ? trim( $fn ) : '';
		$ln = is_string( $ln ) ? trim( $ln ) : '';
		$full = trim( $fn . ' ' . $ln );
		if ( '' !== $full ) {
			return $full;
		}
		return get_the_title( $post_id );
	}
}

if ( ! function_exists( 'jdpower_leaders_is_featured' ) ) {
	/**
	 * Whether a leader is marked featured (larger card, shown first in the block).
	 *
	 * @param int $post_id Leader post ID.
	 * @return bool
	 */
	function jdpower_leaders_is_featured( $post_id ) {
		$post_id = (int) $post_id;
		if ( $post_id <= 0 || ! function_exists( 'get_field' ) ) {
			return false;
		}
		return (bool) get_field( 'leader_featured', $post_id );
	}
}

if ( ! function_exists( 'jdpower_leaders_prioritize_featured_ids' ) ) {
	/**
	 * Stable partition: featured IDs first (keeping their relative order), then the rest.
	 *
	 * @param int[] $ids Ordered leader post IDs.
	 * @return int[]
	 */
	function jdpower_leaders_prioritize_featured_ids( array $ids ) {
		$featured = array();
		$rest      = array();
		foreach ( $ids as $id ) {
			$id = (int) $id;
			if ( $id <= 0 ) {
				continue;
			}
			if ( jdpower_leaders_is_featured( $id ) ) {
				$featured[] = $id;
			} else {
				$rest[] = $id;
			}
		}
		return array_merge( $featured, $rest );
	}
}

if ( ! function_exists( 'jdpower_leaders_leader_ids_for_term' ) ) {
	/**
	 * Published leader post IDs for a Leader Type term, with sort order.
	 *
	 * @param int    $term_id    leader_type term ID.
	 * @param string $sort_order One of date_desc|date_asc|last_name_asc|last_name_desc|menu_order.
	 * @return int[] Featured leaders are moved to the front; order within featured / non-featured follows $sort_order.
	 */
	function jdpower_leaders_leader_ids_for_term( $term_id, $sort_order ) {
		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) {
			return array();
		}
		$term = get_term( $term_id, 'leader_type' );
		if ( ! $term || is_wp_error( $term ) ) {
			return array();
		}

		$base = array(
			'post_type'           => 'leader',
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
			'tax_query'           => array(
				array(
					'taxonomy' => 'leader_type',
					'field'    => 'term_id',
					'terms'    => array( $term_id ),
				),
			),
		);

		$valid_sorts = array( 'date_desc', 'date_asc', 'last_name_asc', 'last_name_desc', 'menu_order' );
		if ( ! in_array( $sort_order, $valid_sorts, true ) ) {
			$sort_order = 'last_name_asc';
		}

		switch ( $sort_order ) {
			case 'date_desc':
				$base['orderby'] = 'date';
				$base['order']   = 'DESC';
				break;
			case 'date_asc':
				$base['orderby'] = 'date';
				$base['order']   = 'ASC';
				break;
			case 'last_name_desc':
				$base['meta_key'] = 'leader_last_name';
				$base['orderby']  = 'meta_value';
				$base['order']    = 'DESC';
				break;
			case 'menu_order':
				$base['orderby'] = array(
					'menu_order' => 'ASC',
					'post_title' => 'ASC',
				);
				break;
			case 'last_name_asc':
			default:
				$base['meta_key'] = 'leader_last_name';
				$base['orderby']  = 'meta_value';
				$base['order']    = 'ASC';
				break;
		}

		$q   = new WP_Query( $base );
		$ids = array_map( 'intval', (array) $q->posts );
		return jdpower_leaders_prioritize_featured_ids( $ids );
	}
}

if ( ! function_exists( 'jdpower_leaders_render_leader_cell' ) ) {
	/**
	 * Echo one leader card + biography dialog.
	 *
	 * @param int    $leader_id Leader post ID.
	 * @param string $block_uid Sanitized block id for DOM ids.
	 * @param string $card_tier  'featured' (left column, large) or 'standard' (grid tile).
	 */
	function jdpower_leaders_render_leader_cell( $leader_id, $block_uid, $card_tier ) {
		$leader_id = (int) $leader_id;
		if ( $leader_id <= 0 || 'publish' !== get_post_status( $leader_id ) ) {
			return;
		}

		$image_id   = (int) get_post_thumbnail_id( $leader_id );
		$display    = jdpower_leaders_display_name( $leader_id );
		$job_raw    = function_exists( 'get_field' ) ? get_field( 'leader_job_title', $leader_id ) : '';
		$job_title  = is_string( $job_raw ) ? trim( $job_raw ) : '';
		$bio_raw    = function_exists( 'get_field' ) ? get_field( 'leader_bio', $leader_id ) : '';
		$bio_html   = is_string( $bio_raw ) ? wp_kses_post( $bio_raw ) : '';
		$file_field = function_exists( 'get_field' ) ? get_field( 'leader_bio_file', $leader_id ) : null;
		$file_url   = '';
		$file_label = '';
		if ( is_array( $file_field ) && ! empty( $file_field['url'] ) ) {
			$file_url = (string) $file_field['url'];
			if ( ! empty( $file_field['filename'] ) ) {
				$file_label = (string) $file_field['filename'];
			} else {
				$file_label = basename( (string) wp_parse_url( $file_url, PHP_URL_PATH ) );
			}
		}

		$dialog_id  = 'leader-dialog-' . $block_uid . '-' . $leader_id;
		$aria_label = function_exists( 'jdpower_pll__' )
			? sprintf( jdpower_pll__( 'Open biography for %s' ), $display )
			: sprintf( __( 'Open biography for %s', 'jdpower' ), $display );

		$is_featured_tier = ( 'featured' === $card_tier );
		$cell_class       = 'leaders-block__cell' . ( $is_featured_tier ? ' leaders-block__cell--featured-slot' : '' );
		$card_class       = 'leaders-block__card leaders-block__card--' . ( $is_featured_tier ? 'featured' : 'standard' );
		?>
		<div class="<?php echo esc_attr( $cell_class ); ?>">
			<div
				class="<?php echo esc_attr( $card_class ); ?>"
				role="button"
				tabindex="0"
				aria-haspopup="dialog"
				aria-controls="<?php echo esc_attr( $dialog_id ); ?>"
				aria-expanded="false"
				aria-label="<?php echo esc_attr( $aria_label ); ?>"
			>
				<div class="leaders-block__media">
					<?php if ( $image_id > 0 ) : ?>
						<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'leaders-block__img', 'alt' => '' ) ); ?>
					<?php else : ?>
						<span class="leaders-block__placeholder" aria-hidden="true"></span>
					<?php endif; ?>
				</div>
				<?php if ( '' !== $display ) : ?>
					<h2 class="<?php echo esc_attr( $is_featured_tier ? 'small' : 'xxxsmall' ); ?> leaders-block__name"><?php echo esc_html( $display ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $job_title ) : ?>
					<p class="leaders-block__title"><?php echo esc_html( $job_title ); ?></p>
				<?php endif; ?>
			</div>

			<dialog class="leaders-block__dialog" id="<?php echo esc_attr( $dialog_id ); ?>" aria-labelledby="<?php echo esc_attr( $dialog_id . '-title' ); ?>">
				<div class="leaders-block__dialog-surface">
					<button type="button" class="leaders-block__dialog-close" aria-label="<?php echo esc_attr__( 'Close dialog', 'jdpower' ); ?>">
						<span class="leaders-block__dialog-close-icon" aria-hidden="true">
							<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" focusable="false">
								<path d="M3.03553 2.5L22.1274 21.5919M2.5 21.5924L21.5919 2.50056" stroke="currentColor" stroke-width="5" stroke-linecap="round" />
							</svg>
						</span>
					</button>
					<div class="leaders-block__dialog-head leaders-block__dialog-head--has-heading">
						<div class="leaders-block__dialog-media">
							<?php if ( $image_id > 0 ) : ?>
								<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'leaders-block__dialog-img', 'alt' => '' ) ); ?>
							<?php else : ?>
								<span class="leaders-block__dialog-placeholder" aria-hidden="true"></span>
							<?php endif; ?>
						</div>
						<?php if ( '' !== $display ) : ?>
							<h2 class="small leaders-block__dialog-name" id="<?php echo esc_attr( $dialog_id . '-title' ); ?>"><?php echo esc_html( $display ); ?></h2>
						<?php endif; ?>
						<?php if ( '' !== $job_title ) : ?>
							<p class="leaders-block__dialog-title"><?php echo esc_html( $job_title ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $file_url ) : ?>
							<p class="leaders-block__dialog-download">
								<a class="leaders-block__dialog-download-link" href="<?php echo esc_url( $file_url ); ?>"<?php echo $file_label ? ' download="' . esc_attr( $file_label ) . '"' : ''; ?>>
									<?php echo esc_html( function_exists( 'jdpower_pll__' ) ? jdpower_pll__( 'Download Bio' ) : __( 'Download Bio', 'jdpower' ) ); ?>
									<span class="leaders-block__dialog-download-icon" aria-hidden="true">
										<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none" focusable="false">
											<path d="M2.24982 15H12.7502C13.9921 15 14.9982 13.9921 15 12.7502V9.74982C15 9.33585 14.6634 8.99928 14.2495 8.99928C13.8355 8.99928 13.4989 9.33585 13.4989 9.74982V12.7502C13.4989 13.1641 13.1641 13.4989 12.7502 13.4989H2.24982C1.83585 13.4989 1.50108 13.1641 1.50108 12.7502V9.74982C1.50108 9.33585 1.16451 8.99928 0.75054 8.99928C0.336573 8.99928 0 9.33585 0 9.74982V12.7502C0 13.9921 1.00792 14.9982 2.24982 15Z" fill="currentColor" />
											<path d="M3.7545 6.96904C3.46112 7.26062 3.46112 7.73758 3.7545 8.02916L6.96904 11.2419C6.96904 11.2419 6.98524 11.2509 6.99064 11.2563C7.28222 11.5371 7.74478 11.5299 8.02916 11.2419L11.2437 8.02916C11.3877 7.88877 11.4687 7.69618 11.4705 7.4964C11.4705 7.29662 11.3931 7.10403 11.2509 6.96184C11.1087 6.81965 10.9161 6.74046 10.7163 6.74226C10.5166 6.74226 10.324 6.82506 10.1836 6.96904L8.25054 8.90209V0.75054C8.25054 0.336573 7.91397 0 7.5 0C7.08603 0 6.74946 0.336573 6.74946 0.75054V8.90389L4.81462 6.96904C4.52304 6.67567 4.04608 6.67567 3.7545 6.96904Z" fill="currentColor" />
										</svg>
									</span>
								</a>
							</p>
						<?php endif; ?>
					</div>
					<?php if ( '' !== trim( wp_strip_all_tags( $bio_html ) ) ) : ?>
						<div class="leaders-block__dialog-bio">
							<?php echo $bio_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post() above ?>
						</div>
					<?php endif; ?>
				</div>
			</dialog>
		</div>
		<?php
	}
}

$classes = 'leaders-block';

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

$padding_top     = $acf_get( 'leaders_padding_top' );
$padding_bottom  = $acf_get( 'leaders_padding_bottom' );
$background_tone = $acf_get( 'leaders_background_tone' );
$photo_style     = $acf_get( 'leaders_photo_style' );

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
	$classes .= ' leaders-block--tone-dark';
} elseif ( 'medium' === $background_tone ) {
	$classes .= ' leaders-block--tone-medium';
} else {
	$classes .= ' leaders-block--tone-light';
}

if ( 'greyscale' === $photo_style ) {
	$classes .= ' leaders-block--photos-greyscale';
}

$pre_heading = $acf_get( 'leaders_pre_heading' );
$heading_raw = $acf_get( 'leaders_heading' );
$display_mode = $acf_get( 'leaders_display_mode' );
if ( empty( $display_mode ) || ! in_array( $display_mode, array( 'dynamic', 'manual' ), true ) ) {
	$display_mode = 'dynamic';
}

$has_pre     = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_heading = is_string( $heading_raw ) && '' !== trim( wp_strip_all_tags( (string) $heading_raw ) );

$sections = array();

if ( 'dynamic' === $display_mode && $acf_block_id ) {
	$dyn_args = array( 'leaders_dynamic_rows', $acf_block_id );
	if ( call_user_func_array( 'have_rows', $dyn_args ) ) {
		while ( call_user_func_array( 'have_rows', $dyn_args ) ) {
			the_row();
			$term_raw = get_sub_field( 'leaders_row_leader_type' );
			$term_id  = 0;
			if ( is_numeric( $term_raw ) ) {
				$term_id = (int) $term_raw;
			} elseif ( is_array( $term_raw ) && ! empty( $term_raw[0] ) && is_numeric( $term_raw[0] ) ) {
				$term_id = (int) $term_raw[0];
			} elseif ( is_object( $term_raw ) && isset( $term_raw->term_id ) ) {
				$term_id = (int) $term_raw->term_id;
			}
			$sort = get_sub_field( 'leaders_row_sort_order' );
			$sort = is_string( $sort ) ? $sort : 'last_name_asc';
			$heading_override = get_sub_field( 'leaders_row_section_heading' );
			$heading_override = is_string( $heading_override ) ? trim( $heading_override ) : '';

			$term_default_heading = '';
			if ( $term_id > 0 ) {
				$type_term = get_term( $term_id, 'leader_type' );
				if ( $type_term && ! is_wp_error( $type_term ) ) {
					$term_default_heading = $type_term->name;
				}
			}
			$sec_heading = '' !== $heading_override ? $heading_override : $term_default_heading;

			$post_ids = jdpower_leaders_leader_ids_for_term( $term_id, $sort );
			$sections[] = array(
				'heading'  => $sec_heading,
				'post_ids' => $post_ids,
			);
		}
	}
} elseif ( 'manual' === $display_mode && $acf_block_id ) {
	$man_args = array( 'leaders_manual_rows', $acf_block_id );
	if ( call_user_func_array( 'have_rows', $man_args ) ) {
		while ( call_user_func_array( 'have_rows', $man_args ) ) {
			the_row();
			$sec_heading = get_sub_field( 'leaders_manual_section_heading' );
			$sec_heading = is_string( $sec_heading ) ? trim( $sec_heading ) : '';
			$selected    = get_sub_field( 'leaders_manual_leaders' );
			if ( ! is_array( $selected ) ) {
				$selected = array();
			}
			$post_ids = array_values(
				array_filter(
					array_map(
						static function ( $id ) {
							$id = (int) $id;
							return ( $id > 0 && 'publish' === get_post_status( $id ) ) ? $id : 0;
						},
						$selected
					)
				)
			);
			$post_ids   = jdpower_leaders_prioritize_featured_ids( $post_ids );
			$sections[] = array(
				'heading'  => $sec_heading,
				'post_ids' => $post_ids,
			);
		}
	}
}

$has_any_leaders = false;
foreach ( $sections as $sec ) {
	if ( ! empty( $sec['post_ids'] ) ) {
		$has_any_leaders = true;
		break;
	}
}

$block_uid = isset( $block['id'] ) && is_string( $block['id'] )
	? preg_replace( '/[^a-zA-Z0-9_-]/', '-', $block['id'] )
	: '';
if ( '' === $block_uid ) {
	$block_uid = 'leaders-' . wp_unique_id();
}

if ( ! $has_pre && ! $has_heading && ! $has_any_leaders ) {
	return;
}

?>
<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_pre || $has_heading ) : ?>
			<div class="leaders-block__intro">
				<?php if ( $has_pre ) : ?>
					<p class="leaders-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
				<?php endif; ?>
				<?php if ( $has_heading ) : ?>
					<h2 class="leaders-block__heading"><?php echo wp_kses_post( $heading_raw ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $sections as $sec_index => $sec ) : ?>
			<?php
			$post_ids = isset( $sec['post_ids'] ) ? $sec['post_ids'] : array();
			if ( empty( $post_ids ) ) {
				continue;
			}
			$sec_heading = isset( $sec['heading'] ) ? $sec['heading'] : '';
			?>
			<?php
			$featured_ids = array();
			$standard_ids = array();
			foreach ( $post_ids as $lid ) {
				$lid = (int) $lid;
				if ( $lid <= 0 ) {
					continue;
				}
				if ( jdpower_leaders_is_featured( $lid ) ) {
					$featured_ids[] = $lid;
				} else {
					$standard_ids[] = $lid;
				}
			}
			// First featured only in the hero column (sort order preserved). Other featured + everyone else use the grid.
			$primary_featured_id = null;
			$grid_ids            = array();
			if ( ! empty( $featured_ids ) ) {
				$primary_featured_id = (int) array_shift( $featured_ids );
				$grid_ids              = array_values(
					array_filter(
						array_map( 'intval', array_merge( $featured_ids, $standard_ids ) )
					)
				);
			}

			$section_classes = 'leaders-block__section';
			if ( $primary_featured_id > 0 ) {
				$section_classes .= ' leaders-block__section--with-featured';
			}
			$split_classes = 'leaders-block__split';
			if ( $primary_featured_id > 0 && empty( $grid_ids ) ) {
				$split_classes .= ' leaders-block__split--solo-featured';
			}
			?>
			<div class="<?php echo esc_attr( $section_classes ); ?>" data-leaders-section="<?php echo (int) $sec_index; ?>">
				<?php if ( is_string( $sec_heading ) && '' !== $sec_heading ) : ?>
					<p class="preheading leaders-block__section-title"><?php echo esc_html( $sec_heading ); ?></p>
				<?php endif; ?>
				<?php if ( $primary_featured_id > 0 ) : ?>
					<div class="<?php echo esc_attr( $split_classes ); ?>">
						<div class="leaders-block__featured-stack">
							<?php jdpower_leaders_render_leader_cell( $primary_featured_id, $block_uid, 'featured' ); ?>
						</div>
						<?php if ( ! empty( $grid_ids ) ) : ?>
							<div class="leaders-block__grid leaders-block__grid--with-featured-rest">
								<?php foreach ( $grid_ids as $gid ) : ?>
									<?php jdpower_leaders_render_leader_cell( (int) $gid, $block_uid, 'standard' ); ?>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="leaders-block__grid leaders-block__grid--uniform">
						<?php foreach ( $post_ids as $leader_id ) : ?>
							<?php jdpower_leaders_render_leader_cell( (int) $leader_id, $block_uid, 'standard' ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
