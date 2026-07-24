<?php
/**
 * Block Name: CTA Banner
 *
 * Optional form embed: inline, modal trigger, or standard CTA links only.
 *
 * @package jdpower
 */

$classes = 'cta-banner';

if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}

$padding_top     = get_field( 'cta_banner_padding_top' );
$padding_bottom  = get_field( 'cta_banner_padding_bottom' );
$background_tone = get_field( 'cta_banner_background_tone' );

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
	$classes .= ' cta-banner--tone-dark';
} else {
	$classes .= ' cta-banner--tone-light';
}

$heading       = get_field( 'cta_banner_heading' );
$heading_size  = get_field( 'cta_banner_heading_size' );
$copy          = get_field( 'cta_banner_copy' );

$valid_heading_sizes = array( 'default', 'large', 'medium', 'small', 'xsmall', 'xxsmall' );
if ( empty( $heading_size ) || ! in_array( $heading_size, $valid_heading_sizes, true ) ) {
	$heading_size = 'default';
}

$heading_classes = array( 'cta-banner__heading' );
if ( 'default' !== $heading_size ) {
	$heading_classes[] = $heading_size;
}
$heading_class_attr = esc_attr( implode( ' ', $heading_classes ) );

$form_display_raw = get_field( 'cta_banner_form_display' );
$form_display     = in_array( $form_display_raw, array( 'buttons', 'embed', 'modal' ), true )
	? $form_display_raw
	: 'buttons';

$form_embed_raw = get_field( 'cta_banner_form_embed' );
$form_trimmed   = is_string( $form_embed_raw ) ? trim( $form_embed_raw ) : '';

// Form embed is trusted editor content (same idea as core Custom HTML); output as stored so scripts/data-* stay intact.
$has_usable_form = '' !== $form_trimmed;

$modal_button_raw = 'modal' === $form_display ? get_field( 'cta_banner_form_modal_button_text' ) : '';
$modal_trigger_text = is_string( $modal_button_raw ) ? trim( $modal_button_raw ) : '';

$modal_heading_raw = 'modal' === $form_display ? get_field( 'cta_banner_form_modal_heading' ) : '';
$modal_heading      = is_string( $modal_heading_raw ) ? trim( $modal_heading_raw ) : '';
$has_modal_heading  = '' !== $modal_heading;

$show_form_embed = $has_usable_form && 'embed' === $form_display;
$show_form_modal = $has_usable_form && 'modal' === $form_display && '' !== $modal_trigger_text;

if ( $show_form_embed ) {
	$classes .= ' cta-banner--form-embed';
}

$cta_primary   = 'buttons' === $form_display ? get_field( 'cta_banner_cta_primary' ) : null;
$cta_secondary = 'buttons' === $form_display ? get_field( 'cta_banner_cta_secondary' ) : null;

$primary_url    = is_array( $cta_primary ) ? ( $cta_primary['url'] ?? '' ) : '';
$primary_title  = is_array( $cta_primary ) ? ( $cta_primary['title'] ?? '' ) : '';
$primary_target = is_array( $cta_primary ) ? ( $cta_primary['target'] ?? '' ) : '';

$primary_target_attr = '';
$primary_rel_attr    = '';
if ( '_blank' === $primary_target ) {
	$primary_target_attr = ' target="_blank"';
	$primary_rel_attr    = ' rel="noopener noreferrer"';
}

$secondary_url    = is_array( $cta_secondary ) ? ( $cta_secondary['url'] ?? '' ) : '';
$secondary_title  = is_array( $cta_secondary ) ? ( $cta_secondary['title'] ?? '' ) : '';
$secondary_target = is_array( $cta_secondary ) ? ( $cta_secondary['target'] ?? '' ) : '';

$secondary_target_attr = '';
$secondary_rel_attr    = '';
if ( '_blank' === $secondary_target ) {
	$secondary_target_attr = ' target="_blank"';
	$secondary_rel_attr    = ' rel="noopener noreferrer"';
}

$has_primary     = $primary_url && $primary_title;
$has_secondary   = $secondary_url && $secondary_title;
$show_cta_row    = ( 'buttons' === $form_display ) && ( $has_primary || $has_secondary );

