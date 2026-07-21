<?php
/**
 * Block Name: Featured Pages
 *
 * @package jdpower
 */

if ( ! function_exists( 'jdpower_featured_pages_card_from_page' ) ) {
	/**
	 * Build card data from a post (page, segment, or solution) using Card Details fields when set.
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

if ( ! function_exists( 'jdpower_featured_pages_render_card_inner' ) ) {
	/**
	 * Echo media + body for one featured page card.
	 *
	 * @param array $card                       Card data.
	 * @param bool  $compact_card_typography     When true (four-column grid), card title uses h2.xsmall (three-column uses h2.small).
	 * @param bool  $display_explore             Show “Explore …” arrow line (linked cards from page selection).
	 */
	function jdpower_featured_pages_render_card_inner( $card, $compact_card_typography = false, $display_explore = false ) {
		$image_id    = isset( $card['image_id'] ) ? (int) $card['image_id'] : 0;
		$heading     = isset( $card['heading'] ) ? $card['heading'] : '';
		$sub_heading = isset( $card['sub_heading'] ) ? $card['sub_heading'] : '';
		$copy_html   = isset( $card['copy_html'] ) ? $card['copy_html'] : '';
		$explore_raw = isset( $card['explore_target'] ) ? $card['explore_target'] : '';

		$heading     = is_string( $heading ) ? trim( $heading ) : '';
		$sub_heading = is_string( $sub_heading ) ? trim( $sub_heading ) : '';
		$explore_raw = is_string( $explore_raw ) ? trim( $explore_raw ) : '';

		$explore_text = '';
		if ( $display_explore && '' !== $explore_raw ) {
			$explore_text = jdpower_format_explore_cta( $explore_raw );
		}

		$title_size_class = $compact_card_typography ? 'xsmall' : 'small';
		?>
		<span class="featured-pages-block__media">
			<?php if ( $image_id > 0 ) : ?>
				<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'featured-pages-block__img' ) ); ?>
			<?php else : ?>
				<span class="featured-pages-block__placeholder" aria-hidden="true"></span>
			<?php endif; ?>
		</span>
		<span class="featured-pages-block__body">
			<?php if ( '' !== $heading ) : ?>
				<h2 class="<?php echo esc_attr( $title_size_class . ' featured-pages-block__card-title' ); ?>"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $sub_heading ) : ?>
				<h3 class="small featured-pages-block__card-sub"><?php echo esc_html( $sub_heading ); ?></h3>
			<?php endif; ?>
			<?php if ( is_string( $copy_html ) && '' !== trim( wp_strip_all_tags( $copy_html ) ) ) : ?>
				<span class="featured-pages-block__card-copy"><?php echo $copy_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses at build ?></span>
			<?php endif; ?>
			<?php if ( '' !== $explore_text ) : ?>
				<span class="arrow-link featured-pages-block__card-explore"><?php echo esc_html( $explore_text ); ?></span>
			<?php endif; ?>
		</span>
		<?php
	}
}

if ( ! function_exists( 'jdpower_featured_pages_render_card' ) ) {
	/**
	 * Echo one featured page card (linked from page or static from custom repeater).
	 *
	 * @param array  $card                   Card data.
	 * @param bool   $as_link                Whether to wrap in a link.
	 * @param string $url                    URL when $as_link is true.
	 * @param bool   $compact_card_typography When true (four-column grid), tighter card heading and sub styles.
	 */
	function jdpower_featured_pages_render_card( $card, $as_link = true, $url = '', $compact_card_typography = false ) {
		$url = is_string( $url ) ? $url : '';
		if ( $as_link && '' === $url ) {
			return;
		}

		$display_explore = $as_link && ! empty( $card['explore_target'] );

		if ( $as_link ) {
			?>
			<a class="featured-pages-block__card" href="<?php echo esc_url( $url ); ?>">
				<?php jdpower_featured_pages_render_card_inner( $card, $compact_card_typography, $display_explore ); ?>
			</a>
			<?php
		} else {
			?>
			<div class="featured-pages-block__card featured-pages-block__card--static">
				<?php jdpower_featured_pages_render_card_inner( $card, $compact_card_typography, false ); ?>
			</div>
			<?php
		}
	}
}

$classes = 'featured-pages-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'featured_pages_padding_top' );
$padding_bottom  = get_field( 'featured_pages_padding_bottom' );
$background_tone = get_field( 'featured_pages_background_tone' );
$source          = get_field( 'featured_pages_source' );

if ( empty( $source ) || ! in_array( $source, array( 'manual_selection', 'custom' ), true ) ) {
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
	$classes .= ' featured-pages-block--tone-dark';
} else {
	$classes .= ' featured-pages-block--tone-light';
}

$classes .= ' featured-pages-block--source-' . $source;

