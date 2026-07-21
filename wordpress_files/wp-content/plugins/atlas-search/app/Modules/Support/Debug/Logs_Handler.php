<?php

declare(strict_types=1);

namespace WPE\AITK\Modules\Support\Debug;

use WPE\AITK\Core\Filesystem\Contracts\Filesystem_Provider_Interface;
use WPE\AITK\Core\Zip\Contracts\Zip_Creator_Interface;
use WPE\AITK\Core\Zip\Exceptions\Zip_Exception;
use WPE\AITK\Modules\Support\Debug\Data\Debug_Logs_Download_Response;
use WPE\AITK\Modules\Support\Debug\Data\Debug_Logs_Status_Response;

final class Logs_Handler {
	private const SMART_SEARCH_LOGS_ZIP = 'smart-search-logs.zip';
	private const MAX_ZIP_SIZE_MB       = 10;

	/**
	 * @var array<string, string>
	 */
	private array $log_files;

	private Filesystem_Provider_Interface $filesystem;

	private Zip_Creator_Interface $zip_creator;

	/**
	 * @param array<string, string> $log_files Map of file paths to names in zip.
	 */
	public function __construct(
		array $log_files,
		Filesystem_Provider_Interface $filesystem,
		Zip_Creator_Interface $zip_creator
	) {
		$this->log_files   = $log_files;
		$this->filesystem  = $filesystem;
		$this->zip_creator = $zip_creator;
	}

	public function get_status(): Debug_Logs_Status_Response {
		return new Debug_Logs_Status_Response( $this->has_logs() );
	}

	public function has_logs(): bool {
		foreach ( array_keys( $this->log_files ) as $path ) {
			if ( $this->filesystem->is_file( $path ) ) {
				return true;
			}
		}

		return false;
	}

	public function create_download(): Debug_Logs_Download_Response {
		try {
			$zip_content = $this->zip_creator->create( $this->log_files );
		} catch ( Zip_Exception $exception ) {
			return $this->error_response( $exception->getMessage() );
		}

		$validation_error = $this->validate_size( $zip_content );

		if ( null !== $validation_error ) {
			return $this->error_response( $validation_error );
		}

		// TODO: create Rest API file download endpoint to avoid base64 encoding and allow for larger files.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Encoding binary zip data for JSON transmission.
		$encoded_content = base64_encode( $zip_content );

		return new Debug_Logs_Download_Response( true, self::SMART_SEARCH_LOGS_ZIP, $encoded_content, '' );
	}

	private function validate_size( string $zip_content ): ?string {
		$max_bytes = self::MAX_ZIP_SIZE_MB * MB_IN_BYTES;
		$zip_size  = strlen( $zip_content );

		if ( $zip_size <= $max_bytes ) {
			return null;
		}

		$size_mb = round( $zip_size / MB_IN_BYTES, 2 );

		return sprintf( 'Log archive too large (%s MB). Maximum size is %s MB.', $size_mb, self::MAX_ZIP_SIZE_MB );
	}

	private function error_response( string $error ): Debug_Logs_Download_Response {
		return new Debug_Logs_Download_Response( false, '', '', $error );
	}
}
