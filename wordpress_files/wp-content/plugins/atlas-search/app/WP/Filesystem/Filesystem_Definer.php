<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Filesystem;

use WPE\AITK\Core\Definer\Contracts\Service_Definer_Interface;

final class Filesystem_Definer implements Service_Definer_Interface {
	/**
	 * @inheritDoc
	 */
	public function define(): array {
		return [
			WP_File_Name_Sanitizer::class => static fn (): WP_File_Name_Sanitizer => new WP_File_Name_Sanitizer(),
			WP_Temp_File_Manager::class   => static fn (): WP_Temp_File_Manager => new WP_Temp_File_Manager(),
		];
	}
}
