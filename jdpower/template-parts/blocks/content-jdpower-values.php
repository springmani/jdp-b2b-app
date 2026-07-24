<?php
/**
 * Block Name: Values
 *
 * @package jdpower
 */

$classes = 'values-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top       = get_field( 'values_padding_top' );
$padding_bottom    = get_field( 'values_padding_bottom' );
$background_tone   = get_field( 'values_background_tone' );
$animate_cards     = function_exists( 'jdpower_acf_flag_on' ) ? jdpower_acf_flag_on( get_field( 'values_animate_cards' ) ) : true;
$intro_layout       = get_field( 'values_intro_layout' );
$intro_column_ratio = get_field( 'values_intro_column_ratio' );
$pre_heading        = get_field( 'values_pre_heading' );
$heading           = get_field( 'values_heading' );
$section_copy      = get_field( 'values_copy' );
$stacked_alignment   = get_field( 'values_intro_stacked_alignment' );
$content_alignment   = get_field( 'values_content_alignment' );
$content_vertical    = get_field( 'values_content_vertical_alignment' );
$column_alignment    = get_field( 'values_column_alignment' );

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
	$classes .= ' values-block--tone-dark';
} elseif ( 'medium' === $background_tone ) {
	$classes .= ' values-block--tone-medium';
} else {
	$classes .= ' values-block--tone-light';
}

if ( empty( $intro_layout ) || ! in_array( $intro_layout, array( 'stacked', 'columns' ), true ) ) {
	$intro_layout = 'stacked';
}
$classes .= ' values-block--intro-layout-' . $intro_layout;

$valid_intro_ratios = array( '50_50', '60_40', '70_30', '80_20' );
if ( empty( $intro_column_ratio ) || ! in_array( $intro_column_ratio, $valid_intro_ratios, true ) ) {
	$intro_column_ratio = '50_50';
}
$values_intro_col_classes = array(
	'50_50' => array( 'heading' => 'col-lg-6', 'copy' => 'col-lg-6' ),
	'60_40' => array( 'heading' => 'col-lg-7', 'copy' => 'col-lg-5' ),
	'70_30' => array( 'heading' => 'col-lg-8', 'copy' => 'col-lg-4' ),
	'80_20' => array( 'heading' => 'col-lg-10', 'copy' => 'col-lg-2' ),
);
$intro_split_cols = $values_intro_col_classes[ $intro_column_ratio ];
if ( 'columns' === $intro_layout ) {
	$classes .= ' values-block--intro-ratio-' . str_replace( '_', '-', $intro_column_ratio );
}

$has_pre     = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_heading = is_string( $heading ) && '' !== trim( $heading );
$has_copy    = is_string( $section_copy ) && '' !== trim( wp_strip_all_tags( (string) $section_copy ) );

$stacked_align_class = 'values-block__intro--align-center';
if ( 'stacked' === $intro_layout ) {
	if ( empty( $stacked_alignment ) || ! in_array( $stacked_alignment, array( 'left', 'center', 'right' ), true ) ) {
		$stacked_alignment = 'center';
	}
	$stacked_align_class = 'values-block__intro--align-' . $stacked_alignment;
}

if ( empty( $content_alignment ) || ! in_array( $content_alignment, array( 'left', 'center', 'right' ), true ) ) {
	$content_alignment = 'center';
}
$classes .= ' values-block--content-align-' . $content_alignment;

if ( empty( $content_vertical ) || ! in_array( $content_vertical, array( 'top', 'center', 'bottom' ), true ) ) {
	$content_vertical = 'center';
}
$classes .= ' values-block--content-valign-' . $content_vertical;

if ( 'start' === $column_alignment ) {
	$column_alignment = 'left';
} elseif ( 'end' === $column_alignment ) {
	$column_alignment = 'right';
} elseif ( in_array( $column_alignment, array( 'between', 'around', 'evenly' ), true ) ) {
	$column_alignment = 'center';
}

$values_column_justify_classes = array(
	'left'   => 'justify-content-start',
	'center' => 'justify-content-center',
	'right'  => 'justify-content-end',
);
if ( empty( $column_alignment ) || ! isset( $values_column_justify_classes[ $column_alignment ] ) ) {
	$column_alignment = 'center';
}
$values_row_justify_class = $values_column_justify_classes[ $column_alignment ];

