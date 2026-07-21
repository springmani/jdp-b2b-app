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

?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<?php if ( have_rows( 'feature_columns_items' ) ) : ?>
		<div class="container">
			<div class="feature-columns-block__rows">
				<?php
				$row_index = 0;
				while ( have_rows( 'feature_columns_items' ) ) :
					the_row();
					$pre_heading = get_sub_field( 'feature_columns_pre_heading' );
					$image       = get_sub_field( 'feature_columns_image' );
					$heading     = get_sub_field( 'feature_columns_heading' );
					$copy        = get_sub_field( 'feature_columns_copy' );

					$image_id    = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
					$has_image   = $image_id > 0;
					$has_pre     = is_string( $pre_heading ) && '' !== trim( $pre_heading );
					$has_heading = is_string( $heading ) && '' !== trim( wp_strip_all_tags( (string) $heading ) );
					$has_copy    = is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) );

					$image_left = ( $row_index % 2 === 1 );
					$row_mods   = array( 'feature-columns-block__row' );
					if ( ! $has_image ) {
						$row_mods[] = 'feature-columns-block__row--text-only';
					} elseif ( $image_left ) {
						$row_mods[] = 'feature-columns-block__row--image-left';
					} else {
						$row_mods[] = 'feature-columns-block__row--image-right';
					}
					?>
					<div class="<?php echo esc_attr( implode( ' ', $row_mods ) ); ?>">
						<?php if ( $has_image && $image_left ) : ?>
							<div class="feature-columns-block__media">
								<div class="feature-columns-block__figure">
									<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'feature-columns-block__img' ) ); ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="feature-columns-block__text">
							<?php if ( $has_pre ) : ?>
								<p class="feature-columns-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
							<?php endif; ?>

							<?php if ( $has_heading ) : ?>
								<h2 class="<?php echo esc_attr( implode( ' ', $row_heading_classes ) ); ?>"><?php echo wp_kses_post( trim( (string) $heading ) ); ?></h2>
							<?php endif; ?>

							<?php if ( $has_copy ) : ?>
								<div class="feature-columns-block__copy">
									<?php echo wp_kses_post( $copy ); ?>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( $has_image && ! $image_left ) : ?>
							<div class="feature-columns-block__media">
								<div class="feature-columns-block__figure">
									<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'feature-columns-block__img' ) ); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<?php
					$row_index++;
				endwhile;
				?>
			</div>
		</div>
	<?php endif; ?>
</section>
