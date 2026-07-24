<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Recommendations;

use WPE\AITK\Modules\Smart_Search\Recommendations\Data\Recommendations_Config_Data;
use WPE\AITK\WP\Rest\Contracts\Rest_Controller_Interface;
use WPE\AITK\WP\Rest\Exceptions\Server_Exception;
use WPE\AITK\WP\Rest\Route_Config;
use WPE\AITK\WP\Settings\Contracts\Settings_Manager_Interface;
use WPE\AITK\WP\Settings\Exceptions\Setting_Not_Registered_Exception;
use WPE\AITK\WP\User\Capability_Checker;
use WP_REST_Server;

/**
 * Recommendations Network Settings REST Controller.
 *
 * Provides GET / POST endpoints for Recommendations network admin settings,
 * analogous to the WP /wp/v2/settings endpoint but targeting network-level options
 * via Network_Settings_Manager (get_network_option / update_network_option).
 *
 * Routes (all under wpengine-aitk/v1):
 *   GET  /network-settings/recommendations  — read current network settings (or defaults).
 *   POST /network-settings/recommendations  — persist updated network settings.
 */
final class Recommendations_Network_Rest_Controller implements Rest_Controller_Interface {
	public const ROUTE          = '/network-settings/recommendations';
	public const NETWORK_OPTION = 'wpe_aitk/network/config/recommendations';

	private Settings_Manager_Interface $network_settings_manager;

	public function __construct( Settings_Manager_Interface $network_settings_manager ) {
		$this->network_settings_manager = $network_settings_manager;
	}

	/**
	 * @inheritDoc
	 */
	public function get_route_configs(): array {
		return [
			new Route_Config(
				self::ROUTE,
				WP_REST_Server::READABLE,
				[ $this, 'get' ],
				Capability_Checker::MANAGE_NETWORK_OPTIONS,
				Recommendations_Config_Data::class
			),
			new Route_Config(
				self::ROUTE,
				WP_REST_Server::CREATABLE,
				[ $this, 'update' ],
				Capability_Checker::MANAGE_NETWORK_OPTIONS,
				Recommendations_Config_Data::class,
				Recommendations_Config_Data::class
			),
		];
	}

	/**
	 * Returns the current network-level Recommendations settings, or defaults if not yet saved.
	 */
	public function get(): Recommendations_Config_Data {
		$config = $this->network_settings_manager->get( Recommendations_Config_Data::class );

		return $config ?? new Recommendations_Config_Data();
	}

	/**
	 * Persists updated network-level Recommendations settings.
	 *
	 * @param \WPE\AITK\Modules\Smart_Search\Recommendations\Data\Recommendations_Config_Data $config Hydrated request body.
	 *
	 * @throws \WPE\AITK\WP\Rest\Exceptions\Server_Exception If the settings manager fails to persist the config.
	 */
	public function update( Recommendations_Config_Data $config ): Recommendations_Config_Data {
		try {
			$this->network_settings_manager->update( self::NETWORK_OPTION, $config );
		} catch ( Setting_Not_Registered_Exception $e ) {
			throw new Server_Exception( 'Failed to update network recommendations settings.', 0, $e );
		}

		return $config;
	}
}
