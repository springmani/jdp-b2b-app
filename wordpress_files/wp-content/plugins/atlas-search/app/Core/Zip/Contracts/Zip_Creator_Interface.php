<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Zip\Contracts;

interface Zip_Creator_Interface {
	/**
	 * @param array<string, string> $files Map of file paths to names in zip (path => name_in_zip).
	 *
	 * @throws \WPE\AITK\Core\Zip\Exceptions\Zip_Exception
	 */
	public function create( array $files ): string;
}
