<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Script\Contracts;

interface Script_Handler_Interface {
	/**
	 * @throws \WPE\AITK\WP\Asset\Script\Exceptions\Script_Registration_Exception
	 */
	public function register( Script_Asset_Interface $asset ): void;

	/**
	 * @throws \WPE\AITK\WP\Asset\Script\Exceptions\Script_Registration_Exception
	 */
	public function enqueue( Script_Asset_Interface $asset ): void;
}
