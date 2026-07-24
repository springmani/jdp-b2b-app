<?php
/**
 * Block Name: Content With Image
 */

$classes = 'qa-sandbox';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'qa_sandbox_padding_top' );
$padding_bottom  = get_field( 'qa_sandbox_padding_bottom' );
$background_tone = get_field( 'qa_sandbox_background_tone' );

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
	$classes .= ' qa-sandbox--tone-dark';
} else {
	$classes .= ' qa-sandbox--tone-light';
}

$image_position = get_field( 'qa_sandbox_image_position' );
if ( empty( $image_position ) || ! in_array( $image_position, array( 'left', 'right' ), true ) ) {
	$image_position = 'left';
}
if ( 'right' === $image_position ) {
	$classes .= ' qa-sandbox--image-right';
}

$column_split = get_field( 'qa_sandbox_column_split' );
$valid_splits = array( 'half', 'image_wide', 'image_narrow', 'image_two_thirds', 'image_third' );
if ( empty( $column_split ) || ! in_array( $column_split, $valid_splits, true ) ) {
	$column_split = 'half';
}
$split_modifier = 'qa-sandbox__row--split-' . str_replace( '_', '-', $column_split );

$vertical_align = get_field( 'qa_sandbox_vertical_align' );
if ( empty( $vertical_align ) || ! in_array( $vertical_align, array( 'top', 'middle', 'bottom' ), true ) ) {
	$vertical_align = 'top';
}
$classes .= ' qa-sandbox--valign-' . $vertical_align;

$pre_heading_placement = get_field( 'qa_sandbox_pre_heading_placement' );
if ( 'top' !== $vertical_align ) {
	$pre_heading_placement = 'with_content';
}
if ( empty( $pre_heading_placement ) || ! in_array( $pre_heading_placement, array( 'with_content', 'above' ), true ) ) {
	$pre_heading_placement = 'with_content';
}

$pre_heading = get_field( 'qa_sandbox_pre_heading' );
$heading     = get_field( 'qa_sandbox_heading' );
$heading_size = get_field( 'qa_sandbox_heading_size' );
$copy        = get_field( 'qa_sandbox_copy' );
$copy_rows   = get_field( 'qa_sandbox_copy_rows' );
$image       = get_field( 'qa_sandbox_image' );
$image_aspect_ratio = get_field( 'qa_sandbox_image_aspect_ratio' );
$image_caption = get_field( 'qa_sandbox_image_caption' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'default';
}

$heading_classes = array( 'qa-sandbox__heading' );
if ( 'default' !== $heading_size ) {
	$heading_classes[] = $heading_size;
}
$heading_class_attr = esc_attr( implode( ' ', $heading_classes ) );
$cta_extra   = get_field( 'qa_sandbox_ctas' );
$cta_style   = get_field( 'qa_sandbox_cta_style' );

if ( empty( $cta_style ) || ! in_array( $cta_style, array( 'button', 'arrow' ), true ) ) {
	$cta_style = 'button';
}

$cta_links = array();
if ( is_array( $cta ) ) {
	$primary_url   = $cta['url'] ?? '';
	$primary_title = $cta['title'] ?? '';
	if ( $primary_url && $primary_title ) {
		$cta_links[] = $cta;
	}
}
if ( is_array( $cta_extra ) ) {
	foreach ( $cta_extra as $rep_row ) {
		if ( ! is_array( $rep_row ) ) {
			continue;
		}
		$item = isset( $rep_row['qa_sandbox_cta_item'] ) ? $rep_row['qa_sandbox_cta_item'] : null;
		if ( ! is_array( $item ) ) {
			continue;
		}
		$row_url   = $item['url'] ?? '';
		$row_title = $item['title'] ?? '';
		if ( $row_url && $row_title ) {
			$cta_links[] = $item;
		}
	}
}

$has_ctas = count( $cta_links ) > 0;

$image_id  = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
$has_image = $image_id > 0;

$has_media   = $has_image;

$figure_aspect_class = 'qa-sandbox__figure--aspect-square';
if ( $has_image ) {
	$aspect_map = array(
		'square' => 'qa-sandbox__figure--aspect-square',
		'16_9'   => 'qa-sandbox__figure--aspect-16-9',
		'auto'   => 'qa-sandbox__figure--aspect-auto',
	);
	if ( is_string( $image_aspect_ratio ) && isset( $aspect_map[ $image_aspect_ratio ] ) ) {
		$figure_aspect_class = $aspect_map[ $image_aspect_ratio ];
	}
}

$has_caption = is_string( $image_caption ) && '' !== trim( $image_caption );

$has_pre = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$pre_above_columns = $has_pre && 'top' === $vertical_align && 'above' === $pre_heading_placement;

