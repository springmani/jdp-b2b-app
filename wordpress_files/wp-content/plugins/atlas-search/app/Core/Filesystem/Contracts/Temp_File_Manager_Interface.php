<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem\Contracts;

interface Temp_File_Manager_Interface {
	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function create( string $prefix ): string;
}
