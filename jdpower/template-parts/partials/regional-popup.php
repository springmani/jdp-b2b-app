<?php
/**
 * Regional mismatch popup — two-card layout with global and region map images.
 *
 * @package jdpower
 *
 * @var array $args Template arguments from get_template_part().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = isset( $args ) && is_array( $args ) ? $args : array();

$heading          = isset( $args['heading'] ) ? trim( (string) $args['heading'] ) : '';
$sub_heading      = isset( $args['sub_heading'] ) ? trim( (string) $args['sub_heading'] ) : '';
$choices_heading  = isset( $args['choices_heading'] ) ? trim( (string) $args['choices_heading'] ) : '';
$global_image     = isset( $args['global_image'] ) && is_array( $args['global_image'] ) ? $args['global_image'] : null;
$region_image     = isset( $args['region_image'] ) && is_array( $args['region_image'] ) ? $args['region_image'] : null;
$region_label     = isset( $args['region_label'] ) ? trim( (string) $args['region_label'] ) : '';
$continue_url     = isset( $args['continue_url'] ) ? (string) $args['continue_url'] : '';
$remain_label     = isset( $args['remain_label'] ) ? trim( (string) $args['remain_label'] ) : '';
$continue_label   = isset( $args['continue_label'] ) ? trim( (string) $args['continue_label'] ) : '';

if ( '' === $continue_url ) {
	return;
}

/**
 * @param array{id?: int, url?: string, alt?: string}|null $image Image data.
 */
$render_choice_image = static function ( $image ) {
	if ( ! is_array( $image ) || empty( $image['url'] ) ) {
		return;
	}
	$url = trim( (string) $image['url'] );
	$alt = isset( $image['alt'] ) ? trim( (string) $image['alt'] ) : '';
	if ( '' === $url ) {
		return;
	}
	?>
	<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy" decoding="async" />
	<?php
};

$has_title = '' !== $heading || '' !== $region_label;
?>
<dialog
	id="jdpower-regional-popup"
	class="regional-popup"
	<?php echo $has_title ? 'aria-labelledby="jdpower-regional-popup-title"' : ''; ?>
>
	<div class="regional-popup__surface">
		<button type="button" class="regional-popup__close" aria-label="<?php echo esc_attr__( 'Close dialog', 'jdpower' ); ?>">
			<span class="regional-popup__close-icon" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 25 25" fill="none" focusable="false">
					<path d="M3.03553 2.5L22.1274 21.5919M2.5 21.5924L21.5919 2.50056" stroke="currentColor" stroke-width="5" stroke-linecap="round" />
				</svg>
			</span>
		</button>

		<div class="regional-popup__icon" aria-hidden="true">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/images/region.svg' ); ?>"
				alt=""
				width="16"
				height="22"
				decoding="async"
			/>
		</div>

		<?php if ( $has_title ) : ?>
			<h2 class="regional-popup__heading" id="jdpower-regional-popup-title">
				<?php if ( '' !== $heading ) : ?>
					<span class="regional-popup__heading-line"><?php echo esc_html( $heading ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $region_label ) : ?>
					<span class="regional-popup__heading-region"><?php echo esc_html( $region_label ); ?></span><span class="regional-popup__heading-period" aria-hidden="true">.</span>
				<?php endif; ?>
			</h2>
		<?php endif; ?>

		<?php if ( '' !== $sub_heading ) : ?>
			<p class="regional-popup__sub-heading"><?php echo esc_html( $sub_heading ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $choices_heading ) : ?>
			<p class="regional-popup__choices-heading"><?php echo esc_html( $choices_heading ); ?></p>
		<?php endif; ?>

		<div class="regional-popup__choices">
			<?php if ( '' !== $remain_label ) : ?>
				<button type="button" class="regional-popup__choice regional-popup__choice--global regional-popup__remain">
					<span class="regional-popup__choice-media">
						<?php $render_choice_image( $global_image ); ?>
					</span>
					<span class="regional-popup__choice-label"><?php echo esc_html( $remain_label ); ?></span>
				</button>
			<?php endif; ?>

			<?php if ( '' !== $continue_label ) : ?>
				<a href="<?php echo esc_url( $continue_url ); ?>" class="regional-popup__choice regional-popup__choice--region regional-popup__continue">
					<span class="regional-popup__choice-media">
						<?php $render_choice_image( $region_image ); ?>
					</span>
					<span class="regional-popup__choice-label"><?php echo esc_html( $continue_label ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</dialog>
