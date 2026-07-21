<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Filesystem;

use WPE\AITK\Core\Filesystem\Contracts\File_Handler_Interface;
use WPE\AITK\Core\Filesystem\Contracts\Filesystem_Provider_Interface;

final class File_Handler implements File_Handler_Interface {
	private const MAX_FILE_SIZE    = 1048576;
	private const PRUNE_KEEP_LINES = 2000;

	private Filesystem_Provider_Interface $filesystem;
	private int $max_file_size;
	private int $keep_lines;

	public function __construct(
		Filesystem_Provider_Interface $filesystem,
		int $max_file_size = self::MAX_FILE_SIZE,
		int $keep_lines = self::PRUNE_KEEP_LINES
	) {
		$this->filesystem    = $filesystem;
		$this->max_file_size = $max_file_size;
		$this->keep_lines    = $keep_lines;
	}

	public function write( string $path, string $content ): void {
		$this->filesystem->append( $path, $content );
	}

	public function prune_if_needed( string $path ): void {
		if ( ! $this->filesystem->exists( $path ) ) {
			return;
		}

		if ( $this->filesystem->size( $path ) <= $this->max_file_size ) {
			return;
		}

		$keep_lines = $this->keep_lines;

		$this->filesystem->replace_contents(
			$path,
			static function ( string $contents ) use ( $keep_lines ): string {
				$lines = array_filter( explode( "\n", $contents ), static fn ( string $line ): bool => '' !== $line );
				$kept  = array_slice( $lines, -$keep_lines );

				return implode( "\n", $kept ) . "\n";
			}
		);
	}
}
