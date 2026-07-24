<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Plugin;

use WPE\AITK\Modules\System\Data\Capability;
use WPE\AITK\WP\Capability\Contracts\Capability_Service_Interface;
use WPE\AITK\WP\Plugin\Contracts\Plugin_Name_Resolver_Interface;

final class Plugin_Name_Resolver implements Plugin_Name_Resolver_Interface {
	public const ON_PLATFORM_NAME  = 'WP Engine AI Toolkit';
	public const OFF_PLATFORM_NAME = 'Smart Search AI';

	/**
	 * @var \WPE\AITK\WP\Capability\Contracts\Capability_Service_Interface
	 */
	private Capability_Service_Interface $capability_service;

	private ?string $plugin_name = null;

	public function __construct( Capability_Service_Interface $capability_service ) {
		$this->capability_service = $capability_service;
	}

	public function resolve(): string {
		if ( null !== $this->plugin_name ) {
			return $this->plugin_name;
		}

		if ( $this->capability_service->has_capability( Capability::EXTENDED_FEATURES ) ) {
			$this->plugin_name = self::ON_PLATFORM_NAME;
		} else {
			$this->plugin_name = self::OFF_PLATFORM_NAME;
		}

		return $this->plugin_name;
	}
}