$intro_column_ratio = get_field( 'featured_pages_intro_column_ratio' );
$valid_intro_ratios = array( '50_50', '60_40', '70_30' );
if ( empty( $intro_column_ratio ) || ! in_array( $intro_column_ratio, $valid_intro_ratios, true ) ) {
	$intro_column_ratio = '50_50';
}
$classes .= ' featured-pages-block--intro-ratio-' . str_replace( '_', '-', $intro_column_ratio );

$featured_pages_intro_col_classes = array(
	'50_50' => array( 'heading' => 'col-lg-6', 'copy' => 'col-lg-6' ),
	'60_40' => array( 'heading' => 'col-lg-7', 'copy' => 'col-lg-5' ),
	'70_30' => array( 'heading' => 'col-lg-8', 'copy' => 'col-lg-4' ),
);
$intro_split_cols = $featured_pages_intro_col_classes[ $intro_column_ratio ];

$pre_heading = get_field( 'featured_pages_pre_heading' );
$heading     = get_field( 'featured_pages_heading' );
$copy        = get_field( 'featured_pages_copy' );
$cta_link    = get_field( 'featured_pages_cta' );

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

if ( 'manual_selection' === $source ) {
	$selected = get_field( 'featured_pages_selected_pages' );
	if ( ! is_array( $selected ) ) {
		$selected = array();
	}
	$selected = array_map( 'intval', $selected );
	$selected = array_values( array_filter( $selected ) );

	foreach ( $selected as $post_id ) {
		$built = jdpower_featured_pages_card_from_page( $post_id );
		if ( null !== $built ) {
			$cards[] = $built;
		}
	}
} elseif ( 'custom' === $source && have_rows( 'featured_pages_items' ) ) {
	while ( have_rows( 'featured_pages_items' ) ) {
		the_row();
		$image   = get_sub_field( 'featured_pages_card_image' );
		$heading = get_sub_field( 'featured_pages_card_heading' );
		$sub     = get_sub_field( 'featured_pages_card_sub_heading' );
		$copy_f  = get_sub_field( 'featured_pages_card_copy' );

		$image_id  = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
		$heading   = is_string( $heading ) ? trim( $heading ) : '';
		$sub       = is_string( $sub ) ? trim( $sub ) : '';
		$copy_html = is_string( $copy_f ) ? wp_kses_post( $copy_f ) : '';

		if ( '' === $heading && '' === $sub && '' === trim( wp_strip_all_tags( $copy_html ) ) && $image_id <= 0 ) {
			continue;
		}

		$cards[] = array(
			'image_id'       => $image_id,
			'heading'        => $heading,
			'sub_heading'    => $sub,
			'copy_html'      => $copy_html,
			'url'            => '',
			'explore_target' => '',
		);
	}
}

$has_cards = ! empty( $cards );
$has_intro = $has_pre || $has_heading || $has_copy;

if ( ! $has_intro && ! $has_cards && ! $has_cta ) {
	return;
}

$intro_split = $has_heading && $has_copy;

$column_size_raw = get_field( 'featured_pages_column_size' );
$grid_cols       = ( 4 === (int) $column_size_raw ) ? 4 : 3;
$compact_card_typography = ( 4 === $grid_cols );

?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_intro ) : ?>
			<?php if ( $has_pre ) : ?>
				<p class="featured-pages-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
			<?php endif; ?>

			<?php if ( $intro_split ) : ?>
				<div class="row featured-pages-block__intro-row align-items-start align-items-lg-end">
					<div class="col-12 <?php echo esc_attr( $intro_split_cols['heading'] ); ?> featured-pages-block__intro-col featured-pages-block__intro-col--heading">
						<h2 class="featured-pages-block__heading"><?php echo wp_kses_post( $heading ); ?></h2>
					</div>
					<div class="col-12 <?php echo esc_attr( $intro_split_cols['copy'] ); ?> featured-pages-block__copy featured-pages-block__copy--aside">
						<?php echo wp_kses_post( $copy ); ?>
					</div>
				</div>
			<?php elseif ( $has_heading || $has_copy ) : ?>
				<div class="featured-pages-block__intro featured-pages-block__intro--stacked">
					<?php if ( $has_heading ) : ?>
						<h2 class="featured-pages-block__heading"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $has_copy ) : ?>
						<div class="featured-pages-block__copy">
							<?php echo wp_kses_post( $copy ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $has_cards ) : ?>
			<div class="featured-pages-block__grid featured-pages-block__grid--cols-<?php echo (int) $grid_cols; ?>">
				<?php
				foreach ( $cards as $card ) {
					if ( 'manual_selection' === $source ) {
						jdpower_featured_pages_render_card( $card, true, isset( $card['url'] ) ? $card['url'] : '', $compact_card_typography );
					} else {
						jdpower_featured_pages_render_card( $card, false, '', $compact_card_typography );
					}
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( $has_cta ) : ?>
			<div class="featured-pages-block__cta">
				<a class="arrow-link featured-pages-block__cta-link" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $cta_target_attr . $cta_rel_attr; ?>>
					<?php echo esc_html( trim( $cta_title ) ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
