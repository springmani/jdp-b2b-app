<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Plugin;

use WPE\AITK\WP\Plugin\Contracts\Plugin_Config_Interface;

final class Config implements Plugin_Config_Interface {
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	private string $path;

	public function __construct( string $version, string $plugin_path ) {
		$this->version = $version;
		$this->path    = $plugin_path;
	}

	public function get_version(): string {
		return $this->version;
	}

	public function get_path(): string {
		return $this->path;
	}

	public function get_basename(): string {
		return plugin_basename( $this->path );
	}
}
