<?php
/**
 * Block Name: Info Banner
 *
 * Slim strip: optional icon image, uppercase heading, body copy.
 *
 * @package jdpower
 */

$classes = 'info-banner';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$icon_raw    = function_exists( 'get_field' ) ? get_field( 'info_banner_icon' ) : '';
$heading_raw = function_exists( 'get_field' ) ? get_field( 'info_banner_heading' ) : '';
$copy_raw    = function_exists( 'get_field' ) ? get_field( 'info_banner_copy' ) : '';

$icon_id = is_array( $icon_raw ) && ! empty( $icon_raw['ID'] ) ? (int) $icon_raw['ID'] : 0;

$heading = is_string( $heading_raw ) ? trim( $heading_raw ) : '';
$copy    = is_string( $copy_raw ) ? trim( $copy_raw ) : '';

$has_content = '' !== $heading || '' !== trim( wp_strip_all_tags( $copy ) ) || $icon_id > 0;

// Front end: hide empty block. Editor: keep a visible empty state so the block can be selected.
if ( ! $has_content && empty( $is_preview ) ) {
	return;
}

if ( ! $has_content ) {
	?>
	<section class="<?php echo esc_attr( $classes ); ?>">
		<div class="container">
			<?php
			jdpower_acf_block_editor_placeholder(
				__( 'Add an icon, heading, or copy in the block sidebar.', 'jdpower' ),
				$block
			);
			?>
		</div>
	</section>
	<?php
	return;
}

$has_icon_text_neighbors = ( '' !== $heading || '' !== trim( wp_strip_all_tags( $copy ) ) );
$icon_alt                = '';
if ( ! $has_icon_text_neighbors && $icon_id > 0 ) {
	$icon_alt_meta = get_post_meta( $icon_id, '_wp_attachment_image_alt', true );
	$icon_alt      = is_string( $icon_alt_meta ) ? trim( $icon_alt_meta ) : '';
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<div class="info-banner__inner">
			<?php if ( $icon_id > 0 ) : ?>
				<span class="info-banner__icon"<?php echo $has_icon_text_neighbors ? ' aria-hidden="true"' : ''; ?>>
					<?php
					echo wp_get_attachment_image(
						$icon_id,
						'full',
						false,
						array(
							'class'    => 'info-banner__icon-img',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'alt'      => $has_icon_text_neighbors ? '' : $icon_alt,
						)
					);
					?>
				</span>
			<?php endif; ?>
			<div class="info-banner__text">
				<?php if ( '' !== $heading ) : ?>
					<h2 class="xsmaller info-banner__heading"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $copy ) : ?>
					<div class="info-banner__copy"><?php echo wp_kses_post( wpautop( $copy ) ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
