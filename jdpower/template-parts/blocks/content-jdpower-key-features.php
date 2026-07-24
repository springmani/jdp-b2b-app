<?php
/**
 * Block Name: Key Features
 *
 * @package jdpower
 */

$classes = 'key-features-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'key_features_padding_top' );
$padding_bottom  = get_field( 'key_features_padding_bottom' );
$background_tone = get_field( 'key_features_background_tone' );
$columns         = get_field( 'key_features_columns' );
$pre_heading     = get_field( 'key_features_pre_heading' );
$items_raw       = get_field( 'key_features_items' );

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
	$classes .= ' key-features-block--tone-dark';
} else {
	$classes .= ' key-features-block--tone-light';
}

if ( empty( $columns ) || ! in_array( (string) $columns, array( '2', '3' ), true ) ) {
	$columns = '3';
}
$classes .= ' key-features-block--cols-' . $columns;

$has_pre = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_items = is_array( $items_raw ) && ! empty( $items_raw );

?>

<?php if ( $has_pre || $has_items ) : ?>
<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_pre ) : ?>
			<p class="key-features-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
		<?php endif; ?>

		<?php if ( $has_items && have_rows( 'key_features_items' ) ) : ?>
			<div class="key-features-block__grid">
				<?php
				while ( have_rows( 'key_features_items' ) ) :
					the_row();
					$icon    = get_sub_field( 'key_features_icon' );
					$image   = get_sub_field( 'key_features_image' );
					$heading = get_sub_field( 'key_features_heading' );
					$copy    = get_sub_field( 'key_features_copy' );

					$icon_id     = is_array( $icon ) && ! empty( $icon['ID'] ) ? (int) $icon['ID'] : 0;
					$image_id    = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
					$has_image   = $image_id > 0;
					$has_icon    = $icon_id > 0 && ! $has_image;
					$has_heading = is_string( $heading ) && '' !== trim( $heading );
					$has_copy    = is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) );
					?>
					<div class="key-features-block__column">
						<?php if ( $has_image ) : ?>
							<div class="key-features-block__figure key-features-block__figure--photo">
								<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'key-features-block__img' ) ); ?>
							</div>
						<?php elseif ( $has_icon ) : ?>
							<div class="key-features-block__figure key-features-block__figure--icon">
								<?php
								$icon_path = get_attached_file( $icon_id );
								$icon_svg    = '';
								if ( $icon_path && is_readable( $icon_path ) && 'svg' === strtolower( pathinfo( $icon_path, PATHINFO_EXTENSION ) ) ) {
									$icon_svg = file_get_contents( $icon_path );
									$icon_svg = ( false !== $icon_svg ) ? $icon_svg : '';
								}
								if ( $icon_svg ) {
									echo '<span class="key-features-block__icon">' . $icon_svg . '</span>';
								} else {
									echo wp_get_attachment_image( $icon_id, 'medium', false, array( 'class' => 'key-features-block__icon' ) );
								}
								?>
							</div>
						<?php endif; ?>

						<?php if ( $has_heading ) : ?>
							<h2 class="key-features-block__heading small"><?php echo nl2br( esc_html( trim( $heading ) ) ); ?></h2>
						<?php endif; ?>

						<?php if ( $has_copy ) : ?>
							<div class="key-features-block__copy">
								<?php echo wp_kses_post( $copy ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>
