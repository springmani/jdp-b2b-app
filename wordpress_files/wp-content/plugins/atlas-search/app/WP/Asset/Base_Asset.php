<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset;

use WPE\AITK\WP\Asset\Contracts\Asset_Interface;

abstract class Base_Asset implements Asset_Interface {
	/**
	 * The handle of the script/style.
	 *
	 * @var string
	 */
	private string $handle;

	/**
	 * The source URL of the script/style.
	 *
	 * @var string
	 */
	private string $src;

	/**
	 * An array of other script/style handles this script/style depends on.
	 *
	 * @var array<string> $deps
	 */
	private array $deps;

	/**
	 * @param string $handle
	 * @param string $src
	 * @param array<string> $deps
	 */
	public function __construct( string $handle, string $src, array $deps = [] ) {
		$this->handle = $handle;
		$this->src    = $src;
		$this->deps   = $deps;
	}

	public function get_handle(): string {
		return $this->handle;
	}

	public function get_src(): string {
		return $this->src;
	}

	/**
	 * @inheritDoc
	 */
	public function get_deps(): array {
		return $this->deps;
	}
}
