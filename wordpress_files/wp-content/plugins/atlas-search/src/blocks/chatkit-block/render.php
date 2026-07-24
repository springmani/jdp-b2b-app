<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 *
 * @phpcs:disable Squiz.Commenting.FileComment.MissingPackageTag
 */

use WPE\AITK\Modules\ChatKit\ChatKit_Settings;

// Default attributes.
$attributes = wp_parse_args(
	$attributes,
	[
		'displayMode'        => 'inline',
		'fabPosition'        => 'bottom-right',
		'customHeight'       => '600px',
		'colorScheme'        => 'light',
		'borderRadius'       => 'medium',
		'density'            => 'normal',
		'greeting'           => 'Hello! How can I help you today?',
		'placeholder'        => 'Type your message...',
		'attachmentsEnabled' => true,
	]
);

// Check if ChatKit is enabled and properly configured.
$settings = ChatKit_Settings::from_options();

if ( ! $settings->is_enabled() || empty( $settings->get_api_key() ) || empty( $settings->get_workflow_id() ) ) {
	// ChatKit is not properly configured - show admin notice if user has permissions.
	if ( current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-warning">';
		echo '<p>' . esc_html__(
			'ChatKit block requires configuration. Please configure ChatKit in the plugin settings.',
			'wpengine-smart-search'
		) . '</p>';
		echo '</div>';
	}

	return;
}

// Enqueue ChatKit scripts (this will be handled by the ChatKit_Subscriber).
do_action( 'wpengine_chatkit_block_render' );

$display_mode        = $attributes['displayMode'];
$fab_position        = $attributes['fabPosition'];
$custom_height       = $attributes['customHeight'];
$color_scheme        = $attributes['colorScheme'];
$border_radius       = $attributes['borderRadius'];
$density             = $attributes['density'];
$greeting            = $attributes['greeting'];
$placeholder         = $attributes['placeholder'];
$attachments_enabled = $attributes['attachmentsEnabled'];

// Generate unique block ID.
$block_id = 'chatkit-' . wp_unique_id();

// Initial context for Interactivity API.
$context = wp_interactivity_data_wp_context(
	[
		'isOpen'             => false,
		'blockId'            => $block_id,
		'colorScheme'        => $color_scheme,
		'borderRadius'       => $border_radius,
		'density'            => $density,
		'greeting'           => $greeting,
		'placeholder'        => $placeholder,
		'attachmentsEnabled' => $attachments_enabled,
	]
);

// Wrapper attributes.
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class'             => 'wpengine-chatkit-block wpengine-chatkit-block--' . esc_attr( $display_mode ),
		'data-display-mode' => esc_attr( $display_mode ),
		'data-fab-position' => esc_attr( $fab_position ),
	]
);
$fab_container_cls  = 'wpengine-chatkit-block__fab-container wpengine-chatkit-block__fab-container--' . esc_attr(
	$fab_position
);

?>

<div
	data-wp-interactive="wpengine-chatkit-block"
	data-wp-init="actions.init"
	<?php echo wp_kses_data( $wrapper_attributes ); ?>
	<?php echo wp_kses_data( $context ); ?>
>

	<?php

	if ( 'inline' === $display_mode ) :
		?>
		<div class="wpengine-chatkit-block__container" style="height: <?php echo esc_attr( $custom_height ); ?>;">
			<openai-chatkit id="<?php echo esc_attr( $block_id ); ?>"></openai-chatkit>
		</div>

	<?php endif; ?>

	<?php

	if ( 'fab' === $display_mode ) :
		?>
		<div class="<?php echo esc_attr( $fab_container_cls ); ?>">
			<button
				class="wpengine-chatkit-block__fab"
				aria-label="<?php esc_attr_e( 'Open chat', 'wpengine-smart-search' ); ?>"
				data-chatkit-toggle
				data-wp-on--click="actions.toggleFab"
			>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z" fill="currentColor"/>
				</svg>
			</button>
			<div class="wpengine-chatkit-block__widget wpengine-chatkit-block__widget--hidden">
				<openai-chatkit id="<?php echo esc_attr( $block_id ); ?>"></openai-chatkit>
			</div>
		</div>

	<?php endif; ?>

</div>
