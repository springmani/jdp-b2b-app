<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem\Contracts;

interface File_Pruner_Interface {
	/**
	 * @throws \WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception
	 */
	public function prune_if_needed( string $path ): void;
}
