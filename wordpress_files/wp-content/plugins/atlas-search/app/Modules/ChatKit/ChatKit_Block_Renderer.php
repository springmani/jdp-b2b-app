<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\ChatKit;

/**
 * ChatKit Block Renderer.
 *
 * Handles rendering ChatKit blocks programmatically.
 */
final class ChatKit_Block_Renderer {
	/**
	 * Render ChatKit block.
	 *
	 * @param array<string, string|bool> $attributes Block attributes.
	 *
	 * @return string Rendered block HTML.
	 */
	public function render( array $attributes ): string {
		return render_block(
			[
				'attrs'     => $attributes,
				'blockName' => 'wpengine-smart-search/chatkit',
			]
		);
	}

	/**
	 * Enqueue scripts for FAB mode.
	 *
	 * Called on wp_enqueue_scripts to ensure scripts are enqueued before wp_head.
	 * Processes the shortcode to trigger script registration but discards output.
	 */
	public function enqueue_fab_scripts(): void {
		if ( ! $this->should_render_fab() ) {
			return;
		}

		// Process shortcode to trigger script enqueuing, but discard the output.
		// This ensures scripts are registered during wp_enqueue_scripts, not wp_footer.
		do_shortcode( '[chatkit displaymode="fab" fabposition="bottom-right"]' );
	}

	/**
	 * Render ChatKit in FAB (Floating Action Button) mode.
	 *
	 * Outputs the FAB directly (for use with wp_footer hook).
	 * Scripts are already enqueued by enqueue_fab_scripts().
	 */
	public function render_fab(): void {
		if ( ! $this->should_render_fab() ) {
			return;
		}

		// Render the shortcode. Scripts are already enqueued by enqueue_fab_scripts().
		echo do_shortcode( '[chatkit displaymode="fab" fabposition="bottom-right"]' );
	}

	/**
	 * Render shortcode with provided attributes.
	 *
	 * @param array<string, string|bool> $atts Shortcode attributes.
	 *
	 * @return string Rendered block HTML.
	 */
	public function render_shortcode( array $atts ): string {
		// WordPress converts shortcode attributes to lowercase, so we need to use lowercase keys.
		$shortcode_atts = shortcode_atts(
			[
				'attachmentsenabled' => 'true',
				'borderradius'       => 'medium',
				'colorscheme'        => 'light',
				'customheight'       => '600px',
				'density'            => 'normal',
				'displaymode'        => 'inline',
				'fabposition'        => 'bottom-right',
				'greeting'           => 'Hello! How can I help you today?',
				'placeholder'        => 'Type your message...',
			],
			$atts,
			'chatkit'
		);

		// TODO: dynamically map attributes instead of hardcoding them from lowercase to camelCase.

		// Map lowercase shortcode attributes to camelCase block attributes.
		$attributes = [
			'attachmentsEnabled' => filter_var( $shortcode_atts['attachmentsenabled'], FILTER_VALIDATE_BOOLEAN ),
			'borderRadius'       => $shortcode_atts['borderradius'],
			'colorScheme'        => $shortcode_atts['colorscheme'],
			'customHeight'       => $shortcode_atts['customheight'],
			'density'            => $shortcode_atts['density'],
			'displayMode'        => $shortcode_atts['displaymode'],
			'fabPosition'        => $shortcode_atts['fabposition'],
			'greeting'           => $shortcode_atts['greeting'],
			'placeholder'        => $shortcode_atts['placeholder'],
		];

		return $this->render( $attributes );
	}

	/**
	 * Check if ChatKit should be rendered.
	 */
	public function should_render(): bool {
		$settings = ChatKit_Settings::from_options();

		return $settings->is_enabled()
			&& ! empty( $settings->get_api_key() )
			&& ! empty( $settings->get_workflow_id() );
	}

	/**
	 * Check if FAB mode should be rendered.
	 */
	public function should_render_fab(): bool {
		if ( ! $this->should_render() ) {
			return false;
		}

		$settings = ChatKit_Settings::from_options();

		return $settings->is_fab_enabled();
	}
}
