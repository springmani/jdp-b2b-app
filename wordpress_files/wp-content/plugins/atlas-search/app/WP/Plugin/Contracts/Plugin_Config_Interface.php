<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Plugin\Contracts;

interface Plugin_Config_Interface {
	public function get_version(): string;

	public function get_path(): string;

	public function get_basename(): string;
}
