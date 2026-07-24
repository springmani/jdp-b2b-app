<?php
/**
 * Block Name: Content Banner
 */

$classes = 'content-banner';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'content_banner_padding_top' );
$padding_bottom  = get_field( 'content_banner_padding_bottom' );
$background_tone = get_field( 'content_banner_background_tone' );

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

if ( empty( $background_tone ) || ! in_array( $background_tone, array( 'dark', 'gradient', 'medium', 'light' ), true ) ) {
	$background_tone = 'dark';
}

if ( 'gradient' === $background_tone ) {
	$classes .= ' content-banner--bg-gradient';
	$classes .= ' content-banner--tone-dark';
} else {
	$classes .= ' bg-' . $background_tone;

	if ( 'dark' === $background_tone ) {
		$classes .= ' content-banner--tone-dark';
	} elseif ( 'medium' === $background_tone ) {
		$classes .= ' content-banner--tone-medium';
	} else {
		$classes .= ' content-banner--tone-light';
	}
}

$pre_heading   = get_field( 'content_banner_pre_heading' );
$heading       = get_field( 'content_banner_heading' );
$heading_size  = get_field( 'content_banner_heading_size' );
$copy          = get_field( 'content_banner_copy' );
$cta           = get_field( 'content_banner_cta' );
$cta_style     = get_field( 'content_banner_cta_style' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'large';
}

$heading_classes = array( 'content-banner__heading' );
if ( 'default' !== $heading_size ) {
	$heading_classes[] = $heading_size;
}

if ( empty( $cta_style ) || ! in_array( $cta_style, array( 'button', 'arrow' ), true ) ) {
	$cta_style = 'button';
}

$cta_url    = is_array( $cta ) ? ( $cta['url'] ?? '' ) : '';
$cta_title  = is_array( $cta ) ? ( $cta['title'] ?? '' ) : '';
$cta_target = is_array( $cta ) ? ( $cta['target'] ?? '' ) : '';

$target_attr = '';
$rel_attr    = '';
if ( '_blank' === $cta_target ) {
	$target_attr = ' target="_blank"';
	$rel_attr    = ' rel="noopener noreferrer"';
}

$has_cta = $cta_url && $cta_title;

$has_pre_heading = is_string( $pre_heading ) && '' !== trim( $pre_heading );
$has_heading     = is_string( $heading ) && '' !== trim( $heading );
$has_copy        = is_string( $copy ) && '' !== trim( $copy );

if ( ! $has_pre_heading && ! $has_heading && ! $has_copy && ! $has_cta ) {
	return;
}
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<div class="content-banner__inner">
			<?php if ( $has_pre_heading ) : ?>
				<p class="content-banner__pre preheading"><?php echo esc_html( trim( $pre_heading ) ); ?></p>
			<?php endif; ?>

			<?php if ( $has_heading ) : ?>
				<h2 class="<?php echo esc_attr( implode( ' ', $heading_classes ) ); ?>"><?php echo wp_kses_post( $heading ); ?></h2>
			<?php endif; ?>

			<?php if ( $has_copy ) : ?>
				<div class="content-banner__copy">
					<?php echo wp_kses_post( wpautop( $copy ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $has_cta ) : ?>
				<?php if ( 'arrow' === $cta_style ) : ?>
					<a class="arrow-link content-banner__link" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $target_attr . $rel_attr; ?>>
						<?php echo esc_html( $cta_title ); ?>
					</a>
				<?php else : ?>
					<a class="btn btn-small content-banner__btn" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $target_attr . $rel_attr; ?>>
						<?php echo esc_html( $cta_title ); ?>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
