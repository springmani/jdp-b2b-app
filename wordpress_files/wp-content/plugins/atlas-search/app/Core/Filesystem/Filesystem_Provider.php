<?php
// phpcs:disable WordPress.WP.AlternativeFunctions,Generic.PHP.ForbiddenFunctions.Found


declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem;

use WPE\AITK\Core\Filesystem\Contracts\Filesystem_Provider_Interface;
use WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception;

final class Filesystem_Provider implements Filesystem_Provider_Interface {
	public function exists( string $path ): bool {
		return file_exists( $path );
	}

	public function get_contents( string $path ): string {
		$contents = file_get_contents( $path );

		if ( false === $contents ) {
			throw Filesystem_Exception::read_failed( $path );
		}

		return $contents;
	}

	public function put_contents( string $path, string $content ): void {
		if ( false === file_put_contents( $path, $content ) ) {
			throw Filesystem_Exception::write_failed( $path );
		}
	}

	public function size( string $path ): int {
		$size = filesize( $path );

		if ( false === $size ) {
			throw Filesystem_Exception::size_failed( $path );
		}

		return $size;
	}

	public function append( string $path, string $content ): void {
		$handle = fopen( $path, 'a' );

		if ( false === $handle ) {
			throw Filesystem_Exception::open_failed( $path );
		}

		try {
			if ( ! flock( $handle, LOCK_EX ) ) {
				throw Filesystem_Exception::lock_failed( $path );
			}

			if ( false === fwrite( $handle, $content ) ) {
				throw Filesystem_Exception::write_failed( $path );
			}
		} finally {
			flock( $handle, LOCK_UN );
			fclose( $handle );
		}
	}

	public function replace_contents( string $path, callable $transformer ): void {
		$handle = fopen( $path, 'c+' );

		if ( false === $handle ) {
			throw Filesystem_Exception::open_failed( $path );
		}

		try {
			if ( ! flock( $handle, LOCK_EX ) ) {
				throw Filesystem_Exception::lock_failed( $path );
			}

			$contents    = stream_get_contents( $handle );
			$transformed = $transformer( false === $contents ? '' : $contents );

			ftruncate( $handle, 0 );
			rewind( $handle );

			if ( false === fwrite( $handle, $transformed ) ) {
				throw Filesystem_Exception::write_failed( $path );
			}
		} finally {
			flock( $handle, LOCK_UN );
			fclose( $handle );
		}
	}

	public function delete( string $path ): void {
		if ( ! file_exists( $path ) ) {
			return;
		}

		if ( ! unlink( $path ) ) {
			throw Filesystem_Exception::delete_failed( $path );
		}
	}

	public function is_file( string $path ): bool {
		return is_file( $path );
	}
}
