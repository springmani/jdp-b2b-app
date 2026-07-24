<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\ChatKit;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\System\Data\Capability;
use WPE\AITK\WP\Capability\Capability_Service;

/**
 * ChatKit Subscriber.
 *
 * Handles OpenAI ChatKit integration and frontend/backend initialization.
 */
final class ChatKit_Subscriber extends Hook_Subscriber {
	/**
	 * API handler.
	 *
	 * @var \WPE\AITK\Modules\ChatKit\API_Handler
	 */
	private API_Handler $api_handler;

	/**
	 * Script enqueuer.
	 *
	 * @var \WPE\AITK\Modules\ChatKit\ChatKit_Script_Enqueuer
	 */
	private ChatKit_Script_Enqueuer $script_enqueuer;

	/**
	 * Block renderer.
	 *
	 * @var \WPE\AITK\Modules\ChatKit\ChatKit_Block_Renderer
	 */
	private ChatKit_Block_Renderer $block_renderer;

	/**
	 * Settings handler.
	 *
	 * @var \WPE\AITK\Modules\ChatKit\ChatKit_Settings_Handler
	 */
	private ChatKit_Settings_Handler $settings_handler;

	public function register(): void {
		$capability_service = $this->container->get( Capability_Service::class );

		if ( ! $capability_service->has_capability( Capability::EXTENDED_FEATURES ) ) {
			return;
		}

		// Inject dependencies from container.
		$this->api_handler      = $this->container->get( API_Handler::class );
		$this->script_enqueuer  = $this->container->get( ChatKit_Script_Enqueuer::class );
		$this->block_renderer   = $this->container->get( ChatKit_Block_Renderer::class );
		$this->settings_handler = $this->container->get( ChatKit_Settings_Handler::class );

		// Register hooks.
		add_action( 'init', [ $this, 'register_activation_defaults' ] );
		add_action( 'rest_api_init', [ $this->api_handler, 'register_routes' ] );
		add_action( 'wp_enqueue_scripts', [ $this->block_renderer, 'enqueue_fab_scripts' ] );
		add_action( 'wp_footer', [ $this->block_renderer, 'render_fab' ] );
		add_shortcode( 'chatkit', [ $this->block_renderer, 'render_shortcode' ] );
		add_action( 'enqueue_block_editor_assets', [ $this->script_enqueuer, 'enqueue' ] );
		add_action( 'wp_ajax_get_chatkit_settings', [ $this->settings_handler, 'handle_get' ] );
		add_action( 'wp_ajax_save_chatkit_settings', [ $this->settings_handler, 'handle_save' ] );
		add_action( 'wpengine_chatkit_block_render', [ $this->script_enqueuer, 'enqueue' ] );
	}

	/**
	 * Register default options on plugin activation.
	 */
	public function register_activation_defaults(): void {
		// Initialize settings with defaults if not already set.
		if ( false === get_option( 'wpe_chatkit_settings' ) ) {
			$default_settings = new ChatKit_Settings();
			$default_settings->save();
		}
	}
}
