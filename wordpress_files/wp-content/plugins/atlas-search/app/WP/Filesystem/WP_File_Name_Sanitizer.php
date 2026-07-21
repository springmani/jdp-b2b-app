<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Filesystem;

use WPE\AITK\Core\Filesystem\Contracts\File_Name_Sanitizer_Interface;

final class WP_File_Name_Sanitizer implements File_Name_Sanitizer_Interface {
	public function sanitize( string $name ): ?string {
		$sanitized = sanitize_file_name( $name );

		if ( '' === $sanitized || '.' === $sanitized ) {
			return null;
		}

		return $sanitized;
	}
}
