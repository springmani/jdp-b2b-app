<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem\Contracts;

interface File_Name_Sanitizer_Interface {
	public function sanitize( string $name ): ?string;
}