$items_rows = array();
$items_raw  = get_field( 'values_items' );
if ( is_array( $items_raw ) ) {
	foreach ( $items_raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$title    = isset( $row['values_item_title'] ) ? trim( (string) $row['values_item_title'] ) : '';
		$copy_raw = isset( $row['values_item_copy'] ) ? $row['values_item_copy'] : '';
		$copy_raw = is_string( $copy_raw ) ? $copy_raw : '';
		$has_card_copy = '' !== trim( wp_strip_all_tags( $copy_raw ) );
		$link_arr      = isset( $row['values_item_link'] ) && is_array( $row['values_item_link'] ) ? $row['values_item_link'] : array();
		if ( '' === $title && ! $has_card_copy ) {
			continue;
		}
		$items_rows[] = array(
			'title'     => $title,
			'copy_html' => $copy_raw,
			'link'      => $link_arr,
		);
	}
}

$has_items = count( $items_rows ) > 0;

if ( ! $has_pre && ! $has_heading && ! $has_copy && ! $has_items ) {
	return;
}

$intro_solo_class = $has_items ? '' : ' values-block__intro--solo';
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_pre || $has_heading || $has_copy ) : ?>
			<?php if ( 'columns' === $intro_layout ) : ?>
				<header class="values-block__intro values-block__intro--columns<?php echo esc_attr( $intro_solo_class ); ?>">
					<?php if ( $has_pre ) : ?>
						<p class="values-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
					<?php endif; ?>
					<?php if ( $has_heading || $has_copy ) : ?>
						<div class="row values-block__intro-row align-items-start align-items-lg-end">
							<?php if ( $has_heading ) : ?>
								<div class="col-12 <?php echo ( $has_copy ) ? esc_attr( $intro_split_cols['heading'] ) : 'col-lg-12'; ?> values-block__intro-col values-block__intro-col--heading">
									<h2 class="values-block__heading"><?php echo wp_kses_post( $heading ); ?></h2>
								</div>
							<?php endif; ?>
							<?php if ( $has_copy ) : ?>
								<div class="col-12 <?php echo ( $has_heading ) ? esc_attr( $intro_split_cols['copy'] ) : 'col-lg-12'; ?> values-block__intro-col values-block__intro-col--copy">
									<div class="values-block__copy">
										<?php echo wp_kses_post( $section_copy ); ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</header>
			<?php else : ?>
				<header class="values-block__intro values-block__intro--stacked <?php echo esc_attr( $stacked_align_class ); ?><?php echo esc_attr( $intro_solo_class ); ?>">
					<?php if ( $has_pre ) : ?>
						<p class="values-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
					<?php endif; ?>
					<?php if ( $has_heading ) : ?>
						<h2 class="values-block__heading"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>
					<?php if ( $has_copy ) : ?>
						<div class="values-block__copy">
							<?php echo wp_kses_post( $section_copy ); ?>
						</div>
					<?php endif; ?>
				</header>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $has_items ) : ?>
			<div class="values-block__cards">
				<div class="row values-block__row <?php echo esc_attr( $values_row_justify_class ); ?>">
					<?php foreach ( $items_rows as $card_index => $row ) : ?>
						<?php
						$link_arr        = isset( $row['link'] ) && is_array( $row['link'] ) ? $row['link'] : array();
						$card_url        = isset( $link_arr['url'] ) ? trim( (string) $link_arr['url'] ) : '';
						$target          = isset( $link_arr['target'] ) ? (string) $link_arr['target'] : '';
						$card_item_index = (int) $card_index + 1;
						$value_col_reveal = $animate_cards ? ' jdp-animate-view-fade-in-up' : '';
						?>
					<div class="col-12 col-lg-4<?php echo esc_attr( $value_col_reveal ); ?>">
						<?php if ( '' !== $card_url ) : ?>
						<a class="values-block__card" href="<?php echo esc_url( $card_url ); ?>"
							<?php echo '_blank' === $target ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
						>
						<?php else : ?>
						<article class="values-block__card">
						<?php endif; ?>
							<span class="values-block__card-index" aria-hidden="true"><?php echo esc_html( (string) $card_item_index ); ?></span>
							<?php if ( '' !== $row['title'] ) : ?>
								<h2 class="values-block__card-title small"><?php echo esc_html( $row['title'] ); ?></h2>
							<?php endif; ?>
							<?php if ( '' !== trim( wp_strip_all_tags( $row['copy_html'] ) ) ) : ?>
								<div class="values-block__card-copy">
									<?php echo wp_kses_post( $row['copy_html'] ); ?>
								</div>
							<?php endif; ?>
						<?php if ( '' !== $card_url ) : ?>
						</a>
						<?php else : ?>
						</article>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
