<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Debug\Flushers;

use WPE\AITK\Core\Filesystem\Contracts\File_Handler_Interface;
use WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception;
use WPE\AITK\WP\Debug\Contracts\Log_Flusher_Interface;

final class File_Flusher implements Log_Flusher_Interface {
	private string $log_path;

	private File_Handler_Interface $file_handler;

	public function __construct( string $log_path, File_Handler_Interface $file_handler ) {
		$this->log_path     = $log_path;
		$this->file_handler = $file_handler;
	}

	public function get_log_path(): string {
		return $this->log_path;
	}

	/**
	 * @inheritDoc
	 */
	public function flush( array $entries ): void {
		if ( [] === $entries ) {
			return;
		}

		try {
			$this->file_handler->write( $this->log_path, implode( '', $entries ) );
			$this->file_handler->prune_if_needed( $this->log_path );
		} catch ( Filesystem_Exception $exception ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, Generic.PHP.ForbiddenFunctions.Found
			error_log( '[AITK] [file_flusher] ERROR: ' . $exception->getMessage() );
		}
	}
}
