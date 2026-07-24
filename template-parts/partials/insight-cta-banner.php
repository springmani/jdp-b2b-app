<?php
/**
 * Theme Settings CTA banner on insight singles.
 *
 * @package jdpower
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'get_field' ) || ! get_field( 'insight_cta_banner_enabled', 'option' ) ) {
	return;
}

$heading = get_field( 'insight_cta_banner_heading', 'option' );
$copy    = get_field( 'insight_cta_banner_copy', 'option' );

$form_display = get_field( 'insight_cta_banner_form_display', 'option' );
$form_display = in_array( $form_display, array( 'buttons', 'embed', 'modal' ), true ) ? $form_display : 'buttons';

$form_trimmed    = trim( (string) get_field( 'insight_cta_banner_form_embed', 'option' ) );
$has_usable_form = '' !== $form_trimmed;

$modal_trigger_text = 'modal' === $form_display ? trim( (string) get_field( 'insight_cta_banner_form_modal_button_text', 'option' ) ) : '';
$modal_heading      = 'modal' === $form_display ? trim( (string) get_field( 'insight_cta_banner_form_modal_heading', 'option' ) ) : '';

$show_form_embed = $has_usable_form && 'embed' === $form_display;
$show_form_modal = $has_usable_form && 'modal' === $form_display && '' !== $modal_trigger_text;

$cta_primary   = 'buttons' === $form_display ? get_field( 'insight_cta_banner_cta_primary', 'option' ) : null;
$cta_secondary = 'buttons' === $form_display ? get_field( 'insight_cta_banner_cta_secondary', 'option' ) : null;

$primary_url   = is_array( $cta_primary ) ? ( $cta_primary['url'] ?? '' ) : '';
$primary_title = is_array( $cta_primary ) ? ( $cta_primary['title'] ?? '' ) : '';
$primary_target = is_array( $cta_primary ) ? ( $cta_primary['target'] ?? '' ) : '';

$secondary_url   = is_array( $cta_secondary ) ? ( $cta_secondary['url'] ?? '' ) : '';
$secondary_title = is_array( $cta_secondary ) ? ( $cta_secondary['title'] ?? '' ) : '';
$secondary_target = is_array( $cta_secondary ) ? ( $cta_secondary['target'] ?? '' ) : '';

$has_heading = is_string( $heading ) && '' !== trim( $heading );
$has_copy    = is_string( $copy ) && '' !== trim( wp_strip_all_tags( $copy ) );
$has_primary = $primary_url && $primary_title;
$has_secondary = $secondary_url && $secondary_title;
$show_cta_row = ( 'buttons' === $form_display ) && ( $has_primary || $has_secondary );

if ( ! $has_heading && ! $has_copy && ! $show_form_embed && ! $show_form_modal && ! $show_cta_row ) {
	return;
}

$classes = 'cta-banner cta-banner--insight-single pt-default pb-none bg-light cta-banner--tone-light';
if ( $show_form_embed ) {
	$classes .= ' cta-banner--form-embed';
}

$dialog_id = 'cta-banner-dialog-insight-single';
?>

<section class="<?php echo esc_attr( $classes ); ?>">
	<div class="container">
		<div class="cta-banner__inner">
			<?php if ( $show_form_embed ) : ?>
				<div class="row cta-banner__columns align-items-start">
					<div class="col-12 col-lg-6 cta-banner__content">
						<?php if ( $has_heading ) : ?>
							<h2 class="cta-banner__heading"><?php echo wp_kses_post( $heading ); ?></h2>
						<?php endif; ?>
						<?php if ( $has_copy ) : ?>
							<div class="cta-banner__copy"><?php echo wp_kses_post( wpautop( $copy ) ); ?></div>
						<?php endif; ?>
					</div>
					<div class="col-12 col-lg-6 cta-banner__form">
						<?php echo $form_trimmed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted embed field. ?>
					</div>
				</div>
			<?php else : ?>
				<?php if ( $has_heading ) : ?>
					<h2 class="cta-banner__heading"><?php echo wp_kses_post( $heading ); ?></h2>
				<?php endif; ?>
				<?php if ( $has_copy ) : ?>
					<div class="cta-banner__copy"><?php echo wp_kses_post( wpautop( $copy ) ); ?></div>
				<?php endif; ?>
				<?php if ( $show_form_modal ) : ?>
					<div class="cta-banner__actions">
						<button type="button" class="btn btn-small cta-banner__modal-trigger" aria-haspopup="dialog" aria-controls="<?php echo esc_attr( $dialog_id ); ?>" aria-expanded="false"><?php echo esc_html( $modal_trigger_text ); ?></button>
					</div>
				<?php elseif ( $show_cta_row ) : ?>
					<div class="cta-banner__actions">
						<?php if ( $has_primary ) : ?>
							<a class="btn btn-small" href="<?php echo esc_url( $primary_url ); ?>"<?php echo '_blank' === $primary_target ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $primary_title ); ?></a>
						<?php endif; ?>
						<?php if ( $has_secondary ) : ?>
							<a class="btn btn-small" href="<?php echo esc_url( $secondary_url ); ?>"<?php echo '_blank' === $secondary_target ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $secondary_title ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( $show_form_modal ) : ?>
		<dialog class="cta-banner__dialog" id="<?php echo esc_attr( $dialog_id ); ?>"<?php echo '' !== $modal_heading ? ' aria-labelledby="' . esc_attr( $dialog_id . '-heading' ) . '"' : ' aria-label="' . esc_attr__( 'Form dialog', 'jdpower' ) . '"'; ?>>
			<div class="cta-banner__dialog-surface">
				<div class="cta-banner__dialog-head<?php echo '' !== $modal_heading ? ' cta-banner__dialog-head--has-heading' : ''; ?>">
					<?php if ( '' !== $modal_heading ) : ?>
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
					<?php echo $form_trimmed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted embed field. ?>
				</div>
			</div>
		</dialog>
	<?php endif; ?>
</section>
