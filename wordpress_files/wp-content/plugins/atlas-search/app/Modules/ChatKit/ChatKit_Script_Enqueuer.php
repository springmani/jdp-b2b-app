<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\ChatKit;

use Throwable;
use WPE\AITK\Modules\ChatKit\Data\ChatKit_Config_Data;
use WPE\AITK\WP\Nonce\Contracts\Nonce_Interface;
use WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface;

/**
 * ChatKit Script Enqueuer.
 *
 * Handles enqueuing ChatKit CDN script and localizing settings for JavaScript.
 */
final class ChatKit_Script_Enqueuer {
	/**
	 * Script handle for ChatKit CDN.
	 */
	private const CDN_SCRIPT_HANDLE = 'chatkit-cdn';

	/**
	 * JavaScript object name for localized data.
	 */
	private const JS_OBJECT_NAME = 'wpeChatKitSettings';

	/**
	 * Nonce action for REST API.
	 */
	private const NONCE_ACTION = 'wp_rest';

	/**
	 * Script data injector.
	 *
	 * @var \WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface
	 */
	private Script_Data_Injector_Interface $script_injector;

	/**
	 * Nonce handler.
	 *
	 * @var \WPE\AITK\WP\Nonce\Contracts\Nonce_Interface
	 */
	private Nonce_Interface $nonce;

	/**
	 * Constructor.
	 *
	 * @param \WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface $injector Script data injector.
	 * @param \WPE\AITK\WP\Nonce\Contracts\Nonce_Interface                 $nonce    Nonce handler.
	 */
	public function __construct( Script_Data_Injector_Interface $injector, Nonce_Interface $nonce ) {
		$this->script_injector = $injector;
		$this->nonce           = $nonce;
	}

	/**
	 * Enqueue ChatKit CDN script.
	 */
	public function enqueue_cdn(): void {
		// OpenAI CDN doesn't accept query parameters, so version must be null.
		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script(
			self::CDN_SCRIPT_HANDLE,
			'https://cdn.platform.openai.com/deployments/chatkit/chatkit.js',
			[],
			null,
			true
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}

	/**
	 * Enqueue ChatKit scripts with configuration data.
	 */
	public function enqueue(): void {
		$this->enqueue_cdn();
		$this->inject_script_data( $this->get_config_data() );
	}

	/**
	 * Inject script data with error handling.
	 *
	 * @param \WPE\AITK\Core\DTO\Contracts\Array_Convertible_Interface $data Configuration data to inject.
	 */
	private function inject_script_data( $data ): void {
		try {
			$this->script_injector->inject( self::CDN_SCRIPT_HANDLE, $data, self::JS_OBJECT_NAME );
		} catch ( Throwable $e ) {
			// Log error but don't break the page.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'ChatKit script injection failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Get configuration data for JavaScript.
	 *
	 * @throws \WPE\AITK\WP\Nonce\Exceptions\Nonce_Creation_Exception
	 */
	private function get_config_data(): ChatKit_Config_Data {
		$settings = ChatKit_Settings::from_options();

		$is_enabled = $settings->is_enabled()
			&& ! empty( $settings->get_api_key() )
			&& ! empty( $settings->get_workflow_id() );

		return new ChatKit_Config_Data(
			$is_enabled,
			$this->nonce->create( self::NONCE_ACTION ),
			rest_url( 'wpengine-aitk/v1/chatkit/' )
		);
	}
}
