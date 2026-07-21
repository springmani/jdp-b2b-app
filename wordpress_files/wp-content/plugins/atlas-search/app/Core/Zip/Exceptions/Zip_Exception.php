<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Zip\Exceptions;

use RuntimeException;
use Throwable;

final class Zip_Exception extends RuntimeException {
	public static function empty_file_list(): self {
		return new self( 'Cannot create zip from an empty file list' );
	}

	public static function archive_failed( ?Throwable $previous = null ): self {
		return new self( 'Failed to create zip archive', 0, $previous );
	}

	public static function no_valid_files(): self {
		return new self( 'No valid files could be added to the zip archive' );
	}
}
