<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset;

use WPE\AITK\WP\Plugin\Contracts\Plugin_Config_Interface;

abstract class Base_Asset_Handler {
	public const REGISTERED = 'registered';

	/**
	 * @var \WPE\AITK\WP\Plugin\Contracts\Plugin_Config_Interface
	 */
	protected Plugin_Config_Interface $config;

	public function __construct( Plugin_Config_Interface $config ) {
		$this->config = $config;
	}

	protected function get_plugin_src( string $relative_path ): string {
		return plugin_dir_url( $this->config->get_path() ) . ltrim( $relative_path, '/' );
	}
}
