<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Recommendations;

use Throwable;
use WPE\AITK\Modules\Smart_Search\Recommendations\Data\Analytics_Script_Data;
use WPE\AITK\Modules\Smart_Search\Recommendations\Data\Recommendations_Config_Data as Data;
use WPE\AITK\WP\Asset\Script\Contracts\Script_Handler_Interface;
use WPE\AITK\WP\Asset\Script\Script_Asset;
use WPE\AITK\WP\Debug\Contracts\Logger_Interface;
use WPE\AITK\WP\Nonce\Contracts\Nonce_Interface;
use WPE\AITK\WP\Script\Contracts\Script_Data_Injector_Interface;
use WPE\AITK\WP\Settings\Network_Settings_Manager;
use WPE\AITK\WP\Settings\Settings_Manager;

/**
 * Enqueues and configures the analytics tracking script for recommendations.
 */
final class Recommendations_Analytics_Handler {
	private const SCRIPT_HANDLE      = 'analytics';
	private const SCRIPT_PATH        = 'public/js/analytics.js';
	private const JS_OBJECT          = 'wpeAitkAnalytics';
	private const USER_SESSION_STORE = 'user-session-store';

	private Script_Handler_Interface $script_handler;
	private Script_Data_Injector_Interface $injector;
	private Nonce_Interface $nonce;
	private Logger_Interface $logger;
	private Settings_Manager $settings_manager;
	private Network_Settings_Manager $network_settings_manager;

	public function __construct(
		Script_Handler_Interface $script_handler,
		Script_Data_Injector_Interface $injector,
		Nonce_Interface $nonce,
		Logger_Interface $logger,
		Settings_Manager $settings_manager,
		Network_Settings_Manager $network_settings_manager
	) {
		$this->script_handler           = $script_handler;
		$this->injector                 = $injector;
		$this->nonce                    = $nonce;
		$this->logger                   = $logger;
		$this->settings_manager         = $settings_manager;
		$this->network_settings_manager = $network_settings_manager;
	}

	public function enqueue_analytics_script(): void {
		$config         = $this->settings_manager->get( Data::class ) ?? new Data();
		$network_config = $this->network_settings_manager->get( Data::class ) ?? new Data();

		if ( ( is_multisite() && ! $network_config->is_analytics_enabled() ) || ! $config->is_analytics_enabled() ) {
			return;
		}

		try {
			$this->script_handler->enqueue(
				new Script_Asset( self::SCRIPT_HANDLE, self::SCRIPT_PATH, [ self::USER_SESSION_STORE ] )
			);

			// Analytics_Script_Data is instantiated here (not in the definer) because its
			// to_array() reads WordPress loop state (is_search, have_posts, etc.) that is
			// only available at enqueue time, not at container-build time.
			$this->injector->inject(
				self::SCRIPT_HANDLE,
				new Analytics_Script_Data( $this->nonce, $this->logger ),
				self::JS_OBJECT
			);
		} catch ( Throwable $e ) {
			$this->logger->error( 'recommendations', 'Analytics script enqueue failed: ' . $e->getMessage() );
		}
	}
}