$block_uid = isset( $block['id'] ) && is_string( $block['id'] )
	? preg_replace( '/[^a-zA-Z0-9_-]/', '-', $block['id'] )
	: '';
if ( '' === $block_uid ) {
	$block_uid = function_exists( 'wp_unique_id' )
		? 'uid-' . wp_unique_id()
		: 'uid-' . uniqid( '', false );
}
$dialog_id = 'cta-banner-dialog-' . $block_uid;
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<div class="cta-banner__inner">
			<?php if ( $show_form_embed ) : ?>
				<div class="row cta-banner__columns align-items-start">
					<div class="col-12 col-lg-6 cta-banner__content">
						<?php if ( ! empty( $heading ) ) : ?>
							<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
						<?php endif; ?>

						<?php if ( ! empty( $copy ) ) : ?>
							<div class="cta-banner__copy">
								<?php echo wp_kses_post( wpautop( $copy ) ); ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="col-12 col-lg-6 cta-banner__form">
						<?php echo $form_trimmed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted ACF embed field; intentionally unfiltered. ?>
					</div>
				</div>
			<?php else : ?>
				<?php if ( ! empty( $heading ) ) : ?>
					<h2 class="<?php echo $heading_class_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_attr() above. ?>"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $copy ) ) : ?>
					<div class="cta-banner__copy">
						<?php echo wp_kses_post( wpautop( $copy ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $show_form_modal ) : ?>
					<div class="cta-banner__actions">
						<button
							type="button"
							class="btn btn-small cta-banner__modal-trigger"
							aria-haspopup="dialog"
							aria-controls="<?php echo esc_attr( $dialog_id ); ?>"
							aria-expanded="false"
						><?php echo esc_html( $modal_trigger_text ); ?></button>
					</div>
				<?php elseif ( $show_cta_row ) : ?>
					<div class="cta-banner__actions">
						<?php if ( $has_primary ) : ?>
							<a class="btn btn-small" href="<?php echo esc_url( $primary_url ); ?>"<?php echo $primary_target_attr . $primary_rel_attr; ?>>
								<?php echo esc_html( $primary_title ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $has_secondary ) : ?>
							<a class="btn btn-small" href="<?php echo esc_url( $secondary_url ); ?>"<?php echo $secondary_target_attr . $secondary_rel_attr; ?>>
								<?php echo esc_html( $secondary_title ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $show_form_modal ) : ?>
		<dialog
			class="cta-banner__dialog"
			id="<?php echo esc_attr( $dialog_id ); ?>"
			<?php if ( $has_modal_heading ) : ?>
				aria-labelledby="<?php echo esc_attr( $dialog_id . '-heading' ); ?>"
			<?php else : ?>
				aria-label="<?php echo esc_attr__( 'Form dialog', 'jdpower' ); ?>"
			<?php endif; ?>
		>
			<div class="cta-banner__dialog-surface">
				<div class="cta-banner__dialog-head<?php echo $has_modal_heading ? ' cta-banner__dialog-head--has-heading' : ''; ?>">
					<?php if ( $has_modal_heading ) : ?>
						<h2 class="large cta-banner__dialog-heading" id="<?php echo esc_attr( $dialog_id ); ?>-heading"><?php echo wp_kses_post( $modal_heading ); ?></h2>
					<?php endif; ?>
					<button type="button" class="cta-banner__dialog-close" aria-label="<?php echo esc_attr__( 'Close dialog', 'jdpower' ); ?>">
						<span class="cta-banner__dialog-close-icon" aria-hidden="true">
							<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none" focusable="false">
								<path d="M3.03553 2.5L22.1274 21.5919M2.5 21.5924L21.5919 2.50056" stroke="currentColor" stroke-width="5" stroke-linecap="round" />
							</svg>
						</span>
					</button>
				</div>
				<div class="cta-banner__form cta-banner__form--modal">
					<?php echo $form_trimmed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted ACF embed field; intentionally unfiltered. ?>
				</div>
			</div>
		</dialog>
	<?php endif; ?>
</section>
