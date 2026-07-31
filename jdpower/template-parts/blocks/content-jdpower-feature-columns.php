<?php
/**
 * Block Name: Feature Columns
 *
 * @package jdpower
 */

$classes = 'feature-columns-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'feature_columns_padding_top' );
$padding_bottom  = get_field( 'feature_columns_padding_bottom' );
$background_tone = get_field( 'feature_columns_background_tone' );
$heading_size    = get_field( 'feature_columns_heading_size' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'default';
}
$row_heading_classes = array( 'feature-columns-block__heading' );
if ( 'default' !== $heading_size ) {
	$row_heading_classes[] = $heading_size;
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
	$classes .= ' feature-columns-block--tone-dark';
} else {
	$classes .= ' feature-columns-block--tone-light';
}

$items_raw = get_field( 'feature_columns_items' );
$rows      = array();

if ( is_array( $items_raw ) ) {
	foreach ( $items_raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$pre_heading = isset( $row['feature_columns_pre_heading'] ) ? $row['feature_columns_pre_heading'] : '';
		$image       = isset( $row['feature_columns_image'] ) ? $row['feature_columns_image'] : null;
		$heading     = isset( $row['feature_columns_heading'] ) ? $row['feature_columns_heading'] : '';
		$copy        = isset( $row['feature_columns_copy'] ) ? $row['feature_columns_copy'] : '';

		$image_id    = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
		$has_image   = $image_id > 0;
		$has_pre     = is_string( $pre_heading ) && '' !== trim( $pre_heading );
		$has_heading = is_string( $heading ) && '' !== trim( wp_strip_all_tags( (string) $heading ) );
		$has_copy    = is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) );

		if ( ! $has_image && ! $has_pre && ! $has_heading && ! $has_copy ) {
			continue;
		}

		$rows[] = array(
			'pre_heading' => $pre_heading,
			'image_id'    => $image_id,
			'heading'     => $heading,
			'copy'        => $copy,
			'has_image'   => $has_image,
			'has_pre'     => $has_pre,
			'has_heading' => $has_heading,
			'has_copy'    => $has_copy,
		);
	}
}

$has_content = count( $rows ) > 0;

// Front end: hide empty block. Editor: keep a visible empty state so the block can be selected.
if ( ! $has_content && empty( $is_preview ) ) {
	return;
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_content ) : ?>
			<div class="feature-columns-block__rows">
				<?php foreach ( $rows as $row_index => $row ) : ?>
					<?php
					$image_left = ( $row_index % 2 === 1 );
					$row_mods   = array( 'feature-columns-block__row' );
					if ( ! $row['has_image'] ) {
						$row_mods[] = 'feature-columns-block__row--text-only';
					} elseif ( $image_left ) {
						$row_mods[] = 'feature-columns-block__row--image-left';
					} else {
						$row_mods[] = 'feature-columns-block__row--image-right';
					}
					?>
					<div class="<?php echo esc_attr( implode( ' ', $row_mods ) ); ?>">
						<?php if ( $row['has_image'] && $image_left ) : ?>
							<div class="feature-columns-block__media">
								<div class="feature-columns-block__figure">
									<?php echo wp_get_attachment_image( $row['image_id'], 'large', false, array( 'class' => 'feature-columns-block__img' ) ); ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="feature-columns-block__text">
							<?php if ( $row['has_pre'] ) : ?>
								<p class="feature-columns-block__pre preheading"><?php echo esc_html( trim( $row['pre_heading'] ) ); ?></p>
							<?php endif; ?>

							<?php if ( $row['has_heading'] ) : ?>
								<h2 class="<?php echo esc_attr( implode( ' ', $row_heading_classes ) ); ?>"><?php echo wp_kses_post( trim( (string) $row['heading'] ) ); ?></h2>
							<?php endif; ?>

							<?php if ( $row['has_copy'] ) : ?>
								<div class="feature-columns-block__copy">
									<?php echo wp_kses_post( $row['copy'] ); ?>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( $row['has_image'] && ! $image_left ) : ?>
							<div class="feature-columns-block__media">
								<div class="feature-columns-block__figure">
									<?php echo wp_get_attachment_image( $row['image_id'], 'large', false, array( 'class' => 'feature-columns-block__img' ) ); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<?php
			jdpower_acf_block_editor_placeholder(
				__( 'Add feature columns in the block sidebar.', 'jdpower' ),
				$block
			);
			?>
		<?php endif; ?>
	</div>
</section>