$copy_rows_render = array();
if ( is_array( $copy_rows ) ) {
	foreach ( $copy_rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$row_sub   = isset( $row['qa_sandbox_copy_row_sub_heading'] ) ? $row['qa_sandbox_copy_row_sub_heading'] : '';
		$row_copy  = isset( $row['qa_sandbox_copy_row_copy'] ) ? $row['qa_sandbox_copy_row_copy'] : '';
		$list_cols = ! empty( $row['qa_sandbox_copy_row_list_columns'] );

		$sub_trim  = is_string( $row_sub ) ? trim( $row_sub ) : '';
		$copy_trim = is_string( $row_copy ) ? trim( wp_strip_all_tags( $row_copy ) ) : '';

		if ( '' === $sub_trim && '' === $copy_trim ) {
			continue;
		}

		$copy_rows_render[] = array(
			'sub_heading'   => is_string( $row_sub ) ? $row_sub : '',
			'copy'          => is_string( $row_copy ) ? $row_copy : '',
			'list_columns'  => $list_cols,
		);
	}
}
$use_copy_rows = count( $copy_rows_render ) > 0;
$has_legacy_copy = ! $use_copy_rows && is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) );

$layout_row_classes = array( 'qa-sandbox__row' );
if ( ! $has_media ) {
	$layout_row_classes[] = 'qa-sandbox__row--text-only';
} else {
	$layout_row_classes[] = ( 'right' === $image_position )
		? 'qa-sandbox__row--image-right'
		: 'qa-sandbox__row--image-left';
	$layout_row_classes[] = $split_modifier;
}
$layout_row_classes[] = 'qa-sandbox__row--valign-' . $vertical_align;

