<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Smart_Search\Recommendations;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\Smart_Search\Recommendations\Data\Recommendations_Config_Data as Data;
use WPE\AITK\Modules\Smart_Search\Recommendations\Tests\Recommendation_Block_Test;
use WPE\AITK\Modules\System\Data\Capability;
use WPE\AITK\WP\Capability\Capability_Service;
use WPE\AITK\WP\Rest\Route_Registrar;
use WPE\AITK\WP\Settings\Contracts\Settings_Manager_Interface;
use WPE\AITK\WP\Settings\Network_Settings_Manager;
use WPE\AITK\WP\Settings\Settings_Manager;
use WPE\AITK\WP\Site_Health\Test\Test_Registry;

final class Recommendations_Subscriber extends Hook_Subscriber {
	// todo: rename to 'wpe_aitk/smart_search/recommendations_config' and migrate existing data.
	public const OPTION = 'aitk/config/recommendations';

	private Settings_Manager_Interface $settings_manager;

	public function register(): void {
		$capability_service = $this->container->get( Capability_Service::class );

		if ( ! $capability_service->has_capability( Capability::RECOMMENDATIONS ) ) {
			return;
		}

		$this->settings_manager = $this->container->get( Settings_Manager::class );

		$has_extended_features = $capability_service->has_capability( Capability::EXTENDED_FEATURES );
		$this->register_setting( $has_extended_features );
		$this->register_network_setting();
		$this->register_analytics();
		$this->register_network_controller();

		$this->container->get( Test_Registry::class )->add( new Recommendation_Block_Test() );
	}

	private function register_setting( bool $has_extended_features ): void {
		$this->settings_manager->add( self::OPTION, Data::class );

		if ( ! $has_extended_features && false === get_option( self::OPTION ) ) {
			$this->settings_manager->update( self::OPTION, new Data( false ) );
		}
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_network_setting(): void {
		$network_manager = $this->container->get( Network_Settings_Manager::class );
		$network_manager->add( Recommendations_Network_Rest_Controller::NETWORK_OPTION, Data::class );
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_network_controller(): void {
		$this->container->get( Route_Registrar::class )->add_controller(
			$this->container->get( Recommendations_Network_Rest_Controller::class )
		);
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_analytics(): void {
		add_action(
			'wp_enqueue_scripts',
			[ $this->container->get( Recommendations_Analytics_Handler::class ), 'enqueue_analytics_script' ]
		);
	}
}
