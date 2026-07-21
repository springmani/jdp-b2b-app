<?php

declare(strict_types=1);

namespace WPE\AITK\Core\Zip;

use WPE\AITK\Core\Filesystem\Contracts\Filesystem_Provider_Interface;
use WPE\AITK\Core\Filesystem\Contracts\Temp_File_Manager_Interface;
use WPE\AITK\Core\Zip\Contracts\Zip_Creator_Interface;
use WPE\AITK\Core\Zip\Contracts\Zip_Entry_Validator_Interface;
use WPE\AITK\Core\Zip\Exceptions\Zip_Exception;
use ZipArchive;

final class Zip_Creator implements Zip_Creator_Interface {
	private Filesystem_Provider_Interface $filesystem;

	private Temp_File_Manager_Interface $temp_files;

	private Zip_Entry_Validator_Interface $validator;

	public function __construct(
		Filesystem_Provider_Interface $filesystem,
		Temp_File_Manager_Interface $temp_files,
		Zip_Entry_Validator_Interface $validator
	) {
		$this->filesystem = $filesystem;
		$this->temp_files = $temp_files;
		$this->validator  = $validator;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \WPE\AITK\Core\Zip\Exceptions\Zip_Exception
	 */
	public function create( array $files ): string {
		if ( empty( $files ) ) {
			throw Zip_Exception::empty_file_list();
		}

		$temp_zip = $this->temp_files->create( 'aitk-zip-' );

		try {
			$this->fill_zip( $temp_zip, $files );

			return $this->filesystem->get_contents( $temp_zip );
		} finally {
			$this->filesystem->delete( $temp_zip );
		}
	}

	/**
	 * @param array<string, string> $files Map of file paths to names in zip.
	 *
	 * @throws \WPE\AITK\Core\Zip\Exceptions\Zip_Exception
	 */
	private function fill_zip( string $zip_path, array $files ): void {
		$zip = new ZipArchive();

		if ( true !== $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
			throw Zip_Exception::archive_failed();
		}

		$files_added = $this->add_files_to_zip( $zip, $files );
		$zip->close();

		if ( 0 === $files_added ) {
			throw Zip_Exception::no_valid_files();
		}
	}

	/**
	 * @param array<string, string> $files Map of file paths to names in zip.
	 */
	private function add_files_to_zip( ZipArchive $zip, array $files ): int {
		$count = 0;

		foreach ( $files as $file_path => $name_in_zip ) {
			$validated = $this->validator->validate( $file_path, $name_in_zip );

			if ( null === $validated ) {
				continue;
			}

			if ( $zip->addFile( $validated->get_real_path(), $validated->get_safe_name() ) ) {
				++$count;
			}
		}

		return $count;
	}
}
