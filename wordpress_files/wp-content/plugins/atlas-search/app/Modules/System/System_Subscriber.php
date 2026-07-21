<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\System;

use WPE\AITK\Core\Subscriber\Hook_Subscriber;
use WPE\AITK\Modules\System\Debug\System_Section;
use WPE\AITK\Modules\System\Tests\REST_API_Test;
use WPE\AITK\WP\Multisite\WP_Multisite_Environment;
use WPE\AITK\WP\Rest\Route_Registrar;
use WPE\AITK\WP\Settings\Settings_Manager;
use WPE\AITK\WP\Settings\Settings_Registrar;
use WPE\AITK\WP\Site_Health\Debug_Info\Debug_Info_Registry;
use WPE\AITK\WP\Site_Health\Test\Async_Test_Controller;
use WPE\AITK\WP\Site_Health\Test\Test_Registry;

use const AtlasSearch\Hooks\SMART_SEARCH_HOOK_ID_PREFIX;

final class System_Subscriber extends Hook_Subscriber {
	public function register(): void {
		$this->register_site_health_tests();
		$this->register_site_health_debug_info();
		$this->register_rest_routes();
		$this->register_settings();
		$this->register_multisite_prefix();
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_site_health_tests(): void {
		$registry = $this->container->get( Test_Registry::class );

		$registry->add( new REST_API_Test() );

		$this->container->get( Route_Registrar::class )->add_controller(
			$this->container->get( Async_Test_Controller::class )
		);

		add_filter( 'site_status_tests', [ $registry, 'register' ] );
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_site_health_debug_info(): void {
		$debug_registry = $this->container->get( Debug_Info_Registry::class );
		$debug_registry->add( new System_Section(
			$this->container->get( WP_Multisite_Environment::class )
		) );

		add_filter(
			'debug_information',
			static fn ( array $info ): array => array_merge( $info, $debug_registry->get_info() )
		);
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_rest_routes(): void {
		add_action( 'rest_api_init', function (): void {
			$this->container->get( Route_Registrar::class )->register_routes();
		} );
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_settings(): void {
		add_action( 'init', function (): void {
			$registrar = $this->container->get( Settings_Registrar::class );

			foreach ( $this->container->get( Settings_Manager::class )->get_all() as $option_name => $data_class ) {
				$registrar->register( $option_name, $data_class );
			}
		} );
	}

	/**
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	private function register_multisite_prefix(): void {
		$environment = $this->container->get( WP_Multisite_Environment::class );

		if ( ! $environment->is_multisite() ) {
			return;
		}

		add_filter(
			SMART_SEARCH_HOOK_ID_PREFIX,
			static fn (): int => $environment->get_current_blog_id(),
			10,
			0
		);
	}
}
