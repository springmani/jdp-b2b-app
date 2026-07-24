<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem\Contracts;

interface File_Writer_Interface {
	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function write( string $path, string $content ): void;
}
