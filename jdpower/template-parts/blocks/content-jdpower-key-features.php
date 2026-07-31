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

$items_rows = array();
if ( is_array( $items_raw ) ) {
	foreach ( $items_raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$icon    = isset( $row['key_features_icon'] ) ? $row['key_features_icon'] : null;
		$image   = isset( $row['key_features_image'] ) ? $row['key_features_image'] : null;
		$heading = isset( $row['key_features_heading'] ) ? $row['key_features_heading'] : '';
		$copy    = isset( $row['key_features_copy'] ) ? $row['key_features_copy'] : '';

		$icon_id     = is_array( $icon ) && ! empty( $icon['ID'] ) ? (int) $icon['ID'] : 0;
		$image_id    = is_array( $image ) && ! empty( $image['ID'] ) ? (int) $image['ID'] : 0;
		$has_image   = $image_id > 0;
		$has_icon    = $icon_id > 0 && ! $has_image;
		$has_heading = is_string( $heading ) && '' !== trim( $heading );
		$has_copy    = is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) );

		if ( ! $has_image && ! $has_icon && ! $has_heading && ! $has_copy ) {
			continue;
		}

		$items_rows[] = array(
			'icon_id'     => $icon_id,
			'image_id'    => $image_id,
			'has_image'   => $has_image,
			'has_icon'    => $has_icon,
			'heading'     => $heading,
			'copy'        => $copy,
			'has_heading' => $has_heading,
			'has_copy'    => $has_copy,
		);
	}
}

$has_items   = count( $items_rows ) > 0;
$has_content = $has_pre || $has_items;

// Front end: hide empty block. Editor: keep a visible empty state so the block can be selected.
if ( ! $has_content && empty( $is_preview ) ) {
	return;
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<?php if ( $has_content ) : ?>
			<?php if ( $has_pre ) : ?>
				<p class="key-features-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
			<?php endif; ?>

			<?php if ( $has_items ) : ?>
				<div class="key-features-block__grid">
					<?php foreach ( $items_rows as $item ) : ?>
						<div class="key-features-block__column">
							<?php if ( $item['has_image'] ) : ?>
								<div class="key-features-block__figure key-features-block__figure--photo">
									<?php echo wp_get_attachment_image( $item['image_id'], 'large', false, array( 'class' => 'key-features-block__img' ) ); ?>
								</div>
							<?php elseif ( $item['has_icon'] ) : ?>
								<div class="key-features-block__figure key-features-block__figure--icon">
									<?php
									$icon_path = get_attached_file( $item['icon_id'] );
									$icon_svg  = '';
									if ( $icon_path && is_readable( $icon_path ) && 'svg' === strtolower( pathinfo( $icon_path, PATHINFO_EXTENSION ) ) ) {
										$icon_svg = file_get_contents( $icon_path );
										$icon_svg = ( false !== $icon_svg ) ? $icon_svg : '';
									}
									if ( $icon_svg ) {
										echo '<span class="key-features-block__icon">' . $icon_svg . '</span>';
									} else {
										echo wp_get_attachment_image( $item['icon_id'], 'medium', false, array( 'class' => 'key-features-block__icon' ) );
									}
									?>
								</div>
							<?php endif; ?>

							<?php if ( $item['has_heading'] ) : ?>
								<h2 class="key-features-block__heading small"><?php echo nl2br( esc_html( trim( $item['heading'] ) ) ); ?></h2>
							<?php endif; ?>

							<?php if ( $item['has_copy'] ) : ?>
								<div class="key-features-block__copy">
									<?php echo wp_kses_post( $item['copy'] ); ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<?php
			jdpower_acf_block_editor_placeholder(
				__( 'Add a pre-heading or features in the block sidebar.', 'jdpower' ),
				$block
			);
			?>
		<?php endif; ?>
	</div>
</section>
