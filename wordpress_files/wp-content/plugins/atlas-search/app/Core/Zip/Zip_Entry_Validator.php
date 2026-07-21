<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Zip;

use WPE\AITK\Core\Filesystem\Contracts\File_Name_Sanitizer_Interface;
use WPE\AITK\Core\Filesystem\Contracts\Filesystem_Provider_Interface;
use WPE\AITK\Core\Zip\Contracts\Zip_Entry_Validator_Interface;
use WPE\AITK\Core\Zip\Data\Validated_Zip_Entry;

final class Zip_Entry_Validator implements Zip_Entry_Validator_Interface {
	private Filesystem_Provider_Interface $filesystem;

	private File_Name_Sanitizer_Interface $sanitizer;

	public function __construct( Filesystem_Provider_Interface $filesystem, File_Name_Sanitizer_Interface $sanitizer ) {
		$this->filesystem = $filesystem;
		$this->sanitizer  = $sanitizer;
	}

	public function validate( string $file_path, string $name_in_zip ): ?Validated_Zip_Entry {
		$real_path = $this->resolve_safe_path( $file_path );

		if ( null === $real_path ) {
			return null;
		}

		$safe_name = $this->sanitizer->sanitize( $name_in_zip );

		if ( null === $safe_name ) {
			return null;
		}

		return new Validated_Zip_Entry( $real_path, $safe_name );
	}

	private function resolve_safe_path( string $file_path ): ?string {
		if ( '' === $file_path || false !== strpos( $file_path, '..' ) ) {
			return null;
		}

		if ( ! $this->filesystem->is_file( $file_path ) ) {
			return null;
		}

		return $file_path;
	}
}
