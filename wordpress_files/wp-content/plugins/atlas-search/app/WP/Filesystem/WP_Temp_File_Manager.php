<?php

declare(strict_types=1);

namespace WPE\AITK\WP\Filesystem;

use WPE\AITK\Core\Filesystem\Contracts\Temp_File_Manager_Interface;
use WPE\AITK\Core\Filesystem\Exceptions\Filesystem_Exception;

final class WP_Temp_File_Manager implements Temp_File_Manager_Interface {
	public function __construct() {
		if ( ! function_exists( 'wp_tempnam' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
	}

	public function create( string $prefix ): string {
		$path = wp_tempnam( $prefix );

		if ( '' === $path ) {
			throw Filesystem_Exception::temp_file_failed();
		}

		return $path;
	}
}