ob_start();
?>
<div class="qa-sandbox__media">

	<?php if ( $has_image ) : ?>
		<div class="<?php echo esc_attr( 'qa-sandbox__figure ' . $figure_aspect_class ); ?>">
			<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'qa-sandbox__img' ) ); ?>
		</div>
		<?php if ( $has_caption ) : ?>
			<p class="qa-sandbox__caption"><?php echo esc_html( trim( $image_caption ) ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php
$media_markup = ob_get_clean();
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $pre_above_columns ) : ?>
			<div class="qa-sandbox__pre-row">
				<p class="qa-sandbox__pre qa-sandbox__pre--above preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( implode( ' ', $layout_row_classes ) ); ?>">
			<?php if ( $has_media && 'right' === $image_position ) : ?>
				<div class="qa-sandbox__body">
					<?php if ( $has_pre && ! $pre_above_columns ) : ?>
						<p class="qa-sandbox__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $heading ) ) : ?>
						<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>

					<?php if ( $use_copy_rows ) : ?>
						<div class="qa-sandbox__copy-rows">
							<?php foreach ( $copy_rows_render as $cr ) : ?>
								<?php
								$copy_row_classes = array( 'qa-sandbox__copy-row' );
								if ( ! empty( $cr['list_columns'] ) ) {
									$copy_row_classes[] = 'qa-sandbox__copy-row--list-columns';
								}
								?>
								<div class="<?php echo esc_attr( implode( ' ', $copy_row_classes ) ); ?>">
									<?php if ( is_string( $cr['sub_heading'] ) && '' !== trim( $cr['sub_heading'] ) ) : ?>
										<h3 class="qa-sandbox__copy-row-heading"><?php echo esc_html( trim( $cr['sub_heading'] ) ); ?></h3>
									<?php endif; ?>
									<?php if ( is_string( $cr['copy'] ) && '' !== trim( wp_strip_all_tags( $cr['copy'] ) ) ) : ?>
										<div class="qa-sandbox__copy">
											<?php echo wp_kses_post( $cr['copy'] ); ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php elseif ( $has_legacy_copy ) : ?>
						<div class="qa-sandbox__copy">
							<?php echo wp_kses_post( $copy ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $has_ctas ) : ?>
						<div class="qa-sandbox__ctas">
							<?php foreach ( $cta_links as $cta_link ) : ?>
								<?php
								$link_url    = isset( $cta_link['url'] ) ? $cta_link['url'] : '';
								$link_title  = isset( $cta_link['title'] ) ? $cta_link['title'] : '';
								$link_target = isset( $cta_link['target'] ) ? $cta_link['target'] : '';
								$target_attr = '';
								$rel_attr    = '';
								if ( '_blank' === $link_target ) {
									$target_attr = ' target="_blank"';
									$rel_attr    = ' rel="noopener noreferrer"';
								}
								?>
								<?php if ( 'arrow' === $cta_style ) : ?>
									<a class="arrow-link qa-sandbox__link" href="<?php echo esc_url( $link_url ); ?>"<?php echo $target_attr . $rel_attr; ?>>
										<?php echo esc_html( $link_title ); ?>
									</a>
								<?php else : ?>
									<a class="btn btn-small qa-sandbox__btn" href="<?php echo esc_url( $link_url ); ?>"<?php echo $target_attr . $rel_attr; ?>>
										<?php echo esc_html( $link_title ); ?>
									</a>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php echo $media_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped when built. ?>

			<?php elseif ( $has_media ) : ?>
				<?php echo $media_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped when built. ?>

				<div class="qa-sandbox__body">
					<?php if ( $has_pre && ! $pre_above_columns ) : ?>
						<p class="qa-sandbox__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $heading ) ) : ?>
						<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>

					<?php if ( $use_copy_rows ) : ?>
						<div class="qa-sandbox__copy-rows">
							<?php foreach ( $copy_rows_render as $cr ) : ?>
								<?php
								$copy_row_classes = array( 'qa-sandbox__copy-row' );
								if ( ! empty( $cr['list_columns'] ) ) {
									$copy_row_classes[] = 'qa-sandbox__copy-row--list-columns';
								}
								?>
								<div class="<?php echo esc_attr( implode( ' ', $copy_row_classes ) ); ?>">
									<?php if ( is_string( $cr['sub_heading'] ) && '' !== trim( $cr['sub_heading'] ) ) : ?>
										<h3 class="qa-sandbox__copy-row-heading"><?php echo esc_html( trim( $cr['sub_heading'] ) ); ?></h3>
									<?php endif; ?>
									<?php if ( is_string( $cr['copy'] ) && '' !== trim( wp_strip_all_tags( $cr['copy'] ) ) ) : ?>
										<div class="qa-sandbox__copy">
											<?php echo wp_kses_post( $cr['copy'] ); ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php elseif ( $has_legacy_copy ) : ?>
						<div class="qa-sandbox__copy">
							<?php echo wp_kses_post( $copy ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $has_ctas ) : ?>
						<div class="qa-sandbox__ctas">
							<?php foreach ( $cta_links as $cta_link ) : ?>
								<?php
								$link_url    = isset( $cta_link['url'] ) ? $cta_link['url'] : '';
								$link_title  = isset( $cta_link['title'] ) ? $cta_link['title'] : '';
								$link_target = isset( $cta_link['target'] ) ? $cta_link['target'] : '';
								$target_attr = '';
								$rel_attr    = '';
								if ( '_blank' === $link_target ) {
									$target_attr = ' target="_blank"';
									$rel_attr    = ' rel="noopener noreferrer"';
								}
								?>
								<?php if ( 'arrow' === $cta_style ) : ?>
									<a class="arrow-link qa-sandbox__link" href="<?php echo esc_url( $link_url ); ?>"<?php echo $target_attr . $rel_attr; ?>>
										<?php echo esc_html( $link_title ); ?>
									</a>
								<?php else : ?>
									<a class="btn btn-small qa-sandbox__btn" href="<?php echo esc_url( $link_url ); ?>"<?php echo $target_attr . $rel_attr; ?>>
										<?php echo esc_html( $link_title ); ?>
									</a>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

			<?php else : ?>
				<div class="qa-sandbox__body">
					<?php if ( $has_pre && ! $pre_above_columns ) : ?>
						<p class="qa-sandbox__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $heading ) ) : ?>
						<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
					<?php endif; ?>

					<?php if ( $use_copy_rows ) : ?>
						<div class="qa-sandbox__copy-rows">
							<?php foreach ( $copy_rows_render as $cr ) : ?>
								<?php
								$copy_row_classes = array( 'qa-sandbox__copy-row' );
								if ( ! empty( $cr['list_columns'] ) ) {
									$copy_row_classes[] = 'qa-sandbox__copy-row--list-columns';
								}
								?>
								<div class="<?php echo esc_attr( implode( ' ', $copy_row_classes ) ); ?>">
									<?php if ( is_string( $cr['sub_heading'] ) && '' !== trim( $cr['sub_heading'] ) ) : ?>
										<h3 class="qa-sandbox__copy-row-heading"><?php echo esc_html( trim( $cr['sub_heading'] ) ); ?></h3>
									<?php endif; ?>
									<?php if ( is_string( $cr['copy'] ) && '' !== trim( wp_strip_all_tags( $cr['copy'] ) ) ) : ?>
										<div class="qa-sandbox__copy">
											<?php echo wp_kses_post( $cr['copy'] ); ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php elseif ( $has_legacy_copy ) : ?>
						<div class="qa-sandbox__copy">
							<?php echo wp_kses_post( $copy ); ?>
						</div>
					<?php endif; ?>

				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
