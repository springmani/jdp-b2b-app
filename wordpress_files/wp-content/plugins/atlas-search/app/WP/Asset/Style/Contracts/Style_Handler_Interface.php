<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Asset\Style\Contracts;

interface Style_Handler_Interface {
	/**
	 * @throws \WPE\AITK\WP\Asset\Style\Exceptions\Style_Registration_Exception If registration fails.
	 */
	public function register( Style_Asset_Interface $asset ): void;

	/**
	 * @throws \WPE\AITK\WP\Asset\Style\Exceptions\Style_Registration_Exception If registration fails.
	 */
	public function enqueue( Style_Asset_Interface $asset ): void;
}
