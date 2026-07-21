<?php
/**
 * Block Name: Sub Brand Logos
 *
 * @package jdpower
 */

$classes = 'sub-brand-logos-block';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top       = get_field( 'sub_brand_logos_padding_top' );
$padding_bottom    = get_field( 'sub_brand_logos_padding_bottom' );
$background_tone   = get_field( 'sub_brand_logos_background_tone' );
$style             = get_field( 'sub_brand_logos_style' );
$columns           = get_field( 'sub_brand_logos_columns' );
$pre_heading     = get_field( 'sub_brand_logos_pre_heading' );
$heading         = get_field( 'sub_brand_logos_heading' );
$copy            = get_field( 'sub_brand_logos_copy' );
$logos_raw       = get_field( 'sub_brand_logos_logos' );

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
	$background_tone = 'medium';
}

if ( 'dark' === $background_tone ) {
	$classes .= ' sub-brand-logos-block--tone-dark';
} else {
	$classes .= ' sub-brand-logos-block--tone-light';
}

if ( empty( $style ) || ! in_array( $style, array( 'boxed', 'fluid' ), true ) ) {
	$style = 'boxed';
}
$classes .= ' sub-brand-logos-block--' . $style;

$inner_classes = array( 'sub-brand-logos-block__inner' );
if ( 'boxed' === $style ) {
	$inner_classes[] = 'bg-' . $background_tone;
} else {
	$classes .= ' bg-' . $background_tone;
}

if ( empty( $columns ) || ! in_array( (string) $columns, array( '1', '2', '3' ), true ) ) {
	$columns = '2';
}
$classes .= ' sub-brand-logos-block--cols-' . $columns;

$has_pre     = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_heading = is_string( $heading ) && '' !== trim( $heading );
$has_copy    = is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) );

$logo_items = array();
if ( is_array( $logos_raw ) ) {
	foreach ( $logos_raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$logo = isset( $row['sub_brand_logos_logo'] ) ? $row['sub_brand_logos_logo'] : null;
		$logo_id = is_array( $logo ) && ! empty( $logo['ID'] ) ? (int) $logo['ID'] : 0;
		if ( $logo_id > 0 ) {
			$logo_items[] = $logo_id;
		}
	}
}
$has_logos = count( $logo_items ) > 0;

$has_content = $has_pre || $has_heading || $has_copy || $has_logos;

?>

<?php if ( $has_content ) : ?>
<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<div class="<?php echo esc_attr( implode( ' ', $inner_classes ) ); ?>">
			<div class="sub-brand-logos-block__row">
			<?php if ( $has_pre || $has_heading || $has_copy ) : ?>
				<div class="sub-brand-logos-block__body">
					<?php if ( $has_pre ) : ?>
						<p class="sub-brand-logos-block__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
					<?php endif; ?>

					<?php if ( $has_heading ) : ?>
						<h2 class="sub-brand-logos-block__heading small"><?php echo esc_html( trim( $heading ) ); ?></h2>
					<?php endif; ?>

					<?php if ( $has_copy ) : ?>
						<div class="sub-brand-logos-block__copy">
							<?php echo wp_kses_post( $copy ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $has_logos ) : ?>
				<div class="sub-brand-logos-block__logos">
					<div class="sub-brand-logos-block__grid">
						<?php foreach ( $logo_items as $logo_id ) : ?>
							<div class="sub-brand-logos-block__logo">
								<?php echo wp_get_attachment_image( $logo_id, 'large', false, array( 'class' => 'sub-brand-logos-block__img' ) ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		</div>
	</div>
</section>
<?php endif; ?>
