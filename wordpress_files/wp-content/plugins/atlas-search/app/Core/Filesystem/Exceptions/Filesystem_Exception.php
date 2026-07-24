<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem\Exceptions;

use RuntimeException;
use Throwable;

final class Filesystem_Exception extends RuntimeException {
	public static function read_failed( string $path, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Failed to read file: %s', $path ),
			0,
			$previous
		);
	}

	public static function write_failed( string $path, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Failed to write file: %s', $path ),
			0,
			$previous
		);
	}

	public static function size_failed( string $path, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Failed to get file size: %s', $path ),
			0,
			$previous
		);
	}

	public static function lock_failed( string $path, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Failed to acquire lock on file: %s', $path ),
			0,
			$previous
		);
	}

	public static function open_failed( string $path, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Failed to open file: %s', $path ),
			0,
			$previous
		);
	}

	public static function delete_failed( string $path, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Failed to delete file: %s', $path ),
			0,
			$previous
		);
	}

	public static function temp_file_failed( ?Throwable $previous = null ): self {
		return new self( 'Failed to create temporary file', 0, $previous );
	}
}
